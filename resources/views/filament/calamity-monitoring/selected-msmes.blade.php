@php
    $total = collect($damages)->sum(
        fn (array $damage): float => (float) ($damage['estimated_cost_of_damages'] ?? 0),
    );
    $withPhotos = collect($damages)
        ->filter(fn (array $damage): bool => filled($damage['photos'] ?? []))
        ->count();
@endphp

<div class="space-y-4">
    @if ($damages === [])
        <p class="text-sm text-gray-500 dark:text-gray-400">
            No MSMEs have been selected yet.
        </p>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">MSME</th>
                            <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Barangay</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Estimated Cost</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Photos</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($damages as $damage)
                            @php
                                $photos = is_array($damage['photos'] ?? null) ? $damage['photos'] : [];
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-950 dark:text-white">
                                    {{ $damage['juridical_name'] ?? $damage['juridical_id'] }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                    {{ $damage['barangay'] ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-950 dark:text-white">
                                    ₱{{ number_format((float) ($damage['estimated_cost_of_damages'] ?? 0), 2) }}
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">
                                    {{ count($photos) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <x-filament::button
                                        color="danger"
                                        icon="heroicon-m-trash"
                                        size="xs"
                                        type="button"
                                        wire:click="removeMsme('{{ $damage['juridical_id'] }}')"
                                        wire:confirm="Remove this MSME from the selection?"
                                    >
                                        Remove
                                    </x-filament::button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-300">
            <span>{{ count($damages) }} MSME(s) selected</span>
            <span>Total estimated damage: <strong class="text-gray-950 dark:text-white">₱{{ number_format((float) $total, 2) }}</strong></span>
            <span>{{ $withPhotos }} with photo evidence</span>
        </div>
    @endif
</div>
