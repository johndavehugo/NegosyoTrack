{{--
    SCCPresyo · Public SRP Price Monitor
    VAMOS Cares colors · Pure Blade + Livewire · No category boxes
    Browsable table with category pill bar, agency filter, search, sort
--}}
<div style="min-height:100vh;display:flex;flex-direction:column;">

{{-- ══ TICKER ═════════════════════════════════════════════════════ --}}
@if($this->tickerItems->isNotEmpty())
<div style="background:#1a3a0f;border-bottom:2px solid #4caf50;overflow:hidden;" aria-label="Live SRP ticker">
    <div style="display:flex;align-items:stretch;">
        <div style="flex-shrink:0;background:#2e7d32;display:flex;align-items:center;gap:7px;padding:0 16px;">
            <span class="live-dot" style="width:6px;height:6px;border-radius:50%;background:#a5d6a7;display:inline-block;"></span>
            <span style="color:#fff;font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;white-space:nowrap;">LIVE SRP</span>
        </div>
        <div style="flex:1;overflow:hidden;padding:7px 0;">
            <div class="ticker-track" style="display:inline-flex;gap:40px;white-space:nowrap;padding:0 20px;">
                @foreach(array_merge($this->tickerItems->all(), $this->tickerItems->all()) as $t)
                <span style="flex-shrink:0;font-size:12px;">
                    <span style="color:#fff;font-weight:600;">{{ $t->product_name }}</span>
                    @if($t->brand_name)<span style="color:#66bb6a;"> ({{ $t->brand_name }})</span>@endif
                    <span style="color:#4caf50;"> — </span>
                    <span style="color:#fff;font-weight:800;" class="mono">₱{{ number_format($t->srp, 2) }}</span>
                    <span style="color:#66bb6a;font-size:11px;"> / {{ $t->unit_of_measure }}</span>
                </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- ══ HEADER ══════════════════════════════════════════════════════ --}}
<header style="background:#1a3a0f;position:sticky;top:0;z-index:50;box-shadow:0 2px 8px rgba(0,0,0,.25);">
    <div style="max-width:1200px;margin:0 auto;padding:0 20px;height:58px;display:flex;align-items:center;gap:14px;">

        <a href="{{ url('/sccpresyo') }}" style="display:flex;align-items:center;gap:9px;text-decoration:none;flex-shrink:0;">
            <div style="width:34px;height:34px;background:#4caf50;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#1a3a0f" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                </svg>
            </div>
            <div style="line-height:1.1;">
                <div style="color:#fff;font-size:16px;font-weight:800;letter-spacing:-.2px;">SCCPresyo</div>
                <div style="color:#81c784;font-size:9px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;margin-top:1px;">San Carlos City</div>
            </div>
        </a>

        <div style="width:1px;height:22px;background:rgba(255,255,255,.15);flex-shrink:0;"></div>
        <span style="color:#81c784;font-size:12px;font-weight:500;display:none;" id="hd-sub">SRP Price Monitor</span>

        <div style="margin-left:auto;display:flex;align-items:center;gap:10px;">
            <span style="color:#81c784;font-size:11px;display:none;" id="hd-date">{{ now()->format('M j, Y') }}</span>
            <div style="display:flex;align-items:center;gap:5px;background:rgba(76,175,80,.18);border:1px solid rgba(76,175,80,.35);border-radius:6px;padding:4px 10px;">
                <span class="live-dot" style="width:5px;height:5px;border-radius:50%;background:#4caf50;display:inline-block;"></span>
                <span style="color:#a5d6a7;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">LIVE</span>
            </div>
        </div>
    </div>
</header>

