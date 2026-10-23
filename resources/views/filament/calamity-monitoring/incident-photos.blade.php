<div class="space-y-4">
    @if ($photos === [])
        <p class="text-sm text-gray-500 dark:text-gray-400">
            No photo evidence files found for this incident.
        </p>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach ($photos as $photo)
                <a
                    href="{{ $photo }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group block overflow-hidden rounded-xl border border-gray-200 bg-gray-50 transition hover:border-primary-400 dark:border-white/10 dark:bg-white/5 dark:hover:border-primary-500"
                >
                    <img
                        src="{{ $photo }}"
                        alt="Photo evidence for {{ $incident->juridical_name ?? $incident->juridical_id }}"
                        class="h-64 w-full object-contain"
                    >

                    <span class="flex items-center justify-center border-t border-gray-200 px-4 py-2 text-sm font-medium text-primary-600 dark:border-white/10 dark:text-primary-400">
                        Open full image
                    </span>
                </a>
            @endforeach
        </div>
    @endif
</div>
