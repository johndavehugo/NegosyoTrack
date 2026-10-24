<x-filament-panels::page>
    @if (filled($calamityId))
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->getChartWidgets() as $widget)
                @livewire($widget, ['calamityId' => $calamityId], key(class_basename($widget).'-'.$calamityId))
            @endforeach
        </div>
    @endif

    {{ $this->table }}
</x-filament-panels::page>
