<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="description" content="SCCPresyo — San Carlos City Official SRP & Prevailing Price Monitor."/>
    <title>SCCPresyo · San Carlos City e-Presyo</title>

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#186b02',
                            dark:    '#125201',
                            light:   '#e8f5e0',
                        },
                    },
                    fontFamily: {
                        sans: ['-apple-system','BlinkMacSystemFont','"Segoe UI"','Roboto','"Helvetica Neue"','Arial','sans-serif'],
                        mono: ['"SF Mono"','"Fira Code"','ui-monospace','monospace'],
                    },
                },
            },
        };
    </script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    <style>
        /* Watchlist drawer slide-in */
        #watchlist-drawer {
            transform: translateX(100%);
            transition: transform 0.28s cubic-bezier(.4,0,.2,1);
        }
        #watchlist-drawer.open  { transform: translateX(0); }
        #drawer-overlay         { opacity: 0; pointer-events: none; transition: opacity .25s; }
        #drawer-overlay.open    { opacity: 1; pointer-events: auto; }

        /* Star filled state */
        .star-btn.starred svg   { fill: #186b02; color: #186b02; }

        /* Custom scrollbar */
        ::-webkit-scrollbar       { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #186b02; border-radius: 3px; }

        /* Live pulse dot */
        @keyframes livepulse { 0%,100%{opacity:1} 50%{opacity:.35} }
        .live-dot { animation: livepulse 1.8s ease-in-out infinite; }

        /* SRP ticker */
        @keyframes scc-tick { from{transform:translateX(0)} to{transform:translateX(-50%)} }
        .ticker-track { animation: scc-tick 60s linear infinite; }
        .ticker-track:hover { animation-play-state: paused; }
        .ticker-wrap { overflow: hidden; }

        /* Hide ticker scrollbar */
        .ticker-wrap::-webkit-scrollbar { display: none; }

        /* Search clear btn */
        input[type="search"]::-webkit-search-cancel-button { display: none; }

        /* Smooth page transitions */
        .price-card { transition: transform 0.15s ease, box-shadow 0.15s ease; }

        /* Sidebar */
        #sidebar-panel { transition: transform 0.3s cubic-bezier(.4,0,.2,1); }
        #sidebar-overlay { transition: opacity 0.25s ease; }
        #sidebar-panel.open { transform: translateX(0) !important; }
        #sidebar-overlay.open { opacity: 1 !important; pointer-events: auto !important; }

        /* ─── Mobile Bottom Navbar ──────────────────────────────── */
        #mobile-bottom-nav {
            /* Sit above the device home indicator */
            padding-bottom: env(safe-area-inset-bottom, 8px);
            /* Glass-morphism feel */
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            background: rgba(255,255,255,0.96);
        }

        .mob-nav-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            gap: 3px;
            padding: 10px 4px 6px;
            border: none;
            background: transparent;
            cursor: pointer;
            color: #94a3b8;
            transition: color 0.18s ease, transform 0.15s ease;
            -webkit-tap-highlight-color: transparent;
            position: relative;
            text-decoration: none;
            min-width: 0;
            /* Ensure pointer events work */
            pointer-events: auto;
        }
        .mob-nav-btn:active { transform: scale(0.88); }
        .mob-nav-btn.active { color: #186b02; }

        .mob-nav-btn .nav-icon {
            width: 22px;
            height: 22px;
            transition: transform 0.22s cubic-bezier(.34,1.56,.64,1);
        }
        .mob-nav-btn.active .nav-icon { transform: translateY(-2px) scale(1.1); }

        .mob-nav-btn .nav-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.02em;
            line-height: 1;
        }

        /* Active indicator pill — REMOVED for clean look */

        /* Filled heart when favorites is active */
        #mob-nav-favorites.active .nav-icon { fill: #186b02; stroke: #186b02; }

        /* Favorites badge */
        #mob-nav-fav-badge {
            position: absolute;
            top: 4px;
            right: calc(50% - 22px);
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            border-radius: 99px;
            background: #186b02;
            color: #fff;
            font-size: 9px;
            font-weight: 900;
            display: none;
            align-items: center;
            justify-content: center;
            line-height: 1;
            pointer-events: none;
            border: 1.5px solid #fff;
        }
        #mob-nav-fav-badge.visible { display: flex; }

        /* ─── Mobile Search Sheet ───────────────────────────────── */
        #mob-search-sheet-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.55);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 80;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }
        #mob-search-sheet-overlay.open {
            opacity: 1;
            pointer-events: auto;
        }

        #mob-search-sheet {
            position: fixed;
            left: 0; right: 0; bottom: 0;
            background: #fff;
            border-radius: 20px 20px 0 0;
            z-index: 81;
            padding-bottom: calc(env(safe-area-inset-bottom, 8px) + 80px); /* clear the bottom nav */
            transform: translateY(100%);
            transition: transform 0.32s cubic-bezier(.4,0,.2,1);
            box-shadow: 0 -8px 40px rgba(0,0,0,0.12);
        }
        #mob-search-sheet.open {
            transform: translateY(0);
        }

        /* Sheet drag handle */
        #mob-search-sheet::before {
            content: '';
            display: block;
            width: 36px;
            height: 4px;
            border-radius: 2px;
            background: #e2e8f0;
            margin: 12px auto 0;
        }

        /* Stagger entrance for suggestion pills */
        @keyframes pill-in {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .search-pill {
            animation: pill-in 0.22s ease both;
        }
        .search-pill:nth-child(1) { animation-delay: 0.04s; }
        .search-pill:nth-child(2) { animation-delay: 0.08s; }
        .search-pill:nth-child(3) { animation-delay: 0.12s; }
        .search-pill:nth-child(4) { animation-delay: 0.16s; }
        .search-pill:nth-child(5) { animation-delay: 0.20s; }
        .search-pill:nth-child(6) { animation-delay: 0.24s; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">

    {!! $slot !!}

    {{-- Watchlist Drawer Overlay --}}
    <div id="drawer-overlay"
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40">
    </div>

    {{-- Watchlist Drawer --}}
    <div id="watchlist-drawer"
         class="fixed top-0 right-0 h-full w-full max-w-sm bg-white z-50 shadow-2xl flex flex-col"
         role="dialog" aria-label="My Watchlist">

        {{-- Drawer header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-brand/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-slate-900 text-sm">My Favorites</p>
                    <p class="text-xs text-slate-400">Starred commodities</p>
                </div>
            </div>
            <button id="drawer-close"
                class="p-2 rounded-xl hover:bg-slate-100 transition-colors text-slate-400 hover:text-slate-700"
                aria-label="Close watchlist">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Drawer body --}}
        <div id="drawer-body" class="flex-1 overflow-y-auto p-4 space-y-2"></div>

        {{-- Drawer footer --}}
        <div class="px-4 py-3 border-t border-slate-200 shrink-0">
            <button id="view-all-watchlist-btn"
                class="w-full py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-brand-dark transition-colors">
                View All in Watchlist
            </button>
        </div>
    </div>
    {{-- ══ MOBILE BOTTOM NAVBAR — hidden on md+ ═══════════════════════════════ --}}
    <nav id="mobile-bottom-nav"
         class="md:hidden fixed bottom-0 left-0 right-0 border-t border-slate-200/70 shadow-[0_-1px_20px_rgba(0,0,0,0.06)]"
         style="z-index:9000"
         role="navigation" aria-label="Mobile navigation">
        <div class="flex items-stretch">

            {{-- Home --}}
            <a id="mob-nav-home"
               href="{{ route('sccpresyo.index') }}"
               class="mob-nav-btn active"
               style="text-decoration:none; display:flex; flex:1;"
               aria-label="Home">
                {{-- Modern grid/compass home icon --}}
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9.5z"/>
                    <path d="M9 21V12h6v9"/>
                </svg>
                <span class="nav-label">Home</span>
            </a>

            {{-- Search --}}
            <button id="mob-nav-search"
                    class="mob-nav-btn"
                    aria-label="Search commodities"
                    type="button"
                    style="flex:1;">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7.5"/>
                    <path d="M20 20l-3.5-3.5"/>
                </svg>
                <span class="nav-label">Search</span>
            </button>

            {{-- Favorites --}}
            <button id="mob-nav-favorites"
                    class="mob-nav-btn"
                    aria-label="My favorites"
                    type="button"
                    style="flex:1;">
                {{-- badge --}}
                <span id="mob-nav-fav-badge" aria-hidden="true">0</span>
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
                <span class="nav-label">Favorites</span>
            </button>

        </div>
    </nav>

    {{-- ══ MOBILE SEARCH SHEET ════════════════════════════════════════════════ --}}
    <div id="mob-search-sheet-overlay" aria-hidden="true"></div>

    <div id="mob-search-sheet" role="dialog" aria-modal="true" aria-label="Search commodities">
        <div class="px-5 pt-4 pb-2">

            {{-- Header row --}}
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-base font-black text-slate-900">Search Commodities</p>
                    <p class="text-xs text-slate-400 mt-0.5">Find any product or brand</p>
                </div>
                <button id="mob-search-close"
                        class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 transition-colors text-slate-500"
                        aria-label="Close search">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Search input --}}
            <form id="mob-search-form" method="GET" action="{{ route('sccpresyo.index') }}">
                {{-- Preserve existing filters --}}
                @if(request()->filled('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                @if(request()->filled('sort') && request('sort') !== 'name_asc')
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif

                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/>
                    </svg>
                    <input id="mob-search-input"
                           type="text"
                           name="search"
                           value="{{ request('search', '') }}"
                           placeholder="e.g. Rice, Sugar, Cooking Oil…"
                           autocomplete="off"
                           autocorrect="off"
                           spellcheck="false"
                           enterkeyhint="search"
                           class="w-full pl-11 pr-11 py-3.5 text-base rounded-2xl border-2 border-slate-200
                                  focus:outline-none focus:border-[#186b02] bg-slate-50
                                  placeholder-slate-300 transition-colors font-medium text-slate-800"/>
                    <button id="mob-search-clear"
                            type="button"
                            class="hidden absolute right-3.5 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full
                                   bg-slate-300 hover:bg-slate-400 text-white items-center justify-center transition-colors"
                            aria-label="Clear">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </form>

            {{-- Category quick-filter pills --}}
            <div class="mt-4">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5">Browse by Category</p>
                <div class="flex flex-wrap gap-2" id="mob-search-pills">
                    <a href="{{ route('sccpresyo.index') }}"
                       class="search-pill inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold
                              transition-all border
                              {{ !request()->filled('category')
                                  ? 'bg-[#186b02] text-white border-[#186b02]'
                                  : 'bg-white text-slate-600 border-slate-200 hover:border-[#186b02] hover:text-[#186b02]' }}"
                       style="text-decoration:none">
                        All
                    </a>
                    @foreach ($categories as $cat)
                    <a href="{{ route('sccpresyo.index', ['category' => $cat->id]) }}"
                       class="search-pill inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold
                              transition-all border
                              {{ request('category') == $cat->id
                                  ? 'bg-[#186b02] text-white border-[#186b02]'
                                  : 'bg-white text-slate-600 border-slate-200 hover:border-[#186b02] hover:text-[#186b02]' }}"
                       style="text-decoration:none">
                        {{ $cat->name }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MOBILE NAV + SEARCH SHEET JAVASCRIPT ════════════════════════════════ --}}
    <script>
    // Safe init — works whether DOMContentLoaded already fired or not
    (function initMobileNav() {
        function run() {
            var navHome      = document.getElementById('mob-nav-home');
            var navSearch    = document.getElementById('mob-nav-search');
            var navFavorites = document.getElementById('mob-nav-favorites');
            var navFavBadge  = document.getElementById('mob-nav-fav-badge');
            var sheetOverlay = document.getElementById('mob-search-sheet-overlay');
            var sheet        = document.getElementById('mob-search-sheet');
            var sheetClose   = document.getElementById('mob-search-close');
            var sheetInput   = document.getElementById('mob-search-input');
            var sheetClear   = document.getElementById('mob-search-clear');
            var sheetForm    = document.getElementById('mob-search-form');

            // ── Active state ─────────────────────────────────────
            function setActive(btn) {
                [navHome, navSearch, navFavorites].forEach(function(b) {
                    if (b) b.classList.remove('active');
                });
                if (btn) btn.classList.add('active');
            }

            // ── Favorites badge ──────────────────────────────────
            function syncBadge() {
                if (!navFavBadge) return;
                try {
                    var list = JSON.parse(localStorage.getItem('scc_watchlist_v3') || '[]');
                    var n = list.length;
                    navFavBadge.textContent = n > 99 ? '99+' : String(n);
                    if (n > 0) { navFavBadge.classList.add('visible'); }
                    else       { navFavBadge.classList.remove('visible'); }
                } catch(e) {}
            }
            syncBadge();
            window.addEventListener('storage', syncBadge);
            setInterval(syncBadge, 800);

            // ── Search sheet ─────────────────────────────────────
            function openSheet() {
                if (sheet)        sheet.classList.add('open');
                if (sheetOverlay) sheetOverlay.classList.add('open');
                document.body.style.overflow = 'hidden';
                setTimeout(function(){ if (sheetInput) sheetInput.focus(); }, 300);
                setActive(navSearch);
            }
            function closeSheet() {
                if (sheet)        sheet.classList.remove('open');
                if (sheetOverlay) sheetOverlay.classList.remove('open');
                document.body.style.overflow = '';
                setActive(navHome);
            }

            if (navSearch) {
                navSearch.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (sheet && sheet.classList.contains('open')) closeSheet();
                    else openSheet();
                });
            }
            if (sheetClose)   sheetClose.addEventListener('click',   function(){ closeSheet(); });
            if (sheetOverlay) sheetOverlay.addEventListener('click', function(e){ if (e.target === sheetOverlay) closeSheet(); });

            if (sheetInput) {
                sheetInput.addEventListener('input', function() {
                    var has = sheetInput.value.length > 0;
                    if (sheetClear) {
                        sheetClear.style.display = has ? 'flex' : 'none';
                    }
                });
                sheetInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') { e.preventDefault(); if (sheetForm) sheetForm.submit(); }
                });
                if (sheetInput.value.length > 0 && sheetClear) sheetClear.style.display = 'flex';
            }
            if (sheetClear) {
                sheetClear.addEventListener('click', function() {
                    if (sheetInput) { sheetInput.value = ''; sheetInput.focus(); }
                    sheetClear.style.display = 'none';
                });
            }

            // ── Favorites ────────────────────────────────────────
            if (navFavorites) {
                navFavorites.addEventListener('click', function(e) {
                    e.preventDefault();
                    setActive(navFavorites);

                    if (typeof window.openFavoritesModal === 'function') {
                        window.openFavoritesModal();
                    } else {
                        var ov = document.getElementById('favorites-overlay');
                        if (ov) {
                            ov.style.display = 'flex';
                            document.body.style.overflow = 'hidden';
                        }
                    }

                    // Reset back to Home when modal closes
                    var closeBtn = document.getElementById('favorites-modal-close');
                    var ov2      = document.getElementById('favorites-overlay');
                    if (closeBtn) closeBtn.addEventListener('click', function(){ setActive(navHome); }, { once: true });
                    if (ov2)      ov2.addEventListener('click', function handler(e){
                        if (e.target === ov2) { setActive(navHome); ov2.removeEventListener('click', handler); }
                    });
                });
            }

            // ── Home ─────────────────────────────────────────────
            if (navHome) navHome.addEventListener('click', function(){ setActive(navHome); });

            // ── Escape ───────────────────────────────────────────
            document.addEventListener('keydown', function(e){
                if (e.key === 'Escape') closeSheet();
            });

            // Mark Search active if page loaded with a search query
            if ({{ json_encode(!empty(request('search'))) }}) setActive(navSearch);
        }

        // Run now if DOM ready, otherwise wait
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', run);
        } else {
            run(); // DOM already ready — script is at end of <body>
        }
    })();
    </script>

</body>
</html>
