<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('vendor/maplibre/maplibre-gl.css') }}">

    @include('filament.pages.economic-map.partials.map-tabs', ['active' => 'locations'])

    @include('filament.pages.economic-map.partials.info-bar', [
        'slot' => 'Directory sourced live from SCIMS. A business is Set when its entity number exists in the locations table and Unset when it does not. Use “Set location” to link exact coordinates (stored to 8 decimal places), then the business appears as a pin at that spot on every map view.',
    ])

    <div class="mt-4">
        {{ $this->table }}
    </div>

    @if ($pickerEntityNo)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 p-4"
            x-data="locationPicker()"
            data-bounds="{{ json_encode($pickerBounds) }}"
            data-focus="{{ json_encode($pickerFocus) }}"
            data-search-focus="{{ json_encode($pickerSearchFocus) }}"
            data-city-bounds="{{ json_encode(config('economic-map.city_bounds', [])) }}"
            data-local-places="{{ json_encode(config('economic-map.local_places', [])) }}"
            data-barangays="{{ json_encode($this->pickerBarangays()) }}"
            data-streets="{{ json_encode($this->pickerStreets()) }}"
            data-street="{{ json_encode($pickerEntry['street'] ?? null) }}"
            data-barangay="{{ json_encode($pickerEntry['barangay'] ?? null) }}"
            data-city="{{ json_encode($pickerEntry['city'] ?? null) }}"
            data-lat="{{ json_encode($pickerEntry['latitude'] ?? null) }}"
            data-lng="{{ json_encode($pickerEntry['longitude'] ?? null) }}"
        >
            <div class="max-h-full w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-5 shadow-xl dark:bg-gray-900">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-semibold">Set location</h3>
                        <p class="text-sm text-gray-500">
                            {{ $pickerEntry['business_name'] ?? '' }}
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 font-mono text-xs dark:bg-white/10">{{ $pickerEntry['entity_no'] ?? '' }}</span>
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
                        @input.debounce.200ms="suggestPlaces()"
                        @focus="suggestPlaces()"
                        @keydown.enter.prevent="geocode()"
                        placeholder="Search all San Carlos streets and places, e.g. Rizal St, Onyx St, Greenville…"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5"
                    />
                    <button
                        type="button"
                        @click="geocode()"
                        :disabled="searching"
                        class="shrink-0 rounded-lg bg-gray-900 px-3 py-2 text-sm text-white disabled:opacity-50 dark:bg-white dark:text-gray-900"
                    >
                        <span x-show="! searching">Find</span>
                        <span x-show="searching">…</span>
                    </button>
                </div>

                <div x-show="searchResults.length > 0" class="mt-2 max-h-56 overflow-y-auto rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10">
                    <template x-for="result in searchResults" :key="result.key">
                        <button
                            type="button"
                            @click="useResult(result)"
                            class="flex w-full items-start gap-2 border-b border-gray-100 px-3 py-2 text-left last:border-0 hover:bg-gray-50 dark:border-white/5 dark:hover:bg-white/5"
                        >
                            <span class="mt-0.5 text-sm">📍</span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-xs font-medium text-gray-800 dark:text-gray-200" x-text="result.label"></span>
                                <span class="block text-xs text-gray-400" x-text="result.kind"></span>
                            </span>
                        </button>
                    </template>
                </div>
                <p x-show="searchMessage" x-text="searchMessage" class="mt-2 text-xs text-gray-500"></p>

                <div class="mt-3 overflow-hidden rounded-lg ring-1 ring-gray-950/10">
                    <div id="picker-map" wire:ignore class="h-72 w-full"></div>
                    <p x-show="mapError" x-text="mapError" class="bg-red-50 px-3 py-2 text-xs text-red-600"></p>
                </div>

                @if ($pickerExistingOutsideCity)
                    <p class="mt-1 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700">
                        ⚠ The previous pin was outside San Carlos City and was not loaded. Search for the place or click the correct spot.
                    </p>
                @elseif ($pickerEntry['latitude'] !== null && $pickerEntry['longitude'] !== null)
                    <p class="mt-1 text-xs text-gray-500">📍 Existing coordinates loaded — drag the pin or type new values.</p>
                @elseif ($pickerFocusLabel)
                    <p class="mt-1 text-xs text-gray-500">📍 Centered on {{ $pickerFocusLabel }} — click near the business.</p>
                @else
                    <p class="mt-1 text-xs text-gray-400">No plotted data in this barangay yet — click the business on the map.</p>
                @endif

                <div class="mt-3 grid grid-cols-2 gap-3">
                    <label class="text-xs text-gray-500">Latitude
                        <input type="text" x-model="lat" @change="moveMarker()" inputmode="decimal" placeholder="10.48420000" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5" />
                    </label>
                    <label class="text-xs text-gray-500">Longitude
                        <input type="text" x-model="lng" @change="moveMarker()" inputmode="decimal" placeholder="123.41110000" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5" />
                    </label>
                </div>
                <p
                    x-show="lat !== '' && lng !== ''"
                    class="mt-2 font-mono text-xs text-gray-500"
                    x-text="'Exact pin: ' + lat + ', ' + lng"
                ></p>

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
        function locationPicker() {
            return {
                lat: '',
                lng: '',
                placeQuery: '',
                bounds: null,
                focus: null,
                searchFocus: null,
                cityBounds: null,
                localPlaces: {},
                barangays: [],
                streets: [],
                street: '',
                barangay: '',
                city: '',
                searchResults: [],
                searchMessage: '',
                searching: false,
                mapUrl: @js(asset('vendor/maplibre/maplibre-gl.mjs')),
                map: null,
                marker: null,
                booted: false,
                mapError: '',

                init() {
                    this.focus = this.parseJson(this.$el.dataset.focus);
                    this.bounds = this.parseJson(this.$el.dataset.bounds);
                    this.searchFocus = this.parseJson(this.$el.dataset.searchFocus) || this.focus;
                    this.cityBounds = this.parseJson(this.$el.dataset.cityBounds);
                    this.localPlaces = this.parseJson(this.$el.dataset.localPlaces) || {};
                    this.barangays = this.parseJson(this.$el.dataset.barangays) || [];
                    this.streets = (this.parseJson(this.$el.dataset.streets) || []).map((entry) => ({
                        ...entry,
                        key: this.searchKey(entry.key || entry.name),
                        aliases: Array.from(new Set((entry.aliases || [])
                            .map((alias) => this.searchKey(alias))
                            .filter(Boolean))),
                    }));
                    this.street = this.parseJson(this.$el.dataset.street) || '';
                    this.barangay = this.parseJson(this.$el.dataset.barangay) || '';
                    this.city = this.parseJson(this.$el.dataset.city) || '';

                    const lat = this.parseJson(this.$el.dataset.lat);
                    const lng = this.parseJson(this.$el.dataset.lng);

                    if (lat !== null && lng !== null) {
                        // locations stores 8 decimal places; preserve them
                        // instead of rounding the saved pin on reopen.
                        this.lat = Number(lat).toFixed(8);
                        this.lng = Number(lng).toFixed(8);
                    }

                    this.$nextTick(() => this.bootPicker());
                },

                parseJson(value) {
                    try {
                        return JSON.parse(value ?? 'null');
                    } catch (err) {
                        return null;
                    }
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

                    const start = this.focus
                        ? { center: [this.focus[1], this.focus[0]], zoom: 12 }
                        : { center: [121.7740, 12.8797], zoom: 5 };

                    const options = {
                        container: container,
                        style: { version: 8, sources: {}, layers: [] },
                        center: start.center,
                        zoom: start.zoom,
                    };

                    if (this.cityBounds
                        && this.cityBounds.min_lat !== undefined
                        && this.cityBounds.max_lat !== undefined
                        && this.cityBounds.min_lng !== undefined
                        && this.cityBounds.max_lng !== undefined) {
                        options.maxBounds = [
                            [this.cityBounds.min_lng, this.cityBounds.min_lat],
                            [this.cityBounds.max_lng, this.cityBounds.max_lat],
                        ];
                    }

                    this.map = new ml.Map(options);
                    this.map.addControl(new ml.NavigationControl(), 'top-right');

                    // Anchor the pin tip — not the icon centre — on the
                    // exact coordinate, matching the circle pins on the map.
                    this.marker = new ml.Marker({ draggable: true, color: '#dc2626', anchor: 'bottom' });
                    this.marker.on('dragend', () => {
                        const ll = this.marker.getLngLat();

                        if (! this.withinCityBounds(ll.lat, ll.lng)) {
                            this.mapError = 'That pin is outside San Carlos City. Move it inside the city.';

                            if (this.lat !== '' && this.lng !== '') {
                                this.marker.setLngLat([parseFloat(this.lng), parseFloat(this.lat)]);
                            }

                            return;
                        }

                        this.lat = ll.lat.toFixed(8);
                        this.lng = ll.lng.toFixed(8);
                        this.clearSearchResults();
                        this.mapError = '';
                    });

                    this.map.on('click', (e) => {
                        if (! this.withinCityBounds(e.lngLat.lat, e.lngLat.lng)) {
                            this.mapError = 'That point is outside San Carlos City. Click within the city.';

                            return;
                        }

                        this.lat = e.lngLat.lat.toFixed(8);
                        this.lng = e.lngLat.lng.toFixed(8);
                        this.clearSearchResults();
                        this.mapError = '';
                        this.placeMarker();
                    });

                    this.map.on('load', () => {
                        this.map.resize();
                        this.map.addSource('osm', {
                            type: 'raster',
                            tiles: basemap.tiles,
                            tileSize: basemap.tile_size,
                            attribution: basemap.attribution,
                            maxzoom: basemap.max_zoom,
                        });
                        this.map.addLayer({ id: 'osm', type: 'raster', source: 'osm' });

                        if (this.lat !== '' && this.lng !== '') {
                            this.map.jumpTo({ center: [parseFloat(this.lng), parseFloat(this.lat)], zoom: 16 });
                            this.placeMarker();
                        } else if (this.focus) {
                            this.map.flyTo({ center: [this.focus[1], this.focus[0]], zoom: 14 });
                        } else if (this.bounds) {
                            this.map.fitBounds([[this.bounds[1], this.bounds[0]], [this.bounds[3], this.bounds[2]]], { padding: 40 });
                        }
                    });
                },

                placeMarker() {
                    if (! this.map || this.lat === '' || this.lng === '') return;

                    const lat = parseFloat(this.lat);
                    const lng = parseFloat(this.lng);

                    if (Number.isNaN(lat) || Number.isNaN(lng)) return;

                    this.marker.setLngLat([lng, lat]).addTo(this.map);
                },

                moveMarker() {
                    if (this.lat === '' || this.lng === '') return;

                    const lat = parseFloat(this.lat);
                    const lng = parseFloat(this.lng);

                    if (Number.isNaN(lat) || Number.isNaN(lng)) return;

                    if (! this.withinCityBounds(lat, lng)) {
                        this.mapError = 'These coordinates are outside San Carlos City.';

                        return;
                    }

                    this.mapError = '';
                    this.placeMarker();
                    this.map?.flyTo({ center: [lng, lat], zoom: 16 });
                },

                normalize(value) {
                    return String(value ?? '')
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();
                },

                searchKey(value) {
                    return this.normalize(value)
                        .replace(/^(barangay|brgy|sitio|so|purok|prk|po|bo|barrio)\s+/, '')
                        .replace(/\b(saint)\b/g, 'st')
                        .replace(/\b(santa)\b/g, 'sta')
                        .replace(/\b(santo)\b/g, 'sto')
                        .replace(/\s+(street|st|avenue|ave|road|rd|highway|hwy|drive|dr|lane|blvd|boulevard|extension|ext|subdivision|subd|village|vill)$/, '')
                        .replace(/\s+/g, ' ')
                        .trim();
                },

                barangayKey(value) {
                    return this.normalize(value)
                        .replace(/^(barangay|brgy)\s+/, '')
                        .replace(/\b([1-6])\b/g, (number) => ['i', 'ii', 'iii', 'iv', 'v', 'vi'][Number(number) - 1])
                        .trim();
                },

                // Match an already-normalized directory candidate against the
                // user's search key. Lower scores are better.
                matchScore(needle, candidate) {
                    if (! candidate) return null;

                    if (candidate === needle) return 0;
                    if (candidate.startsWith(needle + ' ')) return 1;

                    if (candidate.includes(' ' + needle + ' ') || candidate.endsWith(' ' + needle)) {
                        return 2;
                    }

                    const tokens = needle.split(' ').filter(Boolean);

                    if (tokens.length > 1) {
                        const candidateTokens = candidate.split(' ');

                        if (tokens.every((token) => candidateTokens.includes(token))) {
                            return 3;
                        }
                    }

                    return null;
                },

                distanceTo(result) {
                    const focus = this.searchFocus || this.focus;

                    if (! Array.isArray(focus) || focus.length < 2) return 0;

                    return Math.pow(Number(result.lat) - Number(focus[0]), 2)
                        + Math.pow(Number(result.lng) - Number(focus[1]), 2);
                },

                directoryKind(entry) {
                    const labels = {
                        street: 'Street',
                        subdivision: 'Subdivision',
                        place: 'Place',
                        locality: 'Sitio',
                        purok: 'Purok',
                    };

                    let label = labels[entry.kind] || 'Place';

                    if (entry.precision === 'barangay') {
                        label += ' · approximate';
                    } else if (entry.kind === 'street' && entry.precision === 'subdivision') {
                        label += ' · approximate subdivision';
                    } else if (entry.source === 'osm') {
                        label += ' · OpenStreetMap';
                    } else if (entry.source === 'scims') {
                        label += ' · SCIMS address';
                    } else {
                        label += ' · local directory';
                    }

                    return label;
                },

                directoryMatches(query, limit = 6) {
                    const needle = this.searchKey(query);
                    const prefix = String(query).trim().match(/^(purok|prk|po|bo|barrio|sitio|so)\b/i)?.[1]?.toLowerCase() || null;
                    const allowedKinds = prefix === null
                        ? null
                        : (['purok', 'prk', 'po', 'bo', 'barrio'].includes(prefix)
                            ? ['purok']
                            : ['locality', 'place', 'subdivision']);
                    const strictShort = prefix !== null && needle.length < 2;

                    if (needle.length < (prefix !== null ? 1 : 2)) return [];

                    const matches = [];

                    for (const entry of this.streets) {
                        if (allowedKinds !== null && ! allowedKinds.includes(entry.kind)) continue;

                        let score = null;

                        for (const candidate of [entry.key, ...(entry.aliases || [])]) {
                            if (strictShort && candidate !== needle && ! String(candidate).startsWith(`${needle} `)) {
                                continue;
                            }

                            const candidateScore = this.matchScore(needle, candidate);

                            if (candidateScore !== null) {
                                score = score === null ? candidateScore : Math.min(score, candidateScore);
                            }
                        }

                        if (score === null) continue;

                        const lat = Number(entry.lat);
                        const lng = Number(entry.lng);

                        if (! Number.isFinite(lat) || ! Number.isFinite(lng)) continue;
                        if (! this.withinCityBounds(lat, lng)) continue;

                        const result = {
                            key: `directory-${entry.key}-${entry.barangay || ''}-${lat}-${lng}`,
                            label: entry.label || entry.name,
                            kind: this.directoryKind(entry),
                            barangay: entry.barangay || '',
                            lat,
                            lng,
                            zoom: entry.precision === 'street' ? 17 : (entry.precision === 'subdivision' ? 16 : 15),
                            precision: entry.precision,
                            source: entry.source || 'osm',
                            _kind: entry.kind,
                            _score: score,
                        };

                        result._distance = this.distanceTo(result);
                        matches.push(result);
                    }

                    matches.sort((a, b) => (a._score - b._score)
                        || (a._distance - b._distance)
                        || a.label.localeCompare(b.label));

                    return matches.slice(0, limit);
                },

                localPlaceMatches(query, limit = 3) {
                    const needle = this.searchKey(query);

                    if (needle.length < 2) return [];

                    const matches = [];

                    for (const [name, place] of Object.entries(this.localPlaces)) {
                        const label = Array.isArray(place) ? name : (place?.label || name);
                        const scores = [
                            this.matchScore(needle, this.searchKey(name)),
                            this.matchScore(needle, this.searchKey(label)),
                        ].filter((score) => score !== null);

                        if (scores.length === 0) continue;

                        const lat = Number(Array.isArray(place) ? place[0] : place?.lat);
                        const lng = Number(Array.isArray(place) ? place[1] : place?.lng);

                        if (! Number.isFinite(lat) || ! Number.isFinite(lng)) continue;
                        if (! this.withinCityBounds(lat, lng)) continue;

                        const result = {
                            key: `local-${this.searchKey(label)}-${lat}-${lng}`,
                            label,
                            kind: 'Subdivision · local directory',
                            barangay: '',
                            lat,
                            lng,
                            zoom: 16,
                            precision: 'subdivision',
                            source: 'config',
                            _kind: 'subdivision',
                            _score: Math.min(...scores),
                        };

                        result._distance = this.distanceTo(result);
                        matches.push(result);
                    }

                    matches.sort((a, b) => (a._score - b._score)
                        || (a._distance - b._distance)
                        || a.label.localeCompare(b.label));

                    return matches.slice(0, limit);
                },

                barangayMatches(query, limit = 3) {
                    const normalized = this.normalize(query);
                    const queryKey = this.barangayKey(normalized);

                    if (queryKey === '') return [];

                    const matches = [];

                    for (const place of this.barangays) {
                        const key = this.barangayKey(place.name);
                        const name = this.normalize(place.name);
                        let score = null;

                        if (key === queryKey) {
                            score = 0;
                        } else if (queryKey.length >= 3 && key.startsWith(queryKey + ' ')) {
                            score = 1;
                        } else if (queryKey.length >= 3
                            && (key.includes(queryKey) || name.includes(normalized))) {
                            score = 2;
                        }

                        if (score === null) continue;

                        const lat = Number(place.lat);
                        const lng = Number(place.lng);

                        if (! Number.isFinite(lat) || ! Number.isFinite(lng)) continue;

                        const result = {
                            key: `barangay-${key}`,
                            label: place.name,
                            kind: 'Barangay · centre',
                            barangay: place.name,
                            lat,
                            lng,
                            zoom: 14,
                            precision: 'barangay',
                            source: 'config',
                            _kind: 'barangay',
                            _score: score,
                        };

                        result._distance = this.distanceTo(result);
                        matches.push(result);
                    }

                    matches.sort((a, b) => (a._score - b._score)
                        || (a._distance - b._distance)
                        || a.label.localeCompare(b.label));

                    return matches.slice(0, limit);
                },

                localSuggestions(query) {
                    const trimmedQuery = String(query).trim();
                    const explicitPlacePrefix = /^(purok|prk|sitio|so|po|bo|barrio)\b/i.test(trimmedQuery);
                    const explicitBarangayPrefix = /^(barangay|brgy)\b/i.test(trimmedQuery);

                    if (this.searchKey(query).length < 2 && ! explicitPlacePrefix && ! explicitBarangayPrefix) return [];

                    // "Barangay Rizal" must mean the barangay, not Rizal
                    // Street. Without the prefix, street matches stay first.
                    if (explicitBarangayPrefix) {
                        return this.barangayMatches(query, 8);
                    }

                    const kindOrder = {
                        street: 0,
                        subdivision: 1,
                        place: 1,
                        locality: 1,
                        purok: 1,
                        barangay: 2,
                    };
                    const seen = new Set();

                    return [
                        ...this.directoryMatches(query, 6),
                        ...this.localPlaceMatches(query, 3),
                        ...this.barangayMatches(query, 3),
                    ]
                        .filter((result) => {
                            const base = this.searchKey(String(result.label).split(',')[0]);
                            const kind = result._kind === 'purok' ? '|purok' : '';
                            const key = `${base}|${this.barangayKey(result.barangay || '')}${kind}`;

                            if (seen.has(key)) return false;

                            seen.add(key);

                            return true;
                        })
                        .sort((a, b) => (a._score - b._score)
                            || ((kindOrder[a._kind] ?? 1) - (kindOrder[b._kind] ?? 1))
                            || (a._distance - b._distance)
                            || a.label.localeCompare(b.label))
                        .slice(0, 8);
                },

                suggestPlaces() {
                    if (this.searching) return;

                    const query = this.placeQuery.trim();

                    if (query.length < 2) {
                        this.searchResults = [];
                        this.searchMessage = '';

                        return;
                    }

                    this.searchResults = this.localSuggestions(query);
                    this.searchMessage = '';
                },

                firstPlaceToken(value) {
                    const stopWords = new Set([
                        'the', 'and', 'of', 'street', 'st', 'barangay', 'brgy',
                        'city', 'san', 'carlos', 'negros', 'occidental',
                        'philippines', 'subdivision', 'subd', 'extension', 'ext',
                        'phase', 'purok', 'sitio',
                    ]);

                    return this.normalize(value)
                        .split(' ')
                        .find((token) => token.length >= 3
                            && ! stopWords.has(token)
                            && ! /^\d+$/.test(token)) ?? '';
                },

                candidateQueries(query) {
                    const queries = [];
                    const add = (value) => {
                        const candidate = String(value ?? '').replace(/\s+/g, ' ').trim();

                        if (candidate !== ''
                            && ! queries.some((existing) => existing.toLowerCase() === candidate.toLowerCase())) {
                            queries.push(candidate);
                        }
                    };

                    const city = (this.city || 'San Carlos City')
                        .replace(/^city of\s+/i, '')
                        .trim();
                    const region = 'Negros Occidental';

                    add(query);

                    const queryToken = this.firstPlaceToken(query);
                    if (queryToken !== '') {
                        add([queryToken, city, region].filter(Boolean).join(', '));
                    }

                    const streetToken = this.firstPlaceToken(this.street);
                    if (streetToken !== '') {
                        add([streetToken, city, region].filter(Boolean).join(', '));
                    }

                    return queries.slice(0, 3);
                },

                searchViewbox() {
                    const bounds = this.cityBounds;

                    if (bounds
                        && bounds.min_lat !== undefined
                        && bounds.max_lat !== undefined
                        && bounds.min_lng !== undefined
                        && bounds.max_lng !== undefined) {
                        return [bounds.min_lng, bounds.min_lat, bounds.max_lng, bounds.max_lat].join(',');
                    }

                    const focus = this.searchFocus || this.focus;

                    if (Array.isArray(focus) && focus.length >= 2) {
                        const lat = Number(focus[0]);
                        const lng = Number(focus[1]);

                        if (! Number.isNaN(lat) && ! Number.isNaN(lng)) {
                            const delta = 0.04;

                            return [lng - delta, lat - delta, lng + delta, lat + delta].join(',');
                        }
                    }

                    return null;
                },

                withinCityBounds(lat, lng) {
                    const bounds = this.cityBounds;

                    if (! bounds
                        || bounds.min_lat === undefined
                        || bounds.max_lat === undefined
                        || bounds.min_lng === undefined
                        || bounds.max_lng === undefined) {
                        return true;
                    }

                    return lat >= Number(bounds.min_lat)
                        && lat <= Number(bounds.max_lat)
                        && lng >= Number(bounds.min_lng)
                        && lng <= Number(bounds.max_lng);
                },

                clearSearchResults() {
                    this.searchResults = [];
                    this.searchMessage = '';
                },

                sleep(ms) {
                    return new Promise((resolve) => setTimeout(resolve, ms));
                },

                async searchNominatim(query, viewbox) {
                    const params = new URLSearchParams({
                        format: 'jsonv2',
                        limit: '5',
                        countrycodes: 'ph',
                        addressdetails: '1',
                        q: query,
                    });

                    if (viewbox) {
                        params.set('viewbox', viewbox);
                        params.set('bounded', '1');
                    }

                    const response = await fetch(
                        'https://nominatim.openstreetmap.org/search?' + params.toString(),
                        { headers: { Accept: 'application/json' } },
                    );

                    if (! response.ok) {
                        throw new Error('Geocoder request failed');
                    }

                    const rows = await response.json();

                    return Array.isArray(rows) ? rows : [];
                },

                rankResults(rows) {
                    const focus = this.searchFocus || this.focus;
                    const seen = new Set();

                    return rows
                        .map((row) => {
                            const lat = Number(row.lat);
                            const lng = Number(row.lon);
                            const label = row.display_name ?? row.name ?? 'Unnamed place';
                            const kind = [row.addresstype ?? row.type, row.name]
                                .filter(Boolean)
                                .join(' · ') || 'OpenStreetMap place';

                            return {
                                key: `${row.osm_type ?? ''}-${row.osm_id ?? row.place_id ?? label}-${lat}-${lng}`,
                                label,
                                kind,
                                lat,
                                lng,
                                zoom: 16,
                                precision: 'place',
                                source: 'geocoder',
                                _kind: 'geocoder',
                                _score: 4,
                            };
                        })
                        .filter((row) => {
                            if (! Number.isFinite(row.lat) || ! Number.isFinite(row.lng)) {
                                return false;
                            }

                            if (! this.withinCityBounds(row.lat, row.lng)) {
                                return false;
                            }

                            const key = `${row.label}|${row.lat.toFixed(6)}|${row.lng.toFixed(6)}`;

                            if (seen.has(key)) return false;

                            seen.add(key);

                            return true;
                        })
                        .sort((a, b) => {
                            if (! Array.isArray(focus)) return 0;

                            const distance = (row) => Math.pow(row.lat - Number(focus[0]), 2)
                                + Math.pow(row.lng - Number(focus[1]), 2);

                            return distance(a) - distance(b);
                        })
                        .slice(0, 5);
                },

                applyResult(result) {
                    this.lat = Number(result.lat).toFixed(8);
                    this.lng = Number(result.lng).toFixed(8);
                    this.searchResults = [];
                    this.mapError = this.withinCityBounds(Number(result.lat), Number(result.lng))
                        ? ''
                        : 'That result is outside San Carlos City.';
                    this.placeMarker();
                    this.map?.flyTo({
                        center: [Number(result.lng), Number(result.lat)],
                        zoom: Number(result.zoom) || 16,
                    });
                },

                searchGuidance(result) {
                    if (result._kind === 'barangay') {
                        return 'Barangay centre loaded. Click the exact building on the map.';
                    }

                    if (result.precision === 'street') {
                        return 'Drag the pin to the exact building on this street.';
                    }

                    if (result.source === 'osm' || result.source === 'geocoder') {
                        return 'Mapped location loaded. Drag the pin to the exact building.';
                    }

                    return 'Approximate centre loaded. Drag the pin to the exact building.';
                },

                useResult(result) {
                    this.applyResult(result);
                    this.searchMessage = `Selected ${result.label}. ${this.searchGuidance(result)}`;
                },

                focusOnSearchArea() {
                    const focus = this.searchFocus || this.focus;

                    if (! focus || ! this.map) return;

                    this.map.flyTo({ center: [Number(focus[1]), Number(focus[0])], zoom: 15 });
                },

                async geocode() {
                    if (this.searching) return;

                    const query = this.placeQuery.trim();
                    if (! query) return;

                    this.mapError = '';
                    this.searchMessage = '';
                    this.searchResults = [];

                    // Search the complete local directory first. It combines
                    // every mapped San Carlos street with SCIMS street, sitio,
                    // purok and subdivision names, so common searches never
                    // need a network round-trip. Only fall through to
                    // Nominatim when nothing local matches.
                    const localResults = this.localSuggestions(query);

                    if (localResults.length === 1) {
                        this.applyResult(localResults[0]);
                        this.searchMessage = `Matched ${localResults[0].label}. ${this.searchGuidance(localResults[0])}`;

                        return;
                    }

                    if (localResults.length > 1) {
                        this.searchResults = localResults;
                        this.searchMessage = 'Multiple matches found. Select the closest one below.';

                        return;
                    }

                    this.searching = true;

                    try {
                        const viewbox = this.searchViewbox();
                        const candidates = this.candidateQueries(query);
                        let results = [];

                        for (let index = 0; index < candidates.length; index++) {
                            if (index > 0) await this.sleep(1100);

                            const rows = await this.searchNominatim(candidates[index], viewbox);

                            if (rows.length > 0) {
                                results = this.rankResults(rows);
                                break;
                            }
                        }

                        if (results.length === 0) {
                            this.searchMessage = `No exact match. The map is centered on ${this.barangay || 'San Carlos City'} — click the exact spot.`;
                            this.focusOnSearchArea();

                            return;
                        }

                        if (results.length === 1) {
                            this.applyResult(results[0]);
                            this.searchMessage = `Matched: ${results[0].label}`;

                            return;
                        }

                        this.searchResults = results;
                        this.searchMessage = 'Multiple matches found. Select the correct place below.';
                    } catch (err) {
                        this.mapError = 'Search failed. Check your connection and try again.';
                    } finally {
                        this.searching = false;
                    }
                },
            };
        }
    </script>
</x-filament-panels::page>
