<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('vendor/maplibre/maplibre-gl.css') }}">
    @include('filament.pages.economic-map.partials.controls')

    @php
        $heatGradient = 'linear-gradient(90deg, '.collect(config('economic-map.heat_ramp', []))
            ->sortBy('stop')
            ->map(fn (array $stop): string => sprintf('%s %s%%', $stop['color'], round($stop['stop'] * 100, 2)))
            ->implode(', ').')';
    @endphp

    <div class="mb-4 max-w-xl">
        @include('filament.pages.economic-map.partials.search-bar')
    </div>

    @include('filament.pages.economic-map.partials.map-tabs', ['active' => 'risk'])

    @if (! $hasData)
        <div class="rounded-lg bg-white p-8 text-center shadow-sm dark:bg-gray-900">
            <p class="text-lg font-semibold text-gray-900 dark:text-white">No business data yet</p>
            <p class="mx-auto mt-2 max-w-xl text-sm text-gray-500">
                Risk scoring needs SCIMS business records to compute exposure. Run
                <span class="font-mono">php artisan economic-map:sync</span> or wait for the scheduled sync, then refresh this page.
            </p>
        </div>
    @else
        <div class="grid grid-cols-12 gap-4">
            {{-- Left sidebar --}}
            <div class="col-span-12 space-y-4 md:col-span-4 lg:col-span-3">
                @include('filament.pages.economic-map.partials.stat-card', [
                    'label' => 'Critical Risk Areas',
                    'value' => number_format($levels['Critical'] ?? 0),
                    'color' => '#dc3545',
                ])

                @include('filament.pages.economic-map.partials.stat-card', [
                    'label' => 'High Risk Areas',
                    'value' => number_format($levels['High'] ?? 0),
                    'color' => '#fd7e14',
                ])

                @include('filament.pages.economic-map.partials.stat-card', [
                    'label' => 'Barangays Assessed',
                    'value' => number_format($assessed),
                    'color' => '#343a40',
                ])

                <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Calamity Event</p>
                    <select
                        wire:model.live="selectedCalamity"
                        class="emap-select mt-1 w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-3 text-sm font-medium shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-gray-900 dark:focus:ring-blue-500/20"
                    >
                        <option value="">All events</option>
                        @foreach ($calamities as $calamity)
                            <option value="{{ $calamity['id'] }}">{{ $calamity['name'] }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Risk is recomputed using the historical damage of the selected event (from Calamity Monitoring).</p>
                    @if ($this->selectedCalamityName())
                        <p class="mt-1 text-xs font-medium text-gray-600 dark:text-gray-300">Showing: {{ $this->selectedCalamityName() }}</p>
                    @endif
                </div>

                <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">How it's calculated</p>
                    <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white">Economic Risk = Business Exposure × Historical Damage</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs text-gray-500">
                        <li><span class="font-medium text-gray-700 dark:text-gray-300">Exposure</span> — number of MSMEs in the area (SCIMS registry)</li>
                        <li><span class="font-medium text-gray-700 dark:text-gray-300">Damage</span> — prior losses recorded in Calamity Monitoring</li>
                    </ul>
                    <p class="mt-2 border-t border-gray-100 pt-2 text-xs text-gray-400 dark:border-white/10">Areas with high MSME concentration and a history of losses score highest — pinpointing where a disaster would cause the greatest economic disruption.</p>
                </div>

                <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Reading the map</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs text-gray-500">
                        <li><span class="font-medium text-gray-700 dark:text-gray-300">Heat blur</span> — risk intensity: blue is least risk-prone, red is most risk-prone</li>
                        <li><span class="font-medium text-gray-700 dark:text-gray-300">Marker dot</span> — the barangay's exact risk band, from exposure × historical damage</li>
                    </ul>
                    <p class="mt-2 border-t border-gray-100 pt-2 text-xs text-gray-400 dark:border-white/10">Click a dot for the full exposure and damage breakdown behind its score.</p>
                </div>

                @include('filament.pages.economic-map.partials.summary-card')
            </div>

            {{-- Map panel --}}
            <div class="col-span-12 md:col-span-8 md:flex md:flex-col lg:col-span-9">
                <div class="overflow-hidden rounded-lg shadow-sm md:flex md:min-h-0 md:flex-1 md:flex-col">
                    <div class="flex h-10 shrink-0 items-center gap-2 bg-[#1e2a78] px-4">
                        <p class="flex-1 truncate text-center text-xs font-bold uppercase tracking-wider text-white">🛡 Economic Risk Map — Risk Heat Layer</p>
                        <span class="shrink-0 rounded-full bg-white px-3 py-0.5 text-xs font-semibold text-[#1e2a78]">₱{{ number_format($totalDamage, 2) }} recorded</span>
                    </div>
                    <div class="relative md:min-h-0 md:flex-1">
                        <div id="risk-map" wire:ignore class="h-[60vh] w-full md:h-full"></div>
                        <div class="absolute bottom-10 right-3 rounded-lg bg-white/95 p-3 text-xs shadow-lg dark:bg-gray-900/95">
                            <p class="mb-1.5 font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200">Risk Intensity</p>
                            <div class="h-2 w-36 rounded-full" style="background: {{ $heatGradient }};"></div>
                            <div class="mt-0.5 flex justify-between text-[10px] text-gray-400">
                                <span>Least risk-prone</span>
                                <span>Risk-prone</span>
                            </div>
                            <p class="mb-1.5 mt-2.5 font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200">Risk Level</p>
                            @foreach (config('economic-map.risk_bands', []) as $band)
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
        'slot' => 'Heat blur shows risk intensity — red areas are risk-prone, blue areas are least risk-prone. Dot colour is the exact risk band; click a dot for the full breakdown.',
    ])

    <script type="module">
        import * as maplibregl from '{{ asset('vendor/maplibre/maplibre-gl.mjs') }}';

        const barangays = @js($barangays);
        const bounds = @js($bounds);
        const basemap = @js(config('economic-map.basemap'));
        const heatRamp = @js(config('economic-map.heat_ramp'));
        const el = document.getElementById('risk-map');

        const norm = (e) => e?.detail?.[0] ?? e?.detail ?? {};
        let map = null;
        let popup = null;

        const money = (n) => '₱' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // One point per barangay centroid, weighted by its normalized risk
        // score, so the heat blur reads as risk intensity instead of raw
        // MSME density. The 0.2 floor keeps least-risk barangays visibly
        // blue rather than fading to nothing.
        const heatFeatures = () => ({
            type: 'FeatureCollection',
            features: barangays.map((b) => ({
                type: 'Feature',
                properties: { weight: 0.2 + 0.8 * (b.score ?? 0) },
                geometry: { type: 'Point', coordinates: [b.lng, b.lat] },
            })),
        });

        const centerFeatures = () => ({
            type: 'FeatureCollection',
            features: barangays.map((b) => ({
                type: 'Feature',
                properties: {
                    barangay: b.barangay, score: b.score, color: b.color, level: b.level,
                    count: b.business_count, exposure: b.exposure,
                    affected: b.affected_count, damage: b.total_damage,
                },
                geometry: { type: 'Point', coordinates: [b.lng, b.lat] },
            })),
        });

        const heatColor = ['interpolate', ['linear'], ['heatmap-density']];
        for (const stop of [...heatRamp].sort((a, b) => a.stop - b.stop)) heatColor.push(stop.stop, stop.color);

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

                map.addSource('heat', { type: 'geojson', data: heatFeatures() });
                map.addLayer({
                    id: 'heat', type: 'heatmap', source: 'heat', maxzoom: 17,
                    paint: {
                        // Points carry their barangay's risk score as weight, so
                        // blob colour reads as risk intensity: blue = least
                        // risk-prone, red = most. Exact bands stay on the dots.
                        'heatmap-weight': ['get', 'weight'],
                        'heatmap-radius': ['interpolate', ['linear'], ['zoom'], 9, 18, 12, 30, 14, 46, 16, 64],
                        'heatmap-intensity': ['interpolate', ['linear'], ['zoom'], 9, 0.5, 13, 0.9, 16, 1.4],
                        'heatmap-opacity': ['interpolate', ['linear'], ['zoom'], 10, 0.95, 14, 0.55, 17, 0.25],
                        'heatmap-color': heatColor,
                    },
                });

                map.addSource('centers', { type: 'geojson', data: centerFeatures() });
                map.addLayer({
                    id: 'centers', type: 'circle', source: 'centers',
                    paint: {
                        'circle-radius': ['interpolate', ['linear'], ['zoom'], 10, 4, 14, 6.5],
                        'circle-color': ['get', 'color'],
                        'circle-opacity': 0.95,
                        'circle-stroke-width': 1.5,
                        'circle-stroke-color': '#ffffff',
                    },
                });

                map.on('click', 'centers', (e) => {
                    const p = e.features?.[0]?.properties;
                    if (! p) return;

                    if (! popup) popup = new maplibregl.Popup({ closeButton: true, maxWidth: '280px' });

                    popup.setLngLat(e.lngLat).setHTML(
                        `<div style="font-size:12px;line-height:1.5;min-width:180px;">` +
                        `<p style="font-weight:700;font-size:13px;">${p.barangay}</p>` +
                        `<p><span style="display:inline-block;width:8px;height:8px;border-radius:9999px;background:${p.color};margin-right:4px;"></span><strong>${p.level}</strong> · score ${p.score}</p>` +
                        `<p>Businesses: <strong>${p.count}</strong> (${(p.exposure * 100).toFixed(1)}% exposure)</p>` +
                        `<p>Affected before: <strong>${p.affected}</strong></p>` +
                        `<p>Damage recorded: <strong>${money(p.damage)}</strong></p>` +
                        `</div>`
                    ).addTo(map);

                    map.flyTo({ center: e.lngLat, zoom: 14 });

                    if (window.Livewire) window.Livewire.dispatch('emap-select-barangay', { barangay: p.barangay });
                });
                map.on('mouseenter', 'centers', () => (map.getCanvas().style.cursor = 'pointer'));
                map.on('mouseleave', 'centers', () => (map.getCanvas().style.cursor = ''));

                if (bounds) map.fitBounds([[bounds[1], bounds[0]], [bounds[3], bounds[2]]], { padding: 40 });
            });
        }

        window.addEventListener('emap-focus', (e) => {
            const d = norm(e);
            const found = barangays.find((b) => b.barangay === d.barangay);
            if (map && found) map.flyTo({ center: [found.lng, found.lat], zoom: 14 });
        });

        window.addEventListener('emap-focus-street', (e) => {
            const d = norm(e);
            if (map && d.lat && d.lng) map.flyTo({ center: [d.lng, d.lat], zoom: 16 });
        });

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
