{{-- SCCPresyo | VAMOS Cares design language — white + forest green --}}
<div style="min-height:100vh;display:flex;flex-direction:column;background:#f5f5f5;">

{{-- ══════════════════════════════════════
     TICKER — dark green bar (like VAMOS nav)
══════════════════════════════════════ --}}
@if($this->tickerItems->isNotEmpty())
<div style="background:#1a3a0f;border-bottom:2px solid #4caf50;overflow:hidden;" aria-label="SRP Price Ticker">
    <div style="display:flex;align-items:stretch;">
        <div style="flex-shrink:0;background:#2e7d32;display:flex;align-items:center;gap:8px;padding:0 18px;">
            <span class="pulse-dot" style="width:7px;height:7px;border-radius:50%;background:#a5d6a7;display:inline-block;"></span>
            <span style="color:#fff;font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;white-space:nowrap;">LIVE SRP</span>
        </div>
        <div style="flex:1;overflow:hidden;padding:8px 20px;">
            <div class="ticker-track" style="display:flex;gap:48px;white-space:nowrap;">
                @foreach($this->tickerItems as $t)
                    <span style="font-size:12px;color:#a5d6a7;flex-shrink:0;">
                        <span style="color:#ffffff;font-weight:600;">{{ $t->product_name }}</span>
                        @if($t->brand_name)<span style="color:#66bb6a;"> ({{ $t->brand_name }})</span>@endif
                        <span style="color:#4caf50;"> — </span>
                        <span style="color:#ffffff;font-weight:800;">₱{{ number_format($t->srp, 2) }}</span>
                        <span style="color:#66bb6a;font-size:11px;"> / {{ $t->unit_of_measure }}</span>
                    </span>
                @endforeach
                @foreach($this->tickerItems as $t)
                    <span style="font-size:12px;color:#a5d6a7;flex-shrink:0;">
                        <span style="color:#ffffff;font-weight:600;">{{ $t->product_name }}</span>
                        @if($t->brand_name)<span style="color:#66bb6a;"> ({{ $t->brand_name }})</span>@endif
                        <span style="color:#4caf50;"> — </span>
                        <span style="color:#ffffff;font-weight:800;">₱{{ number_format($t->srp, 2) }}</span>
                        <span style="color:#66bb6a;font-size:11px;"> / {{ $t->unit_of_measure }}</span>
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════
     HEADER — dark forest green like VAMOS nav
══════════════════════════════════════ --}}
<header style="background:#1a3a0f;position:sticky;top:0;z-index:50;box-shadow:0 2px 12px rgba(0,0,0,0.3);">
    <div style="max-width:1200px;margin:0 auto;padding:0 24px;height:60px;display:flex;align-items:center;gap:16px;">

        {{-- Brand mark --}}
        <a href="{{ url('/sccpresyo') }}" style="display:flex;align-items:center;gap:10px;text-decoration:none;flex-shrink:0;">
            <div style="width:36px;height:36px;border-radius:8px;background:#4caf50;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1a3a0f" stroke-width="2.5">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                    <path d="M8 12l3 3 5-5"/>
                </svg>
            </div>
            <div style="line-height:1.1;">
                <div style="color:#ffffff;font-size:16px;font-weight:900;letter-spacing:-.2px;">SCCPresyo</div>
                <div style="color:#81c784;font-size:10px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;">SAN CARLOS CITY</div>
            </div>
        </a>

        <div style="width:1px;height:24px;background:rgba(255,255,255,0.15);flex-shrink:0;"></div>
        <span style="color:#81c784;font-size:12px;font-weight:500;display:none;" class="sm-show">Price Monitoring Portal</span>

        <div style="margin-left:auto;display:flex;align-items:center;gap:12px;">
            <span style="color:#81c784;font-size:11px;display:none;" class="sm-show">
                As of <strong style="color:#fff;">{{ now()->format('M j, Y') }}</strong>
            </span>
            <div style="display:flex;align-items:center;gap:6px;background:rgba(76,175,80,0.2);border:1px solid rgba(76,175,80,0.4);border-radius:99px;padding:5px 12px;">
                <span class="pulse-dot" style="width:6px;height:6px;border-radius:50%;background:#4caf50;display:inline-block;"></span>
                <span style="color:#a5d6a7;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">LIVE</span>
            </div>
        </div>
    </div>