{{-- ══ HERO / SEARCH SECTION ════════════════════════════════════════ --}}
<div style="background:linear-gradient(160deg,#1b4d10 0%,#1a3a0f 50%,#0f2208 100%);padding:40px 20px 36px;position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(76,175,80,.06) 1px,transparent 1px);background-size:24px 24px;pointer-events:none;"></div>
    <div style="position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(to bottom,#4caf50,#1b5e20);"></div>

    {{-- Two-column hero: left = heading + search · right = stat counters --}}
    <div style="max-width:1200px;margin:0 auto;position:relative;display:flex;align-items:flex-start;justify-content:space-between;gap:32px;flex-wrap:wrap;">

        {{-- LEFT — eyebrow, heading, search --}}
        <div style="flex:1;min-width:260px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <div style="width:28px;height:2px;background:#4caf50;border-radius:1px;"></div>
                <span style="color:#81c784;font-size:11px;font-weight:700;letter-spacing:.13em;text-transform:uppercase;">Negosyo Center · San Carlos City, Negros Occidental</span>
            </div>

            <h1 style="color:#fff;font-size:clamp(22px,4vw,38px);font-weight:900;letter-spacing:-.4px;line-height:1.1;text-transform:uppercase;margin-bottom:8px;">
                Suggested Retail<br/><span style="color:#4caf50;">Price Monitor</span>
            </h1>
            <p style="color:#81c784;font-size:13px;line-height:1.6;max-width:440px;margin-bottom:22px;">
                Official SRP data from DTI, DA, DOE and local monitoring agencies.
            </p>

            {{-- Search --}}
            <div style="position:relative;max-width:480px;">
                <svg style="position:absolute;left:11px;top:50%;transform:translateY(-50%);width:15px;height:15px;pointer-events:none;" fill="none" stroke="#9e9e9e" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="search"
                    wire:model.live.debounce.350ms="search"
                    placeholder="Search commodity, brand, establishment…"
                    class="scc-input"
                    style="background:#fff;border-color:#ddd;padding-left:34px;font-size:14px;border-radius:10px;"/>
            </div>
        </div>

        {{-- RIGHT — stat counters --}}
        <div style="display:flex;flex-direction:column;gap:10px;flex-shrink:0;align-self:center;">

            {{-- Active Items --}}
            <div style="background:rgba(76,175,80,.12);border:1px solid rgba(76,175,80,.28);border-radius:12px;padding:14px 20px;min-width:160px;display:flex;align-items:center;gap:14px;">
                <div style="width:38px;height:38px;border-radius:10px;background:rgba(76,175,80,.2);border:1px solid rgba(76,175,80,.35);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:900;color:#fff;line-height:1;" class="mono">{{ $this->totals['commodities'] }}</div>
                    <div style="font-size:11px;color:#81c784;font-weight:600;text-transform:uppercase;letter-spacing:.07em;margin-top:3px;">Active Items</div>
                </div>
            </div>

            {{-- Agencies + Categories in a row --}}
            <div style="display:flex;gap:10px;">
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:14px 16px;flex:1;display:flex;align-items:center;gap:10px;">
                    <div style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#81c784" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18M9 21V7l3-4 3 4v14M3 7h18"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:20px;font-weight:900;color:#fff;line-height:1;" class="mono">{{ $this->totals['agencies'] }}</div>
                        <div style="font-size:10px;color:#81c784;font-weight:600;text-transform:uppercase;letter-spacing:.07em;margin-top:2px;">Agencies</div>
                    </div>
                </div>

                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:14px 16px;flex:1;display:flex;align-items:center;gap:10px;">
                    <div style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#81c784" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" rx="1"/>
                            <rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="3" y="14" width="7" height="7" rx="1"/>
                            <rect x="14" y="14" width="7" height="7" rx="1"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:20px;font-weight:900;color:#fff;line-height:1;" class="mono">{{ $this->totals['categories'] }}</div>
                        <div style="font-size:10px;color:#81c784;font-weight:600;text-transform:uppercase;letter-spacing:.07em;margin-top:2px;">Categories</div>
                    </div>
                </div>
            </div>

        </div>{{-- /right stats --}}
    </div>
</div>

