@php
    $search     = request('search', '');
    $categoryId = request('category', '');
    $agencyId   = request('agency', '');
    $sort       = request('sort', 'name_asc');
@endphp
<div style="min-height:100vh;display:flex;flex-direction:column;">

{{-- ══ TICKER ═════════════════════════════════════════════════════ --}}
@php
    $tickerItems = $variants->getCollection()->filter(fn ($v) => $v->srp > 0)->take(20);
@endphp
@if($tickerItems->isNotEmpty())
<div style="background:#1a3a0f;border-bottom:2px solid #4caf50;overflow:hidden;" aria-label="Live SRP ticker">
    <div style="display:flex;align-items:stretch;">
        <div style="flex-shrink:0;background:#2e7d32;display:flex;align-items:center;gap:7px;padding:0 16px;">
            <span class="live-dot" style="width:6px;height:6px;border-radius:50%;background:#a5d6a7;display:inline-block;"></span>
            <span style="color:#fff;font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;white-space:nowrap;">LIVE SRP</span>
        </div>
        <div style="flex:1;overflow:hidden;padding:7px 0;">
            <div class="ticker-track" style="display:inline-flex;gap:40px;white-space:nowrap;padding:0 20px;">
                @foreach(array_merge($tickerItems->all(), $tickerItems->all()) as $t)
                <span style="flex-shrink:0;font-size:12px;">
                    <span style="color:#fff;font-weight:600;">{{ $t->commodity->product_name }}</span>
                    @if($t->commodity->brand)<span style="color:#66bb6a;"> ({{ $t->commodity->brand->name }})</span>@endif
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
        <a href="{{ route('sccpresyo.index') }}" style="display:flex;align-items:center;gap:9px;text-decoration:none;flex-shrink:0;">
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
        <div style="margin-left:auto;display:flex;align-items:center;gap:10px;">
            <span style="color:#81c784;font-size:11px;">{{ now()->format('M j, Y') }}</span>
            <div style="display:flex;align-items:center;gap:5px;background:rgba(76,175,80,.18);border:1px solid rgba(76,175,80,.35);border-radius:6px;padding:4px 10px;">
                <span class="live-dot" style="width:5px;height:5px;border-radius:50%;background:#4caf50;display:inline-block;"></span>
                <span style="color:#a5d6a7;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">LIVE</span>
            </div>
        </div>
    </div>
</header>

