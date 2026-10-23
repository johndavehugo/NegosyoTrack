@php
    $record = $getRecord();
    $photos = collect($record->photos ?? [])
        ->filter(fn ($photo): bool => filled($photo))
        ->values();

    $preview = $photos->first(
        fn ($photo): bool => \Illuminate\Support\Facades\Storage::disk('public')->exists($photo)
    );
@endphp

@if ($photos->isNotEmpty())
    <div class="flex w-full flex-nowrap items-center justify-center gap-1.5">
        @if ($preview)
            <img
                src="{{ '/storage/'.ltrim($preview, '/') }}"
                alt=""
                class="h-6 w-6 shrink-0 rounded border border-gray-200 object-cover dark:border-white/10"
            >
        @endif

        <x-filament::button
            aria-label="View photo evidence"
            class="shrink-0 whitespace-nowrap"
            color="gray"
            icon="heroicon-m-photo"
            size="xs"
            title="View photo evidence"
            wire:click.prevent.stop="mountTableAction('viewPhotos', '{{ $getRecordKey() }}')"
        >
            View
        </x-filament::button>
    </div>
@else
    <div class="flex w-full items-center justify-center">
        <x-filament::button
            class="shrink-0 whitespace-nowrap"
            color="primary"
            icon="heroicon-m-plus"
            size="xs"
            wire:click.prevent.stop="mountTableAction('addPhotos', '{{ $getRecordKey() }}')"
        >
            Add photo
        </x-filament::button>
    </div>
@endif
