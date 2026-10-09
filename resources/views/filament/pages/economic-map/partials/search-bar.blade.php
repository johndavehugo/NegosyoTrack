<div class="relative">
    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">⌕</span>
    <input
        type="text"
        wire:model.live.debounce.500ms="searchQuery"
        placeholder="Search barangay or street…"
        autocomplete="off"
        class="emap-input w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-9 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-gray-900 dark:focus:ring-blue-500/20"
    />

    @if ($searchQuery !== '')
        <button
            type="button"
            wire:click="clearSearch"
            class="absolute right-3 top-1/2 flex h-5 w-5 -translate-y-1/2 items-center justify-center rounded-full bg-gray-100 text-xs text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-white/10"
        >
            ✕
        </button>
    @endif

    @if (count($searchResults) > 0)
        <ul class="emap-results absolute z-20 mt-2 w-full overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            @foreach ($searchResults as $index => $match)
                @php
                    $label = e($match['label']);
                    $q = trim($searchQuery);
                    if ($q !== '' && stripos($label, $q) !== false) {
                        $label = str_ireplace(e($q), '<mark>'.e($q).'</mark>', $label);
                    }
                @endphp
                <li class="{{ $index > 0 ? 'border-t border-gray-100 dark:border-white/5' : '' }}">
                    <button
                        type="button"
                        wire:click="selectSearchResult({{ $index }})"
                        class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm hover:bg-blue-50 dark:hover:bg-blue-500/10"
                    >
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs {{ $match['type'] === 'barangay' ? 'bg-blue-100' : 'bg-gray-100 dark:bg-white/10' }}">
                            {{ $match['type'] === 'barangay' ? '🏘' : '📍' }}
                        </span>
                        <span class="min-w-0 flex-1 truncate text-gray-800 dark:text-gray-200">{!! $label !!}</span>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-xs {{ $match['type'] === 'barangay' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500 dark:bg-white/10' }}">
                            {{ $match['type'] }}
                        </span>
                    </button>
                </li>
            @endforeach
        </ul>
    @elseif ($searchQuery !== '')
        <p class="absolute z-20 mt-2 w-full rounded-xl bg-white px-4 py-3 text-sm text-gray-400 shadow-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            No matches — try a barangay or street name.
        </p>
    @endif
</div>