{{-- ══ HERO ════════════════════════════════════════════════════════ --}}
<div style="background:linear-gradient(160deg,#1b4d10 0%,#1a3a0f 50%,#0f2208 100%);padding:40px 20px 36px;position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(76,175,80,.06) 1px,transparent 1px);background-size:24px 24px;pointer-events:none;"></div>
    <div style="max-width:1200px;margin:0 auto;position:relative;display:flex;align-items:flex-start;justify-content:space-between;gap:32px;flex-wrap:wrap;">

        <div style="flex:1;min-width:260px;">
            <h1 style="color:#fff;font-size:clamp(22px,4vw,38px);font-weight:900;letter-spacing:-.4px;line-height:1.1;text-transform:uppercase;margin-bottom:8px;">
                Suggested Retail<br/><span style="color:#4caf50;">Price Monitor</span>
            </h1>
            <p style="color:#81c784;font-size:13px;line-height:1.6;max-width:440px;margin-bottom:22px;">
                Official SRP data from DTI, DA, DOE and local monitoring agencies.
            </p>
            {{-- Search form --}}
            <form method="GET" action="{{ route('sccpresyo.index') }}" style="position:relative;max-width:480px;">
                @if($categoryId)<input type="hidden" name="category" value="{{ $categoryId }}">@endif
                @if($agencyId)<input type="hidden" name="agency" value="{{ $agencyId }}">@endif
                @if($sort !== 'name_asc')<input type="hidden" name="sort" value="{{ $sort }}">@endif
                <svg style="position:absolute;left:11px;top:50%;transform:translateY(-50%);width:15px;height:15px;pointer-events:none;" fill="none" stroke="#9e9e9e" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="search" name="search" value="{{ $search }}"
                    placeholder="Search commodity, brand…"
                    class="scc-input"
                    style="background:#fff;border-color:#ddd;padding-left:34px;font-size:14px;border-radius:10px;"/>
            </form>
        </div>

        {{-- Stats --}}
        <div style="display:flex;flex-direction:column;gap:10px;flex-shrink:0;align-self:center;">
            <div style="background:rgba(76,175,80,.12);border:1px solid rgba(76,175,80,.28);border-radius:12px;padding:14px 20px;min-width:160px;display:flex;align-items:center;gap:14px;">
                <div style="width:38px;height:38px;border-radius:10px;background:rgba(76,175,80,.2);border:1px solid rgba(76,175,80,.35);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:900;color:#fff;line-height:1;" class="mono">{{ $totals['commodities'] }}</div>
                    <div style="font-size:11px;color:#81c784;font-weight:600;text-transform:uppercase;letter-spacing:.07em;margin-top:3px;">Active Items</div>
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:14px 16px;flex:1;display:flex;align-items:center;gap:10px;">
                    <div>
                        <div style="font-size:20px;font-weight:900;color:#fff;line-height:1;" class="mono">{{ $totals['agencies'] }}</div>
                        <div style="font-size:10px;color:#81c784;font-weight:600;text-transform:uppercase;letter-spacing:.07em;margin-top:2px;">Agencies</div>
                    </div>
                </div>
                <div style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:14px 16px;flex:1;display:flex;align-items:center;gap:10px;">
                    <div>
                        <div style="font-size:20px;font-weight:900;color:#fff;line-height:1;" class="mono">{{ $totals['categories'] }}</div>
                        <div style="font-size:10px;color:#81c784;font-weight:600;text-transform:uppercase;letter-spacing:.07em;margin-top:2px;">Categories</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ FILTER TOOLBAR ═══════════════════════════════════════════════ --}}
