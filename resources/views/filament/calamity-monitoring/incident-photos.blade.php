<div class="space-y-4">
    @if ($photos === [])
        <p class="text-sm text-gray-500 dark:text-gray-400">
            No photo evidence files found for this incident.
        </p>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach ($photos as $photo)
                <figure
                    class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5"
                >
                    <a
                        href="{{ $photo['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="block"
                    >
                        <img
                            src="{{ $photo['url'] }}"
                            alt="Photo evidence for {{ $incident->juridical_name ?? $incident->juridical_id }}"
                            class="h-64 w-full object-contain"
                        >
                    </a>

                    <figcaption
                        class="flex items-center justify-between gap-2 border-t border-gray-200 px-4 py-2 dark:border-white/10"
                    >
                        <a
                            href="{{ $photo['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-sm font-medium text-primary-600 dark:text-primary-400"
                        >
                            Open full image
                        </a>

                        <x-filament::button
                            color="danger"
                            icon="heroicon-m-trash"
                            size="xs"
                            type="button"
                            wire:click="deleteIncidentPhoto({{ $incident->id }}, '{{ $photo['path'] }}')"
                            wire:confirm="Delete this photo? This cannot be undone."
                        >
                            Delete
                        </x-filament::button>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    @endif
</div>
