<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('vendor/maplibre/maplibre-gl.css') }}">
    @include('filament.pages.economic-map.partials.controls')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    <div class="mb-4 max-w-xl">
        @include('filament.pages.economic-map.partials.search-bar')
    </div>

    @include('filament.pages.economic-map.partials.map-tabs', ['active' => 'distribution'])

    @if (! $hasData)
        <div class="rounded-lg bg-white p-8 text-center shadow-sm dark:bg-gray-900">
            <p class="text-lg font-semibold text-gray-900 dark:text-white">No geocoded businesses yet</p>
            <p class="mx-auto mt-2 max-w-xl text-sm text-gray-500">
                Sector distribution needs coordinates to plot. Set them in
                <a href="{{ \App\Filament\Pages\EconomicMap\BusinessLocations::getUrl() }}" class="font-medium text-blue-700 hover:underline">Business Locations</a>.
            </p>
        </div>
    @else
        <div class="grid grid-cols-12 gap-4">
            {{-- Left sidebar --}}
            <div class="col-span-12 space-y-4 md:col-span-4 lg:col-span-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Distribution by Sector</h3>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500 dark:bg-white/10">{{ number_format($total) }} MSMEs</span>
                </div>

                <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Filter by sector</p>
                    <select
                        wire:model.live="sectorFilter"
                        class="emap-select mt-1 w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-3 text-sm font-medium shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-gray-900 dark:focus:ring-blue-500/20"
                    >
                        <option value="">All Sectors</option>
                        @foreach ($sectors as $sector)
                            <option value="{{ $sector['name'] }}">{{ $sector['name'] }} — {{ $sector['count'] }}</option>
                        @endforeach
                    </select>
                    @if ($activeSector)
                        <p class="mt-1 text-xs text-gray-500">{{ count($sectorPins) }} pin{{ count($sectorPins) === 1 ? '' : 's' }} on the map.</p>
                    @endif
                </div>

                <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-900">
                    <p class="mb-1.5 text-xs font-medium uppercase tracking-wide text-gray-400">Legend</p>
                    <ul class="space-y-1 text-xs">
                        @foreach ($sectors as $sector)
                            <li class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $sector['color'] }};"></span>
                                <span class="truncate">{{ $sector['name'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-900">
                    <p class="mb-1.5 text-xs font-medium uppercase tracking-wide text-gray-400">Sector Totals</p>
                    <ul class="space-y-1 text-xs">
                        @foreach ($sectors as $sector)
                            <li class="flex items-center justify-between gap-2">
                                <span class="flex min-w-0 items-center gap-2 text-gray-600 dark:text-gray-300">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $sector['color'] }};"></span>
                                    <span class="truncate">{{ $sector['name'] }}</span>
                                </span>
                                <span class="shrink-0 font-semibold text-gray-900 dark:text-white">{{ $sector['count'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @include('filament.pages.economic-map.partials.summary-card')
            </div>

            {{-- Map panel + sector share strip --}}
            <div class="col-span-12 space-y-4 md:col-span-8 lg:col-span-9">
                <div class="overflow-hidden rounded-lg shadow-sm">
                    <div class="flex h-10 shrink-0 items-center gap-2 bg-[#1e2a78] px-4">
                        <p class="flex-1 truncate text-center text-xs font-bold uppercase tracking-wider text-white">🌐 MSME Distribution Map — Dominant Sector per Barangay</p>
                        <span class="shrink-0 rounded-full bg-white px-3 py-0.5 text-xs font-semibold text-[#1e2a78]">{{ number_format($total) }} Registered MSMEs</span>
                    </div>
                    <div class="relative">
                        <div id="distribution-map" wire:ignore class="h-[60vh] w-full"></div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-900">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Sector Share <span class="font-normal text-gray-400">All Barangays Combined</span></h3>
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-500 dark:bg-white/10">{{ number_format($total) }} MSMEs across {{ collect($sectors)->where('count', '>', 0)->count() }} sectors</span>
                    </div>
                    <div class="mt-3 grid grid-cols-12 items-center gap-4">
                        <div class="col-span-12 sm:col-span-5">
                            <div class="mx-auto max-w-[220px]">
                                <canvas id="distPieChart"></canvas>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-7">
                            <ul class="grid grid-cols-1 gap-x-4 xl:grid-cols-2">
                                @foreach ($sectors as $sector)
                                    <li class="py-1">
                                        <div class="flex items-center justify-between gap-2 text-xs">
                                            <span class="flex min-w-0 items-center gap-2 font-semibold text-gray-800 dark:text-gray-200">
                                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $sector['color'] }};"></span>
                                                <span class="truncate">{{ $sector['name'] }}</span>
                                            </span>
                                            <span class="shrink-0 text-gray-500">{{ number_format($sector['count']) }} ({{ $sector['share'] }}%)</span>
                                        </div>
                                        <div class="ml-4 mt-1 h-1 rounded-full bg-gray-100 dark:bg-white/10">
                                            <div class="h-1 rounded-full" style="width: {{ $sector['share'] }}%; background: {{ $sector['color'] }};"></div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('filament.pages.economic-map.partials.info-bar', [
        'slot' => 'Distribution reflects the dominant industry sector per barangay from the local registry. Select a sector to view its individual registered MSME locations.',
    ])

    <script type="module">
        import * as maplibregl from '{{ asset('vendor/maplibre/maplibre-gl.mjs') }}';

        const barangays = @js($barangays);
        const bounds = @js($bounds);
        const sectorChart = @js($sectors);
        const basemap = @js(config('economic-map.basemap'));
        const el = document.getElementById('distribution-map');

        const norm = (e) => e?.detail?.[0] ?? e?.detail ?? {};
        let map = null;

        const maxTotal = barangays.length > 0 ? Math.max(...barangays.map((b) => b.total)) : 1;
        const radius = (total) => 5 + 9 * Math.sqrt(total / Math.max(maxTotal, 1));

        function boot() {
            if (! el || map) return;

            // The theme <link> lives in the page body, so the container may
            // still be 0px tall when this module runs. Wait for real size —
            // MapLibre initialised at 0x0 stays blank forever.
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

            map = new maplibregl.Map({ container: el, style: { version: 8, sources: {}, layers: [] }, center: [0, 0], zoom: 2 });
            map.addControl(new maplibregl.NavigationControl(), 'top-left');
            map.addControl(new maplibregl.ScaleControl({ position: 'bottom-right' }));

            map.on('load', () => {
                map.resize();
                map.addSource('osm', { type: 'raster', tiles: basemap.tiles, tileSize: basemap.tile_size, attribution: basemap.attribution, maxzoom: basemap.max_zoom });
                map.addLayer({ id: 'osm', type: 'raster', source: 'osm' });

                map.addSource('circles', {
                    type: 'geojson',
                    data: {
                        type: 'FeatureCollection',
                        features: barangays.map((b) => ({
                            type: 'Feature',
                            properties: { barangay: b.barangay, total: b.total, dominant: b.dominant, color: b.dominant_color, radius: radius(b.total) },
                            geometry: { type: 'Point', coordinates: [b.lng, b.lat] },
                        })),
                    },
                });
                map.addLayer({
                    id: 'circles', type: 'circle', source: 'circles',
                    paint: {
                        'circle-radius': ['get', 'radius'],
                        'circle-color': ['get', 'color'],
                        'circle-opacity': 0.65,
                        'circle-stroke-width': 2,
                        'circle-stroke-color': '#ffffff',
                    },
                });

                map.addSource('pins', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
                map.addLayer({ id: 'pins', type: 'circle', source: 'pins', paint: { 'circle-radius': 5, 'circle-color': '#1d4ed8', 'circle-stroke-width': 2, 'circle-stroke-color': '#ffffff' } });

                map.on('click', 'circles', (e) => {
                    const name = e.features?.[0]?.properties?.barangay;
                    if (name && window.Livewire) window.Livewire.dispatch('emap-select-barangay', { barangay: name });
                });
                map.on('mouseenter', 'circles', () => (map.getCanvas().style.cursor = 'pointer'));
                map.on('mouseleave', 'circles', () => (map.getCanvas().style.cursor = ''));

                if (bounds) map.fitBounds([[bounds[1], bounds[0]], [bounds[3], bounds[2]]], { padding: 40 });
            });

            const canvas = document.getElementById('distPieChart');
            if (canvas && window.Chart && ! canvas._emapChart) {
                canvas._emapChart = new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: sectorChart.map((s) => s.name),
                        datasets: [{ data: sectorChart.map((s) => s.count), backgroundColor: sectorChart.map((s) => s.color), borderWidth: 1 }],
                    },
                    options: { plugins: { legend: { display: false } }, maintainAspectRatio: true },
                });
            }
        }

        const renderPins = (pins) => {
            if (! map) return;
            map.getSource('pins')?.setData({
                type: 'FeatureCollection',
                features: (pins ?? []).map((p) => ({
                    type: 'Feature',
                    properties: { name: p.name },
                    geometry: { type: 'Point', coordinates: [p.lng, p.lat] },
                })),
            });
        };

        window.addEventListener('emap-focus', (e) => {
            const d = norm(e);
            const found = barangays.find((b) => b.barangay === d.barangay);
            if (map && found) map.flyTo({ center: [found.lng, found.lat], zoom: 14 });
            renderPins(d.pins);
        });

        window.addEventListener('emap-focus-street', (e) => {
            const d = norm(e);
            if (map && d.lat && d.lng) map.flyTo({ center: [d.lng, d.lat], zoom: 16 });
            renderPins(d.pins);
        });

        window.addEventListener('emap-sector-pins', (e) => {
            const d = norm(e);
            renderPins(d.pins);
            if (! map || (d.pins ?? []).length === 0) return;

            const lngs = d.pins.map((p) => p.lng);
            const lats = d.pins.map((p) => p.lat);
            const spanLng = Math.max(...lngs) - Math.min(...lngs);
            const spanLat = Math.max(...lats) - Math.min(...lats);

            // A single pin (or coincident pins) has no span — fitBounds would
            // max out the zoom, so fly to a sane level instead.
            if (d.pins.length === 1 || (spanLng < 0.005 && spanLat < 0.005)) {
                map.flyTo({ center: [lngs[0], lats[0]], zoom: 14 });
                return;
            }

            map.fitBounds([[Math.min(...lngs), Math.min(...lats)], [Math.max(...lngs), Math.max(...lats)]], { padding: 60 });
        });

        window.addEventListener('emap-clear-pins', () => renderPins([]));
        window.addEventListener('emap-data-refreshed', () => window.location.reload());
        document.addEventListener('livewire:navigated', () => boot());

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => map && map.resize(), 200);
        });

        boot();
    </script>
</x-filament-panels::page>