</header>

{{-- ══════════════════════════════════════
     HERO — dark green with city hall feel
     (mirrors the VAMOS hero section)
══════════════════════════════════════ --}}
<div style="background:linear-gradient(160deg,#1b4d10 0%,#1a3a0f 50%,#0f2208 100%);padding:48px 24px;position:relative;overflow:hidden;">

    {{-- Subtle texture overlay dots --}}
    <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(76,175,80,0.08) 1px,transparent 1px);background-size:24px 24px;pointer-events:none;"></div>

    {{-- Accent line left border --}}
    <div style="position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(to bottom,#4caf50,#1b5e20);"></div>

    <div style="max-width:1200px;margin:0 auto;position:relative;">

        {{-- Eyebrow label --}}
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div style="height:2px;width:32px;background:#4caf50;border-radius:1px;"></div>
            <span style="color:#81c784;font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;">Negosyo Center · San Carlos City, Negros Occidental</span>
        </div>

        {{-- Heading — VAMOS uses ALL CAPS bold --}}
        <div style="margin-bottom:8px;">
            <div style="color:#a5d6a7;font-size:13px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;margin-bottom:4px;">OFFICIAL</div>
            <h1 style="font-size:clamp(28px,5vw,44px);font-weight:900;color:#ffffff;line-height:1.05;letter-spacing:-.5px;text-transform:uppercase;">
                SUGGESTED RETAIL<br/>
                <span style="color:#4caf50;">PRICE MONITOR</span>
            </h1>
        </div>
        <p style="color:#81c784;font-size:14px;line-height:1.6;max-width:560px;margin-bottom:24px;">
            SRP data from DTI, DA, DOE and local monitoring agencies — updated regularly for public reference.
        </p>

        {{-- Search bar — white pill like VAMOS CTA --}}
        <div style="display:flex;flex-wrap:wrap;gap:10px;max-width:700px;">
            <div style="position:relative;flex:1;min-width:220px;">
                <svg style="position:absolute;left:14px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#9e9e9e;pointer-events:none;" fill="none" stroke="#9e9e9e" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input
                    type="search"
                    wire:model.live.debounce.350ms="search"
                    placeholder="Search commodity, brand, establishment…"
                    style="width:100%;padding:13px 16px 13px 42px;border-radius:10px;border:2px solid transparent;background:#ffffff;color:#111;font-size:14px;font-weight:500;box-shadow:0 2px 8px rgba(0,0,0,0.2);"
                />
            </div>
            <select wire:model.live="agencyFilter"
                style="padding:13px 16px;border-radius:10px;border:2px solid transparent;background:#ffffff;color:#333;font-size:13px;font-weight:600;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,0.2);">
                <option value="">All Agencies</option>
                @foreach($this->agencies as $ag)
                    <option value="{{ $ag->id }}">{{ $ag->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Stats pills --}}
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:20px;">
            <span style="background:rgba(76,175,80,0.2);border:1px solid rgba(76,175,80,0.35);color:#a5d6a7;border-radius:99px;padding:5px 14px;font-size:12px;font-weight:600;">
                📦 {{ $this->totals['commodities'] }} Active Items
            </span>
            <span style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:#81c784;border-radius:99px;padding:5px 14px;font-size:12px;font-weight:600;">
                🏛️ {{ $this->totals['agencies'] }} Agencies
            </span>
            <span style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:#81c784;border-radius:99px;padding:5px 14px;font-size:12px;font-weight:600;">
                🏷️ {{ $this->totals['categories'] }} Categories
            </span>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════
     CONTENT — white body like VAMOS "Know More"
