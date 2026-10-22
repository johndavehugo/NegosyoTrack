<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="description" content="SCCPresyo — San Carlos City Official SRP Price Monitor."/>
    <title>SCCPresyo · Price Monitor</title>

    @livewireStyles

    {{-- ApexCharts (already published by filament:upgrade) --}}
    <script src="{{ asset('js/app/components/apexcharts.js') }}"></script>

    <style>
    /*
     * SCCPresyo — VAMOS Cares color system
     * ──────────────────────────────────────
     * Header / nav bg : #1a3a0f   dark forest green
     * Primary green   : #4caf50   buttons, accents, prices
     * Mid green       : #2e7d32   hover, darker accents
     * Light green bg  : #e8f5e9   selected pills, row hover
     * Body bg         : #f5f5f5   off-white page
     * Card bg         : #ffffff   white panels
     * Border          : #e0e0e0
     * Text main       : #111111
     * Text sub        : #555555
     * Text muted      : #888888
     */

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --g-dark:   #1a3a0f;
        --g-mid:    #2e7d32;
        --g-main:   #4caf50;
        --g-light:  #e8f5e9;
        --g-bright: #a5d6a7;
        --body:     #f5f5f5;
        --card:     #ffffff;
        --border:   #e0e0e0;
        --t-main:   #111111;
        --t-sub:    #555555;
        --t-muted:  #888888;
        --t-faint:  #bbbbbb;
    }

    html { scroll-behavior: smooth; }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
                     'Helvetica Neue', Arial, sans-serif;
        background: var(--body);
        color: var(--t-main);
        font-size: 14px;
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
    }

    /* Scrollbar */
    ::-webkit-scrollbar       { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: #f0f0f0; }
    ::-webkit-scrollbar-thumb { background: var(--g-main); border-radius: 3px; }

    /* Nav tabs scrollbar — hide it, scroll still works */
    .scc-nav-tabs::-webkit-scrollbar { display: none; }
    .scc-nav-tabs { -ms-overflow-style: none; scrollbar-width: none; }

    /* Commodity modal two-col — stack on mobile */
    @media (max-width: 640px) {
        .commodity-modal-cols { grid-template-columns: 1fr !important; }
        .commodity-modal-left { border-right: none !important; border-bottom: 1px solid #e0e0e0; }
    }

    /* Ticker */
    @keyframes scc-tick { from { transform:translateX(0) } to { transform:translateX(-50%) } }
    .ticker-track { animation: scc-tick 55s linear infinite; }
    .ticker-track:hover { animation-play-state: paused; }

    /* Live dot */
    @keyframes livepulse { 0%,100%{opacity:1} 50%{opacity:.3} }
    .live-dot { animation: livepulse 1.8s ease-in-out infinite; }

    /* Spinner */
    @keyframes spin { to { transform: rotate(360deg) } }
    .scc-spin { animation: spin .65s linear infinite; }

    /* Price table */
    .ptable { width:100%; border-collapse:collapse; }
    .ptable thead th {
        padding: 10px 14px;
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em;
        color: var(--t-muted);
        background: #fafafa;
        border-bottom: 2px solid var(--g-light);
        white-space: nowrap;
        text-align: left;
    }
    .ptable thead th:last-child { text-align: right; }
    .ptable tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
    .ptable tbody tr:last-child { border-bottom: none; }
    .ptable tbody tr:hover { background: var(--g-light); }
    .ptable tbody tr:hover .pname { color: var(--g-mid); }
    .ptable td { padding: 13px 14px; vertical-align: middle; }
    .ptable td:last-child { text-align: right; }

    /* Responsive col visibility */
    .c-brand { display:none; }
    .c-estab { display:none; }
    .c-unit  { display:none; }
    .c-cat   { display:none; }
    @media (min-width:560px) { .c-unit  { display:table-cell; } }
    @media (min-width:720px) { .c-brand { display:table-cell; } }
    @media (min-width:900px) { .c-estab { display:table-cell; } .c-cat { display:table-cell; } }

    /* Category pill bar */
    .pill-bar {
        display: flex; gap: 7px; flex-wrap: nowrap;
        overflow-x: auto; padding-bottom: 4px; -webkit-overflow-scrolling: touch;
    }
    .pill-bar::-webkit-scrollbar { height: 3px; }

    .pill {
        flex-shrink: 0;
        padding: 6px 14px;
        border-radius: 99px;
        font-size: 12px; font-weight: 600;
        border: 1.5px solid var(--border);
        background: var(--card);
        color: var(--t-sub);
        cursor: pointer;
        white-space: nowrap;
        transition: all .12s;
    }
    .pill:hover { border-color: var(--g-main); color: var(--g-mid); }
    .pill.active {
        background: var(--g-light);
        border-color: var(--g-bright);
        color: var(--g-mid);
        font-weight: 700;
    }

    /* Inputs */
    .scc-input {
        width: 100%;
        padding: 9px 14px 9px 36px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        background: var(--card);
        font-size: 13px;
        color: var(--t-main);
        transition: border-color .15s;
    }
    .scc-input:focus { outline: none; border-color: var(--g-main); box-shadow: 0 0 0 3px #4caf5020; }

    .scc-select {
        padding: 9px 13px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        background: var(--card);
        font-size: 12px; font-weight: 600;
        color: var(--t-sub);
        cursor: pointer;
        transition: border-color .15s;
    }
    .scc-select:focus { outline: none; border-color: var(--g-main); }

    /* Buttons */
    .btn-primary {
        background: var(--g-main);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 9px 18px;
        font-size: 13px; font-weight: 700;
        cursor: pointer;
        transition: background .12s;
    }
    .btn-primary:hover { background: var(--g-mid); }

    .btn-ghost {
        background: transparent;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        padding: 7px 14px;
        font-size: 12px; font-weight: 600;
        color: var(--t-sub);
        cursor: pointer;
        transition: all .12s;
    }
    .btn-ghost:hover { border-color: var(--g-main); color: var(--g-mid); }

    /* Chip (active filter) */
    .chip {
        display: inline-flex; align-items: center; gap: 5px;
        background: var(--g-light);
        border: 1px solid var(--g-bright);
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 11px; font-weight: 700;
        color: var(--g-mid);
    }
    .chip button { background: none; border: none; cursor: pointer; color: inherit; opacity: .7; font-size: 13px; line-height: 1; }
    .chip button:hover { opacity: 1; }

    /* Pagination */
    .pager { display:flex; gap:4px; align-items:center; flex-wrap:wrap; }
    .pager-btn {
        padding: 6px 12px;
        border: 1.5px solid var(--border);
        border-radius: 7px;
        background: var(--card);
        font-size: 12px; font-weight: 600;
        color: var(--t-sub);
        cursor: pointer;
        transition: all .12s;
    }
    .pager-btn:hover { border-color: var(--g-main); color: var(--g-mid); }
    .pager-btn.active { background: var(--g-dark); border-color: var(--g-dark); color: #fff; }
    .pager-btn:disabled { opacity:.35; cursor:not-allowed; }

    /* Utility */
    .mono { font-family: 'SF Mono','Fira Code',ui-monospace,monospace; }
    .truncate { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    </style>
</head>
<body>
    {!! $slot !!}
    @livewireScripts
</body>
</html>
