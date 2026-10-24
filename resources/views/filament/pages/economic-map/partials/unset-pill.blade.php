@php($unset = $unsetCount)

<a
    href="{{ \App\Filament\Pages\EconomicMap\BusinessLocations::getUrl() }}"
    title="Businesses whose SCIMS entity number is not yet linked to a location"
    class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800 hover:bg-amber-200 dark:bg-amber-500/10 dark:text-amber-300"
>
    📍 {{ number_format($unset) }} unset location{{ $unset === 1 ? '' : 's' }}
</a>
