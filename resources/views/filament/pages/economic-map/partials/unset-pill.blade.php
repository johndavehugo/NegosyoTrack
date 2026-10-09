<a
    href="{{ \App\Filament\Pages\EconomicMap\BusinessLocations::getUrl() }}"
    class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800 hover:bg-amber-200 dark:bg-amber-500/10 dark:text-amber-300"
>
    📍 {{ number_format($unsetCount) }} unset location{{ $unsetCount === 1 ? '' : 's' }}
</a>
