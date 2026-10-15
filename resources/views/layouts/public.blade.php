<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SCC Presyo') — {{ config('app.name') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-gray-50 antialiased">

    {{-- Navbar --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-14">
            <div class="flex items-center gap-3">
                {{-- Simple logo mark --}}
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-600 text-white font-bold text-sm">₱</span>
                <span class="font-semibold text-gray-900 tracking-tight">SCC Presyo</span>
                <span class="hidden sm:inline-block text-xs text-gray-400">Pampubliko na Listahan ng Presyo</span>
            </div>
            <span class="text-xs text-gray-400">{{ now()->format('F j, Y') }}</span>
        </div>
    </header>

    {{-- Page content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <footer class="mt-12 border-t border-gray-200 py-6 text-center text-xs text-gray-400">
        {{ config('app.name') }} &bull; Para sa kaalaman ng publiko
    </footer>

    @stack('scripts')
</body>
</html>
