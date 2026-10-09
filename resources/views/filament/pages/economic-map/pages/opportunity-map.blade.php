<x-filament-panels::page>
    @include('filament.pages.economic-map.partials.controls')

    <div class="mb-4 max-w-xl">
        @include('filament.pages.economic-map.partials.search-bar')
    </div>

    @include('filament.pages.economic-map.partials.map-tabs', ['active' => 'opportunity'])

    <div class="mb-4 grid grid-cols-1 gap-3 text-center sm:grid-cols-2">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-2xl font-bold">{{ number_format($geocodedCount) }}</div>
            <div class="text-xs text-gray-500">Geocoded businesses (live inputs ready)</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            @include('filament.pages.economic-map.partials.unset-pill')
            <div class="mt-1 text-xs text-gray-500">awaiting coordinates</div>
        </div>
    </div>

    @include('filament.pages.economic-map.partials.wip', [
        'message' => 'The legacy opportunity score mixes live business density with static tourism, agriculture, population, and infrastructure guesses. We ship no static values, so the tab stays parked until those four inputs have real sources.',
        'waitingOn' => [
            'Tourism potential per barangay from a real source',
            'Agriculture potential per barangay from a real source',
            'Population figures from a real source (replaces the 2020 census hardcode)',
            'Infrastructure levels from a real source',
        ],
        'ready' => [
            'Commercial density, growth (new registrations), and sector diversity — computable live today',
            'The scoring formula is preserved and will be re-enabled once inputs are real',
        ],
    ])
</x-filament-panels::page>
