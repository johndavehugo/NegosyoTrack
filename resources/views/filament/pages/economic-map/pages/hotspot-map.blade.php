<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('vendor/maplibre/maplibre-gl.css') }}">
    @include('filament.pages.economic-map.partials.controls')

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="w-full max-w-xl">
            @include('filament.pages.economic-map.partials.search-bar')
        </div>
        @include('filament.pages.economic-map.partials.unset-pill')
    </div>

    @include('filament.pages.economic-map.partials.map-tabs', ['active' => 'hotspot'])

    @if (! $hasData)
        <div class="rounded-lg bg-white p-8 text-center shadow-sm dark:bg-gray-900">
            @if ($unsetCount > 0)
                <p class="text-lg font-semibold text-gray-900 dark:text-white">No business locations set yet</p>
                <p class="mx-auto mt-2 max-w-xl text-sm text-gray-500">
                    This map only plots businesses with a location. Set coordinates on the
                    <a href="{{ \App\Filament\Pages\EconomicMap\BusinessLocations::getUrl() }}" class="font-medium text-blue-600 hover:underline">Business Locations</a>
                    page and they will appear here.
                </p>
            @else
                <p class="text-lg font-semibold text-gray-900 dark:text-white">No business data yet</p>
                <p class="mx-auto mt-2 max-w-xl text-sm text-gray-500">
                    The SCIMS registry snapshot is empty. Run
                    <span class="font-mono">php artisan economic-map:sync</span> or wait for the scheduled sync, then refresh this page.
                </p>
            @endif
        </div>
    @else
        <div class="grid grid-cols-12 gap-4">
            {{-- Left sidebar --}}
            <div class="col-span-12 space-y-4 md:col-span-4 lg:col-span-3">
                @include('filament.pages.economic-map.partials.stat-card', [
                    'label' => 'MSMEs with Locations',
                    'value' => number_format($total),
                    'color' => '#2563eb',
                ])

                @include('filament.pages.economic-map.partials.stat-card', [
                    'label' => 'Top Barangay',
                    'value' => $top['barangay'] ?? '—',
                    'title' => $top['barangay'] ?? '',
                    'color' => '#dc2626',
                ])

                @include('filament.pages.economic-map.partials.stat-card', [
                    'label' => 'Barangays with Locations',
                    'value' => number_format($barangayCount),
                    'color' => '#16a34a',
                ])

                <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Barangay Ranking</h3>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500 dark:bg-white/10">{{ $barangayCount }} ranked</span>
                    </div>
                    <p class="mt-3 text-xs font-medium uppercase tracking-wide text-gray-400">Top barangay to lowest</p>
                    <select
                        wire:model.live="selectedBarangay"
                        class="emap-select mt-1 w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-3 text-sm font-medium shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-gray-900 dark:focus:ring-blue-500/20"
                    >
                        <option value="">🏆 Select barangay…</option>
                        @foreach ($ranked as $index => $entry)
                            <option value="{{ $entry['barangay'] }}">{{ $index + 1 }}. {{ $entry['barangay'] }} — {{ $entry['count'] }}</option>
                        @endforeach
                    </select>
                    @if ($summary)
                        @php
                            $rankPos = collect($ranked)->search(fn ($e) => $e['barangay'] === $summary['barangay']);
                            $rankEntry = $rankPos !== false ? $ranked[$rankPos] : null;
                        @endphp
                        <div class="mt-2 rounded-lg bg-blue-50 p-3 ring-1 ring-blue-100 dark:bg-blue-500/10 dark:ring-blue-500/20">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">{{ $rankPos !== false ? $rankPos + 1 : '–' }}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ $summary['barangay'] }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ number_format($summary['total']) }} MSMEs ·
                                        <span class="font-semibold" @if ($rankEntry) style="color: {{ $rankEntry['color'] }};" @endif>{{ $rankEntry['level'] ?? '' }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                    <p class="mt-1 text-xs text-gray-400">Select a Barangay to fly to it on the map.</p>
                </div>

                <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Individual MSME Locations</h3>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500 dark:bg-white/10">{{ count($mapPins) > 0 ? count($mapPins) : 'None' }}</span>
                    </div>
                    @if ($summary && count($pins) > 0)
                        <p class="mt-2 text-xs text-gray-500">{{ $summary['barangay'] }} · {{ number_format(count($pins)) }} located MSMEs in this area · SCIMS registry</p>
                    @else
                        <p class="mt-2 text-xs text-gray-500">All located MSMEs are pinned; exact coordinates appear in each popup.</p>
                    @endif
                    <p class="mt-1 text-xs text-gray-400">Each pin comes from the coordinates set on the Business Locations page. Pins sharing the same coordinate are fanned out slightly; barangay totals include only located businesses.</p>
                    @if ($summary)
                        <button type="button" wire:click="clearSelection" class="mt-2 rounded-lg px-3 py-1.5 text-xs ring-1 ring-gray-300 hover:bg-gray-50 dark:ring-white/10">✕ Clear selection</button>
                    @endif
                </div>

            </div>

            {{-- Map panel --}}
            <div class="col-span-12 md:col-span-8 md:flex md:flex-col lg:col-span-9">
                <div class="overflow-hidden rounded-lg shadow-sm md:flex md:min-h-0 md:flex-1 md:flex-col">
                    <div class="flex h-10 shrink-0 items-center gap-2 bg-[#1e2a78] px-4">
                        <p class="flex-1 truncate text-center text-xs font-bold uppercase tracking-wider text-white">🔥 Economic Hotspot Map — Business Concentration per Barangay</p>
                        <span class="shrink-0 rounded-full bg-white px-3 py-0.5 text-xs font-semibold text-[#1e2a78]">{{ number_format($total) }} Located MSMEs</span>
                    </div>
                    <div class="relative md:min-h-0 md:flex-1">
                        <div id="hotspot-map" wire:ignore class="h-[60vh] w-full md:h-full"></div>
                        <div class="absolute bottom-10 right-3 rounded-lg bg-white/95 p-3 text-xs shadow-lg dark:bg-gray-900/95">
                            <p class="mb-1.5 font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200">Concentration</p>
                            @foreach (config('economic-map.hotspot_bands', []) as $band)
                                <div class="flex items-center gap-2 py-0.5 text-gray-600 dark:text-gray-300">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $band['color'] }};"></span>
                                    {{ $band['label'] }} (≥{{ (int) ($band['min'] * 100) }}%{{ $band['min'] >= 0.6 ? ' of top' : '+' }})
                                </div>
                            @endforeach
                            <div class="flex items-center gap-2 py-0.5 text-gray-600 dark:text-gray-300">
                                <span class="h-2.5 w-2.5 rounded-full bg-[#1d4ed8]"></span>
                                Individual MSME
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('filament.pages.economic-map.partials.info-bar', [
        'slot' => 'Hotspots reflect only businesses with a set location. Every located business is pinned from its exact saved coordinates; pins sharing the same coordinate are fanned out for visibility, and popups show the exact coordinates. The larger barangay circles are concentration summaries plotted at their average centre. Select a hotspot circle or barangay ranking to fly to that area.',
    ])

    <script type="module">
        import * as maplibregl from '{{ asset('vendor/maplibre/maplibre-gl.mjs') }}';

        const circles = @js($ranked);
        const allPins = @js($mapPins);
        const bounds = @js($bounds);
        const maxCount = @js($top['count'] ?? 1);
        const basemap = @js(config('economic-map.basemap'));
        const el = document.getElementById('hotspot-map');

        @include('filament.pages.economic-map.partials.pin-layer')

        const norm = (e) => e?.detail?.[0] ?? e?.detail ?? {};
        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
        }[char]));

        @include('filament.pages.economic-map.partials.pin-popup')

        let map = null;
        let pinPopup = null;
        let hoverPopup = null;
        let hoveredPinKey = null;

        const radius = (count) => 6 + 8 * Math.sqrt(count / Math.max(maxCount, 1));

        function circleFeatures() {
            return circles.map((c) => ({
                type: 'Feature',
                properties: { barangay: c.barangay, count: c.count, color: c.color, radius: radius(c.count) },
                geometry: { type: 'Point', coordinates: [c.lng, c.lat] },
            }));
        }

        function pinFeatures(pins) {
            return (pins ?? []).map((p) => ({
                type: 'Feature',
                properties: {
                    name: p.name, entity_no: p.entity_no, street: p.street,
                    barangay: p.barangay, industry: p.industry, sector: p.sector,
                    color: p.color, lat: p.lat, lng: p.lng,
                    offset: Array.isArray(p.offset) ? p.offset : [0, 0],
                },
                // Exact stored coordinate — never the barangay average.
                // The offset only moves the rendered icon, not this geometry.
                geometry: { type: 'Point', coordinates: [p.lng, p.lat] },
            }));
        }

        const pinPoint = (p) => {
            const point = map.project([Number(p.lng), Number(p.lat)]);
            const offset = Array.isArray(p.offset) ? p.offset : [0, 0];

            return map.unproject([
                point.x + Number(offset[0] ?? 0),
                point.y + Number(offset[1] ?? 0),
            ]);
        };

        const hidePinTooltip = () => {
            hoveredPinKey = null;
            hoverPopup?.remove();
        };

        const showPinTooltip = (p) => {
            if (! map) return;

            if (! hoverPopup) {
                hoverPopup = new maplibregl.Popup({
                    closeButton: false,
                    closeOnClick: false,
                    focusAfterOpen: false,
                    anchor: 'bottom',
                    offset: [0, -18],
                    maxWidth: '320px',
                    className: 'emap-pin-tooltip',
                });
            }

            hoverPopup.setLngLat(pinPoint(p)).setHTML(emapPinPopupHtml(p)).addTo(map);
        };

        function renderPins(pins) {
            if (! map) return;
            hidePinTooltip();
            emapRenderPins(map, pinFeatures(pins));
        }

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

                map.addSource('circles', { type: 'geojson', data: { type: 'FeatureCollection', features: circleFeatures() } });
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

                emapAddPinLayer(map, pinFeatures(allPins));

                map.on('click', 'circles', (e) => {
                    // Pins sit on top of the aggregate circles; prefer the
                    // exact business pin when both are under the cursor.
                    if (map.queryRenderedFeatures(e.point, { layers: ['pins'] }).length > 0) return;

                    const name = e.features?.[0]?.properties?.barangay;
                    if (name && window.Livewire) window.Livewire.dispatch('emap-select-barangay', { barangay: name });
                });
                map.on('mouseenter', 'circles', () => (map.getCanvas().style.cursor = 'pointer'));
                map.on('mouseleave', 'circles', () => (map.getCanvas().style.cursor = ''));

                map.on('click', 'pins', (e) => {
                    const p = e.features?.[0]?.properties;
                    if (! p) return;

                    hidePinTooltip();

                    if (! pinPopup) pinPopup = new maplibregl.Popup({ closeButton: true, maxWidth: '300px' });

                    pinPopup.setLngLat([Number(p.lng), Number(p.lat)]).setHTML(emapPinPopupHtml(p, true)).addTo(map);
                });

                map.on('mouseenter', 'pins', (e) => {
                    map.getCanvas().style.cursor = 'pointer';

                    const p = e.features?.[0]?.properties;
                    if (! p) return;

                    hoveredPinKey = `${p.entity_no || ''}|${p.lat},${p.lng}`;
                    showPinTooltip(p);
                });

                map.on('mousemove', 'pins', (e) => {
                    const p = e.features?.[0]?.properties;
                    if (! p) return;

                    const key = `${p.entity_no || ''}|${p.lat},${p.lng}`;
                    if (hoveredPinKey === key) return;

                    hoveredPinKey = key;
                    showPinTooltip(p);
                });

                map.on('mouseleave', 'pins', () => {
                    map.getCanvas().style.cursor = '';
                    hidePinTooltip();
                });

                map.on('click', hidePinTooltip);
                map.on('movestart', hidePinTooltip);
                map.on('zoomstart', hidePinTooltip);

                if (bounds) map.fitBounds([[bounds[1], bounds[0]], [bounds[3], bounds[2]]], { padding: 40 });
            });
        }

        window.addEventListener('emap-focus', (e) => {
            const d = norm(e);
            const found = circles.find((c) => c.barangay === d.barangay);
            if (map && found) map.flyTo({ center: [found.lng, found.lat], zoom: 14 });
        });

        window.addEventListener('emap-focus-street', (e) => {
            const d = norm(e);
            if (map && d.lat && d.lng) map.flyTo({ center: [d.lng, d.lat], zoom: 16 });
        });

        window.addEventListener('emap-clear-pins', () => renderPins(allPins));
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
