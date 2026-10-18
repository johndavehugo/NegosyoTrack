<?php

// Economic Map tunables — presentation + policy values only.
// No locations, no coordinates, no LGU datasets live here:
// live data comes from GET /api/msme, reference places from AddressApiService.
return [
    // Seconds the /api/msme business list is cached.
    'cache_ttl_seconds' => 21600,

    // Cache key prefix for all economic-map entries.
    'cache_prefix' => 'economic_map',

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

    // Density ramp for the risk map's heat layer: MapLibre 'heatmap-density'
    // (kernel weight, not risk) -> colour. Sequential and deliberately distinct
    // from risk_bands, so blob colour never reads as a risk level — the small
    // marker dots carry risk_bands, the blobs carry this. Single source for the
    // heatmap-color expression and the legend gradient, so they cannot drift.
    // Stops must stay ascending on [0..1].
    'heat_ramp' => [
        ['stop' => 0.00, 'color' => 'rgba(30, 64, 175, 0)'],
        ['stop' => 0.10, 'color' => '#1d4ed8'],
        ['stop' => 0.30, 'color' => '#0891b2'],
        ['stop' => 0.50, 'color' => '#16a34a'],
        ['stop' => 0.70, 'color' => '#ca8a04'],
        ['stop' => 0.85, 'color' => '#ea580c'],
        ['stop' => 1.00, 'color' => '#b91c1c'],
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
