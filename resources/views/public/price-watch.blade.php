@extends('layouts.public')

@section('title', 'San Carlos City e-Presyo')

@section('content')

{{-- ─── Hero & Filter Toolbar ──────────────────────────────────────── --}}
<div class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

        {{-- Title row --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#186b02]/10 text-[#186b02] text-xs font-semibold mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#186b02] animate-pulse inline-block"></span>
                    Live Monitoring
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 leading-tight">
                    San Carlos City e-Presyo
                </h1>
                <p class="mt-1.5 text-sm text-slate-500 max-w-xl">
                    Find out the Suggested Retail Price (SRP) and the current prices of basic commodities in San Carlos City. Click on any product to view its price across different establishments.
                </p>
            </div>

            {{-- Stats --}}
            <div class="flex items-center gap-3 shrink-0">
                <div class="text-center px-4 py-2 rounded-xl bg-slate-50 border border-slate-200/80">
                    <p class="text-xl font-bold text-slate-900">{{ number_format($totalMonitored) }}</p>
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider mt-0.5">Monitored</p>
                </div>
                <div class="text-center px-4 py-2 rounded-xl bg-[#186b02]/10 border border-[#186b02]/20">
                    <p class="text-xl font-bold text-[#186b02]">{{ number_format($withPrevailing) }}</p>
                    <p class="text-[10px] text-[#186b02]/70 uppercase tracking-wider mt-0.5">With Price</p>
                </div>
                <div class="text-center px-4 py-2 rounded-xl bg-amber-50 border border-amber-200/80">
                    <p class="text-xl font-bold text-amber-600">{{ number_format($totalMonitored - $withPrevailing) }}</p>
                    <p class="text-[10px] text-amber-500 uppercase tracking-wider mt-0.5">Pending</p>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ url('/sccpresyo') }}" id="filter-form">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[180px]">
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Category</label>
                    <select name="category" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#186b02] focus:border-transparent appearance-none cursor-pointer">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[180px]">
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Sort By</label>
                    <select name="sort" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#186b02] focus:border-transparent appearance-none cursor-pointer">
                        <option value="recent"     {{ request('sort', 'recent') === 'recent'     ? 'selected' : '' }}>Recently Updated</option>
                        <option value="price_asc"  {{ request('sort') === 'price_asc'            ? 'selected' : '' }}>Price: Low → High</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc'           ? 'selected' : '' }}>Price: High → Low</option>
                        <option value="name"       {{ request('sort') === 'name'                 ? 'selected' : '' }}>Name A → Z</option>
                    </select>
                </div>
                @if (request()->hasAny(['category', 'sort']))
                    <a href="{{ url('/sccpresyo') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-500 hover:border-slate-300 hover:text-slate-700 transition-all bg-white shadow-sm self-end">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Clear
                    </a>
                @endif
            </div>
        </form>

        {{-- Tabs --}}
        <div class="flex items-center gap-2 mt-5">
            <button id="tab-all"
                class="tab-btn inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 bg-[#186b02] text-white shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                All Commodities
            </button>
            <button id="tab-watchlist"
                class="tab-btn inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border border-slate-200 text-slate-600 hover:border-[#186b02] hover:text-[#186b02] bg-white">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                My Watchlist
            </button>

            {{-- Result count — formal placement beside tabs --}}
            <div class="ml-auto flex items-center gap-2 border border-slate-200 rounded-full px-3.5 py-1.5 bg-slate-50">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-xs font-semibold text-slate-600">{{ number_format($variants->total()) }}</span>
                <span class="text-xs text-slate-400">{{ Str::plural('commodity', $variants->total()) }} listed</span>
            </div>
        </div>
    </div>
</div>

