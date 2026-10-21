<x-filament-panels::page>
    @include('filament.pages.economic-map.partials.map-tabs', ['active' => 'locations'])

    @include('filament.pages.economic-map.partials.info-bar', [
        'slot' => 'Read-only directory sourced live from SCIMS. Select a row to open the business profile. Map pins are shown only when SCIMS provides real coordinates; barangay totals on the other map views still include every registered business.',
    ])

    <div class="mt-4">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
