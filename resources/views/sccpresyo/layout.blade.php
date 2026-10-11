<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="description" content="San Carlos City SCCPresyo — Official Public Price Monitoring. Check Suggested Retail Prices (SRP) for basic goods and commodities."/>
    <title>SCCPresyo · San Carlos City Price Monitoring</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net"/>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet"/>

    {{-- App CSS (Tailwind v4) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Livewire styles --}}
    @livewireStyles
</head>
<body class="antialiased bg-slate-50 text-slate-800">

    {{ $slot }}

    {{-- Livewire scripts --}}
    @livewireScripts
</body>
</html>
