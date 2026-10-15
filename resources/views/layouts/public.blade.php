<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'San Carlos City e-Presyo') — {{ config('app.name') }}</title>
    <meta name="description" content="San Carlos City e-Presyo — transparent public commodity price monitoring and watchlist for San Carlos City residents.">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        html { scroll-behavior: smooth; }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #186b02; }

        /* ── Star button ─────────────────────────────────── */
        .star-btn.starred svg { fill: #186b02; stroke: #186b02; }
        .star-btn svg { transition: fill 0.15s, stroke 0.15s, transform 0.15s; }
        .star-btn:hover svg { transform: scale(1.2); }

        /* ── Card entrance ───────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .price-card { animation: fadeUp 0.28s ease both; }
        .price-card:nth-child(1) { animation-delay: 0.03s; }
        .price-card:nth-child(2) { animation-delay: 0.07s; }
        .price-card:nth-child(3) { animation-delay: 0.11s; }
        .price-card:nth-child(4) { animation-delay: 0.15s; }
        .price-card:nth-child(5) { animation-delay: 0.19s; }
        .price-card:nth-child(6) { animation-delay: 0.23s; }

        /* ── Desktop sidebar ─────────────────────────────── */
        #app-sidebar {
            width: 240px;
            overflow: hidden;
            transition: width 0.28s cubic-bezier(0.4,0,0.2,1);
            will-change: width;
        }
        #app-sidebar.collapsed {
            width: 0;
        }
        #app-sidebar .sidebar-label {
            transition: opacity 0.15s ease;
            opacity: 1;
            overflow: hidden;
            white-space: nowrap;
        }
        #app-sidebar.collapsed .sidebar-label {
            opacity: 0;
        }
        #main-content {
            transition: margin-left 0.28s cubic-bezier(0.4,0,0.2,1);
        }

        /* ── Watchlist drawer ────────────────────────────── */
        #watchlist-drawer {
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
        }
        #watchlist-drawer.open { transform: translateX(0); }

        #drawer-overlay {
            opacity: 0; pointer-events: none;
            transition: opacity 0.3s ease;
        }
        #drawer-overlay.open { opacity: 1; pointer-events: auto; }

        /* ── Mobile bottom nav ───────────────────────────── */
        .mobile-nav-item.active svg  { stroke: #186b02; }
        .mobile-nav-item.active span { color: #186b02; }
        .mobile-nav-item.active      { position: relative; }
        .mobile-nav-item.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 2px;
            background: #186b02;
            border-radius: 2px;
        }
        /* Safe area for iPhone home bar */
        .safe-area-inset-bottom {
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 antialiased text-slate-900">

{{-- ══════════════════════════════════════════════════════════
     WATCHLIST DRAWER (right side)
     ══════════════════════════════════════════════════════════ --}}
<div id="drawer-overlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40"></div>

<aside id="watchlist-drawer" class="fixed top-0 right-0 h-full w-full sm:w-96 bg-white z-50 shadow-2xl flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 shrink-0">
        <div class="flex items-center gap-2.5">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#186b02]/10">
                <svg class="w-4 h-4 text-[#186b02]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </span>
            <div>
                <p class="font-bold text-slate-900 text-sm leading-none">My Watchlist</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Starred commodities</p>
            </div>
        </div>
        <button id="drawer-close" class="p-2 rounded-lg hover:bg-slate-100 transition-colors text-slate-400 hover:text-slate-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <div id="drawer-body" class="flex-1 overflow-y-auto px-4 py-4 space-y-3"></div>
    <div class="px-5 py-4 border-t border-slate-200 shrink-0">
        <button id="view-all-watchlist-btn"
            class="w-full py-2.5 rounded-xl bg-[#186b02] text-white text-sm font-semibold hover:bg-[#145901] transition-colors">
            View All in Watchlist Tab
        </button>
    </div>
</aside>

{{-- ══════════════════════════════════════════════════════════
     STICKY HEADER
     ══════════════════════════════════════════════════════════ --}}
<header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm">
    <div class="px-4 sm:px-6">
        <div class="flex items-center gap-3 h-15" style="height:60px;">

            {{-- Sidebar toggle (desktop only) --}}
            <button id="sidebar-toggle"
                class="hidden lg:inline-flex items-center justify-center w-9 h-9 rounded-lg hover:bg-slate-100 transition-colors text-slate-500 shrink-0"
                aria-label="Toggle sidebar" title="Toggle sidebar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Logo --}}
            <a href="{{ url('/sccpresyo') }}" class="flex items-center gap-2.5 shrink-0">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-[#186b02] text-white font-bold text-base shadow-sm select-none">₱</span>
                <div class="hidden sm:block">
                    <p class="text-sm font-bold text-slate-900 leading-none">SCC e-Presyo</p>
                    <p class="text-[10px] text-slate-500 leading-none mt-0.5">San Carlos City Price Watch</p>
                </div>
            </a>

            {{-- Search (desktop) --}}
            <div class="flex-1 max-w-lg mx-auto hidden md:block">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19A8 8 0 1 0 11 3a8 8 0 0 0 0 16z"/>
                    </svg>
                    <input type="text" id="header-search"
                        placeholder="Search commodities or brands..."
                        class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-full focus:outline-none focus:ring-2 focus:ring-[#186b02] focus:border-transparent focus:bg-white transition-all">
                </div>
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <span class="hidden sm:inline-block text-xs text-slate-400">Updated {{ now()->format('M j, Y') }}</span>

                {{-- Watchlist button --}}
                <button id="watchlist-toggle-btn"
                    class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-full border border-slate-200 hover:border-[#186b02] hover:text-[#186b02] text-sm font-medium text-slate-600 transition-all duration-200 bg-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    <span class="hidden sm:inline">Watchlist</span>
                    <span id="watchlist-count" class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-[#186b02] text-white text-[10px] font-bold hidden">0</span>
                </button>
            </div>
        </div>

        {{-- Mobile search bar --}}
        <div class="md:hidden pb-3">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19A8 8 0 1 0 11 3a8 8 0 0 0 0 16z"/>
                </svg>
                <input type="text" id="mobile-search"
                    placeholder="Search commodities or brands..."
                    class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-full focus:outline-none focus:ring-2 focus:ring-[#186b02] focus:border-transparent focus:bg-white transition-all">
            </div>
        </div>
    </div>
</header>

{{-- ══════════════════════════════════════════════════════════
     BODY LAYOUT — sidebar + content
     ══════════════════════════════════════════════════════════ --}}
<div class="flex min-h-[calc(100vh-60px)]">

    {{-- ── Desktop Sidebar ──────────────────────────────────── --}}
    <aside id="app-sidebar"
        class="hidden lg:flex flex-col shrink-0 bg-white border-r border-slate-200 sticky top-[60px] h-[calc(100vh-60px)] overflow-y-auto overflow-x-hidden z-20">

        <nav class="flex-1 py-4 px-2 space-y-1">

            {{-- ── Section: Main ── --}}
            <p class="sidebar-section-title px-3 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Main</p>

            {{-- Home --}}
            <a href="{{ url('/sccpresyo') }}"
                class="sidebar-nav-item group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                       {{ request()->is('sccpresyo') && !request()->hasAny(['category','sort']) ? 'bg-[#186b02]/10 text-[#186b02]' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="sidebar-label">Home</span>
            </a>

            {{-- Price Watch --}}
            <a href="{{ url('/sccpresyo') }}"
                class="sidebar-nav-item group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                       bg-[#186b02] text-white shadow-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="sidebar-label">Price Watch</span>
            </a>

            {{-- Watchlist (opens drawer) --}}
            <button id="sidebar-watchlist-btn"
                class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all">
                <div class="relative shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    <span id="sidebar-wl-badge" class="absolute -top-1 -right-1 hidden w-4 h-4 rounded-full bg-[#186b02] text-white text-[9px] font-bold flex items-center justify-center">0</span>
                </div>
                <span class="sidebar-label">My Watchlist</span>
            </button>

            {{-- ── Divider ── --}}
            <div class="py-1"><div class="border-t border-slate-100"></div></div>

            {{-- ── Section: Browse ── --}}
            <p class="sidebar-section-title px-3 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Browse</p>

            {{-- All Commodities --}}
            <a href="{{ url('/sccpresyo') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 10h16M4 14h10"/>
                </svg>
                <span class="sidebar-label">All Commodities</span>
            </a>

            {{-- Price Alerts (informational) --}}
            <button id="sidebar-alerts-btn"
                class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="sidebar-label">Price Alerts</span>
            </button>

            {{-- Compare Prices --}}
            <button
                class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all"
                onclick="document.getElementById('tab-all')?.click(); document.getElementById('header-search')?.focus();">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span class="sidebar-label">Compare Prices</span>
            </button>

            {{-- ── Divider ── --}}
            <div class="py-1"><div class="border-t border-slate-100"></div></div>

            {{-- ── Section: Info ── --}}
            <p class="sidebar-section-title px-3 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Info</p>

            {{-- About SRP --}}
            <button id="sidebar-srp-btn"
                class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="sidebar-label">What is SRP?</span>
            </button>

            {{-- Report Overpricing --}}
            <a href="mailto:sccpresyo@sancarlos.gov.ph?subject=Price%20Overpricing%20Report"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition-all">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span class="sidebar-label">Report Overpricing</span>
            </a>
        </nav>

        {{-- Sidebar stat block --}}
        <div class="sidebar-stat-block px-3 pb-4">
            <div class="bg-[#186b02]/8 rounded-xl p-3 border border-[#186b02]/15" style="background:rgba(24,107,2,0.06)">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-[#186b02]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span class="text-xs font-semibold text-[#186b02]">DTI Monitored</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed">Prices are verified and monitored by the Department of Trade and Industry.</p>
            </div>
        </div>
    </aside>

    {{-- ── Main content area ─────────────────────────────────── --}}
    <div id="main-content" class="flex-1 min-w-0 flex flex-col">
        <main class="flex-1 pb-20 lg:pb-0">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="mt-8 border-t border-slate-200 bg-white hidden lg:block">
            <div class="px-6 py-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-[#186b02] text-white font-bold text-xs">₱</span>
                        <div>
                            <p class="text-sm font-bold text-slate-700 leading-none">SCC e-Presyo</p>
                            <p class="text-[10px] text-slate-400 leading-none mt-0.5">San Carlos City</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 text-center">
                        Prices monitored by the City Government of San Carlos for public awareness. &bull; Data as of {{ now()->format('F j, Y') }}
                    </p>
                </div>
            </div>
        </footer>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     MOBILE BOTTOM NAVIGATION (hidden on lg+)
     ══════════════════════════════════════════════════════════ --}}
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-white/95 backdrop-blur-md border-t border-slate-200 shadow-lg safe-area-inset-bottom">
    <div class="grid grid-cols-4 h-16">

        {{-- Home --}}
        <a href="{{ url('/sccpresyo') }}"
            class="mobile-nav-item active flex flex-col items-center justify-center gap-1 text-slate-500 hover:text-[#186b02] transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[10px] font-medium">Home</span>
        </a>

        {{-- Browse / Price Watch --}}
        <button id="mobile-browse-btn"
            class="mobile-nav-item flex flex-col items-center justify-center gap-1 text-slate-500 hover:text-[#186b02] transition-colors"
            onclick="document.getElementById('header-search')?.focus(); window.scrollTo({top:0,behavior:'smooth'});">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35M11 19A8 8 0 1 0 11 3a8 8 0 0 0 0 16z"/>
            </svg>
            <span class="text-[10px] font-medium">Search</span>
        </button>

        {{-- Compare --}}
        <button
            class="mobile-nav-item flex flex-col items-center justify-center gap-1 text-slate-500 hover:text-[#186b02] transition-colors"
            onclick="document.getElementById('tab-all')?.click(); window.scrollTo({top: document.getElementById('cards-grid')?.offsetTop - 200 || 0, behavior:'smooth'});">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span class="text-[10px] font-medium">Prices</span>
        </button>

        {{-- Favorites / Watchlist --}}
        <button id="mobile-watchlist-btn"
            class="mobile-nav-item relative flex flex-col items-center justify-center gap-1 text-slate-500 hover:text-[#186b02] transition-colors">
            <div class="relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                <span id="mobile-wl-badge" class="absolute -top-1.5 -right-1.5 hidden min-w-[16px] h-4 px-1 rounded-full bg-[#186b02] text-white text-[9px] font-bold flex items-center justify-center">0</span>
            </div>
            <span class="text-[10px] font-medium">Favorites</span>
        </button>
    </div>
</nav>

{{-- ══════════════════════════════════════════════════════════
     SRP INFO MODAL (sidebar button)
     ══════════════════════════════════════════════════════════ --}}
<div id="srp-modal-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <div class="flex items-start justify-between gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-[#186b02]/10 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-[#186b02]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <button id="srp-modal-close" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <h3 class="text-base font-bold text-slate-900 mb-2">What is SRP?</h3>
        <p class="text-sm text-slate-600 leading-relaxed mb-3">
            The <strong>Suggested Retail Price (SRP)</strong> is the government-recommended ceiling price for basic and prime commodities, set by the Department of Trade and Industry (DTI).
        </p>
        <p class="text-sm text-slate-600 leading-relaxed mb-4">
            Sellers are not legally required to follow SRP, but the <strong>Prevailing Price</strong> shown here reflects the actual market price observed in San Carlos City establishments.
        </p>
        <div class="bg-amber-50 border border-amber-200/80 rounded-xl p-3">
            <p class="text-xs text-amber-700 font-medium">💡 If a product is sold above SRP, you may report it to the DTI or the City Government.</p>
        </div>
    </div>
</div>

{{-- Price Alerts info modal --}}
<div id="alerts-modal-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <div class="flex items-start justify-between gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <button id="alerts-modal-close" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <h3 class="text-base font-bold text-slate-900 mb-2">Price Alerts</h3>
        <p class="text-sm text-slate-600 leading-relaxed mb-4">
            Price alerts notify you when a commodity's prevailing price rises above or drops below the SRP. Star any commodity to add it to your watchlist — you can review your starred items anytime.
        </p>
        <div class="bg-[#186b02]/8 border border-[#186b02]/20 rounded-xl p-3" style="background:rgba(24,107,2,0.06)">
            <p class="text-xs text-[#186b02] font-medium">⭐ Tap the star icon on any commodity card to start tracking it.</p>
        </div>
    </div>
</div>

@stack('scripts')

<script>
(function () {
    // ── Sidebar toggle ───────────────────────────────────────
    const sidebar     = document.getElementById('app-sidebar');
    const toggleBtn   = document.getElementById('sidebar-toggle');
    const SIDEBAR_KEY = 'scc_sidebar_open';

    function applySidebar(open) {
        if (!sidebar) return;
        sidebar.classList.toggle('collapsed', !open);
        localStorage.setItem(SIDEBAR_KEY, open ? '1' : '0');
    }

    // Restore state
    const savedOpen = localStorage.getItem(SIDEBAR_KEY);
    applySidebar(savedOpen === null ? true : savedOpen === '1');

    toggleBtn?.addEventListener('click', () => {
        const isCollapsed = sidebar.classList.contains('collapsed');
        applySidebar(isCollapsed);
    });

    // ── Sidebar watchlist button → opens drawer ──────────────
    document.getElementById('sidebar-watchlist-btn')?.addEventListener('click', () => {
        document.getElementById('watchlist-toggle-btn')?.click();
    });

    // ── Mobile watchlist button → opens drawer ───────────────
    document.getElementById('mobile-watchlist-btn')?.addEventListener('click', () => {
        document.getElementById('watchlist-toggle-btn')?.click();
    });

    // ── Sync sidebar & mobile watchlist badges ───────────────
    function syncSidebarBadges() {
        const wl  = new Set(JSON.parse(localStorage.getItem('pw_watchlist') || '[]'));
        const n   = wl.size;
        const sb  = document.getElementById('sidebar-wl-badge');
        const mb  = document.getElementById('mobile-wl-badge');
        const hb  = document.getElementById('watchlist-count');
        [sb, mb].forEach(el => {
            if (!el) return;
            el.textContent = n;
            el.classList.toggle('hidden', n === 0);
        });
        if (hb) {
            hb.textContent = n;
            hb.classList.toggle('hidden', n === 0);
        }
    }

    // Poll storage for badge updates (works across the same tab)
    syncSidebarBadges();
    setInterval(syncSidebarBadges, 800);

    // ── SRP info modal ───────────────────────────────────────
    const srpOverlay = document.getElementById('srp-modal-overlay');
    document.getElementById('sidebar-srp-btn')?.addEventListener('click', () => {
        srpOverlay.classList.remove('hidden');
        srpOverlay.classList.add('flex');
    });
    document.getElementById('srp-modal-close')?.addEventListener('click', () => {
        srpOverlay.classList.add('hidden');
        srpOverlay.classList.remove('flex');
    });
    srpOverlay?.addEventListener('click', e => {
        if (e.target === srpOverlay) { srpOverlay.classList.add('hidden'); srpOverlay.classList.remove('flex'); }
    });

    // ── Price Alerts modal ───────────────────────────────────
    const alertsOverlay = document.getElementById('alerts-modal-overlay');
    document.getElementById('sidebar-alerts-btn')?.addEventListener('click', () => {
        alertsOverlay.classList.remove('hidden');
        alertsOverlay.classList.add('flex');
    });
    document.getElementById('alerts-modal-close')?.addEventListener('click', () => {
        alertsOverlay.classList.add('hidden');
        alertsOverlay.classList.remove('flex');
    });
    alertsOverlay?.addEventListener('click', e => {
        if (e.target === alertsOverlay) { alertsOverlay.classList.add('hidden'); alertsOverlay.classList.remove('flex'); }
    });

    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        srpOverlay?.classList.add('hidden');    srpOverlay?.classList.remove('flex');
        alertsOverlay?.classList.add('hidden'); alertsOverlay?.classList.remove('flex');
    });
})();
</script>
</body>
</html>
