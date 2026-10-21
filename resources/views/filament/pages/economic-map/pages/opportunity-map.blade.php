<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('vendor/maplibre/maplibre-gl.css') }}">
    @include('filament.pages.economic-map.partials.controls')

    <div class="mb-4 max-w-xl">
        @include('filament.pages.economic-map.partials.search-bar')
    </div>

    @include('filament.pages.economic-map.partials.map-tabs', ['active' => 'opportunity'])

    @if (! $hasData)
        <div class="rounded-lg bg-white p-8 text-center shadow-sm dark:bg-gray-900">
            <p class="text-lg font-semibold text-gray-900 dark:text-white">No business data yet</p>
            <p class="mx-auto mt-2 max-w-xl text-sm text-gray-500">
                The SCIMS registry snapshot is empty. Run
                <span class="font-mono">php artisan economic-map:sync</span> or wait for the scheduled sync, then refresh this page.
            </p>
        </div>
    @else
        <div class="grid grid-cols-12 gap-4">

            {{-- ── Left sidebar ── --}}
            <div class="col-span-12 space-y-4 md:col-span-4 lg:col-span-3">

                {{-- Stat: High / Very High --}}
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $highVeryHigh }}</div>
                    <div class="mt-0.5 text-sm text-gray-500">High / Very High Areas</div>
                </div>

                {{-- Stat: Barangays Assessed --}}
                <div class="rounded-xl border-l-4 border-[#3b82f6] bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="text-3xl font-bold text-[#3b82f6]">{{ $barangayCount }}</div>
                    <div class="mt-0.5 text-sm text-gray-500">Barangays Assessed</div>
                </div>

                {{-- Where to Invest & Support --}}
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Where to Invest &amp; Support</h3>
                    <div class="space-y-2">
                        @foreach ($highlights as $h)
                            <div class="flex items-center justify-between gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-white/5">
                                <span class="flex items-center gap-1.5 text-xs text-gray-500">
                                    <span>{{ $h['icon'] }}</span>
                                    <span>{{ $h['label'] }}</span>
                                </span>
                                <span class="shrink-0 text-xs font-semibold text-gray-800 dark:text-gray-100">{{ $h['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- How it's Calculated --}}
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">How It's Calculated</h3>
                    <ul class="space-y-1.5 text-xs text-gray-500">
                        @foreach ($howCalculated as $item)
                            <li>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $item['term'] }}</span>
                                — {{ $item['detail'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Barangay selector --}}
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Barangay Ranking</h3>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500 dark:bg-white/10">{{ $barangayCount }} ranked</span>
                    </div>
                    <p class="mt-3 text-xs font-medium uppercase tracking-wide text-gray-400">Highest score to lowest</p>
                    <select
                        wire:model.live="selectedBarangay"
                        class="emap-select mt-1 w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-3 text-sm font-medium shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-gray-900 dark:focus:ring-blue-500/20"
                    >
                        <option value="">🌱 Select barangay…</option>
                        @foreach ($ranked as $index => $entry)
                            <option value="{{ $entry['barangay'] }}">
                                {{ $index + 1 }}. {{ $entry['barangay'] }} — {{ $entry['level'] }}
                            </option>
                        @endforeach
                    </select>

                    @if ($summary)
                        @php
                            $rankPos   = collect($ranked)->search(fn ($e) => $e['barangay'] === $summary['barangay']);
                            $rankEntry = $rankPos !== false ? $ranked[$rankPos] : null;
                        @endphp
                        <div class="mt-2 rounded-lg bg-blue-50 p-3 ring-1 ring-blue-100 dark:bg-blue-500/10 dark:ring-blue-500/20">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">
                                    {{ $rankPos !== false ? $rankPos + 1 : '–' }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ $summary['barangay'] }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ number_format($summary['total']) }} MSMEs ·
                                        <span class="font-semibold" @if ($rankEntry) style="color: {{ $rankEntry['color'] }};" @endif>
                                            {{ $rankEntry['level'] ?? '' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (count($pins) > 0)
                        <button type="button" wire:click="clearSelection" class="mt-2 rounded-lg px-3 py-1.5 text-xs ring-1 ring-gray-300 hover:bg-gray-50 dark:ring-white/10">
                            ✕ Clear locations
                        </button>
                    @endif
                </div>

            </div>

            {{-- ── Map panel ── --}}
            <div class="col-span-12 md:col-span-8 md:flex md:flex-col lg:col-span-9">
                <div class="overflow-hidden rounded-lg shadow-sm md:flex md:min-h-0 md:flex-1 md:flex-col">

                    {{-- Header bar --}}
                    <div class="flex h-10 shrink-0 items-center gap-2 bg-[#1e2a78] px-4">
                        <p class="flex-1 truncate text-center text-xs font-bold uppercase tracking-wider text-white">
                            🌱 Economic Opportunity Map — Investment &amp; Development Guidance
                        </p>
                        <span class="shrink-0 rounded-full bg-white px-3 py-0.5 text-xs font-semibold text-[#1e2a78]">
                            {{ $barangayCount }} Barangays Assessed
                        </span>
                    </div>

                    {{-- Map container --}}
                    <div class="relative md:min-h-0 md:flex-1">
                        <div id="opportunity-map" wire:ignore class="h-[60vh] w-full md:h-full"></div>

                        {{-- Legend --}}
                        <div class="absolute bottom-10 right-3 rounded-lg bg-white/95 p-3 text-xs shadow-lg dark:bg-gray-900/95">
                            <p class="mb-1.5 font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200">Opportunity Level</p>
                            @foreach (config('economic-map.opportunity_bands', []) as $band)
                                <div class="flex items-center gap-2 py-0.5 text-gray-600 dark:text-gray-300">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $band['color'] }};"></span>
                                    {{ $band['label'] }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>

        </div>
    @endif

    @include('filament.pages.economic-map.partials.info-bar', [
        'slot' => 'Opportunity scores blend commercial density, new-registration growth, sector diversity, agriculture share, and livelihood gap — all relative to other barangays. Business counts come from the SCIMS registry; barangays without GPS coordinates are plotted at their approximate centre. Population is sourced from the Philippine Standard Geographic Code API.',
    ])

    <script type="module">
        import * as maplibregl from '{{ asset('vendor/maplibre/maplibre-gl.mjs') }}';

        const circles  = @js($ranked);
        const bounds   = @js($bounds);
        const maxCount = Math.max(...circles.map((c) => c.count), 1);
        const basemap  = @js(config('economic-map.basemap'));
        const el       = document.getElementById('opportunity-map');

        const norm = (e) => e?.detail?.[0] ?? e?.detail ?? {};
        let map = null;

        const radius = (count) => 6 + 10 * Math.sqrt(count / maxCount);

        function circleFeatures() {
            return circles.map((c) => ({
                type: 'Feature',
                properties: {
                    barangay: c.barangay,
                    count: c.count,
                    level: c.level,
                    color: c.color,
                    radius: radius(c.count),
                },
                geometry: { type: 'Point', coordinates: [c.lng, c.lat] },
            }));
        }

        function pinFeatures(pins) {
            return (pins ?? []).map((p) => ({
                type: 'Feature',
                properties: { name: p.name, street: p.street, industry: p.industry },
                geometry: { type: 'Point', coordinates: [p.lng, p.lat] },
            }));
        }

        function renderPins(pins) {
            if (! map) return;
            map.getSource('pins')?.setData({ type: 'FeatureCollection', features: pinFeatures(pins) });
        }

        function boot() {
            if (! el || map) return;

            if (el.clientHeight === 0) {
                let waits = 0;
                const timer = setInterval(() => {
                    if (el.clientHeight > 0 || ++waits > 100) {
                        clearInterval(timer);
                        if (el.clientHeight > 0) boot();
                        else el.innerHTML = '<p class="p-8 text-center text-sm text-gray-500">Map failed to initialise (no render size). Reload the page.</p>';
                    }
                }, 50);
                return;
            }

            map = new maplibregl.Map({
                container: el,
                style: { version: 8, sources: {}, layers: [] },
                center: [0, 0],
                zoom: 2,
            });

            map.addControl(new maplibregl.NavigationControl(), 'top-left');
            map.addControl(new maplibregl.ScaleControl({ position: 'bottom-right' }));

            map.on('load', () => {
                map.resize();

                // Basemap
                map.addSource('osm', {
                    type: 'raster',
                    tiles: basemap.tiles,
                    tileSize: basemap.tile_size,
                    attribution: basemap.attribution,
                    maxzoom: basemap.max_zoom,
                });
                map.addLayer({ id: 'osm', type: 'raster', source: 'osm' });

                // Opportunity circles
                map.addSource('circles', {
                    type: 'geojson',
                    data: { type: 'FeatureCollection', features: circleFeatures() },
                });
                map.addLayer({
                    id: 'circles',
                    type: 'circle',
                    source: 'circles',
                    paint: {
                        'circle-radius':        ['get', 'radius'],
                        'circle-color':         ['get', 'color'],
                        'circle-opacity':       0.70,
                        'circle-stroke-width':  2,
                        'circle-stroke-color':  '#ffffff',
                    },
                });

                // Barangay labels inside circles
                map.addLayer({
                    id: 'circle-labels',
                    type: 'symbol',
                    source: 'circles',
                    layout: {
                        'text-field':  ['get', 'barangay'],
                        'text-size':   10,
                        'text-anchor': 'center',
                    },
                    paint: {
                        'text-color':       '#ffffff',
                        'text-halo-color':  'rgba(0,0,0,0.4)',
                        'text-halo-width':  1,
                    },
                });

                // Individual business pins (loaded on barangay select)
                map.addSource('pins', {
                    type: 'geojson',
                    data: { type: 'FeatureCollection', features: [] },
                });
                map.addLayer({
                    id: 'pins',
                    type: 'circle',
                    source: 'pins',
                    paint: {
                        'circle-radius':       5,
                        'circle-color':        '#1d4ed8',
                        'circle-stroke-width': 2,
                        'circle-stroke-color': '#ffffff',
                    },
                });

                // Popup on circle click
                const popup = new maplibregl.Popup({ closeButton: false, closeOnClick: false });

                map.on('click', 'circles', (e) => {
                    const props = e.features?.[0]?.properties;
                    if (! props) return;

                    popup
                        .setLngLat(e.lngLat)
                        .setHTML(
                            `<div class="text-xs">
                                <p class="font-bold">${props.barangay}</p>
                                <p>${props.level} · ${props.count} MSMEs</p>
                            </div>`
                        )
                        .addTo(map);

                    if (window.Livewire) {
                        window.Livewire.dispatch('emap-select-barangay', { barangay: props.barangay });
                    }
                });

                map.on('mouseenter', 'circles', () => (map.getCanvas().style.cursor = 'pointer'));
                map.on('mouseleave', 'circles', () => {
                    map.getCanvas().style.cursor = '';
                    popup.remove();
                });

                if (bounds) {
                    map.fitBounds(
                        [[bounds[1], bounds[0]], [bounds[3], bounds[2]]],
                        { padding: 40 }
                    );
                }
            });
        }

        window.addEventListener('emap-focus', (e) => {
            const d = norm(e);
            const found = circles.find((c) => c.barangay === d.barangay);
            if (map && found) map.flyTo({ center: [found.lng, found.lat], zoom: 14 });
            renderPins(d.pins);
        });

        window.addEventListener('emap-focus-street', (e) => {
            const d = norm(e);
            if (map && d.lat && d.lng) map.flyTo({ center: [d.lng, d.lat], zoom: 16 });
            renderPins(d.pins);
        });

        window.addEventListener('emap-clear-pins',      () => renderPins([]));
        window.addEventListener('emap-data-refreshed',  () => window.location.reload());

        document.addEventListener('livewire:navigated', () => boot());

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => map && map.resize(), 200);
        });

        boot();
    </script>
</x-filament-panels::page>
