<?php

namespace App\Http\Controllers;

use App\Models\PriceMonitoring\Agency;
use App\Models\PriceMonitoring\Category;
use App\Models\PriceMonitoring\CommodityVariant;
use Illuminate\Http\Request;

class PublicPriceController extends Controller
{
    public function index(Request $request)
    {
        $agencies   = Agency::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        $query = CommodityVariant::with([
            'commodity.brand',
            'commodity.category.agency',
            'commodity.juridical',
        ])
        ->whereHas('commodity', fn ($q) => $q->where('is_active', true));

        // Filter by agency
        if ($request->filled('agency')) {
            $query->whereHas(
                'commodity.category',
                fn ($q) => $q->where('agency_id', $request->agency)
            );
        }

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

        $variants = $query->orderBy('updated_at', 'desc')->paginate(30)->withQueryString();

        // Summary stats
        $totalMonitored = CommodityVariant::whereHas(
            'commodity', fn ($q) => $q->where('is_active', true)
        )->count();

        $withPrevailing = CommodityVariant::whereHas(
            'commodity', fn ($q) => $q->where('is_active', true)
        )->whereNotNull('prevailing_price')->count();

        return view('public.price-watch', compact(
            'variants',
            'agencies',
            'categories',
            'totalMonitored',
            'withPrevailing',
        ));
    }
}
