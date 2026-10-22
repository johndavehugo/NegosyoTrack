<?php

namespace App\Http\Controllers;

use App\Models\PriceMonitoring\Category;
use App\Models\PriceMonitoring\Commodity;
use App\Models\PriceMonitoring\CommodityVariant;
use App\Models\PriceMonitoring\PriceLog;
use Illuminate\Http\Request;

class PublicPriceController extends Controller
{
    // ── Main listing page ─────────────────────────────────────────
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $query = CommodityVariant::with([
            'commodity.brand',
            'commodity.category.agency',
            'commodity.juridical',
        ])
        ->whereHas('commodity', fn ($q) => $q->where('is_active', true));

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas(
                'commodity',
                fn ($q) => $q->where('category_id', $request->category)
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
        match (request('sort', 'recent')) {
            'price_asc'  => $query->orderBy('prevailing_price', 'asc')->orderBy('srp', 'asc'),
            'price_desc' => $query->orderBy('prevailing_price', 'desc')->orderBy('srp', 'desc'),
            'name'       => $query->orderByRaw('(SELECT product_name FROM commodities WHERE commodities.id = commodity_variants.commodity_id)'),
            default      => $query->orderBy('updated_at', 'desc'),
        };

        $variants = $query->paginate(30)->withQueryString();

        // Summary stats
        $totalMonitored = CommodityVariant::whereHas(
            'commodity', fn ($q) => $q->where('is_active', true)
        )->count();

        $withPrevailing = CommodityVariant::whereHas(
            'commodity', fn ($q) => $q->where('is_active', true)
        )->whereNotNull('prevailing_price')->count();

        return view('public.price-watch', compact(
            'variants',
            'categories',
            'totalMonitored',
            'withPrevailing',
        ));
    }

    // ── Commodity detail — JSON for the modal ─────────────────────
    public function detail(int $variantId)
    {
        // Load the clicked variant with its commodity
        $variant = CommodityVariant::with([
            'commodity.brand',
            'commodity.category.agency',
            'commodity.juridical.address',
        ])->findOrFail($variantId);

        $commodity = $variant->commodity;

        // ── Establishments: find all active commodities that share the
        //    same product_name (and same brand if present), then gather
        //    their variants with the same unit_of_measure so we compare apples-to-apples.
        $siblingsQuery = Commodity::with(['juridical.address', 'variants'])
            ->where('product_name', $commodity->product_name)
            ->where('is_active', true);

        if ($commodity->brand_id) {
            $siblingsQuery->where('brand_id', $commodity->brand_id);
        }

        $siblings = $siblingsQuery->get();

        // Build establishments list — only show variants matching the same unit
        $establishments = $siblings->map(function (Commodity $sib) use ($variant) {
            // Pick the variant with the same unit_of_measure
            $sibVariant = $sib->variants
                ->firstWhere('unit_of_measure', $variant->unit_of_measure)
                ?? $sib->variants->first();

            if (! $sibVariant) {
                return null;
            }

            $srp        = $sibVariant->srp        ? (float) $sibVariant->srp        : null;
            $prevailing = $sibVariant->prevailing_price ? (float) $sibVariant->prevailing_price : null;
            $diff       = ($srp !== null && $prevailing !== null) ? round($prevailing - $srp, 2) : null;
            $pct        = ($diff !== null && $srp > 0) ? round(($diff / $srp) * 100, 1) : null;

            $address = null;
            if ($sib->juridical?->address) {
                $address = $sib->juridical->address->full_address;
            }

            return [
                'variant_id'        => $sibVariant->id,
                'is_current'        => $sibVariant->id === $variant->id,
                'establishment'     => $sib->juridical?->name ?? 'Unknown Establishment',
                'address'           => $address,
                'contact_no'        => $sib->juridical?->contact_no,
                'unit'              => $sibVariant->unit_of_measure,
                'srp'               => $srp,
                'prevailing_price'  => $prevailing,
                'diff'              => $diff,
                'pct'               => $pct,
                'status'            => match (true) {
                    $diff === null      => 'no_data',
                    $diff > 0           => 'above',
                    $diff < 0           => 'below',
                    default             => 'equal',
                },
            ];
        })
        ->filter()
        ->sortBy('prevailing_price')   // cheapest first
        ->values();

        // ── Price history: last 60 logs for this specific commodity variant's commodity
        //    We log against commodity_id (not variant), so pull logs for all sibling
        //    commodity IDs and average per date — gives a meaningful trend line.
        $siblingIds = $siblings->pluck('id');

        $logs = PriceLog::whereIn('commodity_id', $siblingIds)
            ->where('status', 'ACTIVE')
            ->orderBy('monitored_at', 'asc')
            ->limit(60)
            ->get(['prevailing_price', 'monitored_at']);

        $chartLabels = $logs->map(fn ($l) => $l->monitored_at->format('M j'))->values();
        $chartData   = $logs->map(fn ($l) => (float) $l->prevailing_price)->values();

        // If no logs yet, fall back to a single "current" data point per establishment
        if ($chartData->isEmpty() && $establishments->isNotEmpty()) {
            $chartLabels = $establishments
                ->whereNotNull('prevailing_price')
                ->map(fn ($e) => $e['establishment'])
                ->values();
            $chartData = $establishments
                ->whereNotNull('prevailing_price')
                ->map(fn ($e) => $e['prevailing_price'])
                ->values();
        }

        // ── Summary info
        $pricesWithData   = $establishments->where('status', '!=', 'no_data');
        $lowestEntry      = $pricesWithData->sortBy('prevailing_price')->first();
        $highestEntry     = $pricesWithData->sortByDesc('prevailing_price')->first();
        $avgPrice         = $pricesWithData->avg('prevailing_price');

        return response()->json([
            'variant' => [
                'id'            => $variant->id,
                'product_name'  => $commodity->product_name,
                'brand'         => $commodity->brand?->name,
                'category'      => $commodity->category?->name,
                'agency'        => $commodity->category?->agency?->name,
                'unit'          => $variant->unit_of_measure,
                'srp'           => $variant->srp        ? (float) $variant->srp : null,
                'prevailing'    => $variant->prevailing_price ? (float) $variant->prevailing_price : null,
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
                'labels' => $chartLabels,
                'data'   => $chartData,
                'has_history' => $logs->isNotEmpty(),
            ],
        ]);
    }
}
