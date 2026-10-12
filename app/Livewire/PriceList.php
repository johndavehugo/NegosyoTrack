<?php

namespace App\Livewire;

use App\Models\PriceMonitoring\Agency;
use App\Models\PriceMonitoring\Category;
use App\Models\PriceMonitoring\Commodity;
use App\Models\PriceMonitoring\PriceLog;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PriceList extends Component
{
    use WithPagination;

    // ── filters (URL-bound for shareability) ─────────────────────
    #[Url(as: 'q',        history: true, keep: false)]
    public string $search = '';

    #[Url(as: 'category', history: true, keep: false)]
    public string $categoryId = '';

    #[Url(as: 'agency',   history: true, keep: false)]
    public string $agencyId = '';

    public string $sort = 'name_asc';

    protected int $perPage = 20;

    // ── reset page when any filter changes ───────────────────────
    public function updatedSearch(): void     { $this->resetPage(); }
    public function updatedCategoryId(): void { $this->resetPage(); }
    public function updatedAgencyId(): void   { $this->resetPage(); }
    public function updatedSort(): void       { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search     = '';
        $this->categoryId = '';
        $this->agencyId   = '';
        $this->sort       = 'name_asc';
        $this->resetPage();
    }

    public function selectCategory(string $id): void
    {
        $this->categoryId = ($this->categoryId === $id) ? '' : $id;
        $this->resetPage();
    }

    // ── commodity detail modal ────────────────────────────────────
    public bool  $commodityModalOpen = false;
    public ?int  $selectedCommodityId = null;

    public function openCommodityModal(int $commodityId): void
    {
        $this->selectedCommodityId = $commodityId;
        $this->commodityModalOpen  = true;
    }

    public function closeCommodityModal(): void
    {
        $this->commodityModalOpen  = false;
        $this->selectedCommodityId = null;
    }

    #[Computed]
    public function selectedCommodity(): ?Commodity
    {
        return $this->selectedCommodityId
            ? Commodity::with(['category.agency'])->find($this->selectedCommodityId)
            : null;
    }

    /**
     * Price history for the chart — last 12 log entries grouped by month.
     * Falls back to a synthetic SRP flat-line so the chart always renders.
     */
    #[Computed]
    public function priceChartData(): array
    {
        if (! $this->selectedCommodityId) {
            return ['labels' => [], 'srp' => [], 'prevailing' => []];
        }

        $commodity = $this->selectedCommodity;
        if (! $commodity) {
            return ['labels' => [], 'srp' => [], 'prevailing' => []];
        }

        // Get last 12 months of logs
        $logs = PriceLog::where('commodity_id', $this->selectedCommodityId)
            ->where('status', 'ACTIVE')
            ->orderBy('monitored_at')
            ->get();

        if ($logs->isEmpty()) {
            // No logs — generate 6-month flat SRP line so chart isn't empty
            $labels     = [];
            $srpSeries  = [];
            for ($i = 5; $i >= 0; $i--) {
                $labels[]    = now()->subMonths($i)->format('M Y');
                $srpSeries[] = (float) $commodity->srp;
            }
            return [
                'labels'     => $labels,
                'srp'        => $srpSeries,
                'prevailing' => [], // empty = not plotted
                'has_logs'   => false,
            ];
        }

        // Group by month, average prevailing price
        $grouped = $logs->groupBy(fn ($l) => $l->monitored_at->format('Y-m'));
        $labels     = [];
        $srpSeries  = [];
        $prevSeries = [];

        foreach ($grouped->take(12) as $month => $entries) {
            $labels[]     = \Carbon\Carbon::parse($month)->format('M Y');
            $srpSeries[]  = (float) $commodity->srp;
            $avg = $entries->where('prevailing_price', '>', 0)->avg('prevailing_price');
            $prevSeries[] = $avg ? round((float) $avg, 2) : null;
        }

        return [
            'labels'     => $labels,
            'srp'        => $srpSeries,
            'prevailing' => $prevSeries,
            'has_logs'   => true,
        ];
    }

    /**
     * Parse the establishments string into individual entries with a
     * simulated "current price" for demonstration.
     * When real per-establishment price data exists, replace this.
     */
    #[Computed]
    public function establishmentComparison(): array
    {
        $commodity = $this->selectedCommodity;
        if (! $commodity || blank($commodity->establishments)) {
            return [];
        }

        // Split comma/semicolon-separated establishment names
        $names = collect(preg_split('/[,;]+/', $commodity->establishments))
            ->map(fn ($n) => trim($n))
            ->filter()
            ->values();

        $srp = (float) $commodity->srp;

        // Get latest prevailing price from logs per establishment (no per-estab data yet)
        // For now map all to the same latest log price or SRP
        $latestLog = PriceLog::where('commodity_id', $commodity->id)
            ->where('prevailing_price', '>', 0)
            ->orderByDesc('monitored_at')
            ->first();

        $latestPrice = $latestLog ? (float) $latestLog->prevailing_price : null;

        return $names->map(function (string $name) use ($srp, $latestPrice) {
            // If no per-establishment data, show SRP as the reference price
            $price  = $latestPrice ?? $srp;
            $diff   = $price - $srp;
            $status = match(true) {
                $diff > 0  => 'above',
                $diff < 0  => 'below',
                default    => 'at',
            };
            return [
                'name'   => $name,
                'price'  => $price,
                'srp'    => $srp,
                'diff'   => round($diff, 2),
                'status' => $status,
                'pct'    => $srp > 0 ? round(abs($diff) / $srp * 100, 1) : 0,
            ];
        })->all();
    }

    // ── computed data ────────────────────────────────────────────

    #[Computed(cache: true, seconds: 300)]
    public function agencies()
    {
        return Agency::orderBy('name')->get();
    }

    #[Computed(cache: true, seconds: 300)]
    public function allCategories()
    {
        return Category::with('agency')
            ->withCount(['commodities' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();
    }

    // categories filtered by selected agency (for the pill bar)
    #[Computed]
    public function categories()
    {
        return Category::with('agency')
            ->withCount(['commodities' => fn ($q) => $q->where('is_active', true)])
            ->when($this->agencyId, fn ($q) => $q->where('agency_id', $this->agencyId))
            ->orderBy('name')
            ->get();
    }

    #[Computed(cache: true, seconds: 300)]
    public function totals(): array
    {
        return [
            'commodities' => Commodity::where('is_active', true)->count(),
            'agencies'    => Agency::count(),
            'categories'  => Category::count(),
        ];
    }

    #[Computed(cache: true, seconds: 300)]
    public function tickerItems()
    {
        return Commodity::with('category')
            ->where('is_active', true)
            ->whereNotNull('srp')
            ->orderByDesc('updated_at')
            ->limit(16)
            ->get();
    }

    // ── paginated commodity list ──────────────────────────────────
    #[Computed]
    public function commodities()
    {
        $query = Commodity::with(['category.agency'])
            ->where('is_active', true)
            ->when($this->search, fn ($q, $s) =>
                $q->where(fn ($sub) =>
                    $sub->where('product_name', 'like', "%{$s}%")
                        ->orWhere('brand_name',   'like', "%{$s}%")
                        ->orWhere('establishments', 'like', "%{$s}%")
                )
            )
            ->when($this->categoryId, fn ($q) =>
                $q->where('category_id', $this->categoryId)
            )
            ->when($this->agencyId, fn ($q) =>
                $q->whereHas('category', fn ($s) =>
                    $s->where('agency_id', $this->agencyId)
                )
            );

        $query = match ($this->sort) {
            'name_desc' => $query->orderByDesc('product_name'),
            'srp_asc'   => $query->orderBy('srp'),
            'srp_desc'  => $query->orderByDesc('srp'),
            'cat_asc'   => $query->orderBy(
                               Category::select('name')
                                   ->whereColumn('commodity_categories.id', 'commodities.category_id')
                                   ->limit(1)
                           ),
            default     => $query->orderBy('product_name'),
        };

        return $query->paginate($this->perPage);
    }

    // active label helpers used in the view
    #[Computed]
    public function activeCategoryLabel(): string
    {
        if (blank($this->categoryId)) return '';
        return $this->allCategories->firstWhere('id', $this->categoryId)?->name ?? '';
    }

    #[Computed]
    public function activeAgencyLabel(): string
    {
        if (blank($this->agencyId)) return '';
        return $this->agencies->firstWhere('id', $this->agencyId)?->name ?? '';
    }

    // ── render ───────────────────────────────────────────────────
    public function render()
    {
        return view('sccpresyo.price-list')->layout('sccpresyo.layout');
    }
}
