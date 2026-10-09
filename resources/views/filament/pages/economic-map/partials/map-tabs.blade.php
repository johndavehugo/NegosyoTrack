@php
    $tabs = [
        ['key' => 'hotspot', 'label' => '🔥 Economic Hotspot Map', 'url' => \App\Filament\Pages\EconomicMap\HotspotMap::getUrl()],
        ['key' => 'distribution', 'label' => '🌐 MSME Distribution Map', 'url' => \App\Filament\Pages\EconomicMap\DistributionMap::getUrl()],
        ['key' => 'risk', 'label' => '🛡 Economic Risk Map', 'url' => \App\Filament\Pages\EconomicMap\RiskMap::getUrl()],
        ['key' => 'opportunity', 'label' => '💡 Economic Opportunity Map', 'url' => \App\Filament\Pages\EconomicMap\OpportunityMap::getUrl()],
        ['key' => 'locations', 'label' => '📍 Business Locations', 'url' => \App\Filament\Pages\EconomicMap\BusinessLocations::getUrl()],
    ];
@endphp

<div class="mb-4 flex flex-wrap gap-2">
    @foreach ($tabs as $tab)
        <a
            href="{{ $tab['url'] }}"
            class="rounded-lg border px-3 py-1.5 text-xs font-medium {{ ($active ?? '') === $tab['key'] ? 'border-gray-900 bg-white text-blue-700 shadow-sm dark:bg-gray-900' : 'border-transparent text-gray-800 hover:bg-white dark:text-gray-200 dark:hover:bg-gray-900' }}"
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
