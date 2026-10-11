{{--
    SCCPresyo — Public Price Monitoring
    Standalone Blade + Livewire page. Zero Filament dependency.
--}}
<div class="min-h-screen flex flex-col bg-slate-50">

{{-- ── PRICE TICKER ── --}}
@if($this->tickerItems->isNotEmpty())
<div class="bg-red-900 text-white overflow-hidden" aria-label="Live price ticker">
    <div class="flex items-center">
        <span class="shrink-0 bg-red-700 px-4 py-2 text-xs font-bold uppercase tracking-widest z-10">
            📢 SRP
        </span>
        <div class="relative flex-1 overflow-hidden">
            <div class="flex gap-10 animate-[ticker_40s_linear_infinite] whitespace-nowrap py-2 px-6">
                @foreach($this->tickerItems as $item)
                    <span class="text-xs font-semibold shrink-0">
                        {{ $item->product_name }}
                        @if($item->brand_name) <span class="opacity-60">({{ $item->brand_name }})</span> @endif
                        — <span class="text-red-200 font-bold">₱{{ number_format($item->srp, 2) }}</span>
                        <span class="opacity-50">/ {{ $item->unit_of_measure }}</span>
                    </span>
                @endforeach
                {{-- duplicate for seamless loop --}}
                @foreach($this->tickerItems as $item)
                    <span class="text-xs font-semibold shrink-0">
                        {{ $item->product_name }}
                        @if($item->brand_name) <span class="opacity-60">({{ $item->brand_name }})</span> @endif
                        — <span class="text-red-200 font-bold">₱{{ number_format($item->srp, 2) }}</span>
                        <span class="opacity-50">/ {{ $item->unit_of_measure }}</span>
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── HEADER ── --}}
<header class="bg-red-700 text-white shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex flex-wrap items-center gap-4">

        {{-- Brand --}}
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center text-2xl shrink-0">
                🏷️
            </div>
            <div class="min-w-0">
                <h1 class="text-xl font-extrabold tracking-tight leading-none">SCCPresyo</h1>
                <p class="text-xs text-red-200 mt-0.5 leading-none">San Carlos City · Public Price Monitoring</p>
            </div>
        </div>

        {{-- Right meta --}}
        <div class="ml-auto text-right text-xs text-red-200 leading-relaxed hidden sm:block">
            <span class="font-semibold text-white block">Negosyo Center — San Carlos City</span>
            As of {{ now()->format('F j, Y') }}
        </div>
    </div>

    {{-- Nav tabs --}}
    <div class="bg-red-800/70 border-t border-red-900/40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex gap-1">
            <a href="#" class="px-4 py-2.5 text-xs font-semibold text-white border-b-2 border-red-300">
                Price List
            </a>
            <a href="#about" class="px-4 py-2.5 text-xs font-semibold text-red-300 hover:text-white border-b-2 border-transparent hover:border-red-400 transition-colors">
                About SRP
            </a>
        </div>
    </div>
</header>

