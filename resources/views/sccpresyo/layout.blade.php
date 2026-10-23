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

</body>
</html>