<form method="GET" action="{{ route('sccpresyo.index') }}" id="filter-form">
    @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif
    <div style="background:#fff;border-bottom:1px solid var(--border);position:sticky;top:58px;z-index:40;">
        <div style="max-width:1200px;margin:0 auto;padding:0 20px;">

            {{-- NAV TABS --}}
            <div style="display:flex;align-items:stretch;gap:0;border-bottom:2px solid var(--border);overflow-x:auto;" class="scc-nav-tabs">
                <div style="display:flex;align-items:center;gap:7px;padding:14px 18px 12px;border-bottom:2px solid #4caf50;margin-bottom:-2px;white-space:nowrap;flex-shrink:0;">
                    <span style="font-size:13px;font-weight:700;color:#2e7d32;">Price List</span>
                </div>
                <a href="#srp-guide" style="display:flex;align-items:center;gap:7px;padding:14px 18px 12px;border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;flex-shrink:0;text-decoration:none;color:#666;">
                    <span style="font-size:13px;font-weight:600;">About SRP</span>
                </a>
                <a href="#agencies" style="display:flex;align-items:center;gap:7px;padding:14px 18px 12px;border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;flex-shrink:0;text-decoration:none;color:#666;">
                    <span style="font-size:13px;font-weight:600;">Agencies</span>
                </a>
                <a href="#disclaimer" style="display:flex;align-items:center;gap:7px;padding:14px 18px 12px;border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;flex-shrink:0;text-decoration:none;color:#666;">
                    <span style="font-size:13px;font-weight:600;">Disclaimer</span>
                </a>
            </div>

            {{-- FILTER ROW --}}
            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;padding:12px 0;">
                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">

                    {{-- Category filter --}}
                    <select name="category" onchange="this.form.submit()" class="scc-select" style="min-width:180px;">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($categoryId == $cat->id)>
                                {{ $cat->name }} ({{ $cat->commodities_count }})
                            </option>
                        @endforeach
                    </select>

                    {{-- Agency filter --}}
                    <select name="agency" onchange="this.form.submit()" class="scc-select">
                        <option value="">All Agencies</option>
                        @foreach($agencies as $ag)
                            <option value="{{ $ag->id }}" @selected($agencyId == $ag->id)>{{ $ag->name }}</option>
                        @endforeach
                    </select>

                    {{-- Active chips --}}
                    @if(filled($search))
                    <span class="chip">
                        "{{ Str::limit($search, 22) }}"
                        <a href="{{ route('sccpresyo.index', array_filter(['category' => $categoryId, 'agency' => $agencyId, 'sort' => $sort !== 'name_asc' ? $sort : null])) }}" style="color:inherit;text-decoration:none;opacity:.7;">✕</a>
                    </span>
                    @endif
                    @if(filled($categoryId))
                    <span class="chip">
                        {{ $categories->firstWhere('id', $categoryId)?->name }}
                        <a href="{{ route('sccpresyo.index', array_filter(['search' => $search, 'agency' => $agencyId, 'sort' => $sort !== 'name_asc' ? $sort : null])) }}" style="color:inherit;text-decoration:none;opacity:.7;">✕</a>
                    </span>
                    @endif
                    @if(filled($search) || filled($categoryId) || filled($agencyId))
                    <a href="{{ route('sccpresyo.index') }}" class="btn-ghost" style="font-size:11px;padding:5px 10px;text-decoration:none;">Clear all</a>
                    @endif
                </div>

                {{-- Sort --}}
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:11px;color:#9e9e9e;font-weight:600;">Sort:</span>
                    <select name="sort" onchange="this.form.submit()" class="scc-select" style="font-size:12px;padding:7px 12px;">
                        <option value="name_asc"  @selected($sort === 'name_asc') >Name A → Z</option>
                        <option value="name_desc" @selected($sort === 'name_desc')>Name Z → A</option>
                        <option value="srp_asc"   @selected($sort === 'srp_asc')  >Price: Low → High</option>
                        <option value="srp_desc"  @selected($sort === 'srp_desc') >Price: High → Low</option>
                        <option value="cat_asc"   @selected($sort === 'cat_asc')  >By Category</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- ══ TABLE AREA ══════════════════════════════════════════════════ --}}