══════════════════════════════════════ --}}
<div style="flex:1;max-width:1200px;margin:0 auto;width:100%;padding:28px 24px;display:flex;gap:20px;align-items:flex-start;">

    {{-- ─────────────────────────────────
         LEFT SIDEBAR
    ───────────────────────────────── --}}
    <aside style="width:250px;flex-shrink:0;position:sticky;top:80px;display:flex;flex-direction:column;gap:14px;" class="lg-sidebar">

        {{-- Categories card --}}
        <div style="background:#fff;border-radius:12px;border:1px solid #e0e0e0;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <div style="background:#1a3a0f;padding:13px 16px;display:flex;align-items:center;gap:8px;">
                <div style="width:26px;height:26px;border-radius:6px;background:#4caf50;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#1a3a0f" stroke-width="2.5">
                        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                </div>
                <span style="color:#fff;font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;">Categories</span>
            </div>

            <nav style="padding:8px;">
                {{-- All --}}
                <button
                    wire:click="$set('categoryFilter','')"
                    class="cat-btn {{ $categoryFilter === '' ? 'active' : '' }}"
                    style="display:flex;align-items:center;justify-content:space-between;width:100%;padding:9px 12px;border-radius:8px;border:1px solid transparent;background:transparent;cursor:pointer;text-align:left;margin-bottom:2px;color:#333;font-size:13px;"
                >
                    <span style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:15px;">🏷️</span>
                        <span>All Categories</span>
                    </span>
                    <span class="cat-count" style="font-size:10px;font-weight:700;background:#f5f5f5;color:#888;border-radius:99px;padding:2px 8px;">
                        {{ $this->totals['commodities'] }}
                    </span>
                </button>

                @foreach($this->categories as $cat)
                    @php
                        $cCount   = $cat->commodities_count ?? 0;
                        $isActive = (string)$categoryFilter === (string)$cat->id;
                        $icons    = ['rice'=>'🌾','grain'=>'🌾','sugar'=>'🫙','oil'=>'🛢️','meat'=>'🥩','pork'=>'🐖','chicken'=>'🐔','poultry'=>'🐔','fish'=>'🐟','seafood'=>'🦐','vegetable'=>'🥦','fruit'=>'🍎','fuel'=>'⛽','lpg'=>'⛽','gas'=>'⛽','medicine'=>'💊','egg'=>'🥚','dairy'=>'🥛','noodle'=>'🍜','beverage'=>'🥤','construction'=>'🧱'];
                        $icon     = '📦';
                        foreach($icons as $kw => $em){ if(str_contains(strtolower($cat->name),$kw)){$icon=$em;break;} }
                    @endphp
                    <button
                        wire:click="$set('categoryFilter','{{ $cat->id }}')"
                        class="cat-btn {{ $isActive ? 'active' : '' }}"
                        style="display:flex;align-items:center;justify-content:space-between;width:100%;padding:9px 12px;border-radius:8px;border:1px solid transparent;background:transparent;cursor:pointer;text-align:left;margin-bottom:2px;color:#333;font-size:13px;"
                    >
                        <span style="display:flex;align-items:center;gap:8px;min-width:0;">
                            <span style="font-size:15px;flex-shrink:0;">{{ $icon }}</span>
                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $cat->name }}</span>
                        </span>
                        @if($cCount > 0)
                        <span class="cat-count" style="font-size:10px;font-weight:700;background:#f5f5f5;color:#888;border-radius:99px;padding:2px 8px;flex-shrink:0;margin-left:4px;">
                            {{ $cCount }}
                        </span>
                        @endif
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Agencies card --}}
        <div style="background:#fff;border-radius:12px;border:1px solid #e0e0e0;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <div style="background:#1a3a0f;padding:13px 16px;display:flex;align-items:center;gap:8px;">
                <div style="width:26px;height:26px;border-radius:6px;background:#4caf50;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#1a3a0f" stroke-width="2.5">
                        <path d="M3 21h18M9 21V7l3-4 3 4v14"/>
                    </svg>
                </div>
                <span style="color:#fff;font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;">Agencies</span>
            </div>
            <div style="padding:10px;display:flex;flex-direction:column;gap:6px;">
                @foreach($this->agencies as $ag)
                    @php $agActive = (string)$agencyFilter === (string)$ag->id; @endphp
                    <button
                        wire:click="$set('agencyFilter','{{ $agActive ? '' : $ag->id }}')"
                        style="display:flex;align-items:center;gap:10px;width:100%;padding:10px 12px;border-radius:9px;
                               border:1.5px solid {{ $agActive ? '#a5d6a7' : '#eeeeee' }};
                               background:{{ $agActive ? '#e8f5e9' : '#fafafa' }};
                               cursor:pointer;text-align:left;transition:all .12s;"
                    >
                        <div style="width:34px;height:34px;border-radius:8px;
                                    background:{{ $agActive ? '#4caf50' : '#e8f5e9' }};
                                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span style="font-size:9px;font-weight:900;letter-spacing:.02em;
                                         color:{{ $agActive ? '#fff' : '#2e7d32' }};">
                                {{ strtoupper(substr($ag->code ?? $ag->name, 0, 3)) }}
                            </span>
                        </div>
                        <div style="min-width:0;flex:1;">
                            <div style="font-size:12px;font-weight:700;color:{{ $agActive ? '#2e7d32' : '#333' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $ag->name }}
                            </div>
                            @if($ag->coverage)
                            <div style="font-size:10px;color:#9e9e9e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:1px;">{{ $ag->coverage }}</div>
                            @endif
                        </div>
                        @if($agActive)
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="2.5" style="flex-shrink:0;"><path d="M20 6 9 17l-5-5"/></svg>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Disclaimer --}}
        <div style="background:#fff8e1;border:1px solid #ffe082;border-radius:12px;padding:14px 16px;">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:7px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                <span style="font-size:11px;font-weight:800;color:#b45309;text-transform:uppercase;letter-spacing:.06em;">Disclaimer</span>
            </div>
            <p style="font-size:11px;color:#78350f;line-height:1.6;">
                Prices are <em>Suggested Retail Prices</em> only. Actual market prices may vary per establishment.
            </p>
            <p style="font-size:10px;color:#b45309;margin-top:7px;font-weight:600;">
                Updated: {{ now()->format('M j, Y') }}
            </p>
        </div>

    </aside>

    {{-- ─────────────────────────────────
         RIGHT — commodity content
    ───────────────────────────────── --}}
    <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:14px;">

        {{-- Mobile category pills --}}
        <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:4px;" class="lg-hidden">
            <button wire:click="$set('categoryFilter','')"
                style="flex-shrink:0;padding:7px 16px;border-radius:99px;font-size:12px;font-weight:600;cursor:pointer;transition:all .12s;
                       border:1.5px solid {{ $categoryFilter==='' ? '#4caf50' : '#e0e0e0' }};
                       background:{{ $categoryFilter==='' ? '#e8f5e9' : '#fff' }};
                       color:{{ $categoryFilter==='' ? '#2e7d32' : '#666' }};">
                All
            </button>
            @foreach($this->categories as $cat)
            @php $mActive = (string)$categoryFilter===(string)$cat->id; @endphp
            <button wire:click="$set('categoryFilter','{{ $cat->id }}')"
                style="flex-shrink:0;padding:7px 16px;border-radius:99px;font-size:12px;font-weight:600;cursor:pointer;transition:all .12s;white-space:nowrap;
                       border:1.5px solid {{ $mActive ? '#4caf50' : '#e0e0e0' }};
                       background:{{ $mActive ? '#e8f5e9' : '#fff' }};
                       color:{{ $mActive ? '#2e7d32' : '#666' }};">
                {{ $cat->name }}
            </button>
            @endforeach
        </div>

        {{-- Filter chips + sort --}}
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
            @if($search)
                <span style="display:inline-flex;align-items:center;gap:6px;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:99px;padding:5px 12px;font-size:11px;font-weight:700;">
                    🔍 "{{ $search }}"
                    <button wire:click="$set('search','')" style="background:none;border:none;cursor:pointer;color:#2e7d32;opacity:.7;font-size:12px;">✕</button>
                </span>
            @endif
            @if($categoryFilter)
                @php $aC = $this->categories->firstWhere('id',$categoryFilter); @endphp
                @if($aC)
                <span style="display:inline-flex;align-items:center;gap:6px;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:99px;padding:5px 12px;font-size:11px;font-weight:700;">
                    🏷️ {{ $aC->name }}
                    <button wire:click="$set('categoryFilter','')" style="background:none;border:none;cursor:pointer;color:#2e7d32;opacity:.7;font-size:12px;">✕</button>
                </span>
                @endif
            @endif
            @if($agencyFilter)
                @php $aA = $this->agencies->firstWhere('id',$agencyFilter); @endphp
                @if($aA)
                <span style="display:inline-flex;align-items:center;gap:6px;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:99px;padding:5px 12px;font-size:11px;font-weight:700;">
                    🏛️ {{ $aA->name }}
                    <button wire:click="$set('agencyFilter','')" style="background:none;border:none;cursor:pointer;color:#2e7d32;opacity:.7;font-size:12px;">✕</button>
                </span>
                @endif
            @endif
            @if($search||$categoryFilter||$agencyFilter)
                <button wire:click="$set('search','');$set('categoryFilter','');$set('agencyFilter','')"
                    style="background:none;border:none;cursor:pointer;font-size:11px;color:#9e9e9e;text-decoration:underline;">
                    Clear all
                </button>
            @endif

            <div style="margin-left:auto;display:flex;align-items:center;gap:8px;">
                <svg wire:loading.delay class="animate-spin" style="width:15px;height:15px;color:#4caf50;" fill="none" viewBox="0 0 24 24">
                    <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span style="font-size:11px;color:#9e9e9e;font-weight:600;">Sort:</span>
                <select wire:model.live="sort"
                    style="font-size:12px;padding:7px 12px;border-radius:8px;border:1.5px solid #e0e0e0;background:#fff;color:#333;cursor:pointer;">
                    <option value="name_asc">Name A–Z</option>
                    <option value="name_desc">Name Z–A</option>
                    <option value="srp_asc">Price: Low → High</option>
                    <option value="srp_desc">Price: High → Low</option>
                </select>
            </div>
        </div>

        {{-- No results --}}
        @if($grouped->isEmpty())
        <div style="background:#fff;border-radius:12px;border:1px solid #e0e0e0;padding:60px 24px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <div style="font-size:48px;margin-bottom:16px;">🔍</div>
            <p style="font-size:17px;font-weight:800;color:#111;text-transform:uppercase;letter-spacing:.02em;">No Commodities Found</p>
            <p style="font-size:13px;color:#888;margin-top:6px;">Try adjusting your search or filter.</p>
            <button
                wire:click="$set('search','');$set('categoryFilter','');$set('agencyFilter','')"
                style="margin-top:20px;display:inline-flex;align-items:center;gap:8px;padding:11px 24px;border-radius:8px;background:#4caf50;color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;letter-spacing:.02em;"
            >CLEAR ALL FILTERS</button>
        </div>
        @endif

        {{-- ══ CATEGORY SECTIONS ══ --}}
        @foreach($grouped as $categoryName => $items)
        @php
            $first  = $items->first();
            $agency = $first?->category?->agency;
            $icons  = ['rice'=>'🌾','grain'=>'🌾','sugar'=>'🫙','oil'=>'🛢️','meat'=>'🥩','pork'=>'🐖','chicken'=>'🐔','poultry'=>'🐔','fish'=>'🐟','seafood'=>'🦐','vegetable'=>'🥦','fruit'=>'🍎','fuel'=>'⛽','lpg'=>'⛽','gas'=>'⛽','medicine'=>'💊','egg'=>'🥚','dairy'=>'🥛','noodle'=>'🍜','beverage'=>'🥤','construction'=>'🧱'];
            $sIcon = '📦';
            foreach($icons as $kw => $em){ if(str_contains(strtolower($categoryName??''),$kw)){$sIcon=$em;break;} }
        @endphp

        <div style="background:#fff;border-radius:12px;border:1px solid #e0e0e0;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.06);">

            {{-- Section header — dark green like VAMOS nav --}}
            <div style="background:#1a3a0f;padding:14px 20px;display:flex;align-items:center;gap:12px;">
                <div style="width:36px;height:36px;border-radius:9px;background:#4caf50;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                    {{ $sIcon }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:14px;font-weight:800;color:#fff;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $categoryName ?? 'Uncategorized' }}
                    </div>
                    @if($agency)
                    <div style="font-size:11px;color:#81c784;margin-top:1px;">Monitored by {{ $agency->name }}</div>
                    @endif
                </div>
                @if($agency)
                <span style="flex-shrink:0;font-size:10px;font-weight:800;letter-spacing:.06em;padding:4px 11px;border-radius:99px;background:rgba(76,175,80,0.25);color:#a5d6a7;border:1px solid rgba(76,175,80,0.35);">
                    {{ $agency->code ?? $agency->name }}
                </span>
                @endif
                <span style="flex-shrink:0;font-size:10px;font-weight:700;padding:4px 11px;border-radius:99px;background:rgba(255,255,255,0.1);color:#a5d6a7;border:1px solid rgba(255,255,255,0.15);">
                    {{ $items->count() }} {{ Str::plural('item',$items->count()) }}
                </span>
            </div>

            {{-- Table --}}
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f9f9f9;border-bottom:2px solid #e8f5e9;">
                            <th style="padding:10px 12px 10px 20px;text-align:left;font-size:10px;font-weight:800;color:#9e9e9e;text-transform:uppercase;letter-spacing:.09em;width:40px;">#</th>
                            <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:800;color:#9e9e9e;text-transform:uppercase;letter-spacing:.09em;">Commodity</th>
                            <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:800;color:#9e9e9e;text-transform:uppercase;letter-spacing:.09em;" class="sm-show">Brand</th>
                            <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:800;color:#9e9e9e;text-transform:uppercase;letter-spacing:.09em;" class="md-show">Establishment</th>
                            <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:800;color:#9e9e9e;text-transform:uppercase;letter-spacing:.09em;" class="sm-show">Unit</th>
                            <th style="padding:10px 20px 10px 12px;text-align:right;font-size:10px;font-weight:800;color:#9e9e9e;text-transform:uppercase;letter-spacing:.09em;">SRP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $c)
                        <tr class="price-row" style="border-bottom:1px solid #f5f5f5;">

                            <td style="padding:14px 12px 14px 20px;font-size:11px;color:#bdbdbd;font-family:monospace;">{{ $i+1 }}</td>

                            <td style="padding:14px 12px;">
                                <div class="price-name" style="font-size:13px;font-weight:700;color:#111;transition:color .1s;">
                                    {{ $c->product_name }}
                                </div>
                                @if($c->brand_name)
                                <div style="font-size:11px;color:#9e9e9e;margin-top:2px;" class="sm-hidden">{{ $c->brand_name }}</div>
                                @endif
                                @if($c->establishments)
                                <div style="font-size:11px;color:#9e9e9e;margin-top:2px;display:flex;align-items:center;gap:4px;" class="md-hidden">
                                    <span>🏪</span> {{ $c->establishments }}
                                </div>
                                @endif
                            </td>

                            <td style="padding:14px 12px;font-size:12px;color:#666;" class="sm-show">
                                {{ $c->brand_name ?: '—' }}
                            </td>

                            <td style="padding:14px 12px;font-size:12px;color:#666;" class="md-show">
                                @if($c->establishments)
                                    <span style="display:flex;align-items:center;gap:6px;">
                                        <span style="color:#bdbdbd;">🏪</span>
                                        {{ $c->establishments }}
                                    </span>
                                @else
                                    <span style="color:#e0e0e0;">—</span>
                                @endif
                            </td>

                            <td style="padding:14px 12px;" class="sm-show">
                                <span style="font-size:11px;font-weight:600;padding:3px 9px;border-radius:6px;background:#f5f5f5;color:#666;border:1px solid #e0e0e0;">
                                    {{ $c->unit_of_measure }}
                                </span>
                            </td>

                            <td style="padding:14px 20px 14px 12px;text-align:right;">
                                @if($c->srp)
                                    <div style="font-size:17px;font-weight:900;color:#1a3a0f;letter-spacing:-.3px;">
                                        ₱{{ number_format($c->srp, 2) }}
                                    </div>
                                    <div style="font-size:10px;color:#9e9e9e;margin-top:1px;">/ {{ $c->unit_of_measure }}</div>
                                @else
                                    <span style="font-size:11px;color:#bdbdbd;font-style:italic;">No SRP</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach

        {{-- PAGINATION --}}
        @if($paginated->hasPages())
        <div style="background:#fff;border-radius:12px;border:1px solid #e0e0e0;padding:14px 20px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <span style="font-size:12px;color:#9e9e9e;">
                Showing <strong style="color:#333;">{{ $paginated->firstItem() }}–{{ $paginated->lastItem() }}</strong>
                of <strong style="color:#333;">{{ $paginated->total() }}</strong> active commodities
            </span>
            <div style="display:flex;gap:4px;align-items:center;">
                @if($paginated->onFirstPage())
                    <span style="padding:7px 14px;border-radius:7px;border:1.5px solid #eee;color:#ddd;font-size:12px;cursor:not-allowed;">← Prev</span>
                @else
                    <button wire:click="previousPage" style="padding:7px 14px;border-radius:7px;border:1.5px solid #e0e0e0;background:#fff;color:#666;font-size:12px;cursor:pointer;font-weight:600;">← Prev</button>
                @endif
                @foreach($paginated->getUrlRange(max(1,$paginated->currentPage()-2),min($paginated->lastPage(),$paginated->currentPage()+2)) as $page => $url)
                    @if($page==$paginated->currentPage())
                        <span style="padding:7px 14px;border-radius:7px;background:#1a3a0f;color:#fff;font-size:12px;font-weight:800;border:1.5px solid #1a3a0f;">{{ $page }}</span>
                    @else
                        <button wire:click="gotoPage({{ $page }})" style="padding:7px 14px;border-radius:7px;border:1.5px solid #e0e0e0;background:#fff;color:#666;font-size:12px;cursor:pointer;font-weight:600;">{{ $page }}</button>
                    @endif
                @endforeach
                @if($paginated->hasMorePages())
                    <button wire:click="nextPage" style="padding:7px 14px;border-radius:7px;border:1.5px solid #e0e0e0;background:#fff;color:#666;font-size:12px;cursor:pointer;font-weight:600;">Next →</button>
                @else
                    <span style="padding:7px 14px;border-radius:7px;border:1.5px solid #eee;color:#ddd;font-size:12px;cursor:not-allowed;">Next →</span>
                @endif
            </div>
        </div>
        @endif

        {{-- About --}}
        <div id="about" style="background:#fff;border-radius:12px;border:1px solid #e0e0e0;padding:22px 24px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div style="width:30px;height:30px;border-radius:8px;background:#1a3a0f;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
                </div>
                <span style="font-size:13px;font-weight:800;color:#111;text-transform:uppercase;letter-spacing:.04em;">About SRP</span>
            </div>
            <p style="font-size:12px;color:#555;line-height:1.7;">
                Suggested Retail Prices (SRP) are set by government agencies (DTI, DA, DOE) as price ceilings and consumer guidelines.
                Actual prices may vary per establishment and market conditions.
            </p>
            <p style="font-size:11px;color:#9e9e9e;margin-top:8px;">
                For complaints, visit the <strong style="color:#555;">San Carlos City Negosyo Center</strong> or contact <strong style="color:#555;">CDCPMO</strong>.
            </p>
        </div>

    </div>{{-- /right --}}