{{-- ══ FILTER TOOLBAR ═══════════════════════════════════════════════ --}}
<div style="background:#fff;border-bottom:1px solid var(--border);position:sticky;top:58px;z-index:40;">
    <div style="max-width:1200px;margin:0 auto;padding:0 20px;">

        {{-- NAV TABS --}}
        <div style="display:flex;align-items:stretch;gap:0;border-bottom:2px solid var(--border);overflow-x:auto;-webkit-overflow-scrolling:touch;" class="scc-nav-tabs">

            {{-- Price List tab (current page, always active) --}}
            <div style="display:flex;align-items:center;gap:7px;padding:14px 18px 12px;border-bottom:2px solid #4caf50;margin-bottom:-2px;white-space:nowrap;flex-shrink:0;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                </svg>
                <span style="font-size:13px;font-weight:700;color:#2e7d32;">Price List</span>
            </div>

            {{-- SRP Guide tab --}}
            <a href="#srp-guide" style="display:flex;align-items:center;gap:7px;padding:14px 18px 12px;border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;flex-shrink:0;text-decoration:none;color:#666;transition:color .12s;"
               onmouseover="this.style.color='#2e7d32'" onmouseout="this.style.color='#666'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/>
                </svg>
                <span style="font-size:13px;font-weight:600;">About SRP</span>
            </a>

            {{-- Agencies tab --}}
            <a href="#agencies" style="display:flex;align-items:center;gap:7px;padding:14px 18px 12px;border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;flex-shrink:0;text-decoration:none;color:#666;transition:color .12s;"
               onmouseover="this.style.color='#2e7d32'" onmouseout="this.style.color='#666'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18M9 21V7l3-4 3 4v14M3 7h18"/>
                </svg>
                <span style="font-size:13px;font-weight:600;">Agencies</span>
            </a>

            {{-- Disclaimer tab --}}
            <a href="#disclaimer" style="display:flex;align-items:center;gap:7px;padding:14px 18px 12px;border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;flex-shrink:0;text-decoration:none;color:#666;transition:color .12s;"
               onmouseover="this.style.color='#2e7d32'" onmouseout="this.style.color='#666'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span style="font-size:13px;font-weight:600;">Disclaimer</span>
            </a>
        </div>

        {{-- FILTER ROW — category dropdown, active chips, sort --}}
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;padding:12px 0;">

            {{-- Left: category dropdown + active chips --}}
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">

                {{-- Category filter --}}
                <div style="position:relative;display:flex;align-items:center;">
                    <svg style="position:absolute;left:10px;pointer-events:none;z-index:1;" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2" stroke-linecap="round">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    <select wire:model.live="categoryId" class="scc-select" style="padding-left:30px;min-width:180px;">
                        <option value="">All Categories</option>
                        @foreach($this->allCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->commodities_count }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Active chips --}}
                @if(filled($search))
                <span class="chip">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    "{{ Str::limit($search, 22) }}"
                    <button wire:click="$set('search','')">✕</button>
                </span>
                @endif

                @if(filled($categoryId) && filled($this->activeCategoryLabel))
                <span class="chip">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    {{ $this->activeCategoryLabel }}
                    <button wire:click="$set('categoryId','')">✕</button>
                </span>
                @endif

                @if(filled($agencyId) && filled($this->activeAgencyLabel))
                <span class="chip">
                    {{ $this->activeAgencyLabel }}
                    <button wire:click="$set('agencyId','')">✕</button>
                </span>
                @endif

                @if(filled($search) || filled($categoryId) || filled($agencyId))
                <button wire:click="clearFilters" class="btn-ghost" style="font-size:11px;padding:5px 10px;">
                    Clear all
                </button>
                @endif
            </div>

            {{-- Right: loading + sort --}}
            <div style="display:flex;align-items:center;gap:8px;">
                <svg wire:loading.delay class="scc-spin" style="width:14px;height:14px;color:#4caf50;" fill="none" viewBox="0 0 24 24">
                    <circle style="opacity:.2" cx="12" cy="12" r="10" stroke="#4caf50" stroke-width="3"/>
                    <path fill="#4caf50" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/>
                </svg>
                <span style="font-size:11px;color:#9e9e9e;font-weight:600;">Sort:</span>
                <select wire:model.live="sort" class="scc-select" style="font-size:12px;padding:7px 12px;">
                    <option value="name_asc">Name A → Z</option>
                    <option value="name_desc">Name Z → A</option>
                    <option value="srp_asc">Price: Low → High</option>
                    <option value="srp_desc">Price: High → Low</option>
                    <option value="cat_asc">By Category</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- ══ TABLE AREA ══════════════════════════════════════════════════ --}}