<main style="flex:1;max-width:1200px;margin:0 auto;width:100%;padding:20px;">

    {{-- Result summary --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
        <span style="font-size:13px;color:#555;">
            @if($variants->total() > 0)
                Showing
                <strong style="color:#111;">{{ $variants->firstItem() }}–{{ $variants->lastItem() }}</strong>
                of <strong style="color:#111;">{{ $variants->total() }}</strong>
                {{ Str::plural('item', $variants->total()) }}
                @if(filled($search) || filled($categoryId) || filled($agencyId))
                <span style="color:#9e9e9e;">· filtered</span>
                @endif
            @else
                No items found
            @endif
        </span>
        <span style="font-size:11px;color:#b45309;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:4px 10px;">
            Prices are SRP only — actual prices may vary
        </span>
    </div>

    {{-- TABLE --}}
    <div style="background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;">

        @if($variants->isEmpty())
        <div style="padding:56px 24px;text-align:center;">
            <p style="font-size:16px;font-weight:700;color:#333;margin-bottom:6px;">No commodities found</p>
            <p style="font-size:13px;color:#888;margin-bottom:18px;">Try adjusting your search or filters.</p>
            <a href="{{ route('sccpresyo.index') }}" class="btn-primary" style="text-decoration:none;">Clear all filters</a>
        </div>

        @else
        <div style="overflow-x:auto;">
            <table class="ptable">
                <thead>
                    <tr>
                        <th style="width:40px;padding-left:18px;">#</th>
                        <th>Commodity</th>
                        <th class="c-cat">Category</th>
                        <th class="c-brand">Brand</th>
                        <th class="c-estab">Establishment</th>
                        <th class="c-unit">Unit</th>
                        <th style="padding-right:18px;">SRP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($variants as $i => $v)
                    @php
                        $estName = $v->commodity->juridical_id
                            ? ($establishmentNames[$v->commodity->juridical_id] ?? $v->commodity->juridical_id)
                            : null;
                    @endphp
                    <tr>
                        <td style="color:#ccc;font-size:11px;padding-left:18px;" class="mono">
                            {{ $variants->firstItem() + $i }}
                        </td>

                        {{-- Commodity --}}
                        <td>
                            <div class="pname" style="font-size:13px;font-weight:700;color:#111;line-height:1.3;">
                                {{ $v->commodity->product_name }}
                            </div>
                            @if($v->commodity->brand)
                            <div style="font-size:11px;color:#9e9e9e;margin-top:2px;" class="c-brand-inline">{{ $v->commodity->brand->name }}</div>
                            @endif
                            @if($estName)
                            <div style="font-size:11px;color:#9e9e9e;margin-top:1px;display:flex;align-items:center;gap:3px;" class="c-estab-inline">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity:.4;flex-shrink:0;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                {{ $estName }}
                            </div>
                            @endif
                        </td>

                        {{-- Category --}}
                        <td class="c-cat">
                            <span style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:5px;padding:2px 8px;font-size:11px;font-weight:600;white-space:nowrap;">
                                {{ $v->commodity->category?->name ?? '—' }}
                            </span>
                        </td>

                        {{-- Brand --}}
                        <td class="c-brand" style="font-size:12px;color:#555;">
                            {{ $v->commodity->brand?->name ?: '—' }}
                        </td>

                        {{-- Establishment --}}
                        <td class="c-estab" style="font-size:12px;color:#555;">
                            @if($estName)
                                <span style="display:flex;align-items:center;gap:5px;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#9e9e9e" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                    {{ $estName }}
                                </span>
                            @else
                                <span style="color:#ddd;">—</span>
                            @endif
                        </td>

                        {{-- Unit --}}
                        <td class="c-unit">
                            <span style="background:#f5f5f5;border:1px solid #e0e0e0;border-radius:4px;padding:2px 7px;font-size:11px;font-weight:600;color:#666;white-space:nowrap;">
                                {{ $v->unit_of_measure }}
                            </span>
                        </td>

                        {{-- SRP --}}
                        <td style="padding-right:18px;">
                            @if($v->srp)
                                <div style="font-size:16px;font-weight:900;color:#1a3a0f;letter-spacing:-.3px;white-space:nowrap;" class="mono">
                                    ₱{{ number_format($v->srp, 2) }}
                                </div>
                                <div style="font-size:10px;color:#9e9e9e;margin-top:1px;">/{{ $v->unit_of_measure }}</div>
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
    @if($variants->hasPages())
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-top:18px;">
        <span style="font-size:12px;color:#888;">
            Page {{ $variants->currentPage() }} of {{ $variants->lastPage() }}
        </span>
        <div class="pager">
            @if(!$variants->onFirstPage())
            <a href="{{ $variants->previousPageUrl() }}" class="pager-btn" style="text-decoration:none;">← Prev</a>
            @else
            <button class="pager-btn" disabled>← Prev</button>
            @endif

            @foreach($variants->getUrlRange(max(1, $variants->currentPage() - 2), min($variants->lastPage(), $variants->currentPage() + 2)) as $page => $url)
            <a href="{{ $url }}" class="pager-btn {{ $page == $variants->currentPage() ? 'active' : '' }}" style="text-decoration:none;">{{ $page }}</a>
            @endforeach

            @if($variants->hasMorePages())
            <a href="{{ $variants->nextPageUrl() }}" class="pager-btn" style="text-decoration:none;">Next →</a>
            @else
            <button class="pager-btn" disabled>Next →</button>
            @endif
        </div>
    </div>
    @endif

    {{-- About SRP --}}
    <div id="srp-guide" style="margin-top:24px;background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;">
        <div style="background:#1a3a0f;padding:14px 20px;">
            <span style="color:#fff;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;">About Suggested Retail Prices (SRP)</span>
        </div>
        <div style="padding:20px 22px;">
            <p style="font-size:12px;color:#555;line-height:1.65;margin-bottom:12px;">
                <strong>Suggested Retail Price (SRP)</strong> is the maximum price set by government agencies for basic commodities. It is a consumer protection guideline — retailers are not allowed to sell above this price.
            </p>
            <p style="font-size:12px;color:#555;line-height:1.65;">
                Prices are set and monitored by the <strong>Department of Trade and Industry (DTI)</strong>, Department of Agriculture (DA), Department of Energy (DOE), and other relevant government agencies.
            </p>
        </div>
    </div>

    {{-- Agencies --}}
    <div id="agencies" style="margin-top:16px;background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;">
        <div style="background:#1a3a0f;padding:14px 20px;">
            <span style="color:#fff;font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;">Monitoring Agencies</span>
        </div>
        <div style="padding:16px 20px;display:flex;flex-wrap:wrap;gap:12px;">
            @foreach($agencies as $ag)
            <div style="display:flex;align-items:center;gap:12px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:10px;padding:12px 16px;flex:1;min-width:200px;">
                <div style="width:40px;height:40px;background:#e8f5e9;border:1px solid #a5d6a7;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="font-size:11px;font-weight:900;color:#2e7d32;">{{ strtoupper(substr($ag->code ?? $ag->name, 0, 3)) }}</span>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:700;color:#111;">{{ $ag->name }}</div>
                    @if($ag->coverage)<div style="font-size:11px;color:#888;margin-top:2px;">{{ $ag->coverage }}</div>@endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Disclaimer --}}
    <div id="disclaimer" style="margin-top:16px;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:18px 20px;margin-bottom:24px;">
        <div style="font-size:13px;font-weight:800;color:#92400e;margin-bottom:6px;">Disclaimer</div>
        <p style="font-size:12px;color:#78350f;line-height:1.7;">
            Prices listed are <strong>Suggested Retail Prices (SRP)</strong> only — issued by DTI, DA, DOE and other government agencies as consumer protection guidelines. Actual market prices may vary per establishment and prevailing market conditions.
        </p>
        <p style="font-size:11px;color:#b45309;margin-top:8px;font-weight:600;">
            Last updated: {{ now()->format('F j, Y · g:i A') }}
        </p>
    </div>

</main>

{{-- ══ FOOTER ══════════════════════════════════════════════════════ --}}
<footer style="background:#1a3a0f;border-top:3px solid #4caf50;">
    <div style="max-width:1200px;margin:0 auto;padding:22px 20px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
        <div>
            <div style="color:#fff;font-size:14px;font-weight:800;margin-bottom:4px;">SCCPresyo · San Carlos City</div>
            <div style="font-size:11px;color:#66bb6a;">Powered by <span style="color:#a5d6a7;font-weight:700;">NegosyoTrack</span> · Negosyo Center</div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:6px;">
            @foreach($agencies as $ag)
            <span style="background:rgba(76,175,80,.15);border:1px solid rgba(76,175,80,.3);border-radius:7px;padding:5px 13px;font-size:11px;font-weight:700;color:#a5d6a7;">
                {{ $ag->code ?? $ag->name }}
            </span>
            @endforeach
        </div>
    </div>
</footer>

<style>
    .c-brand-inline { display:block; }
    .c-estab-inline { display:flex; }
    @media (min-width:720px) { .c-brand-inline { display:none; } }
    @media (min-width:900px) { .c-estab-inline { display:none; } }
</style>

</div>
>
