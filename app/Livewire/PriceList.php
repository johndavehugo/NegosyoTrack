<?php

namespace App\Livewire;

use App\Models\PriceMonitoring\Agency;
use App\Models\PriceMonitoring\Category;
use App\Models\PriceMonitoring\Commodity;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PriceList extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true, keep: false)]
    public string $search = '';

    #[Url(as: 'category', history: true, keep: false)]
    public string $categoryFilter = '';

    #[Url(as: 'agency', history: true, keep: false)]
    public string $agencyFilter = '';

    public string $sort = 'name_asc';

    protected int $perPage = 15;

    // ── reset pagination on filter change ────────────────────────

    public function updatedSearch(): void     { $this->resetPage(); }
    public function updatedCategoryFilter(): void { $this->resetPage(); }
    public function updatedAgencyFilter(): void   { $this->resetPage(); }
    public function updatedSort(): void            { $this->resetPage(); }

    // ── computed properties ───────────────────────────────────────

    public function getAgenciesProperty()
    {
        return Agency::orderBy('name')->get();
    }

    public function getCategoriesProperty()
    {
        return Category::withCount(['commodities' => fn ($q) => $q->where('is_active', true)])
            ->when($this->agencyFilter, fn ($q) => $q->where('agency_id', $this->agencyFilter))
            ->orderBy('name')
            ->get();
    }

    public function getTotalsProperty(): array
    {
        return [
            'commodities'    => Commodity::where('is_active', true)->count(),
            'agencies'       => Agency::count(),
            'categories'     => Category::count(),
        ];
    }

    // ── ticker data (top 8 active, cached) ───────────────────────

    public function getTickerItemsProperty()
    {
        return Commodity::with('category.agency')
            ->where('is_active', true)
            ->whereNotNull('srp')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();
    }

    // ── main query ────────────────────────────────────────────────

    public function render()
    {
        $query = Commodity::with(['category.agency'])
            ->where('is_active', true)
            ->when($this->search, fn ($q, $s) =>
                $q->where(fn ($sub) =>
                    $sub->where('product_name', 'like', "%{$s}%")
                        ->orWhere('brand_name', 'like', "%{$s}%")
                        ->orWhere('establishments', 'like', "%{$s}%")
                )
            )
            ->when($this->categoryFilter, fn ($q) =>
                $q->where('category_id', $this->categoryFilter)
            )
            ->when($this->agencyFilter, fn ($q) =>
                $q->whereHas('category', fn ($sub) =>
                    $sub->where('agency_id', $this->agencyFilter)
                )
            );

        $query = match ($this->sort) {
            'name_asc'    => $query->orderBy('product_name'),
            'name_desc'   => $query->orderByDesc('product_name'),
            'srp_asc'     => $query->orderBy('srp'),
            'srp_desc'    => $query->orderByDesc('srp'),
            default       => $query->orderBy('product_name'),
        };

        // Group by category for the section headers
        $grouped = $query->get()->groupBy('category.name');
        $paginated = $query->paginate($this->perPage);

        return view('sccpresyo.price-list', [
            'grouped'  => $grouped,
            'paginated' => $paginated,
        ])->layout('sccpresyo.layout');
    }
}