<main style="flex:1;max-width:1200px;margin:0 auto;width:100%;padding:20px;">

    {{-- Result summary --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
        <span style="font-size:13px;color:#555;">
            @if($this->commodities->total() > 0)
                Showing
                <strong style="color:#111;">{{ $this->commodities->firstItem() }}–{{ $this->commodities->lastItem() }}</strong>
                of <strong style="color:#111;">{{ $this->commodities->total() }}</strong>
                {{ Str::plural('item', $this->commodities->total()) }}
                @if(filled($search) || filled($categoryId) || filled($agencyId))
                <span style="color:#9e9e9e;">· filtered</span>
                @endif
            @else
                No items found
            @endif
        </span>

        {{-- Disclaimer badge --}}
        <span style="font-size:11px;color:#b45309;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:4px 10px;display:flex;align-items:center;gap:5px;">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
            Prices are SRP only — actual prices may vary
        </span>
    </div>

    {{-- TABLE --}}
    <div style="background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;">

        @if($this->commodities->isEmpty())
        <div style="padding:56px 24px;text-align:center;">
            <div style="width:52px;height:52px;background:#f5f5f5;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#9e9e9e" stroke-width="1.8" stroke-linecap="round">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
            </div>
            <p style="font-size:16px;font-weight:700;color:#333;margin-bottom:6px;">No commodities found</p>
            <p style="font-size:13px;color:#888;margin-bottom:18px;">Try adjusting your search or filters.</p>
            <button wire:click="clearFilters" class="btn-primary">Clear all filters</button>
        </div>

        @else
        <div style="overflow-x:auto;">
            <table class="ptable">
                <thead>
                    <tr>
                        <th style="width:40px;padding-left:18px;">#</th>
                        <th>
                            Commodity
                            <span style="font-size:10px;font-weight:500;color:#a5d6a7;margin-left:6px;">↗ click to compare</span>
                        </th>
                        <th class="c-cat">Category</th>
                        <th class="c-brand">Brand</th>
                        <th class="c-estab">Establishment</th>
                        <th class="c-unit">Unit</th>
                        <th style="padding-right:18px;">SRP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->commodities as $i => $c)
                    <tr wire:click="openCommodityModal({{ $c->id }})"
                        style="cursor:pointer;" title="Click to compare prices">
                        {{-- # --}}
                        <td style="color:#ccc;font-size:11px;padding-left:18px;" class="mono">
                            {{ $this->commodities->firstItem() + $i }}
                        </td>

                        {{-- Commodity --}}
                        <td>
                            <div class="pname" style="font-size:13px;font-weight:700;color:#111;line-height:1.3;transition:color .1s;">
                                {{ $c->product_name }}
                            </div>
                            @if($c->brand_name)
                            <div style="font-size:11px;color:#9e9e9e;margin-top:2px;" class="c-brand-inline">{{ $c->brand_name }}</div>
                            @endif
                            @if($c->establishments)
                            <div style="font-size:11px;color:#9e9e9e;margin-top:1px;display:flex;align-items:center;gap:3px;" class="c-estab-inline">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity:.4;flex-shrink:0;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                {{ $c->establishments }}
                            </div>
                            @endif
                        </td>

                        {{-- Category --}}
                        <td class="c-cat">
                            <span style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:5px;padding:2px 8px;font-size:11px;font-weight:600;white-space:nowrap;">
                                {{ $c->category?->name ?? '—' }}
                            </span>
                        </td>

                        {{-- Brand --}}
                        <td class="c-brand" style="font-size:12px;color:#555;">
                            {{ $c->brand_name ?: '—' }}
                        </td>

                        {{-- Establishment --}}
                        <td class="c-estab" style="font-size:12px;color:#555;">
                            @if($c->establishments)
                                <span style="display:flex;align-items:center;gap:5px;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#9e9e9e" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                    {{ $c->establishments }}
                                </span>
                            @else
                                <span style="color:#ddd;">—</span>
                            @endif
                        </td>

                        {{-- Unit --}}
                        <td class="c-unit">
                            <span style="background:#f5f5f5;border:1px solid #e0e0e0;border-radius:4px;padding:2px 7px;font-size:11px;font-weight:600;color:#666;white-space:nowrap;">
                                {{ $c->unit_of_measure }}
                            </span>
                        </td>

                        {{-- SRP --}}
                        <td style="padding-right:18px;">
                            @if($c->srp)
                                <div style="font-size:16px;font-weight:900;color:#1a3a0f;letter-spacing:-.3px;white-space:nowrap;" class="mono">
                                    ₱{{ number_format($c->srp, 2) }}
                                </div>
                                <div style="font-size:10px;color:#9e9e9e;margin-top:1px;">/{{ $c->unit_of_measure }}</div>
                            @else
                                <span style="font-size:11px;color:#ccc;font-style:italic;">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- PAGINATION --}}
    @if($this->commodities->hasPages())
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-top:18px;">
        <span style="font-size:12px;color:#888;">
            Page {{ $this->commodities->currentPage() }} of {{ $this->commodities->lastPage() }}
        </span>
        <div class="pager">
            {{-- Prev --}}
            <button
                wire:click="previousPage"
                class="pager-btn"
                @if($this->commodities->onFirstPage()) disabled @endif>
                ← Prev
            </button>

            {{-- Page numbers --}}
            @foreach($this->commodities->getUrlRange(
                max(1, $this->commodities->currentPage() - 2),
                min($this->commodities->lastPage(), $this->commodities->currentPage() + 2)
            ) as $page => $url)
                <button
                    wire:click="gotoPage({{ $page }})"
                    class="pager-btn {{ $page == $this->commodities->currentPage() ? 'active' : '' }}">
                    {{ $page }}
                </button>
            @endforeach

            {{-- Next --}}
            <button
                wire:click="nextPage"
                class="pager-btn"
                @if(!$this->commodities->hasMorePages()) disabled @endif>
                Next →
            </button>
        </div>
    </div>
    @endif

    {{-- About SRP section --}}
    <div id="srp-guide" style="margin-top:24px;background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;">
        <div style="background:#1a3a0f;padding:14px 20px;display:flex;align-items:center;gap:10px;">
            <div style="width:30px;height:30px;background:#4caf50;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#1a3a0f" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
            </div>
            <span style="color:#fff;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;">About Suggested Retail Prices (SRP)</span>
        </div>
        <div style="padding:20px 22px;display:grid;grid-template-columns:1fr;gap:16px;">
            <div style="display:flex;gap:14px;align-items:flex-start;">
                <div style="width:36px;height:36px;background:#e8f5e9;border:1px solid #a5d6a7;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="2" stroke-linecap="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:700;color:#111;margin-bottom:4px;">What is SRP?</div>
                    <p style="font-size:12px;color:#555;line-height:1.65;">Suggested Retail Price (SRP) is the maximum price set by government agencies for basic commodities. It is a consumer protection guideline — retailers are not allowed to sell above this price.</p>
                </div>
            </div>
            <div style="display:flex;gap:14px;align-items:flex-start;">
                <div style="width:36px;height:36px;background:#e8f5e9;border:1px solid #a5d6a7;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="2" stroke-linecap="round"><path d="M3 21h18M9 21V7l3-4 3 4v14M3 7h18"/></svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:700;color:#111;margin-bottom:4px;">Who sets the SRP?</div>
                    <p style="font-size:12px;color:#555;line-height:1.65;">Prices are set and monitored by the Department of Trade and Industry (DTI), Department of Agriculture (DA), Department of Energy (DOE), and other relevant government agencies.</p>
                </div>
            </div>
            <div style="display:flex;gap:14px;align-items:flex-start;">
                <div style="width:36px;height:36px;background:#fffbeb;border:1px solid #fde68a;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:700;color:#111;margin-bottom:4px;">Price Complaints</div>
                    <p style="font-size:12px;color:#555;line-height:1.65;">If a retailer is selling above the SRP, you may report it to the <strong>San Carlos City Negosyo Center</strong> or the <strong>CDCPMO</strong>. You can also call the DTI Consumer Hotline at <strong>1-384</strong>.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Agencies section --}}
    <div id="agencies" style="margin-top:16px;background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;">
        <div style="background:#1a3a0f;padding:14px 20px;display:flex;align-items:center;gap:10px;">
            <div style="width:30px;height:30px;background:#4caf50;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#1a3a0f" stroke-width="2.2" stroke-linecap="round"><path d="M3 21h18M9 21V7l3-4 3 4v14M3 7h18"/></svg>
            </div>
            <span style="color:#fff;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;">Monitoring Agencies</span>
        </div>
        <div style="padding:16px 20px;display:flex;flex-wrap:wrap;gap:12px;">
            @foreach($this->agencies as $ag)
            <div style="display:flex;align-items:center;gap:12px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:10px;padding:12px 16px;flex:1;min-width:200px;">
                <div style="width:40px;height:40px;background:#e8f5e9;border:1px solid #a5d6a7;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="font-size:11px;font-weight:900;color:#2e7d32;letter-spacing:.02em;">{{ strtoupper(substr($ag->code ?? $ag->name, 0, 3)) }}</span>
                </div>
                <div style="min-width:0;">
                    <div style="font-size:13px;font-weight:700;color:#111;">{{ $ag->name }}</div>
                    @if($ag->coverage)<div style="font-size:11px;color:#888;margin-top:2px;">{{ $ag->coverage }}</div>@endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Disclaimer section --}}
    <div id="disclaimer" style="margin-top:16px;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:18px 20px;display:flex;gap:14px;align-items:flex-start;">
        <div style="width:36px;height:36px;border-radius:9px;background:#fef3c7;border:1px solid #fde68a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div>
            <div style="font-size:13px;font-weight:800;color:#92400e;margin-bottom:6px;">Disclaimer</div>
            <p style="font-size:12px;color:#78350f;line-height:1.7;">
                Prices listed are <strong>Suggested Retail Prices (SRP)</strong> only — issued by DTI, DA, DOE and other government agencies as consumer protection guidelines. Actual market prices may vary per establishment and prevailing market conditions. This data is provided for <strong>public information purposes only</strong>.
            </p>
            <p style="font-size:11px;color:#b45309;margin-top:8px;font-weight:600;">
                Last updated: {{ now()->format('F j, Y · g:i A') }}
            </p>
        </div>
    </div>

