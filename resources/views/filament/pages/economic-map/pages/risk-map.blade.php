<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('vendor/maplibre/maplibre-gl.css') }}">
    @include('filament.pages.economic-map.partials.controls')

    @php
        $heatRamp = collect(config('economic-map.heat_ramp', []))->sortBy('stop');
        $heatGradient = 'linear-gradient(90deg, '.$heatRamp
            ->map(fn (array $stop): string => sprintf('%s %s%%', $stop['color'], round($stop['stop'] * 100, 2)))
            ->implode(', ').')';
        $heatDefaults = array_merge(
            ['radius' => 55, 'opacity' => 0.95, 'intensity' => 1.5],
            config('economic-map.heat_defaults', []),
        );
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="w-full max-w-xl">
            @include('filament.pages.economic-map.partials.search-bar')
        </div>
        @include('filament.pages.economic-map.partials.unset-pill')
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
                        <option value="" @selected(blank($selectedCalamity))>All events</option>
                        @foreach ($calamities as $calamity)
                            <option
                                value="{{ $calamity['id'] }}"
                                @selected((string) $selectedCalamity === (string) $calamity['id'])
                            >{{ $calamity['name'] }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Risk is recomputed using the historical damage of the selected event (from Calamity Monitoring).</p>
                    <p wire:loading wire:target="selectedCalamity" class="mt-1 text-xs font-medium text-blue-600 dark:text-blue-400">Refreshing risk data…</p>
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
                        <li><span class="font-medium text-gray-700 dark:text-gray-300">Heat blur</span> — risk intensity: green is least risk-prone, yellow is moderate, red is most risk-prone</li>
                        <li><span class="font-medium text-gray-700 dark:text-gray-300">Heat only</span> — no business pins or barangay dots are drawn, so the colour is the whole story</li>
                        <li><span class="font-medium text-gray-700 dark:text-gray-300">Heat panel</span> — adjust radius, opacity, and intensity, or reset to the tuned defaults</li>
                    </ul>
                    <p class="mt-2 border-t border-gray-100 pt-2 text-xs text-gray-400 dark:border-white/10">Search for a barangay to fly there, or pick a Calamity Event to recompute the heat.</p>
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

                        {{-- Maptive-style heat controls. wire:ignore keeps the
                             slider values while Livewire refreshes the heat. --}}
                        <div
                            id="risk-heat-panel"
                            data-heat-panel
                            wire:ignore
                            class="absolute right-3 top-3 w-56 overflow-hidden rounded-lg bg-white/95 text-xs shadow-lg ring-1 ring-black/5 backdrop-blur sm:w-64 dark:bg-gray-900/95 dark:ring-white/10"
                        >
                            <div class="flex items-center justify-between border-b border-gray-100 px-3 py-2 dark:border-white/10">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200">Heat Map</p>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        data-heat-reset
                                        class="text-[10px] font-semibold uppercase tracking-wide text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                    >Reset</button>
                                    <button
                                        type="button"
                                        data-heat-toggle
                                        aria-expanded="true"
                                        aria-controls="risk-heat-controls"
                                        aria-label="Collapse heat controls"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                    >
                                        <span data-heat-toggle-icon>−</span>
                                    </button>
                                </div>
                            </div>

                            <div id="risk-heat-controls" data-heat-body class="space-y-3 p-3">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <label for="heat-radius" class="font-medium text-gray-600 dark:text-gray-300">Radius</label>
                                        <output for="heat-radius" data-heat-output="radius" class="font-semibold text-gray-500 dark:text-gray-400">{{ (int) $heatDefaults['radius'] }} px</output>
                                    </div>
                                    <input
                                        id="heat-radius"
                                        data-heat="radius"
                                        type="range"
                                        min="24"
                                        max="140"
                                        step="1"
                                        value="{{ (int) $heatDefaults['radius'] }}"
                                        class="mt-1 w-full cursor-pointer accent-red-600"
                                    >
                                </div>

                                <div>
                                    <div class="flex items-center justify-between">
                                        <label for="heat-opacity" class="font-medium text-gray-600 dark:text-gray-300">Opacity</label>
                                        <output for="heat-opacity" data-heat-output="opacity" class="font-semibold text-gray-500 dark:text-gray-400">{{ (int) round($heatDefaults['opacity'] * 100) }}%</output>
                                    </div>
                                    <input
                                        id="heat-opacity"
                                        data-heat="opacity"
                                        type="range"
                                        min="0.15"
                                        max="1"
                                        step="0.05"
                                        value="{{ $heatDefaults['opacity'] }}"
                                        class="mt-1 w-full cursor-pointer accent-red-600"
                                    >
                                </div>

                                <div>
                                    <div class="flex items-center justify-between">
                                        <label for="heat-intensity" class="font-medium text-gray-600 dark:text-gray-300">Intensity</label>
                                        <output for="heat-intensity" data-heat-output="intensity" class="font-semibold text-gray-500 dark:text-gray-400">{{ (int) round($heatDefaults['intensity'] * 100) }}%</output>
                                    </div>
                                    <input
                                        id="heat-intensity"
                                        data-heat="intensity"
                                        type="range"
                                        min="0.2"
                                        max="2"
                                        step="0.05"
                                        value="{{ $heatDefaults['intensity'] }}"
                                        class="mt-1 w-full cursor-pointer accent-red-600"
                                    >
                                </div>

                                <div class="border-t border-gray-100 pt-2 dark:border-white/10">
                                    <p class="mb-1 font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200">Risk Intensity</p>
                                    <div class="h-2 w-full rounded-full" style="background: {{ $heatGradient }};"></div>
                                    <div class="mt-0.5 flex justify-between text-[10px] text-gray-400">
                                        <span>Low</span>
                                        <span>Medium</span>
                                        <span>High</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('filament.pages.economic-map.partials.info-bar', [
        'slot' => 'The risk map is heat-only: no business pins or barangay dots are drawn. Green areas are least risk-prone, yellow moderate, and red most risk-prone. Use the Heat Map panel to adjust radius, opacity, and intensity, or reset to the tuned defaults. Search and the Calamity Event filter still drive the heat.',
    ])

    <script type="module">
        import * as maplibregl from '{{ asset('vendor/maplibre/maplibre-gl.mjs') }}';

        let barangays = @js($barangays);
        let bounds = @js($bounds);
        const basemap = @js(config('economic-map.basemap'));
        const heatRamp = @js(config('economic-map.heat_ramp'));
        const heatDefaults = @js($heatDefaults);
        const heatSettings = { ...heatDefaults };
        const el = document.getElementById('risk-map');

        const norm = (e) => e?.detail?.[0] ?? e?.detail ?? {};
        const asList = (value) => {
            if (Array.isArray(value)) return value;
            if (value && typeof value === 'object') return Object.values(value);

            return null;
        };

        let map = null;

        // One point per barangay centroid, weighted by its normalized risk
        // score, so the heat blur reads as risk intensity instead of raw
        // MSME density. The 0.25 floor keeps least-risk barangays visibly
        // green rather than fading to nothing.
        const heatFeatures = () => ({
            type: 'FeatureCollection',
            features: barangays.map((b) => ({
                type: 'Feature',
                properties: { weight: 0.25 + 0.75 * (b.score ?? 0) },
                geometry: { type: 'Point', coordinates: [b.lng, b.lat] },
            })),
        });

        const heatColor = ['interpolate', ['linear'], ['heatmap-density']];
        for (const stop of [...heatRamp].sort((a, b) => a.stop - b.stop)) heatColor.push(stop.stop, stop.color);

        // The sliders drive a base value that still scales with zoom, so the
        // blobs stay soft on the city view and do not swallow the streets
        // when the user zooms into a barangay.
        const heatRadiusExpression = (base) => ['interpolate', ['linear'], ['zoom'], 9, base * 0.65, 12, base * 0.9, 14, base * 1.1, 17, base * 1.4];
        const heatOpacityExpression = (base) => ['interpolate', ['linear'], ['zoom'], 10, base, 13, base * 0.85, 15, base * 0.65, 17, base * 0.4];
        const heatIntensityExpression = (base) => ['interpolate', ['linear'], ['zoom'], 9, base * 0.75, 13, base, 17, base * 1.15];

        const heatPanel = document.querySelector('[data-heat-panel]');
        const heatInputs = heatPanel ? [...heatPanel.querySelectorAll('[data-heat]')] : [];

        const formatHeatValue = (key, value) => key === 'radius'
            ? `${Math.round(value)} px`
            : `${Math.round(value * 100)}%`;

        const syncHeatControls = () => {
            for (const input of heatInputs) {
                const key = input.dataset.heat;
                const value = heatSettings[key];
                input.value = value;

                const output = heatPanel?.querySelector(`[data-heat-output="${key}"]`);
                if (output) output.textContent = formatHeatValue(key, value);
            }
        };

        function applyHeatPaint() {
            if (! map || ! map.getLayer('heat')) return;

            map.setPaintProperty('heat', 'heatmap-radius', heatRadiusExpression(heatSettings.radius));
            map.setPaintProperty('heat', 'heatmap-opacity', heatOpacityExpression(heatSettings.opacity));
            map.setPaintProperty('heat', 'heatmap-intensity', heatIntensityExpression(heatSettings.intensity));
        }

        for (const input of heatInputs) {
            input.addEventListener('input', () => {
                const key = input.dataset.heat;
                heatSettings[key] = Number(input.value);
                syncHeatControls();
                applyHeatPaint();
            });
        }

        syncHeatControls();

        heatPanel?.querySelector('[data-heat-reset]')?.addEventListener('click', () => {
            Object.assign(heatSettings, heatDefaults);
            syncHeatControls();
            applyHeatPaint();
        });

        const heatBody = heatPanel?.querySelector('[data-heat-body]');
        const heatToggle = heatPanel?.querySelector('[data-heat-toggle]');

        heatToggle?.addEventListener('click', () => {
            if (! heatBody) return;

            const collapsed = heatBody.classList.toggle('hidden');
            heatToggle.setAttribute('aria-expanded', String(! collapsed));

            const icon = heatToggle.querySelector('[data-heat-toggle-icon]');
            if (icon) icon.textContent = collapsed ? '+' : '−';
        });

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
                        // blob colour reads as risk intensity: green = least
                        // risk-prone, red = most.
                        'heatmap-weight': ['get', 'weight'],
                        'heatmap-radius': heatRadiusExpression(heatSettings.radius),
                        'heatmap-opacity': heatOpacityExpression(heatSettings.opacity),
                        'heatmap-intensity': heatIntensityExpression(heatSettings.intensity),
                        'heatmap-color': heatColor,
                    },
                });

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

        window.addEventListener('emap-risk-updated', (e) => {
            const d = norm(e);
            const nextBarangays = asList(d.barangays);

            if (nextBarangays) barangays = nextBarangays;
            if (! map) return;

            // Refresh the heat layer in place. Reloading the page would reset
            // the Calamity Event select and the heat panel sliders.
            map.getSource('heat')?.setData(heatFeatures());
        });

        document.addEventListener('livewire:navigated', () => boot());

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => map && map.resize(), 200);
        });

        boot();
    </script>
</x-filament-panels::page>