</div>{{-- /content --}}

{{-- ══════════════════════════════════════
     FOOTER — dark green like VAMOS
══════════════════════════════════════ --}}
<footer style="background:#1a3a0f;border-top:3px solid #4caf50;margin-top:8px;">
    <div style="max-width:1200px;margin:0 auto;padding:24px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <div style="width:28px;height:28px;border-radius:7px;background:#4caf50;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1a3a0f" stroke-width="2.5"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/><path d="M8 12l3 3 5-5"/></svg>
                </div>
                <span style="color:#fff;font-size:15px;font-weight:900;letter-spacing:-.2px;">SCCPresyo</span>
                <span style="color:#4caf50;font-size:11px;font-weight:700;">· San Carlos City</span>
            </div>
            <div style="font-size:11px;color:#66bb6a;">Powered by <span style="color:#a5d6a7;font-weight:700;">NegosyoTrack</span> · Negosyo Center</div>
            <div style="font-size:11px;color:#388e3c;margin-top:3px;">Data from DTI, DA, DOE and local monitoring agencies.</div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
            @foreach($this->agencies as $ag)
                <span style="background:rgba(76,175,80,0.15);border:1px solid rgba(76,175,80,0.3);border-radius:8px;padding:6px 14px;font-size:11px;font-weight:700;color:#a5d6a7;">
                    {{ $ag->code ?? $ag->name }}
                </span>
            @endforeach
        </div>
    </div>
</footer>

{{-- Responsive helpers: show lg sidebar, hide on small --}}
<style>
    .lg-sidebar { display: none; }
    .lg-hidden  { display: flex; }
    .sm-show    { display: none !important; }
    .md-show    { display: none !important; }
    .sm-hidden  { display: block; }
    .md-hidden  { display: block; }
    @media (min-width: 640px) {
        .sm-show   { display: table-cell !important; }
        .sm-hidden { display: none !important; }
    }
    @media (min-width: 768px) {
        .md-show   { display: table-cell !important; }
        .md-hidden { display: none !important; }
    }
    @media (min-width: 1024px) {
        .lg-sidebar { display: flex; }
        .lg-hidden  { display: none; }
    }
    header .sm-show { display: none; }
    @media (min-width: 640px) { header .sm-show { display: block; } }
</style>

</div>{{-- /root --}}