</main>

{{-- ══ FOOTER ══════════════════════════════════════════════════════ --}}
<footer style="background:#1a3a0f;border-top:3px solid #4caf50;margin-top:8px;">
    <div style="max-width:1200px;margin:0 auto;padding:22px 20px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
        <div>
            <div style="display:flex;align-items:center;gap:9px;margin-bottom:6px;">
                <div style="width:26px;height:26px;background:#4caf50;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#1a3a0f" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                </div>
                <span style="color:#fff;font-size:14px;font-weight:800;">SCCPresyo</span>
                <span style="color:#4caf50;font-size:11px;font-weight:600;">· San Carlos City</span>
            </div>
            <div style="font-size:11px;color:#66bb6a;">Powered by <span style="color:#a5d6a7;font-weight:700;">NegosyoTrack</span> · Negosyo Center</div>
            <div style="font-size:11px;color:#388e3c;margin-top:2px;">Data from DTI, DA, DOE and local monitoring agencies.</div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:6px;">
            @foreach($this->agencies as $ag)
            <span style="background:rgba(76,175,80,.15);border:1px solid rgba(76,175,80,.3);border-radius:7px;padding:5px 13px;font-size:11px;font-weight:700;color:#a5d6a7;">
                {{ $ag->code ?? $ag->name }}
            </span>
            @endforeach
        </div>
    </div>
