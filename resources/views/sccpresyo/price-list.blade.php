@php
    $search     = request('search', '');
    $categoryId = request('category', '');
    $agencyId   = request('agency', '');
    $sort       = request('sort', 'name_asc');
@endphp

{{-- ══ SRP TICKER ══════════════════════════════════════════════════ --}}
@php
    $tickerItems = $variants->getCollection()->filter(fn ($v) => $v->srp > 0)->take(18);
@endphp
@if ($tickerItems->isNotEmpty())
<div class="bg-brand border-b-2 border-brand-dark overflow-hidden ticker-wrap" aria-label="Live SRP ticker">
    <div class="flex items-stretch">
        <div class="shrink-0 bg-brand-dark flex items-center gap-2 px-4 py-2">
            <span class="live-dot w-1.5 h-1.5 rounded-full bg-green-300 inline-block"></span>
            <span class="text-white text-[10px] font-black tracking-widest uppercase">Live SRP</span>
        </div>
        <div class="flex-1 overflow-hidden py-2 px-3">
            <div class="ticker-track inline-flex gap-10 whitespace-nowrap">
                @foreach (array_merge($tickerItems->all(), $tickerItems->all()) as $t)
                <span class="shrink-0 text-xs flex items-center gap-1.5">
                    <span class="text-white font-semibold">{{ $t->commodity->product_name }}</span>
                    @if ($t->commodity->brand)<span class="text-green-300">({{ $t->commodity->brand->name }})</span>@endif
                    <span class="text-green-400">—</span>
                    <span class="text-white font-black font-mono">₱{{ number_format($t->srp, 2) }}</span>
                    <span class="text-green-400 text-[11px]">/ {{ $t->unit_of_measure }}</span>
                </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- ══ STICKY HEADER ════════════════════════════════════════════════ --}}
<header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3 h-16">

            {{-- Hamburger menu button --}}
            <button id="sidebar-toggle-btn"
                class="shrink-0 p-2 rounded-xl hover:bg-slate-100 transition-colors text-slate-500 hover:text-slate-800"
                aria-label="Open menu" title="Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Brand --}}
            <a href="{{ route('sccpresyo.index') }}"
               class="flex items-center gap-2.5 shrink-0 group"
               style="text-decoration:none">
                <div class="w-9 h-9 bg-brand rounded-xl flex items-center justify-center shadow-sm group-hover:bg-brand-dark transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                </div>
                <div class="leading-tight">
                    <p class="text-sm font-black text-slate-900 tracking-tight">SCCPresyo</p>
                    <p class="text-[10px] font-semibold text-slate-400 tracking-wider uppercase">San Carlos City</p>
                </div>
            </a>

            <div class="w-px h-6 bg-slate-200 shrink-0 hidden sm:block"></div>

            {{-- Desktop search --}}
            <div class="hidden sm:flex flex-1 max-w-md relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input id="header-search" type="search"
                       value="{{ $search }}"
                       placeholder="Search commodity or brand…"
                       class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-slate-200 bg-slate-50
                              focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent
                              placeholder-slate-400 transition-all"/>
            </div>

            <div class="ml-auto flex items-center gap-2">
                {{-- Live badge --}}
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-brand/10 border border-brand/20">
                    <span class="live-dot w-1.5 h-1.5 rounded-full bg-brand inline-block"></span>
                    <span class="text-[10px] font-black tracking-widest text-brand uppercase">Live</span>
                </div>

                {{-- Date --}}
                <span class="hidden md:block text-xs text-slate-400">{{ now()->format('M j, Y') }}</span>

                {{-- Favorites button --}}
                <button id="watchlist-toggle-btn"
                    class="relative flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200
                           hover:border-brand hover:text-brand text-slate-600 transition-all text-sm font-semibold bg-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    <span class="hidden sm:inline">Favorites</span>
                    <span id="watchlist-count"
                        class="hidden absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1
                               rounded-full bg-brand text-white text-[10px] font-black
                               flex items-center justify-center leading-none">0</span>
                </button>
            </div>
        </div>
    </div>
</header>

{{-- ══ SIDEBAR ══════════════════════════════════════════════════════ --}}
{{-- Overlay --}}
<div id="sidebar-overlay"
     class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 opacity-0 pointer-events-none transition-opacity duration-250">
</div>

