<?php

// Economic Map tunables — presentation + policy values only.
// Business + address data comes from the SCIMS juridical API; reference
// places come from AddressApiService. Calamity totals stay in MySQL.
return [
    // Seconds the SCIMS business snapshot is cached. The scheduled
    // economic-map:sync command refreshes it, and a last-good copy is
    // retained for seven days if SCIMS is unavailable.
    'cache_ttl_seconds' => 21600,

    // Cache key prefix for all economic-map entries.
    'cache_prefix' => 'economic_map',

    // Dedicated store for the multi-megabyte SCIMS snapshot. Keep this on a
    // file/Redis store; the database cache can exceed max_allowed_packet.
    'business_cache_store' => env('ECONOMIC_MAP_CACHE_STORE', 'economic-map'),

    // Display-only barangay centres used when SCIMS rows have no lat/lng.
    // SCIMS exposes address text only, so aggregate maps plot each barangay
    // at its approximate centre instead of dropping it. Replace these with
    // surveyed coordinates when they become available. Keys are normalized
    // with EconomicMapService::normalizePlace().
    'barangay_centroids' => [
        'bagonbon' => [10.5830880, 123.3976730],
        'buluangan' => [10.3854749, 123.3380553],
        'codcod' => [10.4580638, 123.2167268],
        'ermita' => [10.4451333, 123.4178966],
        'guadalupe' => [10.4543580, 123.3664640],
        // Some SCIMS records use this neighbouring Vallehermoso barangay.
        'macapso' => [10.3777000, 123.3317000],
        'nataban' => [10.4963672, 123.3051995],
        'palampas' => [10.5231890, 123.3942330],
        'i' => [10.4849928, 123.4198576],
        'ii' => [10.4808069, 123.4146528],
        'iii' => [10.4828613, 123.4244736],
        'iv' => [10.4830000, 123.4210000],
        'v' => [10.4790954, 123.4129110],
        'vi' => [10.4795504, 123.4224985],
        'prosperidad' => [10.5181471, 123.3061980],
        'punao' => [10.5283256, 123.4327758],
        'quezon' => [10.4392722, 123.2497611],
        'rizal' => [10.4880260, 123.3961847],
        'san juan' => [10.4621858, 123.4398743],
    ],

    // Fallback when a SCIMS barangay has no configured centre.
    'city_center' => [10.4849, 123.4199],

    // Generous San Carlos City bounding box. The Business Locations picker
    // uses this to keep geocoding local and to reject pins accidentally
    // dropped in another province.
    'city_bounds' => [
        'min_lat' => 10.25,
        'max_lat' => 10.70,
        'min_lng' => 123.10,
        'max_lng' => 123.55,
    ],

    // Display-only fan-out for pins that share the same saved coordinate.
    // Offsets are in screen pixels and never change the stored lat/lng.
    'overlap_pin_radius_px' => 12,
    'overlap_pin_max_radius_px' => 24,

    // Local place aliases that OSM indexes under a different or simpler name.
    // Values are subdivision centroids with the label shown in the picker;
    // the marker is placed there so the user can drag it to the exact building.
    'local_places' => [
        'Greenville' => [
            'label' => 'Greenville, Barangay Rizal',
            'lat' => 10.4881589,
            'lng' => 123.3986898,
        ],
        'St. Vincent' => [
            'label' => 'St. Vincent, Barangay I',
            'lat' => 10.4924079,
            'lng' => 123.4248450,
        ],
        'Saint Vincent' => [
            'label' => 'St. Vincent, Barangay I',
            'lat' => 10.4924079,
            'lng' => 123.4248450,
        ],
    ],

    // OSM raster basemap for MapLibre (no key required).
    'basemap' => [
        'tiles' => [
            'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
        ],
        'tile_size' => 256,
        'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        'max_zoom' => 19,
    ],

    // Hotspot density bands, evaluated top-down on count / max_count.
    'hotspot_bands' => [
        ['label' => 'Very High', 'color' => '#7b1fa2', 'min' => 0.60],
        ['label' => 'High', 'color' => '#dc3545', 'min' => 0.30],
        ['label' => 'Moderate', 'color' => '#fd7e14', 'min' => 0.15],
        ['label' => 'Low', 'color' => '#ffc107', 'min' => 0.00],
    ],

    // Opportunity bands, evaluated top-down on composite score 0..1.
    // Score blends commercial density, growth momentum, and livelihood gap.
    'opportunity_bands' => [
        ['label' => 'Very High', 'color' => '#16a34a', 'min' => 0.60],
        ['label' => 'High',      'color' => '#3b82f6', 'min' => 0.30],
        ['label' => 'Moderate',  'color' => '#d97706', 'min' => 0.15],
        ['label' => 'Low',       'color' => '#6b7280', 'min' => 0.00],
    ],

    // Risk bands, evaluated top-down on normalized score 0..1.
    // Score = exposure × damage factor (no hazard input — see service).
    'risk_bands' => [
        ['label' => 'Critical', 'color' => '#dc3545', 'min' => 0.66],
        ['label' => 'High', 'color' => '#fd7e14', 'min' => 0.40],
        ['label' => 'Moderate', 'color' => '#ffc107', 'min' => 0.20],
        ['label' => 'Low', 'color' => '#28a745', 'min' => 0.00],
    ],

    // Risk-intensity ramp for the risk map's heat layer: MapLibre
    // 'heatmap-density' over barangay points weighted by their risk score ->
    // colour. Green = least risk-prone, yellow = moderate, red = most
    // risk-prone. Single source for the heatmap-color expression and the
    // legend gradient, so they cannot drift. Stops must stay ascending on
    // [0..1].
    'heat_ramp' => [
        ['stop' => 0.00, 'color' => 'rgba(0, 200, 83, 0)'],
        ['stop' => 0.10, 'color' => 'rgba(0, 200, 83, 0.65)'],
        ['stop' => 0.26, 'color' => '#00c853'],
        ['stop' => 0.44, 'color' => '#ffeb3b'],
        ['stop' => 0.60, 'color' => '#ffb300'],
        ['stop' => 0.76, 'color' => '#ff6d00'],
        ['stop' => 0.88, 'color' => '#e53935'],
        ['stop' => 1.00, 'color' => '#b71c1c'],
    ],

    // Defaults for the risk map's live heat controls. Radius is in screen
    // pixels at mid zoom and scales with zoom; opacity and intensity are
    // MapLibre multipliers. Keep both in sync with the slider bounds in the
    // risk-map blade view.
    'heat_defaults' => [
        'radius' => 55,
        'opacity' => 0.95,
        'intensity' => 1.5,
    ],

    // Canonical 17-sector palette (PSIC-aligned, matches Industries::All()).
    'sector_colors' => [
        'AGRICULTURE' => '#28a745',
        'FISHING' => '#17a2b8',
        'MINING AND QUARRYING' => '#795548',
        'MANUFACTURING' => '#6f42c1',
        'ELECTRICITY, GAS, AND WATER SUPPLY' => '#fd7e14',
        'CONSTRUCTION' => '#e65100',
        'WHOLESALE AND RETAIL TRADE' => '#007bff',
        'HOTELS AND RESTAURANTS' => '#e83e8c',
        'TRANSPORT, STORAGE, AND COMMUNICATION' => '#20c997',
        'FINANCIAL INTERMEDIATION' => '#ffc107',
        'REAL ESTATE, RENTING, AND BUSINESS ACTIVITIES' => '#6610f2',
        'PUBLIC ADMINISTRATION AND DEFENSE' => '#343a40',
        'EDUCATION' => '#0dcaf0',
        'HEALTH AND SOCIAL WORKER' => '#dc3545',
        'OTHER COMMUNITY, SOCIAL AND PERSONAL SERVICE ACTIVITIES' => '#6c757d',
        'ACTIVITIES OF PRIVATE HOUSEHOLDS AS EMPLOYERS...' => '#adb5bd',
        'EXTRA-TERRITORIAL ORGANIZATIONS AND BODIES' => '#495057',
    ],

    // Fallback color for sectors outside the canonical list.
    'sector_fallback_color' => '#6c757d',
];