{{-- ── HERO SEARCH ── --}}
<div class="bg-gradient-to-br from-red-900 via-red-800 to-red-700 text-white px-4 sm:px-6 py-8">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-lg sm:text-xl font-bold mb-1">Find the official SRP for goods in San Carlos City</h2>
        <p class="text-sm text-red-200 mb-5">Prices are Suggested Retail Prices (SRP) set by monitoring agencies. Updated regularly.</p>

        {{-- Search + filters --}}
        <div class="flex flex-wrap gap-3">
            {{-- Search input --}}
            <div class="relative flex-1 min-w-52">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-red-300 pointer-events-none">🔍</span>
                <input
                    type="search"
                    wire:model.live.debounce.350ms="search"
                    placeholder="Search commodity, brand, establishment…"
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white/15 text-white placeholder-red-300 text-sm border border-white/20 focus:outline-none focus:ring-2 focus:ring-white/30 focus:bg-white/20 transition"
                    aria-label="Search commodities"
                />
            </div>

            {{-- Agency filter --}}
            <select
                wire:model.live="agencyFilter"
                class="py-2.5 px-3 rounded-xl bg-white/15 text-white text-sm border border-white/20 focus:outline-none focus:ring-2 focus:ring-white/30 transition cursor-pointer"
            >
                <option value="">All Agencies</option>
                @foreach($this->agencies as $agency)
                    <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                @endforeach
            </select>

            {{-- Category filter --}}
            <select
                wire:model.live="categoryFilter"
                class="py-2.5 px-3 rounded-xl bg-white/15 text-white text-sm border border-white/20 focus:outline-none focus:ring-2 focus:ring-white/30 transition cursor-pointer"
            >
                <option value="">All Categories</option>
                @foreach($this->categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Summary badges --}}
        <div class="flex flex-wrap gap-2 mt-4">
            <span class="bg-white/10 border border-white/20 rounded-full px-3 py-1 text-xs font-semibold">
                📦 {{ $this->totals['commodities'] }} Active Commodities
            </span>
            <span class="bg-white/10 border border-white/20 rounded-full px-3 py-1 text-xs font-semibold">
                🏛️ {{ $this->totals['agencies'] }} Monitoring Agencies
            </span>
            <span class="bg-white/10 border border-white/20 rounded-full px-3 py-1 text-xs font-semibold">
                🏷️ {{ $this->totals['categories'] }} Categories
            </span>
        </div>
    </div>
</div>

