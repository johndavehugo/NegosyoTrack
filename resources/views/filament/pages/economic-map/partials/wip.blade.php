<div class="rounded-lg bg-white p-6 text-center shadow-sm dark:bg-gray-900">
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/10">
        <span class="text-2xl">🚧</span>
    </div>
    <h3 class="mt-4 text-lg font-semibold">Work in progress</h3>
    <p class="mx-auto mt-2 max-w-xl text-sm text-gray-500">{{ $message }}</p>

    @if (! empty($waitingOn ?? []))
        <div class="mx-auto mt-4 max-w-xl rounded-lg bg-gray-50 p-4 text-left text-sm dark:bg-white/5">
            <p class="font-medium">Waiting on real sources for:</p>
            <ul class="mt-1 list-disc space-y-0.5 pl-5 text-gray-500">
                @foreach ($waitingOn as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! empty($ready ?? []))
        <div class="mx-auto mt-3 max-w-xl rounded-lg bg-emerald-50 p-4 text-left text-sm dark:bg-emerald-500/10">
            <p class="font-medium">Already live underneath:</p>
            <ul class="mt-1 list-disc space-y-0.5 pl-5 text-gray-500">
                @foreach ($ready as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
