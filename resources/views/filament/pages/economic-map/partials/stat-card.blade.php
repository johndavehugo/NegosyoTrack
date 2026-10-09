<div class="rounded-lg border-l-4 bg-white p-3 shadow-sm dark:bg-gray-900" style="border-left-color: {{ $color }};">
    <p class="text-sm text-gray-500">{{ $label }}</p>
    <p class="truncate text-xl font-bold text-gray-900 dark:text-white" @if (! empty($title)) title="{{ $title }}" @endif>{{ $value }}</p>
    @if (! empty($slot) && trim($slot) !== '')
        <div class="mt-1">{{ $slot }}</div>
    @endif
</div>