</footer>

{{-- ══ INLINE STYLES (mobile helpers) ════════════════════════════ --}}
<style>
    /* Show nav sub-labels on wider screens */
    @media (min-width:520px) {
        #hd-sub, #hd-date { display:block !important; }
    }
    /* Hide inline brand/estab labels when cols appear */
    .c-brand-inline { display:block; }
    .c-estab-inline { display:flex; }
    @media (min-width:720px) { .c-brand-inline { display:none; } }
    @media (min-width:900px) { .c-estab-inline { display:none; } }
</style>

{{-- ══ COMMODITY DETAIL MODAL ════════════════════════════════════ --}}
@if($commodityModalOpen && $this->selectedCommodity)
@php $sc = $this->selectedCommodity; @endphp
<div
    style="position:fixed;inset:0;z-index:300;background:rgba(15,30,10,.7);display:flex;align-items:center;justify-content:center;padding:16px;"
    wire:click.self="closeCommodityModal"
>
    <div style="background:#fff;width:100%;max-width:900px;max-height:92vh;border-radius:16px;display:flex;flex-direction:column;overflow:hidden;border:1px solid #e0e0e0;"
         x-data x-init="document.body.style.overflow='hidden'" x-destroy="document.body.style.overflow=''">

        {{-- MODAL HEADER --}}
        <div style="background:#1a3a0f;padding:18px 22px;display:flex;align-items:flex-start;gap:14px;flex-shrink:0;">
            <div style="width:44px;height:44px;background:#4caf50;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1a3a0f" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                </svg>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="color:#fff;font-size:18px;font-weight:900;line-height:1.2;letter-spacing:-.2px;">{{ $sc->product_name }}</div>
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:6px;">
                    @if($sc->brand_name)
                    <span style="background:rgba(255,255,255,.1);color:#a5d6a7;border-radius:5px;padding:2px 9px;font-size:11px;font-weight:600;">{{ $sc->brand_name }}</span>
                    @endif
                    <span style="background:rgba(255,255,255,.1);color:#a5d6a7;border-radius:5px;padding:2px 9px;font-size:11px;font-weight:600;">{{ $sc->category?->name }}</span>
                    <span style="background:rgba(76,175,80,.25);color:#a5d6a7;border-radius:5px;padding:2px 9px;font-size:11px;font-weight:700;border:1px solid rgba(76,175,80,.4);">
                        SRP ₱{{ number_format($sc->srp, 2) }} / {{ $sc->unit_of_measure }}
                    </span>
                </div>
            </div>
            <button wire:click="closeCommodityModal"
                style="width:34px;height:34px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:8px;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .12s;"
                onmouseover="this.style.background='rgba(255,255,255,.2)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- MODAL BODY --}}
        <div style="flex:1;overflow-y:auto;">
            <div style="display:grid;grid-template-columns:1fr;gap:0;">

                {{-- TOP ROW: SRP badge + monitored by --}}
                <div style="background:#f9fbe7;border-bottom:1px solid #e0e0e0;padding:14px 22px;display:flex;flex-wrap:wrap;align-items:center;gap:16px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="text-align:center;">
                            <div style="font-size:28px;font-weight:900;color:#1a3a0f;letter-spacing:-.5px;" class="mono">₱{{ number_format($sc->srp, 2) }}</div>
                            <div style="font-size:10px;font-weight:700;color:#2e7d32;text-transform:uppercase;letter-spacing:.08em;margin-top:2px;">Official SRP Ceiling</div>
                        </div>
                        <div style="width:1px;height:36px;background:#e0e0e0;"></div>
                        <div>
                            <div style="font-size:11px;color:#888;font-weight:600;margin-bottom:2px;">Unit of Measure</div>
                            <div style="font-size:14px;font-weight:700;color:#333;">{{ $sc->unit_of_measure }}</div>
                        </div>
                        @if($sc->category?->agency)
                        <div style="width:1px;height:36px;background:#e0e0e0;"></div>
                        <div>
                            <div style="font-size:11px;color:#888;font-weight:600;margin-bottom:2px;">Monitored By</div>
                            <div style="font-size:13px;font-weight:700;color:#333;">{{ $sc->category->agency->name }}</div>
                        </div>
                        @endif
                    </div>
                    <div style="margin-left:auto;">
                        <div style="background:#e8f5e9;border:1px solid #a5d6a7;border-radius:8px;padding:8px 14px;text-align:center;">
                            <div style="font-size:10px;font-weight:700;color:#2e7d32;text-transform:uppercase;letter-spacing:.07em;">SRP Status</div>
                            <div style="font-size:12px;font-weight:800;color:#1b5e20;margin-top:2px;">✓ Regulated</div>
                        </div>
                    </div>
                </div>

                {{-- TWO-COL: establishments + chart --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;min-height:360px;" class="commodity-modal-cols">

                    {{-- LEFT — ESTABLISHMENT COMPARISON --}}
                    <div style="border-right:1px solid #e0e0e0;padding:20px;" class="commodity-modal-left">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                            <div style="width:28px;height:28px;background:#e8f5e9;border:1px solid #a5d6a7;border-radius:7px;display:flex;align-items:center;justify-content:center;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="2" stroke-linecap="round">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:800;color:#111;">Establishments</div>
                                <div style="font-size:10px;color:#888;">Price vs SRP comparison</div>
                            </div>
                        </div>

                        @if(empty($this->establishmentComparison))
                        <div style="text-align:center;padding:32px 16px;">
                            <div style="width:44px;height:44px;background:#f5f5f5;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9e9e9e" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            </div>
                            <p style="font-size:13px;font-weight:600;color:#555;">No establishments listed</p>
                            <p style="font-size:11px;color:#9e9e9e;margin-top:4px;">Establishment data will appear here once recorded.</p>
                        </div>
                        @else

                        {{-- SRP reference line --}}
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:8px 12px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:11px;font-weight:700;color:#166534;">SRP Ceiling</span>
                            <span style="font-size:14px;font-weight:900;color:#166534;" class="mono">₱{{ number_format($sc->srp, 2) }}</span>
                        </div>

                        <div style="display:flex;flex-direction:column;gap:10px;">
                            @foreach($this->establishmentComparison as $est)
                            @php
                                $barColor = match($est['status']) {
                                    'above' => '#ef4444',
                                    'below' => '#22c55e',
                                    default => '#4caf50',
                                };
                                $bgColor = match($est['status']) {
                                    'above' => '#fef2f2',
                                    'below' => '#f0fdf4',
                                    default => '#f0fdf4',
                                };
                                $maxPrice = max($sc->srp * 1.3, $est['price'] + 1);
                                $barPct = min(100, round($est['price'] / $maxPrice * 100));
                                $srpPct = min(100, round($sc->srp  / $maxPrice * 100));
                            @endphp
                            <div style="background:{{ $bgColor }};border:1px solid {{ $barColor }}30;border-radius:9px;padding:10px 12px;">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                                    <div style="font-size:12px;font-weight:700;color:#111;line-height:1.3;">{{ $est['name'] }}</div>
                                    <div style="text-align:right;flex-shrink:0;margin-left:8px;">
                                        <div style="font-size:14px;font-weight:900;color:{{ $barColor }};" class="mono">₱{{ number_format($est['price'], 2) }}</div>
                                        @if($est['status'] !== 'at')
                                        <div style="font-size:10px;font-weight:700;color:{{ $barColor }};">
                                            {{ $est['status'] === 'above' ? '▲' : '▼' }}
                                            ₱{{ number_format(abs($est['diff']), 2) }}
                                            ({{ $est['pct'] }}%)
                                            {{ $est['status'] === 'above' ? 'above SRP' : 'below SRP' }}
                                        </div>
                                        @else
                                        <div style="font-size:10px;font-weight:700;color:#2e7d32;">✓ At SRP</div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Price bar --}}
                                <div style="position:relative;height:8px;background:#e5e7eb;border-radius:4px;overflow:visible;">
                                    {{-- SRP marker line --}}
                                    <div style="position:absolute;top:-3px;bottom:-3px;width:2px;background:#1a3a0f;border-radius:1px;left:{{ $srpPct }}%;z-index:2;" title="SRP"></div>
                                    {{-- Price bar --}}
                                    <div style="height:100%;width:{{ $barPct }}%;background:{{ $barColor }};border-radius:4px;transition:width .4s ease;"></div>
                                </div>

                                <div style="display:flex;justify-content:space-between;margin-top:5px;">
                                    <span style="font-size:9px;color:#888;">₱0</span>
                                    <span style="font-size:9px;color:#1a3a0f;font-weight:700;">SRP ₱{{ number_format($sc->srp, 2) }}</span>
                                    <span style="font-size:9px;color:#888;">₱{{ number_format($maxPrice, 0) }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- RIGHT — PRICE HISTORY CHART --}}
                    <div style="padding:20px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                            <div style="width:28px;height:28px;background:#e8f5e9;border:1px solid #a5d6a7;border-radius:7px;display:flex;align-items:center;justify-content:center;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="2" stroke-linecap="round">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:800;color:#111;">Price History</div>
                                <div style="font-size:10px;color:#888;">SRP vs. observed market price</div>
                            </div>
                        </div>

                        {{-- Chart legend --}}
                        <div style="display:flex;gap:14px;margin-bottom:12px;">
                            <div style="display:flex;align-items:center;gap:5px;">
                                <div style="width:20px;height:3px;background:#1a3a0f;border-radius:2px;"></div>
                                <span style="font-size:10px;font-weight:600;color:#555;">SRP Ceiling</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:5px;">
                                <div style="width:20px;height:3px;background:#4caf50;border-radius:2px;"></div>
                                <span style="font-size:10px;font-weight:600;color:#555;">Market Price</span>
                            </div>
                        </div>

                        {{-- ApexCharts div --}}
                        <div id="commodity-price-chart-{{ $sc->id }}" wire:ignore style="width:100%;min-height:220px;"></div>

                        @if(!($this->priceChartData['has_logs'] ?? false))
                        <div style="text-align:center;margin-top:8px;">
                            <span style="font-size:11px;color:#9e9e9e;background:#f5f5f5;border-radius:6px;padding:4px 10px;display:inline-block;">
                                No market price logs yet — showing SRP reference line
                            </span>
                        </div>
                        @endif
                    </div>
                </div>{{-- /two-col --}}

            </div>
        </div>{{-- /modal body --}}

        {{-- MODAL FOOTER --}}
        <div style="padding:12px 22px;border-top:1px solid #e0e0e0;background:#fafafa;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;flex-shrink:0;">
            <p style="font-size:11px;color:#9e9e9e;">
                SRP is the government-set maximum price. Report overpricing to the Negosyo Center.
            </p>
            <button wire:click="closeCommodityModal"
                class="btn-primary" style="padding:8px 20px;font-size:12px;">
                Close
            </button>
        </div>

    </div>
