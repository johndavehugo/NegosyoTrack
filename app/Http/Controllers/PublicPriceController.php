<?php

namespace App\Http\Controllers;

use App\Models\PriceMonitoring\Agency;
use App\Models\PriceMonitoring\Category;
use App\Models\PriceMonitoring\Commodity;
use App\Models\PriceMonitoring\CommodityVariant;
use App\Models\PriceMonitoring\PriceLog;
use App\Services\PriceMonitoringService;
use Illuminate\Http\Request;

class PublicPriceController extends Controller
{
    // ── Main listing page ─────────────────────────────────────────
    public function index(Request $request)
    {
        $categories = Category::withCount('commodities')
            ->orderBy('name')
            ->get();

        $agencies = Agency::orderBy('name')->get();

        $query = CommodityVariant::with([
            'commodity.brand',
            'commodity.category.agency',
        ])
        ->whereHas('commodity', fn ($q) => $q->where('is_active', true));

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas(
                'commodity',
                fn ($q) => $q->where('category_id', $request->category)
            );
        }

        // Filter by agency
        if ($request->filled('agency')) {
            $query->whereHas(
                'commodity.category',
                fn ($q) => $q->where('agency_id', $request->agency)
            );
        }

        // Search by product name or brand
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('commodity', function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$search}%"));
            });
        }

        // Sorting
        match ($request->input('sort', 'name_asc')) {
            'name_desc' => $query->orderByRaw('(SELECT product_name FROM commodities WHERE commodities.id = commodity_variants.commodity_id) DESC'),
            'srp_asc'   => $query->orderBy('srp', 'asc'),
            'srp_desc'  => $query->orderBy('srp', 'desc'),
            'cat_asc'   => $query->orderByRaw('(SELECT category_id FROM commodities WHERE commodities.id = commodity_variants.commodity_id) ASC'),
            default     => $query->orderByRaw('(SELECT product_name FROM commodities WHERE commodities.id = commodity_variants.commodity_id) ASC'),
        };

        $variants = $query->paginate(20)->withQueryString();

        // Resolve establishment names for listed variants (batch by unique entity_nos)
        $entityNos = $variants->pluck('commodity.juridical_id')
            ->filter()
            ->unique()
            ->values();

        $establishmentNames = [];
        if ($entityNos->isNotEmpty()) {
            $svc = app(PriceMonitoringService::class);
            try {
                $establishmentNames = $svc->resolveEstablishmentNames($entityNos->all());
            } catch (\Illuminate\Http\Client\ConnectionException) {
                // API unreachable — show entity_nos as fallback, page still loads
                $establishmentNames = [];
            }
        }

        // Summary stats
        $totalMonitored = CommodityVariant::whereHas(
            'commodity', fn ($q) => $q->where('is_active', true)
        )->count();

        $withPrevailing = CommodityVariant::whereHas(
            'commodity', fn ($q) => $q->where('is_active', true)
        )->whereNotNull('prevailing_price')->count();

        $totals = [
            'commodities' => $totalMonitored,
            'agencies'    => Agency::count(),
            'categories'  => Category::count(),
        ];

        return view('sccpresyo.layout')->with('slot',
            view('sccpresyo.price-list', compact(
                'variants',
                'categories',
                'agencies',
                'totalMonitored',
                'withPrevailing',
                'totals',
                'establishmentNames',
            ))
        );
    }

    // ── Commodity detail — JSON for the modal ─────────────────────
    public function detail(int $variant)
    {
        $variant = CommodityVariant::with([
            'commodity.brand',
            'commodity.category.agency',
        ])->findOrFail($variant);

        $commodity = $variant->commodity;

        // Find sibling commodities (same product + brand) across all establishments
        $siblingsQuery = Commodity::with(['variants'])
            ->where('product_name', $commodity->product_name)
            ->where('is_active', true);

        if ($commodity->brand_id) {
            $siblingsQuery->where('brand_id', $commodity->brand_id);
        }

        $siblings = $siblingsQuery->get();

        // Resolve establishment names for all siblings
        $svc = app(PriceMonitoringService::class);

        // Build establishments list
        $establishments = $siblings->map(function (Commodity $sib) use ($variant, $svc) {
            $sibVariant = $sib->variants
                ->firstWhere('unit_of_measure', $variant->unit_of_measure)
                ?? $sib->variants->first();

            if (! $sibVariant) {
                return null;
            }

            $srp        = $sibVariant->srp               ? (float) $sibVariant->srp               : null;
            $prevailing = $sibVariant->prevailing_price   ? (float) $sibVariant->prevailing_price  : null;
            $diff       = ($srp !== null && $prevailing !== null) ? round($prevailing - $srp, 2) : null;
            $pct        = ($diff !== null && $srp > 0) ? round(($diff / $srp) * 100, 1) : null;

            $estName = $sib->juridical_id
                ? ($svc->resolveEstablishmentName($sib->juridical_id) ?? $sib->juridical_id)
                : 'Unknown Establishment';

            return [
                'variant_id'       => $sibVariant->id,
                'is_current'       => $sibVariant->id === $variant->id,
                'establishment'    => $estName,
                'address'          => null,
                'contact_no'       => null,
                'unit'             => $sibVariant->unit_of_measure,
                'srp'              => $srp,
                'prevailing_price' => $prevailing,
                'diff'             => $diff,
                'pct'              => $pct,
                'status'           => match (true) {
                    $diff === null => 'no_data',
                    $diff > 0      => 'above',
                    $diff < 0      => 'below',
                    default        => 'equal',
                },
            ];
        })
        ->filter()
        ->sortBy('prevailing_price')
        ->values();

        // Price history
        $siblingIds = $siblings->pluck('id');

        $logs = PriceLog::whereIn('commodity_id', $siblingIds)
            ->where('status', 'ACTIVE')
            ->orderBy('monitored_at', 'asc')
            ->limit(60)
            ->get(['prevailing_price', 'monitored_at']);

        $chartLabels = $logs->map(fn ($l) => $l->monitored_at->format('M j'))->values();
        $chartData   = $logs->map(fn ($l) => (float) $l->prevailing_price)->values();

        if ($chartData->isEmpty() && $establishments->isNotEmpty()) {
            $chartLabels = $establishments->whereNotNull('prevailing_price')->map(fn ($e) => $e['establishment'])->values();
            $chartData   = $establishments->whereNotNull('prevailing_price')->map(fn ($e) => $e['prevailing_price'])->values();
        }

        $pricesWithData = $establishments->where('status', '!=', 'no_data');
        $lowestEntry    = $pricesWithData->sortBy('prevailing_price')->first();
        $highestEntry   = $pricesWithData->sortByDesc('prevailing_price')->first();
        $avgPrice       = $pricesWithData->avg('prevailing_price');

        return response()->json([
            'variant' => [
                'id'           => $variant->id,
                'product_name' => $commodity->product_name,
                'brand'        => $commodity->brand?->name,
                'category'     => $commodity->category?->name,
                'agency'       => $commodity->category?->agency?->name,
                'unit'         => $variant->unit_of_measure,
                'srp'          => $variant->srp             ? (float) $variant->srp             : null,
                'prevailing'   => $variant->prevailing_price ? (float) $variant->prevailing_price : null,
            ],
            'establishments' => $establishments,
            'summary' => [
                'total_establishments' => $establishments->count(),
                'lowest_price'         => $lowestEntry  ? (float) $lowestEntry['prevailing_price']  : null,
                'lowest_name'          => $lowestEntry  ? $lowestEntry['establishment']              : null,
                'highest_price'        => $highestEntry ? (float) $highestEntry['prevailing_price']  : null,
                'highest_name'         => $highestEntry ? $highestEntry['establishment']             : null,
                'avg_price'            => $avgPrice     ? round((float) $avgPrice, 2)               : null,
            ],
            'chart' => [
                'labels'      => $chartLabels,
                'data'        => $chartData,
                'has_history' => $logs->isNotEmpty(),
            ],
        ]);
    }
}
