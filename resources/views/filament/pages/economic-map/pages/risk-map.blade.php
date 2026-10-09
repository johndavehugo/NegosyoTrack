<x-filament-panels::page>
    @include('filament.pages.economic-map.partials.controls')

    <div class="mb-4 max-w-xl">
        @include('filament.pages.economic-map.partials.search-bar')
    </div>

    @include('filament.pages.economic-map.partials.map-tabs', ['active' => 'risk'])

    <div class="mb-4 grid grid-cols-1 gap-3 text-center sm:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-2xl font-bold">{{ number_format($calamityCount) }}</div>
            <div class="text-xs text-gray-500">Calamity events tracked</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-2xl font-bold">₱{{ number_format($totalDamage, 2) }}</div>
            <div class="text-xs text-gray-500">Recorded damages</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-2xl font-bold">{{ number_format($totalAffected) }}</div>
            <div class="text-xs text-gray-500">Businesses affected</div>
        </div>
    </div>

    @include('filament.pages.economic-map.partials.wip', [
        'message' => 'The legacy risk score multiplies business exposure by LGU hazard ratings — and those ratings currently exist only as hardcoded guesses, which we refuse to ship. The map stays parked until hazard levels have a real source (e.g. a DRRM module).',
        'waitingOn' => [
            'Barangay hazard ratings from an authoritative source (replaces the legacy hardcoded Critical/High/Moderate table)',
            'Decision on whether the calamity monitoring module API becomes the damage feed (tables are already live underneath)',
        ],
        'ready' => [
            $calamityCount.' calamity events with ₱'.number_format($totalDamage, 2).' in recorded damages, readable today via CalamityFeed',
            'Business exposure per barangay (same counts as the Hotspot tab)',
        ],
    ])
</x-filament-panels::page>
