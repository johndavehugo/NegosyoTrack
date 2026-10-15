@extends('layouts.public')

@section('title', 'Price Watch')

@section('content')

{{-- Header --}}
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Public Price Watch</h1>
    <p class="mt-1 text-sm text-gray-500">
        Compare the Suggested Retail Price (SRP) against the prevailing market price of monitored commodities in your area.
    </p>
</div>

{{-- Stats bar --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
    <div class="bg-white rounded-lg border border-gray-200 px-4 py-3">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Total Monitored</p>
        <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($totalMonitored) }}</p>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 px-4 py-3">
        <p class="text-xs text-gray-500 uppercase tracking-wider">With Prevailing Price</p>
        <p class="mt-1 text-2xl font-bold text-green-600">{{ number_format($withPrevailing) }}</p>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 px-4 py-3 col-span-2 sm:col-span-1">
        <p class="text-xs text-gray-500 uppercase tracking-wider">No Prevailing Price</p>
        <p class="mt-1 text-2xl font-bold text-amber-500">{{ number_format($totalMonitored - $withPrevailing) }}</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ url('/sccpresyo') }}" class="bg-white rounded-lg border border-gray-200 p-4 mb-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

        {{-- Search --}}
        <div class="lg:col-span-2">
            <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input
                type="text"
                id="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Product name or brand..."
                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
        </div>

        {{-- Agency filter --}}
        <div>
            <label for="agency" class="block text-xs font-medium text-gray-600 mb-1">Agency</label>
            <select
                id="agency"
                name="agency"
                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
                <option value="">All Agencies</option>
                @foreach ($agencies as $agency)
                    <option value="{{ $agency->id }}" {{ request('agency') == $agency->id ? 'selected' : '' }}>
                        {{ $agency->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Category filter --}}
        <div>
            <label for="category" class="block text-xs font-medium text-gray-600 mb-1">Category</label>
            <select
                id="category"
                name="category"
                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-3 flex items-center gap-2 flex-wrap">
        <button
            type="submit"
            class="inline-flex items-center gap-1.5 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19A8 8 0 1 0 11 3a8 8 0 0 0 0 16z"/>
            </svg>
            Search
        </button>

        @if (request()->hasAny(['search', 'agency', 'category']))
            <a
                href="{{ url('/sccpresyo') }}"
                class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors"
            >
                Clear Filters
            </a>
        @endif

        <span class="ml-auto text-xs text-gray-400">
            {{ number_format($variants->total()) }} {{ Str::plural('result', $variants->total()) }}
        </span>
    </div>
</form>

{{-- Results --}}
@if ($variants->isEmpty())
    <div class="bg-white rounded-lg border border-gray-200 py-16 text-center">
        <svg class="mx-auto w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 0 1 5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
        </svg>
        <p class="mt-3 text-sm text-gray-500">No results found for your search.</p>
        <a href="{{ url('/sccpresyo') }}" class="mt-3 inline-block text-sm text-blue-600 hover:underline">Clear filters</a>
    </div>
@else

    {{-- Mobile: card layout --}}
    <div class="grid grid-cols-1 gap-3 sm:hidden">
        @foreach ($variants as $variant)
            @php
                $prevailing = $variant->prevailing_price;
                $srp        = $variant->srp;
                $diff       = ($prevailing !== null && $srp !== null) ? $prevailing - $srp : null;
                $isAbove    = $diff !== null && $diff > 0;
                $isBelow    = $diff !== null && $diff < 0;
                $isEqual    = $diff !== null && $diff == 0;
            @endphp
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 text-sm leading-snug">
                            {{ $variant->commodity->product_name }}
                        </p>
                        @if ($variant->commodity->brand)
                            <p class="text-xs text-gray-500">{{ $variant->commodity->brand->name }}</p>
                        @endif
                    </div>
                    <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                        {{ $isAbove ? 'bg-red-100 text-red-700' : ($isBelow ? 'bg-green-100 text-green-700' : ($isEqual ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-500')) }}">
                        @if ($isAbove) ↑ Above SRP
                        @elseif ($isBelow) ↓ Below SRP
                        @elseif ($isEqual) = At SRP
                        @else No Data
                        @endif
                    </span>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <p class="text-xs text-gray-400">Category</p>
                        <p class="text-gray-700">{{ $variant->commodity->category->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Unit / Size</p>
                        <p class="text-gray-700">{{ $variant->unit_of_measure }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">SRP</p>
                        <p class="font-medium text-gray-900">{{ $srp !== null ? '₱ ' . number_format($srp, 2) : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Prevailing Price</p>
                        <p class="font-semibold {{ $isAbove ? 'text-red-600' : ($isBelow ? 'text-green-600' : 'text-gray-900') }}">
                            {{ $prevailing !== null ? '₱ ' . number_format($prevailing, 2) : '—' }}
                        </p>
                    </div>
                </div>

                @if ($variant->commodity->juridical)
                    <p class="mt-2 text-xs text-gray-400">
                        📍 {{ $variant->commodity->juridical->name }}
                    </p>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Desktop: table layout --}}
    <div class="hidden sm:block bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product / Brand</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Agency</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit / Size</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">SRP</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Prevailing Price</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">vs. SRP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($variants as $variant)
                        @php
                            $prevailing = $variant->prevailing_price;
                            $srp        = $variant->srp;
                            $diff       = ($prevailing !== null && $srp !== null) ? $prevailing - $srp : null;
                            $isAbove    = $diff !== null && $diff > 0;
                            $isBelow    = $diff !== null && $diff < 0;
                            $isEqual    = $diff !== null && $diff == 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $variant->commodity->product_name }}</p>
                                @if ($variant->commodity->brand)
                                    <p class="text-xs text-gray-400">{{ $variant->commodity->brand->name }}</p>
                                @endif
                                @if ($variant->commodity->juridical)
                                    <p class="text-xs text-gray-400 mt-0.5">📍 {{ $variant->commodity->juridical->name }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ $variant->commodity->category->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ $variant->commodity->category->agency->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ $variant->unit_of_measure }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 whitespace-nowrap">
                                {{ $srp !== null ? '₱ ' . number_format($srp, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold whitespace-nowrap
                                {{ $isAbove ? 'text-red-600' : ($isBelow ? 'text-green-600' : 'text-gray-900') }}">
                                {{ $prevailing !== null ? '₱ ' . number_format($prevailing, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if ($diff === null)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-500">
                                        No data
                                    </span>
                                @elseif ($isAbove)
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-700">
                                        ↑ +₱{{ number_format($diff, 2) }}
                                    </span>
                                @elseif ($isBelow)
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-700">
                                        ↓ −₱{{ number_format(abs($diff), 2) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-50 text-blue-600">
                                        = At SRP
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($variants->hasPages())
        <div class="mt-4">
            {{ $variants->links() }}
        </div>
    @endif

@endif

@endsection
