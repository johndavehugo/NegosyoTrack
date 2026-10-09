@php
    $heading = $this->getHeading();
    $headerActions = $this->getCachedHeaderActions();
    $headerActionsAlignment = $this->getHeaderActionsAlignment();
    $breadcrumbs = filament()->hasBreadcrumbs() ? $this->getBreadcrumbs() : [];
    $subheading = $this->getSubheading();
@endphp

<div class="flex flex-wrap items-end justify-between gap-x-4 gap-y-3">
    <div class="min-w-0 flex-1">
        <x-filament-panels::header
            :actions="$headerActions"
            :actions-alignment="$headerActionsAlignment"
            :breadcrumbs="$breadcrumbs"
            :heading="$heading"
            :subheading="$subheading"
        />
    </div>

    <div
        x-data="{ searching: false }"
        @input="searching = ($event.target.value.trim().length >= 2)"
        @submit="searching = true"
        x-on:scims-settled.window="searching = false"
        class="relative w-full max-w-md"
    >
    <form wire:submit="searchScims">
        <div class="relative">
            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">⌕</span>
            <input
                type="text"
                wire:model.live.debounce.500ms="scimsQuery"
                placeholder="Search SCIMS by business name…"
                autocomplete="off"
                class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-11 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-gray-900 dark:focus:ring-blue-500/20"
            />
            <button
                type="submit"
                title="Search from SCIMS"
                class="absolute right-1.5 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10"
            >
                <span x-show="! searching">⌕</span>
                <svg x-show="searching" style="display: none;" class="h-4 w-4 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </button>
        </div>
    </form>

        @php
            $typedQuery = trim($this->scimsQuery);
            $queryLength = mb_strlen($typedQuery);
            $resultsFresh = $typedQuery !== '' && $typedQuery === $this->scimsSearchedFor;
        @endphp

        @if ($queryLength === 1)
            <div class="absolute z-20 mt-2 w-full rounded-xl bg-white px-4 py-3 text-sm text-gray-400 shadow-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                Please enter at least 2 characters
            </div>
        @elseif ($queryLength >= 2)
            <div class="absolute z-20 mt-2 w-full overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div>
                    @if ($resultsFresh)
                        @if (count($this->scimsResults) > 0)
                            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2 dark:border-white/5">
                                <span class="text-xs font-medium text-gray-500">
                                    @if ($this->scimsTotalCount > count($this->scimsResults))
                                        Showing {{ count($this->scimsResults) }} of {{ number_format($this->scimsTotalCount) }} — refine to narrow
                                    @else
                                        {{ count($this->scimsResults) }} match{{ count($this->scimsResults) === 1 ? '' : 'es' }} — pick one to autofill
                                    @endif
                                </span>
                                <button type="button" wire:click="clearScimsResults" class="text-xs text-gray-400 hover:text-gray-600">✕</button>
                            </div>
                            <ul class="max-h-72 overflow-auto">
                                @foreach ($this->scimsResults as $index => $row)
                                    <li class="{{ $index > 0 ? 'border-t border-gray-100 dark:border-white/5' : '' }}">
                                        <button
                                            type="button"
                                            wire:click="selectScimsResult({{ $index }})"
                                            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left hover:bg-blue-50 dark:hover:bg-blue-500/10"
                                        >
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-medium text-gray-900 dark:text-white">{{ $row['business']['name'] ?? '—' }}</span>
                                                <span class="block truncate text-xs text-gray-400">
                                                    {{ $row['business']['entity_no'] ?? '—' }}
                                                    @if (! empty($row['business']['address']['city']))
                                                        · {{ $row['business']['address']['city'] }}
                                                    @endif
                                                </span>
                                            </span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-sm text-gray-400">No result</span>
                                <button type="button" wire:click="clearScimsResults" class="text-xs text-gray-400 hover:text-gray-600">✕</button>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    </form>
    </div>
</div>

<script>
    // Backup: clears the search spinner when any Livewire request settles
    // (covers failures, where no re-render resets Alpine state).
    document.addEventListener('livewire:init', () => {
        if (window.__scimsHookRegistered) return;
        window.__scimsHookRegistered = true;
        Livewire.hook('commit', ({ succeed, fail }) => {
            succeed(() => window.dispatchEvent(new Event('scims-settled')));
            fail(() => window.dispatchEvent(new Event('scims-settled')));
        });
    });
</script>