{{-- Sidebar panel (slides in from left) --}}
<aside id="sidebar-panel"
       class="fixed top-0 left-0 h-full w-72 bg-white z-50 shadow-2xl flex flex-col
              -translate-x-full transition-transform duration-300 ease-[cubic-bezier(.4,0,.2,1)]"
       role="dialog" aria-label="Navigation menu">

    {{-- Sidebar header --}}
    <div class="flex items-center justify-between px-5 py-4 bg-brand shrink-0">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                </svg>
            </div>
            <div class="leading-tight">
                <p class="text-white font-black text-sm">SCCPresyo</p>
                <p class="text-green-300 text-[10px] font-semibold tracking-wider uppercase">San Carlos City</p>
            </div>
        </div>
        <button id="sidebar-close-btn"
            class="p-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-white"
            aria-label="Close menu">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Nav items --}}
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

        {{-- Home --}}
        <a href="{{ route('sccpresyo.index') }}"
           class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold
                  text-brand bg-brand/10 border border-brand/20"
           style="text-decoration:none">
            <span class="w-8 h-8 rounded-lg bg-brand/15 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    <polyline stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        points="9 22 9 12 15 12 15 22"/>
                </svg>
            </span>
            Home
        </a>

        {{-- My Favorites --}}
        <button id="sidebar-favorites-btn"
            class="sidebar-nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold
                   text-slate-700 hover:bg-slate-100 transition-colors">
            <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </span>
            <span class="flex-1 text-left">My Favorites</span>
            <span id="sidebar-fav-count"
                class="hidden min-w-[20px] h-5 px-1.5 rounded-full bg-brand text-white text-[10px] font-black
                       items-center justify-center">0</span>
        </button>

        <div class="pt-2 pb-1 px-3">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Browse</p>
        </div>

        {{-- Categories (accordion) --}}
        <button id="sidebar-categories-toggle"
            class="sidebar-nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold
                   text-slate-700 hover:bg-slate-100 transition-colors">
            <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"/>
                </svg>
            </span>
            <span class="flex-1 text-left">Categories</span>
            <svg id="sidebar-cat-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200"
                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        {{-- Category list (collapsed by default) --}}
        <div id="sidebar-categories-list" class="hidden pl-4 space-y-0.5">
            <a href="{{ route('sccpresyo.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold
                      {{ !$categoryId ? 'text-brand bg-brand/8' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }} transition-colors"
               style="text-decoration:none">
                <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50"></span>
                All Categories
            </a>
            @foreach ($categories as $cat)
            <a href="{{ route('sccpresyo.index', array_filter(['category' => $cat->id, 'sort' => $sort !== 'name_asc' ? $sort : null])) }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold
                      {{ $categoryId == $cat->id ? 'text-brand bg-brand/8' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }} transition-colors"
               style="text-decoration:none">
                <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50"></span>
                {{ $cat->name }}
                <span class="ml-auto text-[10px] text-slate-400">({{ $cat->commodities_count }})</span>
            </a>
            @endforeach
        </div>

        {{-- Monitoring Agencies --}}
        <button id="sidebar-agencies-toggle"
            class="sidebar-nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold
                   text-slate-700 hover:bg-slate-100 transition-colors">
            <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </span>
            <span class="flex-1 text-left">Monitoring Agencies</span>
            <svg id="sidebar-ag-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200"
                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        {{-- Agency list (collapsed by default) --}}
        <div id="sidebar-agencies-list" class="hidden pl-4 space-y-0.5">
            @foreach ($agencies as $ag)
            <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg">
                <span class="w-7 h-7 rounded-md bg-brand/10 flex items-center justify-center shrink-0">
                    <span class="text-[9px] font-black text-brand">{{ strtoupper(substr($ag->code ?? $ag->name, 0, 3)) }}</span>
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-700 truncate">{{ $ag->name }}</p>
                    @if ($ag->coverage)
                    <p class="text-[10px] text-slate-400 truncate">{{ $ag->coverage }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="pt-2 pb-1 px-3">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Info</p>
        </div>

        {{-- About SRP --}}
        <button id="sidebar-srp-btn"
           class="sidebar-nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold
                  text-slate-700 hover:bg-slate-100 transition-colors">
            <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            About SRP
        </button>

        {{-- Disclaimer --}}
        <button id="sidebar-disclaimer-btn"
           class="sidebar-nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold
                  text-slate-700 hover:bg-slate-100 transition-colors">
            <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </span>
            Disclaimer
        </button>

    </nav>

    {{-- Sidebar footer --}}
    <div class="px-4 py-4 border-t border-slate-100 shrink-0 bg-slate-50">
        <p class="text-[10px] text-slate-400 text-center font-medium">
            Powered by <span class="font-bold text-brand">NegosyoTrack</span> · Negosyo Center
        </p>
        <p class="text-[10px] text-slate-300 text-center mt-0.5">San Carlos City LGU</p>
    </div>

</aside>

{{-- ══ HERO & FILTERS ════════════════════════════════════════════════ --}}
<div class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

        {{-- Title row --}}
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-5 mb-6">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-brand/10 text-brand text-xs font-bold mb-2">
                    <span class="live-dot w-1.5 h-1.5 rounded-full bg-brand inline-block"></span>
                    Live Monitoring
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 leading-tight tracking-tight">
                    San Carlos City <span class="text-brand">e-Presyo</span>
                </h1>
                <p class="mt-1.5 text-sm text-slate-500 max-w-xl leading-relaxed">
                    Official Suggested Retail Price (SRP) and prevailing prices of basic commodities
                    in San Carlos City. Click any product to compare prices across establishments.
                </p>
            </div>

            {{-- Stats --}}
            <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                <div class="text-center px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200/80 min-w-[80px]">
                    <p class="text-2xl font-black text-slate-900 font-mono">{{ number_format($totals['commodities']) }}</p>
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5 font-semibold">Monitored</p>
                </div>
                <div class="text-center px-4 py-3 rounded-2xl bg-brand/10 border border-brand/20 min-w-[80px]">
                    <p class="text-2xl font-black text-brand font-mono">{{ number_format($totals['categories']) }}</p>
                    <p class="text-[10px] text-brand/60 uppercase tracking-wider mt-0.5 font-semibold">Categories</p>
                </div>
                <div class="text-center px-4 py-3 rounded-2xl bg-amber-50 border border-amber-200/80 min-w-[80px]">
                    <p class="text-2xl font-black text-amber-600 font-mono">{{ number_format($totals['agencies']) }}</p>
                    <p class="text-[10px] text-amber-500 uppercase tracking-wider mt-0.5 font-semibold">Agencies</p>
                </div>
            </div>
        </div>

        {{-- Mobile search --}}
        <div class="sm:hidden mb-4 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input id="mobile-search" type="search"
                   value="{{ $search }}"
                   placeholder="Search commodity or brand…"
                   class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50
                          focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent
                          placeholder-slate-400"/>
        </div>

        {{-- Filter form --}}
        <form method="GET" action="{{ route('sccpresyo.index') }}" id="filter-form">
            @if ($search)<input type="hidden" name="search" value="{{ $search }}">@endif
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[170px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Category</label>
                    <select name="category" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700
                               shadow-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent
                               appearance-none cursor-pointer font-medium">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($categoryId == $cat->id)>
                                {{ $cat->name }} ({{ $cat->commodities_count }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Sort By</label>
                    <select name="sort" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700
                               shadow-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent
                               appearance-none cursor-pointer font-medium">
                        <option value="name_asc"  @selected($sort === 'name_asc') >Name A → Z</option>
                        <option value="name_desc" @selected($sort === 'name_desc')>Name Z → A</option>
                        <option value="srp_asc"   @selected($sort === 'srp_asc')  >Price: Low → High</option>
                        <option value="srp_desc"  @selected($sort === 'srp_desc') >Price: High → Low</option>
                        <option value="cat_asc"   @selected($sort === 'cat_asc')  >By Category</option>
                    </select>
                </div>

                @if (filled($search) || filled($categoryId) || filled($agencyId))
                    <a href="{{ route('sccpresyo.index') }}"
                        class="self-end inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2.5
                               text-sm font-semibold text-slate-500 hover:border-slate-300 hover:text-slate-700
                               transition-all bg-white shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Clear
                    </a>
                @endif
            </div>
        </form>

        {{-- Tabs + result count --}}
        <div class="flex flex-wrap items-center gap-2 mt-5">
            <button id="tab-all"
                class="tab-btn inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold
                       transition-all duration-200 bg-brand text-white shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                All Commodities
            </button>
            <button id="tab-watchlist"
                class="tab-btn inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold
                       transition-all duration-200 border border-slate-200 text-slate-600
                       hover:border-brand hover:text-brand bg-white">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                My Favorites
            </button>

            <div class="ml-auto flex items-center gap-2 border border-slate-200 rounded-full px-3.5 py-1.5 bg-slate-50">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-xs font-bold text-slate-700">{{ number_format($variants->total()) }}</span>
                <span class="text-xs text-slate-400">{{ Str::plural('item', $variants->total()) }}</span>
            </div>
        </div>

    </div>
</div>

{{-- ══ CARDS GRID ════════════════════════════════════════════════════ --}}
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    @if ($variants->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm py-24 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-slate-100 mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-700">No commodities found</h3>
            <p class="mt-1 text-sm text-slate-400">Try adjusting your filters or search.</p>
            <a href="{{ route('sccpresyo.index') }}"
                class="mt-5 inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-brand text-white
                       text-sm font-bold hover:bg-brand-dark transition-colors">
                Clear all filters
            </a>
        </div>

    @else

        {{-- Watchlist empty state (shown by JS when watchlist tab is active and empty) --}}
        <div id="watchlist-empty"
             class="hidden bg-white rounded-2xl border border-slate-200/80 shadow-sm py-24 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-brand/10 mb-4">
                <svg class="w-8 h-8 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-700">Your watchlist is empty</h3>
            <p class="mt-1 text-sm text-slate-400">Star any commodity card to add it here.</p>
        </div>

        {{-- Cards --}}
        <div id="cards-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($variants as $v)
                @php
                    $prevailing = $v->prevailing_price ? (float) $v->prevailing_price : null;
                    $srp        = $v->srp              ? (float) $v->srp              : null;
                    $diff       = ($prevailing !== null && $srp !== null) ? round($prevailing - $srp, 2) : null;
                    $pct        = ($diff !== null && $srp > 0) ? round(($diff / $srp) * 100, 1) : null;
                    $isAbove    = $diff !== null && $diff > 0;
                    $isBelow    = $diff !== null && $diff < 0;
                    $isEqual    = $diff !== null && $diff == 0;
                    $estName    = $v->commodity->juridical_id
                                    ? ($establishmentNames[$v->commodity->juridical_id] ?? null)
                                    : null;
                @endphp

                <div id="card-{{ $v->id }}"
                     class="price-card bg-white rounded-2xl border border-slate-200/80 shadow-sm
                            hover:shadow-lg hover:-translate-y-0.5 flex flex-col overflow-hidden
                            cursor-pointer group"
                     data-id="{{ $v->id }}"
                     data-name="{{ strtolower($v->commodity->product_name . ' ' . ($v->commodity->brand?->name ?? '')) }}"
                     data-product="{{ $v->commodity->product_name }}"
                     data-brand="{{ $v->commodity->brand?->name ?? '' }}"
                     data-unit="{{ $v->unit_of_measure }}"
                     data-srp="{{ $srp ?? '' }}"
                     data-prevailing="{{ $prevailing ?? '' }}"
                     data-category="{{ $v->commodity->category?->name ?? '' }}"
                     role="button" tabindex="0"
                     aria-label="View price details for {{ $v->commodity->product_name }}">

                    {{-- Card header --}}
                    <div class="px-5 pt-5 pb-4 flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                         bg-brand/10 text-brand mb-2">
                                {{ $v->commodity->category?->name ?? 'General' }}
                            </span>
                            <h3 class="font-black text-slate-900 text-lg leading-snug
                                       group-hover:text-brand transition-colors">
                                {{ $v->commodity->product_name }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                @if ($v->commodity->brand)
                                    <span class="text-sm text-slate-500">{{ $v->commodity->brand->name }}</span>
                                    <span class="text-slate-300">·</span>
                                @endif
                                <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-lg">
                                    {{ $v->unit_of_measure }}
                                </span>
                            </div>
                        </div>

                        {{-- Star / watchlist button --}}
                        <button class="star-btn shrink-0 p-2 rounded-xl hover:bg-brand/10 transition-colors"
                                data-id="{{ $v->id }}"
                                aria-label="Add to watchlist" title="Add to watchlist">
                            <svg class="w-5 h-5 text-slate-300 transition-colors" fill="none" stroke="currentColor"
                                 stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Price body --}}
                    <div class="px-5 pb-5 flex-1 flex flex-col gap-4">

                        {{-- SRP + Prevailing price box --}}
                        <div class="bg-slate-50 rounded-xl p-4 space-y-3 border border-slate-100">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">SRP</span>
                                <span class="text-base font-bold text-slate-700 font-mono">
                                    {{ $srp !== null ? '₱ ' . number_format($srp, 2) : '—' }}
                                </span>
                            </div>
                            <div class="border-t border-slate-200"></div>
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Prevailing</span>
                                    @if ($isAbove)
                                        <span class="inline-flex items-center gap-0.5 text-rose-700 bg-rose-50 px-2 py-0.5
                                                     rounded-md text-xs font-bold">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/>
                                            </svg>
                                            +{{ $pct }}%
                                        </span>
                                    @elseif ($isBelow)
                                        <span class="inline-flex items-center gap-0.5 text-emerald-700 bg-emerald-50 px-2 py-0.5
                                                     rounded-md text-xs font-bold">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                            {{ $pct }}%
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xl font-black font-mono
                                    {{ $isAbove ? 'text-rose-600' : ($isBelow ? 'text-emerald-700' : ($isEqual ? 'text-brand' : 'text-slate-400')) }}">
                                    {{ $prevailing !== null ? '₱ ' . number_format($prevailing, 2) : '—' }}
                                </span>
                            </div>
                        </div>

                        {{-- Sparkline + diff badge --}}
                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-9 rounded-lg overflow-hidden opacity-75">
                                @if ($isAbove)
                                    <svg viewBox="0 0 80 32" class="w-full h-full" preserveAspectRatio="none">
                                        <polyline points="0,28 15,24 28,26 42,20 55,15 68,11 80,7"
                                            fill="none" stroke="#f43f5e" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"/>
                                        <polyline points="0,28 15,24 28,26 42,20 55,15 68,11 80,7 80,32 0,32"
                                            fill="#f43f5e" fill-opacity="0.08" stroke="none"/>
                                    </svg>
                                @elseif ($isBelow)
                                    <svg viewBox="0 0 80 32" class="w-full h-full" preserveAspectRatio="none">
                                        <polyline points="0,7 15,11 28,9 42,15 55,18 68,22 80,26"
                                            fill="none" stroke="#186b02" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"/>
                                        <polyline points="0,7 15,11 28,9 42,15 55,18 68,22 80,26 80,32 0,32"
                                            fill="#186b02" fill-opacity="0.08" stroke="none"/>
                                    </svg>
                                @else
                                    <svg viewBox="0 0 80 32" class="w-full h-full" preserveAspectRatio="none">
                                        <polyline points="0,16 15,15 28,17 42,16 55,15 68,16 80,16"
                                            fill="none" stroke="#94a3b8" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"/>
                                        <polyline points="0,16 15,15 28,17 42,16 55,15 68,16 80,16 80,32 0,32"
                                            fill="#94a3b8" fill-opacity="0.06" stroke="none"/>
                                    </svg>
                                @endif
                            </div>
                            @if ($diff === null)
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                                             bg-slate-100 text-slate-500 shrink-0">No data</span>
                            @elseif ($isAbove)
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold
                                             bg-rose-100 text-rose-700 shrink-0">↑ +₱{{ number_format($diff, 2) }}</span>
                            @elseif ($isBelow)
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold
                                             bg-emerald-50 text-emerald-700 shrink-0">↓ −₱{{ number_format(abs($diff), 2) }}</span>
                            @else
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold
                                             bg-brand/10 text-brand shrink-0">✓ At SRP</span>
                            @endif
                        </div>

                        {{-- Establishment & agency --}}
                        @if ($estName || $v->commodity->category?->agency)
                        <div class="pt-3 border-t border-slate-100 space-y-1.5">
                            @if ($estName)
                            <div class="flex items-center gap-2 text-sm text-slate-500">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                    <polyline stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        points="9 22 9 12 15 12 15 22"/>
                                </svg>
                                <span class="truncate text-xs font-medium">{{ $estName }}</span>
                            </div>
                            @endif
                            @if ($v->commodity->category?->agency)
                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Monitored by {{ $v->commodity->category->agency->name }}
                            </div>
                            @endif
                        </div>
                        @endif

                        {{-- Compare hint --}}
                        <div class="flex items-center justify-center gap-1.5 text-xs text-slate-400
                                    group-hover:text-brand transition-colors pt-1 mt-auto">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Click to compare establishments
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if ($variants->hasPages())
        <div class="mt-8 flex flex-wrap items-center justify-between gap-4">
            <span class="text-xs text-slate-500">
                Showing <strong>{{ $variants->firstItem() }}–{{ $variants->lastItem() }}</strong>
                of <strong>{{ $variants->total() }}</strong> items
            </span>
            <div class="flex items-center gap-1.5 flex-wrap">
                @if (!$variants->onFirstPage())
                    <a href="{{ $variants->previousPageUrl() }}"
                       class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-sm font-semibold
                              text-slate-600 hover:border-brand hover:text-brand transition-all">← Prev</a>
                @endif
                @foreach ($variants->getUrlRange(max(1, $variants->currentPage() - 2), min($variants->lastPage(), $variants->currentPage() + 2)) as $page => $url)
                    <a href="{{ $url }}"
                       class="px-3.5 py-2 rounded-xl border text-sm font-semibold transition-all
                              {{ $page == $variants->currentPage()
                                  ? 'bg-brand border-brand text-white shadow-sm'
                                  : 'border-slate-200 bg-white text-slate-600 hover:border-brand hover:text-brand' }}">
                        {{ $page }}
                    </a>
                @endforeach
                @if ($variants->hasMorePages())
                    <a href="{{ $variants->nextPageUrl() }}"
                       class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-sm font-semibold
                              text-slate-600 hover:border-brand hover:text-brand transition-all">Next →</a>
                @endif
            </div>
        </div>
        @endif

    @endif
</main>

{{-- ══ FOOTER ════════════════════════════════════════════════════════ --}}
<footer class="bg-brand border-t-4 border-brand-dark mt-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-white font-black text-sm">SCCPresyo · San Carlos City</p>
            <p class="text-green-300 text-xs mt-0.5">
                Powered by <span class="text-green-200 font-bold">NegosyoTrack</span> · Negosyo Center
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach ($agencies as $ag)
            <span class="bg-white/10 border border-white/20 rounded-lg px-3 py-1.5 text-xs font-bold text-green-200">
                {{ $ag->code ?? $ag->name }}
            </span>
            @endforeach
        </div>
    </div>
</footer>

{{-- ══ DETAIL MODAL ══════════════════════════════════════════════════ --}}
<div id="detail-overlay"
     class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 sm:p-6">
    <div id="detail-modal"
         class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[92vh] flex flex-col overflow-hidden"
         role="dialog" aria-modal="true" aria-labelledby="modal-title">

        {{-- Modal header --}}
        <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-200 shrink-0">
            <div class="min-w-0">
                <div id="modal-category-badge"
                     class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                            bg-brand/10 text-brand mb-1.5"></div>
                <h2 id="modal-title" class="text-xl font-black text-slate-900 leading-snug"></h2>
                <p id="modal-subtitle" class="text-sm text-slate-500 mt-0.5"></p>
            </div>
            <button id="modal-close"
                class="shrink-0 p-2 rounded-xl hover:bg-slate-100 transition-colors
                       text-slate-400 hover:text-slate-700 mt-0.5"
                aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Modal body --}}
        <div class="flex-1 overflow-y-auto">

            {{-- Loading --}}
            <div id="modal-loading" class="flex flex-col items-center justify-center py-24 gap-3">
                <div class="w-10 h-10 rounded-full border-[3px] border-slate-200 border-t-brand animate-spin"></div>
                <p class="text-sm text-slate-400">Loading price data…</p>
            </div>

            {{-- Error --}}
            <div id="modal-error" class="hidden flex-col items-center justify-center py-24 gap-3 text-center px-6">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 flex items-center justify-center mb-1">
                    <svg class="w-7 h-7 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">Failed to load price data</p>
                <p class="text-xs text-slate-400">Please try again.</p>
            </div>

            {{-- Content --}}
            <div id="modal-content" class="hidden">

                {{-- Summary bar --}}
                <div id="modal-summary"
                     class="grid grid-cols-3 divide-x divide-slate-200 border-b border-slate-200 bg-slate-50">
                </div>

                <div class="px-6 py-5 space-y-6">

                    {{-- Establishments --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">
                                Price by Establishment
                            </h3>
                            <span id="modal-unit-label" class="text-xs text-slate-400 font-normal normal-case"></span>
                        </div>
                        <div id="modal-establishments" class="space-y-2"></div>
                    </div>

                    {{-- Chart --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                            </svg>
                            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">Price Trend</h3>
                            <span id="modal-chart-label" class="text-xs text-slate-400 font-normal normal-case ml-auto"></span>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80">
                            <div class="relative h-48">
                                <canvas id="price-chart"></canvas>
                            </div>
                            <div id="no-chart-data"
                                 class="hidden flex-col items-center justify-center h-48 gap-2 text-center">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <p class="text-xs text-slate-400">No price history available yet.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ FAVORITES MODAL ══════════════════════════════════════════════ --}}
<div id="favorites-overlay"
     class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 sm:p-6">
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden"
         role="dialog" aria-modal="true" aria-labelledby="favorites-modal-title">

        {{-- Header --}}
        <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-slate-200 shrink-0 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <div>
                    <h2 id="favorites-modal-title" class="text-base font-black text-slate-900">My Favorites</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Commodities you've starred</p>
                </div>
            </div>
            <button id="favorites-modal-close"
                class="p-2 rounded-xl hover:bg-slate-100 transition-colors text-slate-400 hover:text-slate-700"
                aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div id="favorites-modal-body" class="flex-1 overflow-y-auto p-5 space-y-3"></div>

        {{-- Footer --}}
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 shrink-0 flex items-center justify-between gap-3">
            <span id="favorites-modal-count" class="text-xs text-slate-500 font-medium"></span>
            <button id="favorites-clear-all"
                class="hidden text-xs font-semibold text-rose-500 hover:text-rose-700 transition-colors px-3 py-1.5
                       rounded-lg hover:bg-rose-50">
                Clear all favorites
            </button>
        </div>
    </div>
</div>

{{-- ══ ABOUT SRP MODAL ══════════════════════════════════════════════ --}}
<div id="srp-modal-overlay"
     class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 sm:p-6">
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden"
         role="dialog" aria-modal="true">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0 bg-brand">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-white font-black text-base">About Suggested Retail Price (SRP)</h2>
            </div>
            <button id="srp-modal-close"
                class="p-1.5 rounded-lg bg-white/10 hover:bg-white/25 transition-colors text-white"
                aria-label="Close">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5 text-sm text-slate-600 leading-relaxed">

            <div class="bg-brand/8 border border-brand/20 rounded-xl p-4">
                <p class="font-black text-brand text-sm mb-1">What is SRP?</p>
                <p>The <strong class="text-slate-800">Suggested Retail Price (SRP)</strong> is the maximum price set by the government for basic necessities and prime commodities. Retailers are legally prohibited from selling above the SRP.</p>
            </div>

            <div class="space-y-3">
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">Consumer Protection</p>
                        <p class="text-xs text-slate-500 mt-0.5">SRP protects consumers from price manipulation and ensures fair pricing, especially during calamities or supply shortages.</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">Monitoring Agencies</p>
                        <p class="text-xs text-slate-500 mt-0.5">Prices are set and monitored by the <strong class="text-slate-700">Department of Trade and Industry (DTI)</strong>, Department of Agriculture (DA), Department of Energy (DOE), and other relevant government agencies.</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-200 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">Prevailing Price</p>
                        <p class="text-xs text-slate-500 mt-0.5">The <strong class="text-slate-700">Prevailing Price</strong> is the actual observed market price at a specific establishment. It may be lower or equal to the SRP — if it is higher, the retailer may be in violation.</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-lg bg-brand/10 border border-brand/20 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">San Carlos City Coverage</p>
                        <p class="text-xs text-slate-500 mt-0.5">This platform monitors prices across establishments in <strong class="text-slate-700">San Carlos City, Negros Occidental</strong>, as part of the Negosyo Center's price transparency initiative.</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-500">
                <p class="font-bold text-slate-700 mb-1">Report a Violation</p>
                <p>If you see a price above the SRP, report it to the <strong>DTI Consumer Hotline: 1-DTI (1-384)</strong> or visit the nearest Negosyo Center.</p>
            </div>
        </div>
    </div>
</div>

{{-- ══ DISCLAIMER MODAL ══════════════════════════════════════════════ --}}
<div id="disclaimer-modal-overlay"
     class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 sm:p-6">
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden"
         role="dialog" aria-modal="true">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 shrink-0 bg-amber-500">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h2 class="text-white font-black text-base">Disclaimer</h2>
            </div>
            <button id="disclaimer-modal-close"
                class="p-1.5 rounded-lg bg-white/10 hover:bg-white/25 transition-colors text-white"
                aria-label="Close">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4 text-sm text-slate-600 leading-relaxed">

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <p class="font-black text-amber-700 text-sm mb-1.5">⚠ Important Notice</p>
                <p class="text-xs text-amber-700">Prices listed on this platform are <strong>Suggested Retail Prices (SRP)</strong> issued by government agencies as consumer protection guidelines. Actual market prices may vary.</p>
            </div>

            <div class="space-y-3 text-xs text-slate-600">
                <div class="flex gap-2.5">
                    <span class="text-amber-500 font-black shrink-0 mt-0.5">01</span>
                    <p><strong class="text-slate-800">Informational Purpose Only.</strong> The data provided is for consumer guidance and transparency. It does not constitute a guarantee that prices listed will be found at any specific store at any given time.</p>
                </div>
                <div class="flex gap-2.5">
                    <span class="text-amber-500 font-black shrink-0 mt-0.5">02</span>
                    <p><strong class="text-slate-800">Price Variations.</strong> Actual prevailing prices may differ per establishment, location, and prevailing market conditions including supply, demand, and seasonal factors.</p>
                </div>
                <div class="flex gap-2.5">
                    <span class="text-amber-500 font-black shrink-0 mt-0.5">03</span>
                    <p><strong class="text-slate-800">Data Currency.</strong> While we strive to keep pricing data current, there may be a delay between actual price changes and updates reflected on this platform.</p>
                </div>
                <div class="flex gap-2.5">
                    <span class="text-amber-500 font-black shrink-0 mt-0.5">04</span>
                    <p><strong class="text-slate-800">No Liability.</strong> San Carlos City LGU, the Negosyo Center, and NegosyoTrack shall not be held liable for any decisions made based solely on the information provided on this platform.</p>
                </div>
                <div class="flex gap-2.5">
                    <span class="text-amber-500 font-black shrink-0 mt-0.5">05</span>
                    <p><strong class="text-slate-800">Government Data Source.</strong> SRP data is sourced from official issuances by DTI, DA, DOE, and other authorized government agencies. For the most authoritative and latest SRPs, refer to the respective agency's official publications.</p>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-500">
                <p class="font-bold text-slate-700 mb-1">Last Updated</p>
                <p>{{ now()->format('F j, Y \a\t g:i A') }} · Data is updated by monitoring officers of the San Carlos City Negosyo Center.</p>
            </div>
        </div>

        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 shrink-0">
            <button id="disclaimer-modal-accept"
                class="w-full py-2.5 rounded-xl bg-brand text-white text-sm font-bold hover:bg-brand-dark transition-colors">
                I Understand
            </button>
        </div>
    </div>
</div>

{{-- ══ JAVASCRIPT ════════════════════════════════════════════════════ --}}
<script>
(function () {
    'use strict';

    const DETAIL_BASE = '{{ url("/sccpresyo/detail") }}';
    const STORAGE_KEY = 'scc_watchlist_v2';

    // ── State ──────────────────────────────────────────────────────
    let watchlist = new Set(JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'));
    let activeTab = 'all';
    let priceChartInstance = null;

    // ── DOM refs ───────────────────────────────────────────────────
    const cardsGrid      = document.getElementById('cards-grid');
    const allCards       = cardsGrid ? Array.from(cardsGrid.querySelectorAll('.price-card')) : [];
    const tabAll         = document.getElementById('tab-all');
    const tabWatchlist   = document.getElementById('tab-watchlist');
    const watchlistBtn   = document.getElementById('watchlist-toggle-btn');
    const countBadge     = document.getElementById('watchlist-count');
    const watchlistEmpty = document.getElementById('watchlist-empty');
    const headerSearch   = document.getElementById('header-search');
    const mobileSearch   = document.getElementById('mobile-search');
    const drawer         = document.getElementById('watchlist-drawer');
    const drawerOverlay  = document.getElementById('drawer-overlay');
    const drawerClose    = document.getElementById('drawer-close');
    const drawerBody     = document.getElementById('drawer-body');
    const viewAllBtn     = document.getElementById('view-all-watchlist-btn');
    const detailOverlay  = document.getElementById('detail-overlay');
    const modalClose     = document.getElementById('modal-close');
    const modalLoading   = document.getElementById('modal-loading');
    const modalError     = document.getElementById('modal-error');
    const modalContent   = document.getElementById('modal-content');
    const modalTitle     = document.getElementById('modal-title');
    const modalSubtitle  = document.getElementById('modal-subtitle');
    const modalCategory  = document.getElementById('modal-category-badge');
    const modalSummary   = document.getElementById('modal-summary');
    const modalEstabs    = document.getElementById('modal-establishments');
    const modalUnitLabel = document.getElementById('modal-unit-label');
    const modalChartLabel= document.getElementById('modal-chart-label');
    const noChartData    = document.getElementById('no-chart-data');

    // ── Helpers ────────────────────────────────────────────────────
    function fmt(val) {
        return val != null
            ? '₱ ' + parseFloat(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : '—';
    }

    // ══════════════════════════════════════════════════════════════
    // DETAIL MODAL
    // ══════════════════════════════════════════════════════════════

    function renderSummary(summary) {
        const items = [
            { label: 'Lowest',  value: fmt(summary.lowest_price),  sub: summary.lowest_name  || '—', color: 'text-emerald-700' },
            { label: 'Average', value: fmt(summary.avg_price),     sub: `${summary.total_establishments} establishment${summary.total_establishments !== 1 ? 's' : ''}`, color: 'text-brand' },
            { label: 'Highest', value: fmt(summary.highest_price), sub: summary.highest_name || '—', color: 'text-rose-600' },
        ];
        modalSummary.innerHTML = items.map(i => `
            <div class="px-5 py-4 text-center">
                <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-1 font-semibold">${i.label}</p>
                <p class="text-lg font-black ${i.color}">${i.value}</p>
                <p class="text-[10px] text-slate-400 mt-0.5 truncate">${i.sub}</p>
            </div>`).join('');
    }

    function renderEstablishments(establishments, variant) {
        if (!modalEstabs) return;
        modalEstabs.innerHTML = '';
        if (modalUnitLabel) modalUnitLabel.textContent = variant.unit ? `· per ${variant.unit}` : '';

        if (!establishments.length) {
            modalEstabs.innerHTML = `<p class="text-sm text-slate-400 text-center py-6">No establishment data found.</p>`;
            return;
        }

        const withPrice   = establishments.filter(e => e.prevailing_price != null);
        const lowestPrice = withPrice.length ? Math.min(...withPrice.map(e => parseFloat(e.prevailing_price))) : null;

        const statusColors = {
            above:   { price: 'text-rose-600',    tag: 'bg-rose-50 text-rose-700 border-rose-200',         wrap: 'bg-white border-slate-200' },
            below:   { price: 'text-emerald-700', tag: 'bg-emerald-50 text-emerald-700 border-emerald-200',wrap: 'bg-white border-slate-200' },
            equal:   { price: 'text-brand',       tag: 'bg-brand/10 text-brand border-brand/20',           wrap: 'bg-white border-slate-200' },
            no_data: { price: 'text-slate-400',   tag: 'bg-slate-100 text-slate-500 border-slate-200',     wrap: 'bg-white border-slate-200' },
        };
        const statusLabel = { above: '↑ Above SRP', below: '↓ Below SRP', equal: '✓ At SRP', no_data: 'No data' };

        establishments.forEach((e, idx) => {
            const isLowest  = lowestPrice !== null && e.prevailing_price != null
                           && parseFloat(e.prevailing_price) === lowestPrice && withPrice.length > 1;
            const sc        = statusColors[e.status] ?? statusColors.no_data;
            const wrapClass = isLowest ? 'bg-brand/5 border-brand/20' : `${sc.wrap} hover:bg-slate-50`;

            const row = document.createElement('div');
            row.className = `rounded-xl border transition-colors duration-150 overflow-hidden ${wrapClass}`;
            row.innerHTML = `
                <div class="p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="shrink-0 w-6 h-6 rounded-md flex items-center justify-center text-[11px] font-black
                            ${isLowest ? 'bg-brand text-white' : 'bg-slate-100 text-slate-500'}">${idx + 1}</span>
                        <p class="flex-1 font-bold text-slate-900 text-sm leading-snug truncate">${e.establishment}</p>
                        <span class="shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-md border ${sc.tag}">${statusLabel[e.status] ?? 'No data'}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 ml-9">
                        <div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">SRP</p>
                            <p class="text-base font-black text-slate-700">${fmt(e.srp)}</p>
                        </div>
                        <div class="rounded-xl px-3 py-2.5 border
                            ${e.status === 'above' ? 'bg-rose-50 border-rose-200'
                            : e.status === 'below' ? 'bg-emerald-50 border-emerald-200'
                            : e.status === 'equal' ? 'bg-brand/5 border-brand/20'
                            : 'bg-slate-50 border-slate-200'}">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Prevailing</p>
                            <p class="text-base font-black ${sc.price}">${fmt(e.prevailing_price)}</p>
                        </div>
                    </div>
                </div>`;
            modalEstabs.appendChild(row);
        });
    }

    function renderChart(chart) {
        if (priceChartInstance) { priceChartInstance.destroy(); priceChartInstance = null; }
        const canvas = document.getElementById('price-chart');
        if (!canvas) return;

        if (!chart.data || !chart.data.length) {
            canvas.parentElement.classList.add('hidden');
            if (noChartData) { noChartData.classList.remove('hidden'); noChartData.classList.add('flex'); }
            if (modalChartLabel) modalChartLabel.textContent = '';
            return;
        }

        canvas.parentElement.classList.remove('hidden');
        if (noChartData) { noChartData.classList.add('hidden'); noChartData.classList.remove('flex'); }
        if (modalChartLabel) modalChartLabel.textContent = chart.has_history
            ? `${chart.data.length} data point${chart.data.length !== 1 ? 's' : ''}`
            : 'Current prices by establishment';

        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 180);
        gradient.addColorStop(0, 'rgba(24,107,2,0.18)');
        gradient.addColorStop(1, 'rgba(24,107,2,0)');

        priceChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chart.labels,
                datasets: [{
                    label: 'Prevailing Price (₱)',
                    data: chart.data,
                    borderColor: '#186b02',
                    borderWidth: 2.5,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointRadius: chart.data.length <= 10 ? 5 : 3,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#186b02',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#94a3b8',
                        bodyColor: '#f1f5f9',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => ' ₱ ' + parseFloat(ctx.raw).toLocaleString('en-PH', { minimumFractionDigits: 2 }),
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 11 }, maxRotation: 45 },
                        border: { display: false },
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            color: '#94a3b8', font: { size: 11 },
                            callback: v => '₱' + parseFloat(v).toLocaleString('en-PH', { minimumFractionDigits: 2 }),
                        },
                        border: { display: false },
                    },
                },
            },
        });
    }

    async function openDetailModal(variantId) {
        if (!detailOverlay) return;
        detailOverlay.classList.remove('hidden');
        detailOverlay.classList.add('flex');
        document.body.style.overflow = 'hidden';

        if (modalLoading) modalLoading.classList.remove('hidden');
        if (modalError)   { modalError.classList.add('hidden'); modalError.classList.remove('flex'); }
        if (modalContent) modalContent.classList.add('hidden');
        if (modalTitle)   modalTitle.textContent = 'Loading…';
        if (modalSubtitle) modalSubtitle.textContent = '';
        if (modalCategory) modalCategory.textContent = '';

        try {
            const res  = await fetch(`${DETAIL_BASE}/${variantId}`);
            if (!res.ok) throw new Error('bad response');
            const data = await res.json();

            if (modalCategory) modalCategory.textContent = data.variant.category || 'General';
            if (modalTitle)    modalTitle.textContent    = data.variant.product_name;
            if (modalSubtitle) modalSubtitle.textContent = [
                data.variant.brand,
                data.variant.unit,
                data.variant.agency ? `· Monitored by ${data.variant.agency}` : null,
            ].filter(Boolean).join(' · ');

            renderSummary(data.summary);
            renderEstablishments(data.establishments, data.variant);
            renderChart(data.chart);

            if (modalLoading) modalLoading.classList.add('hidden');
            if (modalContent) modalContent.classList.remove('hidden');

        } catch {
            if (modalLoading) modalLoading.classList.add('hidden');
            if (modalError)   { modalError.classList.remove('hidden'); modalError.classList.add('flex'); }
        }
    }

    function closeDetailModal() {
        if (!detailOverlay) return;
        detailOverlay.classList.add('hidden');
        detailOverlay.classList.remove('flex');
        document.body.style.overflow = '';
        if (priceChartInstance) { priceChartInstance.destroy(); priceChartInstance = null; }
    }

    // Card click
    allCards.forEach(card => {
        card.addEventListener('click', e => {
            if (e.target.closest('.star-btn')) return;
            openDetailModal(card.dataset.id);
        });
        card.addEventListener('keydown', e => {
            if ((e.key === 'Enter' || e.key === ' ') && !e.target.closest('.star-btn')) {
                e.preventDefault();
                openDetailModal(card.dataset.id);
            }
        });
    });

    modalClose?.addEventListener('click', closeDetailModal);
    detailOverlay?.addEventListener('click', e => { if (e.target === detailOverlay) closeDetailModal(); });

    // ══════════════════════════════════════════════════════════════
    // WATCHLIST DRAWER
    // ══════════════════════════════════════════════════════════════

    function openDrawer() { renderDrawer(); drawer.classList.add('open'); drawerOverlay.classList.add('open'); document.body.style.overflow = 'hidden'; }
    function closeDrawer() { drawer?.classList.remove('open'); drawerOverlay?.classList.remove('open'); document.body.style.overflow = ''; }

    function renderDrawer() {
        if (!drawerBody) return;
        drawerBody.innerHTML = '';

        if (watchlist.size === 0) {
            drawerBody.innerHTML = `
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-3">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-600">No starred items yet</p>
                    <p class="text-xs text-slate-400 mt-1">Tap ★ on any card to add it here.</p>
                </div>`;
            return;
        }

        allCards.forEach(card => {
            const id = card.dataset.id;
            if (!watchlist.has(id)) return;

            const product    = card.dataset.product || '—';
            const brand      = card.dataset.brand   || '';
            const unit       = card.dataset.unit    || '';
            const srpVal     = card.dataset.srp     ? parseFloat(card.dataset.srp)        : null;
            const prevVal    = card.dataset.prevailing ? parseFloat(card.dataset.prevailing) : null;
            const category   = card.dataset.category || '';
            const srpDisplay = srpVal  != null ? '₱ ' + srpVal.toFixed(2) : '—';
            const prvDisplay = prevVal != null ? '₱ ' + prevVal.toFixed(2) : '—';

            let priceBadge = '';
            if (prevVal !== null && srpVal !== null) {
                const diff = prevVal - srpVal;
                priceBadge = diff > 0
                    ? `<span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">↑ ₱${diff.toFixed(2)}</span>`
                    : diff < 0
                        ? `<span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">↓ ₱${Math.abs(diff).toFixed(2)}</span>`
                        : `<span class="text-xs font-bold text-brand bg-brand/10 px-2 py-0.5 rounded-full">✓ At SRP</span>`;
            }

            const item = document.createElement('div');
            item.className = 'bg-slate-50 rounded-xl p-3.5 border border-slate-200/80 cursor-pointer hover:border-brand/30 hover:bg-brand/5 transition-all';
            item.innerHTML = `
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0">
                        ${category ? `<span class="text-[10px] font-bold text-brand bg-brand/10 px-2 py-0.5 rounded-full">${category}</span>` : ''}
                        <p class="font-bold text-slate-900 text-sm mt-1 leading-snug">${product}</p>
                        <p class="text-xs text-slate-400">${[brand, unit].filter(Boolean).join(' · ')}</p>
                    </div>
                    <button class="drawer-unstar shrink-0 p-1.5 rounded-lg hover:bg-rose-50 transition-colors"
                            data-id="${id}" title="Remove from watchlist">
                        <svg class="w-4 h-4" fill="#186b02" stroke="#186b02" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </button>
                </div>
                <div class="flex items-center justify-between flex-wrap gap-1">
                    <p class="text-xs text-slate-500">
                        SRP <span class="font-semibold text-slate-700">${srpDisplay}</span>
                        · Now <span class="font-bold text-slate-900">${prvDisplay}</span>
                    </p>
                    ${priceBadge}
                </div>`;

            item.addEventListener('click', e => {
                if (e.target.closest('.drawer-unstar')) return;
                closeDrawer(); openDetailModal(id);
            });

            item.querySelector('.drawer-unstar').addEventListener('click', function (e) {
                e.stopPropagation();
                watchlist.delete(this.dataset.id);
                saveWatchlist(); syncStarButtons(); updateCountBadge();
                item.remove();
                if (watchlist.size === 0) renderDrawer();
                if (activeTab === 'watchlist') applyTabAndSearch();
            });

            drawerBody.appendChild(item);
        });
    }

    watchlistBtn?.addEventListener('click', openFavoritesModal);
    drawerClose?.addEventListener('click', closeDrawer);
    drawerOverlay?.addEventListener('click', e => { if (e.target === drawerOverlay) closeDrawer(); });
    viewAllBtn?.addEventListener('click', () => {
        closeDrawer();
        setActiveTab('watchlist');
        window.scrollTo({ top: (cardsGrid?.offsetTop ?? 0) - 140, behavior: 'smooth' });
    });

    // ══════════════════════════════════════════════════════════════
    // WATCHLIST STATE
    // ══════════════════════════════════════════════════════════════

    function saveWatchlist() {
        localStorage.setItem(STORAGE_KEY, JSON.stringify([...watchlist]));
    }

    function updateCountBadge() {
        if (!countBadge) return;
        const n = watchlist.size;
        countBadge.textContent = n;
        countBadge.classList.toggle('hidden', n === 0);
        countBadge.classList.toggle('flex', n > 0);
    }

    function syncStarButtons() {
        document.querySelectorAll('.star-btn').forEach(btn => {
            const starred = watchlist.has(btn.dataset.id);
            btn.classList.toggle('starred', starred);
            btn.setAttribute('aria-label', starred ? 'Remove from watchlist' : 'Add to watchlist');
            btn.title = starred ? 'Remove from watchlist' : 'Add to watchlist';
        });
    }

    document.querySelectorAll('.star-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.stopPropagation();
            const id = btn.dataset.id;
            if (watchlist.has(id)) {
                watchlist.delete(id);
            } else {
                watchlist.add(id);
                btn.querySelector('svg').animate(
                    [{ transform: 'scale(1)' }, { transform: 'scale(1.5)' }, { transform: 'scale(1)' }],
                    { duration: 280, easing: 'ease-out' }
                );
            }
            saveWatchlist(); syncStarButtons(); updateCountBadge();
            if (activeTab === 'watchlist') applyTabAndSearch();
        });
    });

    // ══════════════════════════════════════════════════════════════
    // TAB + SEARCH FILTER
    // ══════════════════════════════════════════════════════════════

    function applyTabAndSearch() {
        const query = ((headerSearch?.value || '') + (mobileSearch?.value || '')).toLowerCase().trim();
        let visible = 0;
        allCards.forEach(card => {
            const show = (activeTab === 'all' || watchlist.has(card.dataset.id))
                      && (!query || (card.dataset.name || '').includes(query));
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (watchlistEmpty) {
            const empty = activeTab === 'watchlist' && visible === 0;
            watchlistEmpty.classList.toggle('hidden', !empty);
            if (cardsGrid) cardsGrid.style.display = empty ? 'none' : '';
        }
    }

    function setActiveTab(tab) {
        activeTab = tab;
        const isAll = tab === 'all';
        // Active: bg-brand text-white shadow-sm
        // Inactive: border border-slate-200 text-slate-600 bg-white
        [tabAll, tabWatchlist].forEach((btn, i) => {
            if (!btn) return;
            const active = (i === 0 && isAll) || (i === 1 && !isAll);
            btn.className = btn.className
                .replace(/bg-brand\s*|text-white\s*|shadow-sm\s*|border\s+border-slate-200\s*|text-slate-600\s*|bg-white\s*/g, '')
                .trim();
            if (active) {
                btn.classList.add('bg-brand', 'text-white', 'shadow-sm');
                btn.classList.remove('border', 'border-slate-200', 'text-slate-600', 'bg-white');
            } else {
                btn.classList.add('border', 'border-slate-200', 'text-slate-600', 'bg-white');
                btn.classList.remove('bg-brand', 'text-white', 'shadow-sm');
            }
        });
        applyTabAndSearch();
    }

    tabAll?.addEventListener('click', () => setActiveTab('all'));
    tabWatchlist?.addEventListener('click', () => setActiveTab('watchlist'));

    let searchTimer;
    function onSearch(e) {
        clearTimeout(searchTimer);
        if (headerSearch) headerSearch.value = e.target.value;
        if (mobileSearch) mobileSearch.value = e.target.value;
        searchTimer = setTimeout(applyTabAndSearch, 160);
    }
    headerSearch?.addEventListener('input', onSearch);
    mobileSearch?.addEventListener('input', onSearch);

    // Keyboard: Escape closes modals
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeDetailModal();
            closeDrawer();
            closeFavoritesModal();
            closeSrpModal();
            closeDisclaimerModal();
            closeSidebar();
        }
    });

    // ══════════════════════════════════════════════════════════════
    // FAVORITES MODAL
    // ══════════════════════════════════════════════════════════════

    const favOverlay     = document.getElementById('favorites-overlay');
    const favModalBody   = document.getElementById('favorites-modal-body');
    const favModalClose  = document.getElementById('favorites-modal-close');
    const favModalCount  = document.getElementById('favorites-modal-count');
    const favClearAll    = document.getElementById('favorites-clear-all');

    function openFavoritesModal() {
        renderFavoritesModal();
        favOverlay.classList.remove('hidden');
        favOverlay.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeFavoritesModal() {
        favOverlay.classList.add('hidden');
        favOverlay.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function renderFavoritesModal() {
        if (!favModalBody) return;
        favModalBody.innerHTML = '';

        const n = watchlist.size;
        if (favModalCount) {
            favModalCount.textContent = n === 0 ? 'No favorites yet' : `${n} item${n !== 1 ? 's' : ''} starred`;
        }
        if (favClearAll) {
            favClearAll.classList.toggle('hidden', n === 0);
        }

        if (n === 0) {
            favModalBody.innerHTML = `
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-brand/10 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <p class="text-base font-bold text-slate-700">No favorites yet</p>
                    <p class="text-sm text-slate-400 mt-1">Tap the ★ on any commodity card to save it here.</p>
                </div>`;
            return;
        }

        allCards.forEach(card => {
            const id = card.dataset.id;
            if (!watchlist.has(id)) return;

            const product    = card.dataset.product    || '—';
            const brand      = card.dataset.brand      || '';
            const unit       = card.dataset.unit       || '';
            const category   = card.dataset.category   || '';
            const srpVal     = card.dataset.srp        ? parseFloat(card.dataset.srp)         : null;
            const prevVal    = card.dataset.prevailing ? parseFloat(card.dataset.prevailing)  : null;
            const srpDisplay = srpVal  != null ? '₱ ' + srpVal.toFixed(2)  : '—';
            const prvDisplay = prevVal != null ? '₱ ' + prevVal.toFixed(2) : '—';

            let statusBadge = '<span class="text-xs text-slate-400">No price data</span>';
            if (prevVal !== null && srpVal !== null) {
                const diff = prevVal - srpVal;
                if (diff > 0)      statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700">↑ Above SRP &nbsp;+₱${diff.toFixed(2)}</span>`;
                else if (diff < 0) statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">↓ Below SRP &nbsp;−₱${Math.abs(diff).toFixed(2)}</span>`;
                else               statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-brand/10 text-brand">✓ At SRP</span>`;
            }

            const row = document.createElement('div');
            row.className = 'flex items-start gap-3 bg-slate-50 hover:bg-brand/5 border border-slate-200 hover:border-brand/30 rounded-xl p-4 transition-all cursor-pointer group';
            row.innerHTML = `
                <div class="flex-1 min-w-0">
                    ${category ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand/10 text-brand mb-1.5">${category}</span>` : ''}
                    <p class="font-black text-slate-900 text-sm leading-snug group-hover:text-brand transition-colors">${product}</p>
                    <p class="text-xs text-slate-400 mt-0.5">${[brand, unit].filter(Boolean).join(' · ')}</p>
                    <div class="flex flex-wrap items-center gap-3 mt-2.5">
                        <div class="text-center">
                            <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400">SRP</p>
                            <p class="text-sm font-bold text-slate-700">${srpDisplay}</p>
                        </div>
                        <div class="w-px h-6 bg-slate-200"></div>
                        <div class="text-center">
                            <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Prevailing</p>
                            <p class="text-sm font-black ${prevVal !== null && srpVal !== null ? (prevVal > srpVal ? 'text-rose-600' : (prevVal < srpVal ? 'text-emerald-700' : 'text-brand')) : 'text-slate-400'}">${prvDisplay}</p>
                        </div>
                        <div class="ml-1">${statusBadge}</div>
                    </div>
                </div>
                <div class="flex flex-col items-center gap-2 shrink-0">
                    <button class="fav-view-btn p-2 rounded-xl bg-white border border-slate-200 hover:border-brand hover:text-brand text-slate-400 transition-all" data-id="${id}" title="View details">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                    <button class="fav-remove-btn p-2 rounded-xl bg-white border border-slate-200 hover:border-rose-300 hover:text-rose-500 text-slate-400 transition-all" data-id="${id}" title="Remove from favorites">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>`;

            // View details
            row.querySelector('.fav-view-btn').addEventListener('click', e => {
                e.stopPropagation();
                closeFavoritesModal();
                openDetailModal(id);
            });
            // Remove from favorites
            row.querySelector('.fav-remove-btn').addEventListener('click', e => {
                e.stopPropagation();
                watchlist.delete(id);
                saveWatchlist(); syncStarButtons(); updateCountBadge(); syncSidebarFavCount();
                row.remove();
                // re-render count
                const remaining = favModalBody.querySelectorAll('[data-fav-row]').length;
                if (watchlist.size === 0) renderFavoritesModal();
                else if (favModalCount) favModalCount.textContent = `${watchlist.size} item${watchlist.size !== 1 ? 's' : ''} starred`;
                if (favClearAll) favClearAll.classList.toggle('hidden', watchlist.size === 0);
                if (activeTab === 'watchlist') applyTabAndSearch();
            });
            // Row click → view details
            row.addEventListener('click', () => { closeFavoritesModal(); openDetailModal(id); });

            favModalBody.appendChild(row);
        });
    }

    // Clear all
    favClearAll?.addEventListener('click', () => {
        watchlist.clear();
        saveWatchlist(); syncStarButtons(); updateCountBadge(); syncSidebarFavCount();
        renderFavoritesModal();
        if (activeTab === 'watchlist') applyTabAndSearch();
    });

    favModalClose?.addEventListener('click', closeFavoritesModal);
    favOverlay?.addEventListener('click', e => { if (e.target === favOverlay) closeFavoritesModal(); });

    // ══════════════════════════════════════════════════════════════
    // ABOUT SRP MODAL
    // ══════════════════════════════════════════════════════════════

    const srpModalOverlay = document.getElementById('srp-modal-overlay');
    const srpModalClose   = document.getElementById('srp-modal-close');

    function openSrpModal()   { srpModalOverlay?.classList.remove('hidden'); srpModalOverlay?.classList.add('flex'); document.body.style.overflow = 'hidden'; }
    function closeSrpModal()  { srpModalOverlay?.classList.add('hidden'); srpModalOverlay?.classList.remove('flex'); document.body.style.overflow = ''; }

    srpModalClose?.addEventListener('click', closeSrpModal);
    srpModalOverlay?.addEventListener('click', e => { if (e.target === srpModalOverlay) closeSrpModal(); });

    // ══════════════════════════════════════════════════════════════
    // DISCLAIMER MODAL
    // ══════════════════════════════════════════════════════════════

    const disclaimerOverlay = document.getElementById('disclaimer-modal-overlay');
    const disclaimerClose   = document.getElementById('disclaimer-modal-close');
    const disclaimerAccept  = document.getElementById('disclaimer-modal-accept');

    function openDisclaimerModal()  { disclaimerOverlay?.classList.remove('hidden'); disclaimerOverlay?.classList.add('flex'); document.body.style.overflow = 'hidden'; }
    function closeDisclaimerModal() { disclaimerOverlay?.classList.add('hidden'); disclaimerOverlay?.classList.remove('flex'); document.body.style.overflow = ''; }

    disclaimerClose?.addEventListener('click', closeDisclaimerModal);
    disclaimerAccept?.addEventListener('click', closeDisclaimerModal);
    disclaimerOverlay?.addEventListener('click', e => { if (e.target === disclaimerOverlay) closeDisclaimerModal(); });

    // ── Init ───────────────────────────────────────────────────────
    syncStarButtons();
    updateCountBadge();
    applyTabAndSearch();

    // ══════════════════════════════════════════════════════════════
    // SIDEBAR
    // ══════════════════════════════════════════════════════════════

    const sidebarPanel      = document.getElementById('sidebar-panel');
    const sidebarOverlay    = document.getElementById('sidebar-overlay');
    const sidebarToggleBtn  = document.getElementById('sidebar-toggle-btn');
    const sidebarCloseBtn   = document.getElementById('sidebar-close-btn');
    const sidebarFavBtn     = document.getElementById('sidebar-favorites-btn');
    const sidebarFavCount   = document.getElementById('sidebar-fav-count');
    const catToggle         = document.getElementById('sidebar-categories-toggle');
    const catList           = document.getElementById('sidebar-categories-list');
    const catChevron        = document.getElementById('sidebar-cat-chevron');
    const agToggle          = document.getElementById('sidebar-agencies-toggle');
    const agList            = document.getElementById('sidebar-agencies-list');
    const agChevron         = document.getElementById('sidebar-ag-chevron');
    const sidebarSrpBtn     = document.getElementById('sidebar-srp-btn');
    const sidebarDiscBtn    = document.getElementById('sidebar-disclaimer-btn');

    function openSidebar() {
        sidebarPanel?.classList.add('open');
        sidebarOverlay?.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebarPanel?.classList.remove('open');
        sidebarOverlay?.classList.remove('open');
        document.body.style.overflow = '';
    }

    sidebarToggleBtn?.addEventListener('click', openSidebar);
    sidebarCloseBtn?.addEventListener('click', closeSidebar);
    sidebarOverlay?.addEventListener('click', e => { if (e.target === sidebarOverlay) closeSidebar(); });

    // Sidebar → My Favorites → open favorites modal
    sidebarFavBtn?.addEventListener('click', () => { closeSidebar(); openFavoritesModal(); })

    sidebarSrpBtn?.addEventListener('click',  () => { closeSidebar(); openSrpModal(); });
    sidebarDiscBtn?.addEventListener('click', () => { closeSidebar(); openDisclaimerModal(); });

    // Accordion: Categories
    catToggle?.addEventListener('click', () => {
        const open = !catList.classList.contains('hidden');
        catList.classList.toggle('hidden', open);
        catChevron?.classList.toggle('rotate-180', !open);
    });

    // Accordion: Agencies
    agToggle?.addEventListener('click', () => {
        const open = !agList.classList.contains('hidden');
        agList.classList.toggle('hidden', open);
        agChevron?.classList.toggle('rotate-180', !open);
    });

    // Sidebar scroll-to links close sidebar then scroll
    document.querySelectorAll('.sidebar-scroll-link').forEach(link => {
        link.addEventListener('click', () => { closeSidebar(); });
    });

    // Keep sidebar fav count in sync
    function syncSidebarFavCount() {
        if (!sidebarFavCount) return;
        const n = watchlist.size;
        sidebarFavCount.textContent = n;
        sidebarFavCount.classList.toggle('hidden', n === 0);
        sidebarFavCount.classList.toggle('inline-flex', n > 0);
    }

    // Patch existing watchlist functions to also update sidebar count
    const _origSave = saveWatchlist;
    // Wrap star button listener to also sync sidebar — done via mutation after init
    // Simpler: override updateCountBadge to also update sidebar
    const _origUpdateBadge = updateCountBadge;

    // Re-run sidebar sync on every star toggle by observing watchlist changes
    document.querySelectorAll('.star-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // slight delay so the Set has been updated
            setTimeout(syncSidebarFavCount, 10);
        });
    });

    // (Escape is handled globally above)

    syncSidebarFavCount();

})();
</script>