</div>

{{-- ApexCharts script for commodity modal --}}
<script>
(function () {
    const chartData = @js($this->priceChartData);
    const commodityId = {{ $sc->id }};

    function renderChart() {
        const el = document.getElementById('commodity-price-chart-' + commodityId);
        if (!el || typeof ApexCharts === 'undefined') return;

        const series = [
            {
                name: 'SRP Ceiling',
                data: chartData.srp,
                color: '#1a3a0f',
            },
        ];

        const hasPrev = Array.isArray(chartData.prevailing) && chartData.prevailing.some(v => v !== null && v > 0);
        if (hasPrev) {
            series.push({
                name: 'Market Price',
                data: chartData.prevailing,
                color: '#4caf50',
            });
        }

        const options = {
            chart: {
                type: 'line',
                height: 220,
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'inherit',
                animations: { enabled: true, speed: 600 },
                background: 'transparent',
            },
            series: series,
            xaxis: {
                categories: chartData.labels,
                labels: {
                    style: { fontSize: '10px', fontWeight: 600, colors: '#888' },
                    rotate: -20,
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: { fontSize: '10px', colors: '#888' },
                    formatter: (v) => v != null ? '₱' + Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '',
                },
            },
            stroke: {
                curve: 'smooth',
                width: [3, 2.5],
                dashArray: [0, 4],
            },
            markers: {
                size: [4, 4],
                strokeWidth: 2,
                strokeColors: '#fff',
                fillOpacity: 1,
            },
            grid: {
                borderColor: '#e8f5e9',
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },
            },
            tooltip: {
                shared: true,
                intersect: false,
                style: { fontSize: '12px' },
                y: {
                    formatter: (v) => v != null ? '₱' + Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : 'No data',
                },
            },
            legend: { show: false },
            theme: { mode: 'light' },
        };

        // Destroy old chart if any
        if (el._apexChart) { el._apexChart.destroy(); }
        const chart = new ApexCharts(el, options);
        chart.render();
        el._apexChart = chart;
    }

    // Wait a tick for Livewire to finish rendering the modal DOM
    setTimeout(renderChart, 80);
})();
</script>
@endif

</div>{{-- /root --}}
