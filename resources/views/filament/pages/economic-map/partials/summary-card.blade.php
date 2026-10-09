@if ($summary)
    <div class="rounded-lg bg-white p-3 text-sm shadow-sm dark:bg-gray-900">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-base font-semibold">{{ $summary['barangay'] }}</h3>
                @if ($summary['street'])
                    <p class="text-sm text-gray-500">{{ $summary['street'] }}</p>
                @endif
            </div>
            <button type="button" wire:click="clearSelection" class="text-sm text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 text-center">
            <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                <div class="text-xl font-bold">{{ number_format($summary['total']) }}</div>
                <div class="text-xs text-gray-500">MSMEs</div>
            </div>
            <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                <div class="text-xl font-bold">{{ number_format($summary['new']) }}</div>
                <div class="text-xs text-gray-500">New</div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-1.5">
            @foreach ($summary['sizes'] as $size => $count)
                @if ($count > 0)
                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs dark:bg-white/10">{{ $size }} · {{ $count }}</span>
                @endif
            @endforeach
        </div>

        @if (count($summary['top_industries']) > 0)
            <div class="mt-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Top industries</p>
                <ul class="mt-1 space-y-1 text-sm">
                    @foreach ($summary['top_industries'] as $industry)
                        <li class="flex justify-between">
                            <span class="truncate">{{ $industry['name'] }}</span>
                            <span class="ml-2 font-semibold">{{ $industry['count'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (count($summary['sector_mix']) > 0)
            <div class="mt-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Sector mix</p>
                <div class="mt-2 space-y-1.5">
                    @foreach (array_slice($summary['sector_mix'], 0, 5) as $sector)
                        <div>
                            <div class="flex justify-between text-xs">
                                <span class="truncate">{{ $sector['name'] }}</span>
                                <span>{{ $sector['share'] }}%</span>
                            </div>
                            <div class="h-1.5 rounded-full bg-gray-100 dark:bg-white/10">
                                <div class="h-1.5 rounded-full" style="width: {{ $sector['share'] }}%; background: {{ $sector['color'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