{{-- ── MAIN CONTENT ── --}}
<main class="flex-1 max-w-6xl mx-auto w-full px-4 sm:px-6 py-6">

    {{-- Disclaimer --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-xs text-amber-800 flex gap-2 items-start mb-5">
        <span class="text-base shrink-0">⚠️</span>
        <div>
            <strong>Disclaimer:</strong> Prices listed are <em>Suggested Retail Prices (SRP)</em> only.
            Actual market prices may vary per establishment. Data is provided for public information.
            Last updated: <strong>{{ now()->format('F j, Y, g:i A') }}</strong>.
        </div>
    </div>

    {{-- ── FILTER & SORT BAR ── --}}
    <div class="flex flex-wrap items-center gap-2 mb-5">

        {{-- Active filter chips --}}
        @if($search)
            <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 border border-red-200 rounded-full px-3 py-1 text-xs font-semibold">
                🔍 "{{ $search }}"
                <button wire:click="$set('search', '')" class="hover:text-red-900 ml-0.5" aria-label="Clear search">✕</button>
            </span>
        @endif
        @if($categoryFilter)
            @php $activeCat = $this->categories->firstWhere('id', $categoryFilter); @endphp
            @if($activeCat)
            <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 border border-red-200 rounded-full px-3 py-1 text-xs font-semibold">
                🏷️ {{ $activeCat->name }}
                <button wire:click="$set('categoryFilter', '')" class="hover:text-red-900 ml-0.5" aria-label="Clear category">✕</button>
            </span>
            @endif
        @endif
        @if($agencyFilter)
            @php $activeAgency = $this->agencies->firstWhere('id', $agencyFilter); @endphp
            @if($activeAgency)
            <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 border border-red-200 rounded-full px-3 py-1 text-xs font-semibold">
                🏛️ {{ $activeAgency->name }}
                <button wire:click="$set('agencyFilter', '')" class="hover:text-red-900 ml-0.5" aria-label="Clear agency">✕</button>
            </span>
            @endif
        @endif
        @if($search || $categoryFilter || $agencyFilter)
            <button
                wire:click="$set('search', ''); $set('categoryFilter', ''); $set('agencyFilter', '')"
                class="text-xs text-slate-400 hover:text-slate-600 underline ml-1"
            >Clear all filters</button>
        @endif

        {{-- Sort --}}
        <div class="ml-auto flex items-center gap-2">
            <label class="text-xs text-slate-400 font-semibold hidden sm:block">Sort:</label>
            <select
                wire:model.live="sort"
                class="text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white text-slate-600 focus:outline-none focus:ring-2 focus:ring-red-300"
            >
                <option value="name_asc">Name A–Z</option>
                <option value="name_desc">Name Z–A</option>
                <option value="srp_asc">Price: Low to High</option>
                <option value="srp_desc">Price: High to Low</option>
            </select>
        </div>
    </div>

    {{-- ── LOADING INDICATOR ── --}}
    <div wire:loading.delay class="flex items-center gap-2 text-sm text-slate-400 mb-4">
        <svg class="animate-spin h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        Loading…
    </div>

    {{-- ── NO RESULTS ── --}}
    @if($grouped->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
            <div class="text-5xl mb-3">🔍</div>
            <p class="font-semibold text-slate-700 text-lg">No commodities found</p>
            <p class="text-sm text-slate-400 mt-1">Try adjusting your search or filters.</p>
            <button
                wire:click="$set('search', ''); $set('categoryFilter', ''); $set('agencyFilter', '')"
                class="mt-4 inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition"
            >
                Clear filters
            </button>
        </div>
    @endif

    {{-- ── GROUPED SECTIONS ── --}}
    @foreach($grouped as $categoryName => $items)
        @php
            $first = $items->first();
            $agency = $first?->category?->agency;
        @endphp

        {{-- Section header --}}
        <div class="flex items-center gap-3 mb-3 mt-6 first:mt-0">
            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center text-base shrink-0">
                @php
                    $icons = [
                        'rice' => '🌾', 'grain' => '🌾', 'sugar' => '🫙', 'oil' => '🛢️',
                        'meat' => '🥩', 'pork' => '🐖', 'chicken' => '🐔', 'poultry' => '🐔',
                        'fish' => '🐟', 'seafood' => '🦐', 'vegetable' => '🥦', 'fruit' => '🍎',
                        'fuel' => '⛽', 'lpg' => '⛽', 'gas' => '⛽',
                        'medicine' => '💊', 'construction' => '🧱',
                    ];
                    $icon = '📦';
                    foreach ($icons as $keyword => $emoji) {
                        if (str_contains(strtolower($categoryName ?? ''), $keyword)) {
                            $icon = $emoji;
                            break;
                        }
                    }
                @endphp
                {{ $icon }}
            </div>
            <h2 class="text-base font-bold text-slate-800">
                {{ $categoryName ?? 'Uncategorized' }}
            </h2>
            @if($agency)
                <span class="bg-red-50 text-red-700 border border-red-200 rounded-full px-2.5 py-0.5 text-xs font-semibold">
                    {{ $agency->name }}
                </span>
            @endif
            <span class="bg-slate-100 text-slate-500 rounded-full px-2.5 py-0.5 text-xs font-semibold ml-auto">
                {{ $items->count() }} {{ Str::plural('item', $items->count()) }}
            </span>
        </div>

        {{-- Price table --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-2">
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wide w-8">#</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wide">Commodity</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wide hidden sm:table-cell">Brand / Description</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wide hidden md:table-cell">Establishment</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wide hidden sm:table-cell">Unit</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wide">SRP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($items as $index => $commodity)
                        <tr class="hover:bg-red-50/40 transition-colors group">

                            {{-- # --}}
                            <td class="px-4 py-3 text-xs text-slate-300 font-mono">
                                {{ $index + 1 }}
                            </td>

                            {{-- Commodity name --}}
                            <td class="px-4 py-3">
                                <span class="font-semibold text-slate-800 group-hover:text-red-700 transition-colors">
                                    {{ $commodity->product_name }}
                                </span>
                                {{-- Show brand inline on mobile --}}
                                @if($commodity->brand_name)
                                <div class="text-xs text-slate-400 mt-0.5 sm:hidden">{{ $commodity->brand_name }}</div>
                                @endif
                            </td>

                            {{-- Brand --}}
                            <td class="px-4 py-3 text-slate-500 hidden sm:table-cell">
                                {{ $commodity->brand_name ?: '—' }}
                            </td>

                            {{-- Establishment --}}
                            <td class="px-4 py-3 text-slate-500 hidden md:table-cell">
                                @if($commodity->establishments)
                                    <span class="flex items-center gap-1">
                                        <span class="text-slate-300">🏪</span>
                                        {{ $commodity->establishments }}
                                    </span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>

                            {{-- Unit --}}
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <span class="inline-block bg-slate-100 text-slate-500 rounded-md px-2 py-0.5 text-xs font-medium">
                                    {{ $commodity->unit_of_measure }}
                                </span>
                            </td>

                            {{-- SRP --}}
                            <td class="px-4 py-3 text-right">
                                @if($commodity->srp)
                                    <span class="font-bold text-base text-slate-900">
                                        ₱{{ number_format($commodity->srp, 2) }}
                                    </span>
                                    <div class="text-xs text-slate-400 mt-0.5">SRP ceiling</div>
                                @else
                                    <span class="text-slate-300 text-xs">No SRP set</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    {{-- ── PAGINATION ── --}}
    @if($paginated->hasPages())
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-500">
            <span>
                Showing <strong class="text-slate-700">{{ $paginated->firstItem() }}–{{ $paginated->lastItem() }}</strong>
                of <strong class="text-slate-700">{{ $paginated->total() }}</strong> active commodities
            </span>
            <div class="flex items-center gap-1">
                {{-- Prev --}}
                @if($paginated->onFirstPage())
                    <span class="px-3 py-2 rounded-lg border border-slate-200 text-slate-300 cursor-not-allowed select-none">← Prev</span>
                @else
                    <button wire:click="previousPage" class="px-3 py-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-red-50 hover:border-red-200 hover:text-red-700 transition">← Prev</button>
                @endif

                {{-- Page numbers --}}
                @foreach($paginated->getUrlRange(max(1, $paginated->currentPage() - 2), min($paginated->lastPage(), $paginated->currentPage() + 2)) as $page => $url)
                    @if($page == $paginated->currentPage())
                        <span class="px-3 py-2 rounded-lg bg-red-600 text-white font-bold border border-red-600 select-none">{{ $page }}</span>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-3 py-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-red-50 hover:border-red-200 hover:text-red-700 transition">{{ $page }}</button>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($paginated->hasMorePages())
                    <button wire:click="nextPage" class="px-3 py-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-red-50 hover:border-red-200 hover:text-red-700 transition">Next →</button>
                @else
                    <span class="px-3 py-2 rounded-lg border border-slate-200 text-slate-300 cursor-not-allowed select-none">Next →</span>
                @endif
            </div>
        </div>
    @endif

    {{-- ── ABOUT SRP ── --}}
    <div id="about" class="mt-10 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h3 class="text-base font-bold text-slate-800 mb-2">ℹ️ About Suggested Retail Prices (SRP)</h3>
        <p class="text-sm text-slate-500 leading-relaxed">
            The Suggested Retail Prices (SRP) shown on this page are set and monitored by government agencies
            such as the Department of Trade and Industry (DTI), Department of Agriculture (DA), and the
            Department of Energy (DOE). These prices serve as guidelines for retailers and consumers.
            Actual prices may vary depending on the establishment and market conditions.
        </p>
        <p class="text-sm text-slate-500 leading-relaxed mt-2">
            For price complaints or inquiries, please visit the <strong>San Carlos City Negosyo Center</strong>
            or contact the <strong>CDCPMO</strong>.
        </p>
    </div>

</main>

{{-- ── FOOTER ── --}}
<footer class="bg-slate-800 text-slate-400 mt-auto">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="text-sm font-bold text-white mb-1">SCCPresyo · San Carlos City</div>
            <div class="text-xs">Powered by <span class="text-red-400 font-semibold">NegosyoTrack</span> · Negosyo Center</div>
            <div class="text-xs mt-1">Data from DTI, DA, DOE and other monitoring agencies.</div>
        </div>
        <div class="flex flex-wrap gap-2">
            <span class="bg-white/8 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-300">🏛️ DTI</span>
            <span class="bg-white/8 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-300">🌾 DA</span>
            <span class="bg-white/8 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-300">⛽ DOE</span>
            <span class="bg-white/8 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-300">🏙️ CDCPMO</span>
        </div>
    </div>
</footer>

{{-- Ticker animation via inline style (avoids Tailwind purge issues with dynamic animation names) --}}
<style>
    @keyframes ticker {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
    }
</style>

</div>{{-- end single Livewire root --}}