{{-- ─── Cards Grid ─────────────────────────────────────────────────── --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    @if ($variants->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm py-20 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-slate-100 mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-slate-700">No commodities found</h3>
            <p class="mt-1 text-sm text-slate-400">Try adjusting your filters or search query.</p>
            <a href="{{ url('/sccpresyo') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#186b02] text-white text-sm font-medium hover:bg-[#145901] transition-colors">
                Clear all filters
            </a>
        </div>
    @else

        <div id="watchlist-empty" class="hidden bg-white rounded-2xl border border-slate-200/80 shadow-sm py-20 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#186b02]/10 mb-4">
                <svg class="w-8 h-8 text-[#186b02]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-slate-700">Your watchlist is empty</h3>
            <p class="mt-1 text-sm text-slate-400">Star any commodity card to add it here.</p>
        </div>

        <div id="cards-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($variants as $variant)
                @php
                    $prevailing = $variant->prevailing_price;
                    $srp        = $variant->srp;
                    $diff       = ($prevailing !== null && $srp !== null) ? $prevailing - $srp : null;
                    $pct        = ($diff !== null && $srp > 0) ? round(($diff / $srp) * 100, 1) : null;
                    $isAbove    = $diff !== null && $diff > 0;
                    $isBelow    = $diff !== null && $diff < 0;
                    $isEqual    = $diff !== null && $diff == 0;
                @endphp
                <div
                    id="card-{{ $variant->id }}"
                    class="price-card bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex flex-col overflow-hidden cursor-pointer group"
                    data-id="{{ $variant->id }}"
                    data-name="{{ strtolower($variant->commodity->product_name . ' ' . ($variant->commodity->brand->name ?? '')) }}"
                    data-product="{{ $variant->commodity->product_name }}"
                    data-brand="{{ $variant->commodity->brand->name ?? '' }}"
                    data-unit="{{ $variant->unit_of_measure }}"
                    data-srp="{{ $srp ?? '' }}"
                    data-prevailing="{{ $prevailing ?? '' }}"
                    data-category="{{ $variant->commodity->category->name ?? '' }}"
                    role="button"
                    tabindex="0"
                    aria-label="View price details for {{ $variant->commodity->product_name }}"
                >
                    {{-- Card header --}}
                    <div class="px-5 pt-5 pb-4 flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-[#186b02]/10 text-[#186b02] mb-2">
                                {{ $variant->commodity->category->name ?? 'General' }}
                            </span>
                            <h3 class="font-bold text-slate-900 text-lg leading-snug group-hover:text-[#186b02] transition-colors">
                                {{ $variant->commodity->product_name }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                @if ($variant->commodity->brand)
                                    <span class="text-sm text-slate-500">{{ $variant->commodity->brand->name }}</span>
                                @endif
                                <span class="text-xs text-slate-400 font-semibold bg-slate-100 px-2 py-0.5 rounded-md">
                                    {{ $variant->unit_of_measure }}
                                </span>
                            </div>
                        </div>
                        {{-- Star button — stop propagation so it doesn't open the modal --}}
                        <button
                            class="star-btn shrink-0 p-2 rounded-xl hover:bg-[#186b02]/10 transition-colors"
                            data-id="{{ $variant->id }}"
                            aria-label="Add to watchlist"
                            title="Add to watchlist"
                        >
                            <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Price body --}}
                    <div class="px-5 pb-5 flex-1 flex flex-col gap-4">
                        <div class="bg-slate-50 rounded-xl p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">Suggested Retail Price</span>
                                <span class="text-base font-semibold text-slate-700">
                                    {{ $srp !== null ? '₱ ' . number_format($srp, 2) : '—' }}
                                </span>
                            </div>
                            <div class="border-t border-slate-200/80"></div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-slate-500">Prevailing Price</span>
                                    @if ($isAbove)
                                        <span class="inline-flex items-center gap-0.5 text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md text-xs font-semibold">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                                            +{{ $pct }}%
                                        </span>
                                    @elseif ($isBelow)
                                        <span class="inline-flex items-center gap-0.5 text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md text-xs font-semibold">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                            {{ $pct }}%
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xl font-bold {{ $isAbove ? 'text-rose-600' : ($isBelow ? 'text-emerald-700' : ($isEqual ? 'text-[#186b02]' : 'text-slate-400')) }}">
                                    {{ $prevailing !== null ? '₱ ' . number_format($prevailing, 2) : '—' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <div class="flex-1 h-9 overflow-hidden rounded-lg opacity-70">
                                @if ($isAbove)
                                    <svg viewBox="0 0 80 32" class="w-full h-full" preserveAspectRatio="none">
                                        <polyline points="0,28 15,24 28,26 42,20 55,16 68,12 80,8" fill="none" stroke="#f43f5e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <polyline points="0,28 15,24 28,26 42,20 55,16 68,12 80,8 80,32 0,32" fill="#f43f5e" fill-opacity="0.08" stroke="none"/>
                                    </svg>
                                @elseif ($isBelow)
                                    <svg viewBox="0 0 80 32" class="w-full h-full" preserveAspectRatio="none">
                                        <polyline points="0,8 15,12 28,10 42,16 55,18 68,22 80,26" fill="none" stroke="#186b02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <polyline points="0,8 15,12 28,10 42,16 55,18 68,22 80,26 80,32 0,32" fill="#186b02" fill-opacity="0.08" stroke="none"/>
                                    </svg>
                                @else
                                    <svg viewBox="0 0 80 32" class="w-full h-full" preserveAspectRatio="none">
                                        <polyline points="0,16 15,15 28,17 42,16 55,15 68,16 80,16" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <polyline points="0,16 15,15 28,17 42,16 55,15 68,16 80,16 80,32 0,32" fill="#94a3b8" fill-opacity="0.06" stroke="none"/>
                                    </svg>
                                @endif
                            </div>
                            @if ($diff === null)
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium bg-slate-100 text-slate-500 shrink-0">No data</span>
                            @elseif ($isAbove)
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold bg-rose-100 text-rose-700 shrink-0">↑ +₱{{ number_format($diff, 2) }}</span>
                            @elseif ($isBelow)
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 shrink-0">↓ −₱{{ number_format(abs($diff), 2) }}</span>
                            @else
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-[#186b02]/10 text-[#186b02] shrink-0">✓ At SRP</span>
                            @endif
                        </div>

                        @if ($variant->commodity->juridical || ($variant->commodity->category->agency ?? null))
                            <div class="pt-3 border-t border-slate-100 space-y-1.5">
                                @if ($variant->commodity->juridical)
                                    <div class="flex items-center gap-2 text-sm text-slate-500">
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span class="truncate">{{ $variant->commodity->juridical->name }}</span>
                                    </div>
                                @endif
                                @if ($variant->commodity->category->agency ?? null)
                                    <div class="flex items-center gap-2 text-xs text-slate-400">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                        <span>Monitored by {{ $variant->commodity->category->agency->name }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- "Click to compare" hint --}}
                        <div class="flex items-center justify-center gap-1.5 text-xs text-slate-400 group-hover:text-[#186b02] transition-colors pt-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Click to compare establishments
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($variants->hasPages())
            <div class="mt-8 flex justify-center">
                {{ $variants->links() }}
            </div>
        @endif
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     COMMODITY DETAIL MODAL
     ═══════════════════════════════════════════════════════════════════ --}}
<div id="detail-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4 sm:p-6">
    <div id="detail-modal"
        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[92vh] flex flex-col overflow-hidden"
        role="dialog" aria-modal="true" aria-labelledby="modal-title">

        {{-- Modal header --}}
        <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-200 shrink-0">
            <div class="min-w-0">
                <div id="modal-category-badge" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-[#186b02]/10 text-[#186b02] mb-1.5"></div>
                <h2 id="modal-title" class="text-xl font-bold text-slate-900 leading-snug"></h2>
                <p id="modal-subtitle" class="text-sm text-slate-500 mt-0.5"></p>
            </div>
            <button id="modal-close"
                class="shrink-0 p-2 rounded-xl hover:bg-slate-100 transition-colors text-slate-400 hover:text-slate-700 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Modal body (scrollable) --}}
        <div class="flex-1 overflow-y-auto">

            {{-- Loading state --}}
            <div id="modal-loading" class="flex flex-col items-center justify-center py-24 gap-3">
                <div class="w-10 h-10 border-3 border-slate-200 border-t-[#186b02] rounded-full animate-spin" style="border-width:3px"></div>
                <p class="text-sm text-slate-400">Loading price data…</p>
            </div>

            {{-- Error state --}}
            <div id="modal-error" class="hidden flex-col items-center justify-center py-24 gap-3 text-center px-6">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 flex items-center justify-center mb-1">
                    <svg class="w-7 h-7 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">Failed to load price data</p>
                <p class="text-xs text-slate-400">Please try again.</p>
            </div>

            {{-- Content (hidden until loaded) --}}
            <div id="modal-content" class="hidden">

                {{-- Summary stats bar --}}
                <div id="modal-summary" class="grid grid-cols-3 divide-x divide-slate-200 border-b border-slate-200 bg-slate-50"></div>

                <div class="px-6 py-5 space-y-6">

                    {{-- ── Section: Establishments price list ── --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-[#186b02]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Price by Establishment</h3>
                            <span id="modal-unit-label" class="text-xs text-slate-400 font-normal normal-case"></span>
                        </div>
                        <div id="modal-establishments" class="space-y-2"></div>
                    </div>

                    {{-- ── Section: Price chart ── --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-[#186b02]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                            </svg>
                            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Price Trend</h3>
                            <span id="modal-chart-label" class="text-xs text-slate-400 font-normal normal-case ml-auto"></span>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80">
                            <div class="relative h-48">
                                <canvas id="price-chart"></canvas>
                            </div>
                            <div id="no-chart-data" class="hidden flex-col items-center justify-center h-48 gap-2 text-center">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <p class="text-xs text-slate-400">No price history available yet.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>{{-- /modal-content --}}
        </div>{{-- /scrollable body --}}
    </div>
</div>

@endsection

@push('scripts')
{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
(function () {
    'use strict';

    // ── Detail API base URL ──────────────────────────────────────
    const DETAIL_BASE = '{{ url("/sccpresyo/detail") }}';

    // ── Watchlist storage ────────────────────────────────────────
    const STORAGE_KEY = 'pw_watchlist';
    let watchlist = new Set(JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'));
    let activeTab = 'all';

    // ── DOM refs ─────────────────────────────────────────────────
    const cardsGrid      = document.getElementById('cards-grid');
    const allCards       = cardsGrid ? Array.from(cardsGrid.querySelectorAll('.price-card')) : [];
    const tabAll         = document.getElementById('tab-all');
    const tabWatchlist   = document.getElementById('tab-watchlist');
    const watchlistBtn   = document.getElementById('watchlist-toggle-btn');
    const countBadge     = document.getElementById('watchlist-count');
    const watchlistEmpty = document.getElementById('watchlist-empty');
    const headerSearch   = document.getElementById('header-search');
    const mobileSearch   = document.getElementById('mobile-search');

    // Watchlist drawer
    const drawer        = document.getElementById('watchlist-drawer');
    const drawerOverlay = document.getElementById('drawer-overlay');
    const drawerClose   = document.getElementById('drawer-close');
    const drawerBody    = document.getElementById('drawer-body');
    const viewAllBtn    = document.getElementById('view-all-watchlist-btn');

    // Detail modal
    const detailOverlay   = document.getElementById('detail-overlay');
    const detailModal     = document.getElementById('detail-modal');
    const modalClose      = document.getElementById('modal-close');
    const modalLoading    = document.getElementById('modal-loading');
    const modalError      = document.getElementById('modal-error');
    const modalContent    = document.getElementById('modal-content');
    const modalTitle      = document.getElementById('modal-title');
    const modalSubtitle   = document.getElementById('modal-subtitle');
    const modalCategory   = document.getElementById('modal-category-badge');
    const modalSummary    = document.getElementById('modal-summary');
    const modalEstabs     = document.getElementById('modal-establishments');
    const modalUnitLabel  = document.getElementById('modal-unit-label');
    const modalChartLabel = document.getElementById('modal-chart-label');
    const noChartData     = document.getElementById('no-chart-data');

    let priceChartInstance = null;

    // ════════════════════════════════════════════════════════════
    // DETAIL MODAL
    // ════════════════════════════════════════════════════════════

    function fmt(val) {
        return val != null ? '₱ ' + parseFloat(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—';
    }

    function statusBadge(status, diff) {
        const absDiff = diff != null ? Math.abs(diff).toFixed(2) : null;
        const map = {
            above:   `<span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-100 text-rose-700">↑ +₱${absDiff}</span>`,
            below:   `<span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-50 text-emerald-700">↓ −₱${absDiff}</span>`,
            equal:   `<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-[#186b02]/10 text-[#186b02]">✓ At SRP</span>`,
            no_data: `<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-slate-100 text-slate-500">No data</span>`,
        };
        return map[status] || map.no_data;
    }

    function renderEstablishments(establishments, variant) {
        modalEstabs.innerHTML = '';
        modalUnitLabel.textContent = variant.unit ? `· per ${variant.unit}` : '';

        if (!establishments.length) {
            modalEstabs.innerHTML = `<p class="text-sm text-slate-400 text-center py-6">No establishment data found.</p>`;
            return;
        }

        const withPrice   = establishments.filter(e => e.prevailing_price != null);
        const lowestPrice = withPrice.length
            ? Math.min(...withPrice.map(e => parseFloat(e.prevailing_price)))
            : null;

        establishments.forEach((e, idx) => {
            const isLowest  = lowestPrice !== null
                && e.prevailing_price != null
                && parseFloat(e.prevailing_price) === lowestPrice
                && withPrice.length > 1;
            const isCurrent = e.is_current;

            // Status colour tokens
            const statusColors = {
                above:   { price: 'text-rose-600',    tag: 'bg-rose-50 text-rose-700 border-rose-200'    },
                below:   { price: 'text-emerald-700', tag: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
                equal:   { price: 'text-[#186b02]',   tag: 'bg-[#186b02]/8 text-[#186b02] border-[#186b02]/20' },
                no_data: { price: 'text-slate-400',   tag: 'bg-slate-50 text-slate-500 border-slate-200'  },
            };
            const sc = statusColors[e.status] ?? statusColors.no_data;

            // Wrapper background
            const wrapClass = isLowest
                ? 'bg-[#186b02]/5 border-[#186b02]/20'
                : isCurrent
                    ? 'bg-sky-50/60 border-sky-200/50'
                    : 'bg-white border-slate-200 hover:bg-slate-50/60';

            // Status label text
            const statusLabel = {
                above:   `↑ Above SRP`,
                below:   `↓ Below SRP`,
                equal:   `✓ At SRP`,
                no_data: `No data`,
            }[e.status] ?? 'No data';

            const row = document.createElement('div');
            row.className = `rounded-xl border transition-colors duration-150 overflow-hidden ${wrapClass}`;

            row.innerHTML = `
                <div class="p-4">
                    <!-- Header row: rank · name · status tag -->
                    <div class="flex items-center gap-3">
                        <span class="shrink-0 w-6 h-6 rounded-md flex items-center justify-center text-[11px] font-bold
                            ${isLowest ? 'bg-[#186b02] text-white' : 'bg-slate-100 text-slate-500'}">${idx + 1}</span>

                        <p class="flex-1 font-semibold text-slate-900 text-sm leading-snug truncate">${e.establishment}</p>

                        <span class="shrink-0 text-[10px] font-semibold px-2 py-0.5 rounded-md border ${sc.tag}">${statusLabel}</span>
                    </div>

                    ${e.address ? `<p class="mt-1.5 ml-9 text-xs text-slate-400 truncate">${e.address}</p>` : ''}

                    <!-- Price cells -->
                    <div class="mt-3 ml-9 grid grid-cols-2 gap-2">
                        <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5">
                            <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 mb-1">Suggested Retail Price (SRP)</p>
                            <p class="text-base font-bold text-slate-700">${fmt(e.srp)}</p>
                        </div>
                        <div class="rounded-lg px-3 py-2.5 border
                            ${e.status === 'above' ? 'bg-rose-50 border-rose-200'
                            : e.status === 'below' ? 'bg-emerald-50 border-emerald-200'
                            : e.status === 'equal' ? 'bg-[#186b02]/5 border-[#186b02]/20'
                            : 'bg-slate-50 border-slate-200'}">
                            <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 mb-1">Prevailing Price</p>
                            <p class="text-base font-bold ${sc.price}">${fmt(e.prevailing_price)}</p>
                        </div>
                    </div>
                </div>`;

            modalEstabs.appendChild(row);
        });
    }

    function renderSummary(summary) {
        const items = [
            { label: 'Lowest Price',     value: fmt(summary.lowest_price),  sub: summary.lowest_name  || '—', color: 'text-emerald-700' },
            { label: 'Average Price',    value: fmt(summary.avg_price),     sub: `${summary.total_establishments} establishment${summary.total_establishments !== 1 ? 's' : ''}`, color: 'text-[#186b02]' },
            { label: 'Highest Price',    value: fmt(summary.highest_price), sub: summary.highest_name || '—', color: 'text-rose-600' },
        ];
        modalSummary.innerHTML = items.map(item => `
            <div class="px-5 py-4 text-center">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">${item.label}</p>
                <p class="text-lg font-bold ${item.color}">${item.value}</p>
                <p class="text-[10px] text-slate-400 mt-0.5 truncate">${item.sub}</p>
            </div>`).join('');
    }

    function renderChart(chart) {
        // Destroy existing chart instance
        if (priceChartInstance) {
            priceChartInstance.destroy();
            priceChartInstance = null;
        }

        const canvas = document.getElementById('price-chart');

        if (!chart.data || !chart.data.length) {
            canvas.parentElement.classList.add('hidden');
            noChartData.classList.remove('hidden');
            noChartData.classList.add('flex');
            modalChartLabel.textContent = '';
            return;
        }

        canvas.parentElement.classList.remove('hidden');
        noChartData.classList.add('hidden');
        noChartData.classList.remove('flex');

        modalChartLabel.textContent = chart.has_history
            ? `${chart.data.length} data point${chart.data.length !== 1 ? 's' : ''}`
            : 'Current prices by establishment';

        const ctx = canvas.getContext('2d');

        // Gradient fill
        const gradient = ctx.createLinearGradient(0, 0, 0, 180);
        gradient.addColorStop(0,   'rgba(24,107,2,0.18)');
        gradient.addColorStop(1,   'rgba(24,107,2,0)');

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
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11 },
                            callback: v => '₱' + parseFloat(v).toLocaleString('en-PH', { minimumFractionDigits: 2 }),
                        },
                        border: { display: false },
                    },
                },
            },
        });
    }

    async function openDetailModal(variantId) {
        // Show overlay
        detailOverlay.classList.remove('hidden');
        detailOverlay.classList.add('flex');
        document.body.style.overflow = 'hidden';

        // Reset state
        modalLoading.classList.remove('hidden');
        modalError.classList.add('hidden');
        modalError.classList.remove('flex');
        modalContent.classList.add('hidden');
        modalTitle.textContent   = 'Loading…';
        modalSubtitle.textContent = '';
        modalCategory.textContent = '';

        try {
            const res  = await fetch(`${DETAIL_BASE}/${variantId}`);
            if (!res.ok) throw new Error('Network response was not ok');
            const data = await res.json();

            // Populate header
            modalCategory.textContent    = data.variant.category || 'General';
            modalTitle.textContent       = data.variant.product_name;
            modalSubtitle.textContent    = [
                data.variant.brand,
                data.variant.unit,
                data.variant.agency ? `· Monitored by ${data.variant.agency}` : null,
            ].filter(Boolean).join(' · ');

            // Populate sections
            renderSummary(data.summary);
            renderEstablishments(data.establishments, data.variant);
            renderChart(data.chart);

            // Show content
            modalLoading.classList.add('hidden');
            modalContent.classList.remove('hidden');

        } catch (err) {
            modalLoading.classList.add('hidden');
            modalError.classList.remove('hidden');
            modalError.classList.add('flex');
        }
    }

    function closeDetailModal() {
        detailOverlay.classList.add('hidden');
        detailOverlay.classList.remove('flex');
        document.body.style.overflow = '';
        if (priceChartInstance) {
            priceChartInstance.destroy();
            priceChartInstance = null;
        }
    }

    // Card click → open modal
    allCards.forEach(card => {
        card.addEventListener('click', e => {
            // Don't open if clicking the star button
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

    // Close on overlay backdrop click (not on modal itself)
    detailOverlay?.addEventListener('click', e => {
        if (e.target === detailOverlay) closeDetailModal();
    });

    // Escape closes both modal and drawer
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeDetailModal();
            closeDrawer();
        }
    });

    // ════════════════════════════════════════════════════════════
    // WATCHLIST DRAWER
    // ════════════════════════════════════════════════════════════

    function openDrawer() {
        renderDrawer();
        drawer.classList.add('open');
        drawerOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        drawer.classList.remove('open');
        drawerOverlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    function renderDrawer() {
        drawerBody.innerHTML = '';
        if (watchlist.size === 0) {
            drawerBody.innerHTML = `
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-3">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-600">No starred items yet</p>
                    <p class="text-xs text-slate-400 mt-1">Tap the ★ on any card to add it here.</p>
                </div>`;
            return;
        }

        allCards.forEach(card => {
            const id = card.dataset.id;
            if (!watchlist.has(id)) return;

            const product    = card.dataset.product || '—';
            const brand      = card.dataset.brand   || '';
            const unit       = card.dataset.unit    || '';
            const srp        = card.dataset.srp     ? '₱ ' + parseFloat(card.dataset.srp).toFixed(2) : '—';
            const prevailing = card.dataset.prevailing ? parseFloat(card.dataset.prevailing) : null;
            const srpVal     = card.dataset.srp     ? parseFloat(card.dataset.srp) : null;
            const category   = card.dataset.category || '';

            let priceBadge = '';
            if (prevailing !== null && srpVal !== null) {
                const diff = prevailing - srpVal;
                priceBadge = diff > 0
                    ? `<span class="text-xs font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">↑ ₱${diff.toFixed(2)}</span>`
                    : diff < 0
                        ? `<span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">↓ ₱${Math.abs(diff).toFixed(2)}</span>`
                        : `<span class="text-xs font-semibold text-[#186b02] bg-[#186b02]/10 px-2 py-0.5 rounded-full">✓ At SRP</span>`;
            }

            const prevDisplay = prevailing !== null ? '₱ ' + prevailing.toFixed(2) : '—';
            const item = document.createElement('div');
            item.className = 'bg-slate-50 rounded-xl p-3.5 border border-slate-200/80 cursor-pointer hover:border-[#186b02]/30 hover:bg-[#186b02]/5 transition-all';
            item.innerHTML = `
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0">
                        ${category ? `<span class="text-[10px] font-semibold text-[#186b02] bg-[#186b02]/10 px-2 py-0.5 rounded-full">${category}</span>` : ''}
                        <p class="font-semibold text-slate-900 text-sm mt-1 leading-snug">${product}</p>
                        <p class="text-xs text-slate-400">${[brand, unit].filter(Boolean).join(' · ')}</p>
                    </div>
                    <button class="drawer-unstar shrink-0 p-1 rounded-lg hover:bg-rose-50 transition-colors" data-id="${id}" title="Remove from watchlist">
                        <svg class="w-4 h-4" fill="#186b02" stroke="#186b02" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-xs text-slate-500">SRP <span class="font-medium text-slate-700">${srp}</span> · Now <span class="font-semibold text-slate-900">${prevDisplay}</span></p>
                    ${priceBadge}
                </div>`;

            // Click drawer item → open detail modal
            item.addEventListener('click', e => {
                if (e.target.closest('.drawer-unstar')) return;
                closeDrawer();
                openDetailModal(id);
            });

            item.querySelector('.drawer-unstar').addEventListener('click', function (e) {
                e.stopPropagation();
                const removeId = this.dataset.id;
                watchlist.delete(removeId);
                saveWatchlist();
                syncStarButtons();
                updateCountBadge();
                item.remove();
                if (watchlist.size === 0) renderDrawer();
                if (activeTab === 'watchlist') applyTabAndSearch();
            });

            drawerBody.appendChild(item);
        });
    }

    watchlistBtn?.addEventListener('click', openDrawer);
    drawerClose?.addEventListener('click', closeDrawer);
    drawerOverlay?.addEventListener('click', e => { if (e.target === drawerOverlay) closeDrawer(); });
    viewAllBtn?.addEventListener('click', () => {
        closeDrawer();
        setActiveTab('watchlist');
        window.scrollTo({ top: (cardsGrid?.offsetTop ?? 0) - 120, behavior: 'smooth' });
    });

    // ════════════════════════════════════════════════════════════
    // WATCHLIST STATE
    // ════════════════════════════════════════════════════════════

    function saveWatchlist() {
        localStorage.setItem(STORAGE_KEY, JSON.stringify([...watchlist]));
    }

    function updateCountBadge() {
        const n = watchlist.size;
        countBadge.textContent = n;
        countBadge.classList.toggle('hidden', n === 0);
    }

    function syncStarButtons() {
        document.querySelectorAll('.star-btn').forEach(btn => {
            const starred = watchlist.has(btn.dataset.id);
            btn.classList.toggle('starred', starred);
            btn.setAttribute('aria-label', starred ? 'Remove from watchlist' : 'Add to watchlist');
            btn.title = starred ? 'Remove from watchlist' : 'Add to watchlist';
            btn.querySelector('svg').style.color = starred ? '#186b02' : '';
        });
    }

    // ════════════════════════════════════════════════════════════
    // TAB + SEARCH FILTER
    // ════════════════════════════════════════════════════════════

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
        const on  = ['bg-[#186b02]', 'text-white', 'shadow-sm'];
        const off = ['border', 'border-slate-200', 'text-slate-600', 'bg-white'];
        on .forEach(c => tabAll      .classList.toggle(c,  isAll));
        off.forEach(c => tabAll      .classList.toggle(c, !isAll));
        on .forEach(c => tabWatchlist.classList.toggle(c, !isAll));
        off.forEach(c => tabWatchlist.classList.toggle(c,  isAll));
        applyTabAndSearch();
    }

    tabAll?.addEventListener('click', () => setActiveTab('all'));
    tabWatchlist?.addEventListener('click', () => setActiveTab('watchlist'));

    document.querySelectorAll('.star-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.stopPropagation();
            const id = btn.dataset.id;
            if (watchlist.has(id)) { watchlist.delete(id); }
            else {
                watchlist.add(id);
                btn.querySelector('svg').animate(
                    [{ transform: 'scale(1)' }, { transform: 'scale(1.45)' }, { transform: 'scale(1)' }],
                    { duration: 280, easing: 'ease-out' }
                );
            }
            saveWatchlist(); syncStarButtons(); updateCountBadge();
            if (activeTab === 'watchlist') applyTabAndSearch();
        });
    });

    let searchTimer;
    function onSearch(e) {
        clearTimeout(searchTimer);
        if (headerSearch) headerSearch.value = e.target.value;
        if (mobileSearch) mobileSearch.value = e.target.value;
        searchTimer = setTimeout(applyTabAndSearch, 180);
    }
    headerSearch?.addEventListener('input', onSearch);
    mobileSearch?.addEventListener('input', onSearch);

    // ── Init ─────────────────────────────────────────────────────
    syncStarButtons();
    updateCountBadge();
    applyTabAndSearch();

})();
</script>
@endpush
