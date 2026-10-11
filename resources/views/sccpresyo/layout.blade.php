<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="description" content="SCCPresyo — San Carlos City Official Public Price Monitoring. Check official Suggested Retail Prices for basic goods."/>
    <title>SCCPresyo · San Carlos City Price Monitoring</title>

    {{-- Same font stack VAMOS uses: bold sans-serif civic aesthetic --}}
    <link rel="preconnect" href="https://fonts.bunny.net"/>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /*
         * VAMOS Cares Color System (from screenshot):
         * ─────────────────────────────────────────────
         * Header / hero bg : #1a3a0f  (dark forest green, near-black)
         * Hero overlay     : rgba(26,58,15,0.82) over city hall photo
         * Primary green    : #4caf50  (bright mid-green on app screen)
         * Accent green     : #43a047
         * CTA button       : white pill with dark text
         * Body bg          : #ffffff  (clean white — "Know More" section)
         * Section bg       : #f5f5f5 / #fafafa
         * Headings         : #111111  (near-black, all-caps uppercase bold)
         * Body text        : #333333
         * Divider line     : #e0e0e0
         * Card border      : #e8e8e8
         * Tag/badge bg     : #e8f5e9  (very light green)
         * Tag text         : #2e7d32
         */

        :root {
            --vamos-dark:    #1a3a0f;
            --vamos-green:   #4caf50;
            --vamos-mid:     #388e3c;
            --vamos-light:   #e8f5e9;
            --vamos-lighter: #f1f8e9;
            --body-bg:       #f5f5f5;
            --card-bg:       #ffffff;
            --border:        #e0e0e0;
            --text-main:     #111111;
            --text-sub:      #555555;
            --text-muted:    #888888;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            background: var(--body-bg);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f5f5f5; }
        ::-webkit-scrollbar-thumb { background: #4caf50; border-radius: 99px; }

        /* Ticker animation */
        @keyframes scc-ticker {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .ticker-track { animation: scc-ticker 50s linear infinite; }
        .ticker-track:hover { animation-play-state: paused; }

        /* Pulse dot */
        @keyframes vamos-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.5; transform: scale(0.8); }
        }
        .pulse-dot { animation: vamos-pulse 2s ease-in-out infinite; }

        /* Category active state */
        .cat-btn { transition: all .15s; }
        .cat-btn.active {
            background: var(--vamos-light) !important;
            border-color: #a5d6a7 !important;
            color: #2e7d32 !important;
            font-weight: 700 !important;
        }
        .cat-btn.active .cat-count {
            background: #c8e6c9;
            color: #2e7d32;
        }

        /* Table row hover */
        .price-row { transition: background .1s; }
        .price-row:hover { background: #f9fbe7; }
        .price-row:hover .price-name { color: #2e7d32; }

        /* Focus ring for inputs */
        input:focus, select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(76,175,80,.2);
            border-color: #4caf50 !important;
        }
    </style>
</head>
<body>
    {{ $slot }}
    @livewireScripts
</body>
</html>
