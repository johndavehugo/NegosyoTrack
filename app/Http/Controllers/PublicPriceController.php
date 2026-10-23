<?php

namespace App\Http\Controllers;

use App\Models\PriceMonitoring\Agency;
use App\Models\PriceMonitoring\Category;
use App\Models\PriceMonitoring\Commodity;
use App\Models\PriceMonitoring\CommodityVariant;
use App\Models\PriceMonitoring\PriceLog;
use App\Services\PriceMonitoringService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicPriceController extends Controller
{
    // ── Main listing page ─────────────────────────────────────────
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $agencies = Agency::orderBy('name')->get();

        // A commodity row represents one product at one establishment, so the
        // listing groups the same product + brand + size across establishments
        // and exposes the observed price range for comparison.
        $groupBase = DB::table('commodity_variants as v')
            ->join('commodities as c', 'c.id', '=', 'v.commodity_id')
            ->leftJoin('commodity_brands as b', 'b.id', '=', 'c.brand_id')
            ->leftJoin('commodity_categories as cat', 'cat.id', '=', 'c.category_id')
            ->leftJoin('agencies as a', 'a.id', '=', 'cat.agency_id')
            ->where('c.is_active', true)
            ->select(['c.product_name', 'c.brand_id', 'c.category_id', 'v.unit_of_measure'])
            ->selectRaw('MIN(v.id) as variant_id')
            ->selectRaw('MIN(v.srp) as srp')
            ->selectRaw('MIN(v.prevailing_price) as min_price')
            ->selectRaw('MAX(v.prevailing_price) as max_price')
            ->selectRaw('COUNT(DISTINCT COALESCE(c.juridical_id, CONCAT("commodity-", c.id))) as establishment_count')
            ->selectRaw('MAX(b.name) as brand_name')
            ->selectRaw('MAX(cat.name) as category_name')
            ->selectRaw('MAX(a.name) as agency_name')
            ->groupBy('c.product_name', 'c.brand_id', 'c.category_id', 'v.unit_of_measure');

        $totalMonitored = DB::query()->fromSub(clone $groupBase, 'g')->count();

        // Category counters use product groups, not establishment rows.
        $categoryCounts = DB::query()
            ->fromSub(clone $groupBase, 'g')
            ->selectRaw('g.category_id, COUNT(*) as group_count')
            ->groupBy('g.category_id')
            ->pluck('group_count', 'category_id');

        $categories->each(
            fn (Category $cat) => $cat->commodities_count = (int) ($categoryCounts[$cat->id] ?? 0)
        );

        $groupQuery = clone $groupBase;

        // Filter by category
        if ($request->filled('category')) {
            $groupQuery->where('c.category_id', $request->category);
        }

        // Filter by agency
        if ($request->filled('agency')) {
            $groupQuery->where('cat.agency_id', $request->agency);
        }

        // Search by product name or brand
        if ($request->filled('search')) {
            $search = $request->search;
            $groupQuery->where(function ($q) use ($search) {
                $q->where('c.product_name', 'like', "%{$search}%")
                    ->orWhere('b.name', 'like', "%{$search}%");
            });
        }

        // Sorting (aggregate aliases are safe to order by in MySQL)
        match ($request->input('sort', 'name_asc')) {
            'name_desc' => $groupQuery->orderBy('c.product_name', 'desc'),
            'srp_asc' => $groupQuery->orderBy('srp', 'asc'),
            'srp_desc' => $groupQuery->orderBy('srp', 'desc'),
            'cat_asc' => $groupQuery->orderBy('cat.name', 'asc'),
            default => $groupQuery->orderBy('c.product_name', 'asc'),
        };

        $groups = $groupQuery
            ->orderBy('v.unit_of_measure')
            ->paginate(20)
            ->withQueryString();

        // ── Establishments for the visible groups ─────────────────────
        $pageGroups = $groups->getCollection();

        $pageVariants = collect();

        if ($pageGroups->isNotEmpty()) {
            $productNames = $pageGroups->pluck('product_name')->unique()->values();
            $units = $pageGroups->pluck('unit_of_measure')->unique()->values();

            $pageVariants = CommodityVariant::with(['commodity.brand'])
                ->whereIn('unit_of_measure', $units)
                ->whereHas('commodity', fn ($q) => $q
                    ->where('is_active', true)
                    ->whereIn('product_name', $productNames)
                )
                ->get();
        }

        // Resolve establishment names for listed variants (batch by unique entity_nos)
        $entityNos = $pageVariants->pluck('commodity.juridical_id')
            ->filter()
            ->unique()
            ->values();

        $establishmentNames = [];
        if ($entityNos->isNotEmpty()) {
            try {
                $establishmentNames = app(PriceMonitoringService::class)
                    ->resolveEstablishmentNames($entityNos->all());
            } catch (ConnectionException) {
                // API unreachable — show entity_nos as fallback, page still loads
                $establishmentNames = [];
            }
        }

        $establishmentsByKey = $pageVariants
            ->groupBy(fn (CommodityVariant $variant) => $this->groupKey(
                $variant->commodity->product_name,
                $variant->commodity->brand_id,
                $variant->unit_of_measure,
            ))
            ->map(fn ($variants) => $variants
                ->map(function (CommodityVariant $variant) use ($establishmentNames) {
                    $entityNo = $variant->commodity->juridical_id;

                    return [
                        'variant_id' => $variant->id,
                        'establishment' => $entityNo
                            ? ($establishmentNames[$entityNo] ?? $entityNo)
                            : 'Unknown Establishment',
                        'unit' => $variant->unit_of_measure,
                        'srp' => $variant->srp !== null ? (float) $variant->srp : null,
                        'prevailing_price' => $variant->prevailing_price !== null ? (float) $variant->prevailing_price : null,
                    ];
                })
                ->sortBy(fn (array $e) => $e['prevailing_price'] ?? PHP_INT_MAX)
                ->values()
            );

        foreach ($pageGroups as $group) {
            $group->group_key = $this->groupKey($group->product_name, $group->brand_id, $group->unit_of_measure);

            $group->srp = $group->srp !== null ? (float) $group->srp : null;
            $group->min_price = $group->min_price !== null ? (float) $group->min_price : null;
            $group->max_price = $group->max_price !== null ? (float) $group->max_price : null;
            $group->establishment_count = (int) $group->establishment_count;

            $group->establishments = $establishmentsByKey->get($group->group_key, collect());
            $group->establishment_names = $group->establishments->pluck('establishment')->all();

            $priced = $group->establishments->filter(fn (array $e) => $e['prevailing_price'] !== null);

            $group->lowest_name = $priced->isNotEmpty() ? $priced->first()['establishment'] : null;
            $group->highest_name = $priced->isNotEmpty() ? $priced->last()['establishment'] : null;
        }

        // Summary stats
        $totals = [
            'commodities' => $totalMonitored,
            'agencies' => Agency::count(),
            'categories' => Category::count(),
        ];

        return view('sccpresyo.layout')->with('slot',
            view('sccpresyo.price-list', compact(
                'groups',
                'categories',
                'agencies',
                'totals',
            ))
        );
    }

    /**
     * Stable key for a product + brand + size group.
     * Used by the listing cards, the watchlist and the detail modal.
     */
    private function groupKey(?string $productName, $brandId, ?string $unit): string
    {
        return sha1(($productName ?? '').'|'.($brandId ?? '0').'|'.($unit ?? ''));
    }

    // ── Commodity detail — JSON for the modal ─────────────────────
    public function detail(int $variant)
    {
        $variant = CommodityVariant::with([
            'commodity.brand',
            'commodity.category.agency',
        ])->findOrFail($variant);

        $commodity = $variant->commodity;

        // Find sibling commodities (same product + brand + category) across all establishments
        $siblingsQuery = Commodity::with(['variants'])
            ->where('product_name', $commodity->product_name)
            ->where('category_id', $commodity->category_id)
            ->where('is_active', true);

        if ($commodity->brand_id) {
            $siblingsQuery->where('brand_id', $commodity->brand_id);
        } else {
            $siblingsQuery->whereNull('brand_id');
        }

        $siblings = $siblingsQuery->get();

        // Resolve establishment names for all siblings
        $svc = app(PriceMonitoringService::class);

        // Build establishments list — only exact size matches so we never
        // compare a price per kilo against a price per piece.
        $establishments = $siblings->map(function (Commodity $sib) use ($variant, $svc) {
            $sibVariant = $sib->variants
                ->firstWhere('unit_of_measure', $variant->unit_of_measure);

            if (! $sibVariant) {
                return null;
            }

            $srp = $sibVariant->srp ? (float) $sibVariant->srp : null;
            $prevailing = $sibVariant->prevailing_price ? (float) $sibVariant->prevailing_price : null;
            $diff = ($srp !== null && $prevailing !== null) ? round($prevailing - $srp, 2) : null;
            $pct = ($diff !== null && $srp > 0) ? round(($diff / $srp) * 100, 1) : null;

            $estName = $sib->juridical_id
                ? ($svc->resolveEstablishmentName($sib->juridical_id) ?? $sib->juridical_id)
                : 'Unknown Establishment';

            return [
                'variant_id' => $sibVariant->id,
                'is_current' => $sibVariant->id === $variant->id,
                'establishment' => $estName,
                'address' => null,
                'contact_no' => null,
                'unit' => $sibVariant->unit_of_measure,
                'srp' => $srp,
                'prevailing_price' => $prevailing,
                'diff' => $diff,
                'pct' => $pct,
                'status' => match (true) {
                    $diff === null => 'no_data',
                    $diff > 0 => 'above',
                    $diff < 0 => 'below',
                    default => 'equal',
                },
            ];
        })
            ->filter()
        // Lowest first, but entries without a prevailing price go last.
            ->sortBy(fn (array $e) => $e['prevailing_price'] ?? PHP_INT_MAX)
            ->values();

        // Price history — take the most recent 60 entries, then show oldest → newest.
        $siblingIds = $siblings->pluck('id');

        $logs = PriceLog::whereIn('commodity_id', $siblingIds)
            ->where('status', 'ACTIVE')
            ->orderByDesc('monitored_at')
            ->orderByDesc('id')
            ->limit(60)
            ->get(['prevailing_price', 'monitored_at'])
            ->reverse()
            ->values();

        $chartLabels = $logs->map(fn ($l) => $l->monitored_at->format('M j'))->values();
        $chartData = $logs->map(fn ($l) => (float) $l->prevailing_price)->values();

        if ($chartData->isEmpty() && $establishments->isNotEmpty()) {
            $chartLabels = $establishments->whereNotNull('prevailing_price')->map(fn ($e) => $e['establishment'])->values();
            $chartData = $establishments->whereNotNull('prevailing_price')->map(fn ($e) => $e['prevailing_price'])->values();
        }

        $pricesWithData = $establishments->where('status', '!=', 'no_data');
        $lowestEntry = $pricesWithData->sortBy('prevailing_price')->first();
        $highestEntry = $pricesWithData->sortByDesc('prevailing_price')->first();
        $avgPrice = $pricesWithData->avg('prevailing_price');

        return response()->json([
            'variant' => [
                'id' => $variant->id,
                'product_name' => $commodity->product_name,
                'brand' => $commodity->brand?->name,
                'category' => $commodity->category?->name,
                'agency' => $commodity->category?->agency?->name,
                'unit' => $variant->unit_of_measure,
                'srp' => $variant->srp ? (float) $variant->srp : null,
                'prevailing' => $variant->prevailing_price ? (float) $variant->prevailing_price : null,
            ],
            'establishments' => $establishments,
            'summary' => [
                'total_establishments' => $establishments->count(),
                'lowest_price' => $lowestEntry ? (float) $lowestEntry['prevailing_price'] : null,
                'lowest_name' => $lowestEntry ? $lowestEntry['establishment'] : null,
                'highest_price' => $highestEntry ? (float) $highestEntry['prevailing_price'] : null,
                'highest_name' => $highestEntry ? $highestEntry['establishment'] : null,
                'avg_price' => $avgPrice ? round((float) $avgPrice, 2) : null,
            ],
            'chart' => [
                'labels' => $chartLabels,
                'data' => $chartData,
                'has_history' => $logs->isNotEmpty(),
            ],
        ]);
    }
}
