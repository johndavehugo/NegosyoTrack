<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('vendor/maplibre/maplibre-gl.css') }}">

    @include('filament.pages.economic-map.partials.map-tabs', ['active' => 'locations'])

    {{ $this->table }}

    @if ($pickerAddressId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 p-4" x-data="locationPicker()" data-bounds='@js($pickerBounds)' data-focus='@js($pickerFocus)'>
            <script type="application/json" id="emap-picker-focus">@json($pickerFocus)</script>
            <div class="w-full max-w-2xl rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-semibold">Set location</h3>
                        <p class="text-sm text-gray-500">
                            {{ $pickerEntry['business_name'] ?? '' }}
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs dark:bg-white/10">{{ $pickerEntry['side'] ?? '' }}</span>
                        </p>
                        <p class="mt-1 text-xs text-gray-400">
                            {{ collect([$pickerEntry['street'] ?? null, $pickerEntry['barangay'] ?? null, $pickerEntry['city'] ?? null])->filter()->join(', ') }}
                        </p>
                    </div>
                    <button type="button" wire:click="closePicker" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <div class="mt-4 flex gap-2">
                    <input
                        type="text"
                        x-model="placeQuery"
                        @keydown.enter.prevent="geocode()"
                        placeholder="Search a street or place, e.g. Rizal St San Carlos City…"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5"
                    />
                    <button type="button" @click="geocode()" class="shrink-0 rounded-lg bg-gray-900 px-3 py-2 text-sm text-white dark:bg-white dark:text-gray-900">Find</button>
                </div>

                <div class="mt-3 overflow-hidden rounded-lg ring-1 ring-gray-950/10">
                    <div id="picker-map" wire:ignore class="h-72 w-full"></div>
                    <p x-show="mapError" x-text="mapError" class="bg-red-50 px-3 py-2 text-xs text-red-600"></p>
                </div>
                @if ($pickerFocusLabel)
                    <p class="mt-1 text-xs text-gray-500">📍 Centered on {{ $pickerFocusLabel }} — click near the existing pin.</p>
                @else
                    <p class="mt-1 text-xs text-gray-400">Showing all plotted data — nothing located in this barangay yet.</p>
                @endif

                <div class="mt-3 grid grid-cols-2 gap-3">
                    <label class="text-xs text-gray-500">Latitude
                        <input type="text" x-model="lat" @change="moveMarker()" inputmode="decimal" placeholder="10.4842000" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5" />
                    </label>
                    <label class="text-xs text-gray-500">Longitude
                        <input type="text" x-model="lng" @change="moveMarker()" inputmode="decimal" placeholder="123.4111000" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5" />
                    </label>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" wire:click="closePicker" class="rounded-lg px-4 py-2 text-sm ring-1 ring-gray-300 dark:ring-white/10">Cancel</button>
                    <button type="button" @click="$wire.saveLocation(lat, lng)" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">Save location</button>
                </div>
            </div>
        </div>
    @endif

    <script>
        // Defined once per page load (NOT inside the conditional modal):
        // Livewire does not execute scripts inserted by morph updates, so the
        // picker function must already exist globally when the modal appears.
        // Per-open data (bounds) arrives via the modal's data-bounds attribute.
        function locationPicker() {
            return {
                lat: '',
                lng: '',
                    placeQuery: '',
                    bounds: null,
                    focus: null,
                    mapUrl: @js(asset('vendor/maplibre/maplibre-gl.mjs')),
                map: null,
                marker: null,
                booted: false,
                mapError: '',

                init() {
                    this.focus = null;
                    try {
                        const raw = document.getElementById('emap-picker-focus')?.textContent;
                        const parsed = raw ? JSON.parse(raw) : null;
                        if (Array.isArray(parsed) && parsed.length === 2) {
                            this.focus = parsed;
                        }
                    } catch (err) {
                        this.focus = null;
                    }
                    if (! this.focus) {
                        try {
                            this.focus = JSON.parse(this.$el.dataset.focus || 'null');
                        } catch (err) {
                            this.focus = null;
                        }
                    }
                    this.$nextTick(() => this.bootPicker());
                },

                async bootPicker() {
                    if (this.booted) return;
                    this.booted = true;

                    const container = document.getElementById('picker-map');
                    if (! container) return;
                    container.innerHTML = '';

                    let ml = null;
                    try {
                        ml = await import(this.mapUrl);
                    } catch (err) {
                        this.mapError = 'Map library failed to load. Check your connection and reopen.';
                        return;
                    }

                    const basemap = @js(config('economic-map.basemap'));

                    // Focus wins immediately via constructor center/zoom so the
                    // initial view never depends on the style 'load' event.
                    // It starts one level out, then flies in for the animation.
                    const start = this.focus
                        ? { center: [this.focus[1], this.focus[0]], zoom: 11 }
                        : { center: [121.7740, 12.8797], zoom: 5 };

                    this.map = new ml.Map({
                        container: container,
                        style: { version: 8, sources: {}, layers: [] },
                        center: start.center,
                        zoom: start.zoom,
                    });
                    this.map.addControl(new ml.NavigationControl(), 'top-right');

                    this.map.on('load', () => {
                        this.map.resize();
                        this.map.addSource('osm', { type: 'raster', tiles: basemap.tiles, tileSize: basemap.tile_size, attribution: basemap.attribution, maxzoom: basemap.max_zoom });
                        this.map.addLayer({ id: 'osm', type: 'raster', source: 'osm' });

                        if (this.focus) {
                            this.map.flyTo({ center: [this.focus[1], this.focus[0]], zoom: 14 });
                        } else if (this.bounds) {
                            this.map.fitBounds([[this.bounds[1], this.bounds[0]], [this.bounds[3], this.bounds[2]]], { padding: 40 });
                        }
                    });

                    this.marker = new ml.Marker({ draggable: true, color: '#dc2626' });
                    this.marker.on('dragend', () => {
                        const ll = this.marker.getLngLat();
                        this.lat = ll.lat.toFixed(7);
                        this.lng = ll.lng.toFixed(7);
                    });

                    this.map.on('click', (e) => {
                        this.lat = e.lngLat.lat.toFixed(7);
                        this.lng = e.lngLat.lng.toFixed(7);
                        this.placeMarker();
                    });
                },

                placeMarker() {
                    if (! this.map || this.lat === '' || this.lng === '') return;
                    this.marker.setLngLat([parseFloat(this.lng), parseFloat(this.lat)]).addTo(this.map);
                },

                moveMarker() {
                    if (this.lat === '' || this.lng === '') return;
                    this.placeMarker();
                    this.map.flyTo({ center: [parseFloat(this.lng), parseFloat(this.lat)], zoom: 16 });
                },

                async geocode() {
                    if (! this.placeQuery.trim()) return;
                    const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=ph&q=' + encodeURIComponent(this.placeQuery);
                    const res = await fetch(url);
                    const rows = await res.json();
                    if (rows.length > 0) {
                        this.lat = parseFloat(rows[0].lat).toFixed(7);
                        this.lng = parseFloat(rows[0].lon).toFixed(7);
                        this.placeMarker();
                        this.map.flyTo({ center: [parseFloat(this.lng), parseFloat(this.lat)], zoom: 16 });
                    }
                },
            };
        }
    </script>
</x-filament-panels::page>
