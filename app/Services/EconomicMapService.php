<?php

namespace App\Services;

use App\Enums\Industries;
use App\Services\EconomicMap\CalamityFeed;use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
/**
 * Economic Map data layer. No models — everything derives from
 * GET /api/msme (cached), plus reference places via AddressApiService.
 *
 * Only geocoded rows (real, nonzero lat/long) ever reach the maps.
 * Rows without coordinates surface through unsetLocations() instead.
 */
class EconomicMapService
{
    /**
     * Industry keyword rules evaluated in order. First match wins,
     * mirroring the legacy classifier. Keys match Industries::Array().
     */
    protected const SECTOR_RULES = [
        ['sector' => 'AGRICULTURE', 'keywords' => ['AGRICULTUR', 'FARM', 'LIVESTOCK', 'POULTRY', 'CROPS', 'PLANTATION', 'AGRI']],
        ['sector' => 'FISHING', 'keywords' => ['FISHING', 'FISHERY', 'AQUACULTURE', 'FISH POND', 'SEAWEED']],
        ['sector' => 'MINING AND QUARRYING', 'keywords' => ['MINING', 'QUARRY', 'MINERAL', 'SAND AND GRAVEL', 'EXTRACTION']],
        ['sector' => 'MANUFACTURING', 'keywords' => ['MANUFACTUR', 'FACTORY', 'FABRICATION', 'PROCESSING', 'GARMENT', 'PRODUCTION', 'MILL', 'BAKERY', 'BAKING', 'PRINTING']],
        ['sector' => 'ELECTRICITY, GAS, AND WATER SUPPLY', 'keywords' => ['ELECTRIC', 'POWER', 'GAS', 'WATER SUPPLY', 'UTILITIES']],
        ['sector' => 'CONSTRUCTION', 'keywords' => ['CONSTRUCTION', 'BUILDING', 'CONTRACTOR', 'CIVIL WORKS', 'ENGINEERING']],
        ['sector' => 'WHOLESALE AND RETAIL TRADE', 'keywords' => ['WHOLESALE', 'RETAIL', 'SARI-SARI', 'STORE', 'TRADING', 'TRADE', 'DEALER', 'MARKET', 'SUPERMARKET', 'PHARMACY', 'HARDWARE']],
        ['sector' => 'HOTELS AND RESTAURANTS', 'keywords' => ['HOTEL', 'INN', 'LODGING', 'PENSION', 'RESTAURANT', 'EATERY', 'FOOD SERVICE', 'FOODS', 'CAFETERIA', 'CATERING', 'FAST FOOD', 'CANTEEN']],
        ['sector' => 'TRANSPORT, STORAGE, AND COMMUNICATION', 'keywords' => ['TRANSPORT', 'STORAGE', 'COMMUNICATION', 'LOGISTIC', 'COURIER', 'SHIPPING', 'FREIGHT', 'TRUCKING', 'TAXI', 'TRICYCLE']],
        ['sector' => 'FINANCIAL INTERMEDIATION', 'keywords' => ['BANK', 'LENDING', 'FINANCE', 'CREDIT', 'INSURANCE', 'PAWNSHOP', 'REMITTANCE', 'MICROFINANCE']],
        ['sector' => 'REAL ESTATE, RENTING, AND BUSINESS ACTIVITIES', 'keywords' => ['REAL ESTATE', 'RENTING', 'RENTAL', 'LEASING', 'PROPERTY', 'CONSULTANCY', 'CONSULTING', 'ADVERTISING', 'MANPOWER']],
        ['sector' => 'PUBLIC ADMINISTRATION AND DEFENSE', 'keywords' => ['PUBLIC ADMIN', 'GOVERNMENT', 'DEFENSE', 'LGU', 'BARANGAY HALL']],
        ['sector' => 'EDUCATION', 'keywords' => ['EDUCATION', 'SCHOOL', 'TUTORIAL', 'REVIEW', 'TRAINING', 'DAYCARE', 'LEARNING']],
        ['sector' => 'HEALTH AND SOCIAL WORKER', 'keywords' => ['HEALTH', 'CLINIC', 'HOSPITAL', 'DENTAL', 'MEDICAL', 'PHARMACY', 'SOCIAL WORK', 'WELLNESS', 'SPA']],
        ['sector' => 'ACTIVITIES OF PRIVATE HOUSEHOLDS AS EMPLOYERS...', 'keywords' => ['HOUSEHOLD', 'DOMESTIC', 'PRIVATE HOUSEHOLD']],
        ['sector' => 'EXTRA-TERRITORIAL ORGANIZATIONS AND BODIES', 'keywords' => ['EXTRA-TERRITORIAL', 'INTERNATIONAL', 'NGO', 'EMBASSY']],
    ];

    public const DEFAULT_SECTOR = 'OTHER COMMUNITY, SOCIAL AND PERSONAL SERVICE ACTIVITIES';

    public function __construct(protected CalamityFeed $calamities) {}

    // ── Source ────────────────────────────────────────────────────

    protected function ttl(): int
    {
        return (int) config('economic-map.cache_ttl_seconds', 21600);
    }

    protected function listKey(): string
    {
        return config('economic-map.cache_prefix', 'economic_map').'.msme_list';
    }

    /** Raw /api/msme rows. Only this is cached; aggregates compute per request. */
    public function businessList(bool $refresh = false): array
    {
        if ($refresh) {
            Cache::forget($this->listKey());
        }

        return Cache::remember($this->listKey(), $this->ttl(), function (): array {
            try {
                $response = Http::acceptJson()->timeout(60)->get(url('/api/msme'));
            } catch (ConnectionException) {
                return [];
            }

            if ($response->failed()) {
                return [];
            }

            $data = $response->json('data', []);

            return is_array($data) ? $data : [];
        });
    }

    public function refresh(): void
    {
        Cache::forget($this->listKey());
    }

    public function calamityFeed(): CalamityFeed
    {
        return $this->calamities;
    }

    // ── Normalizers ───────────────────────────────────────────────

    /** Canonical key for grouping/matching place strings. No place list needed. */
    public static function normalizePlace(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = (string) preg_replace('/^(brgy\.?|barangay)\s+/i', '', $value);
        $value = (string) preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    public static function hasCoords(mixed $lat, mixed $lng): bool
    {
        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return false;
        }

        return (float) $lat != 0.0 && (float) $lng != 0.0;
    }

    public static function isNew(array $row): bool
    {
        return strtoupper(trim((string) ($row['juridical']['registration_type'] ?? ''))) === 'NEW';
    }

    public function categorizeIndustry(?string $line): string
    {
        $line = strtoupper(trim((string) $line));

        // Values already canonical per Industries::Array() pass straight through.
        if ($line !== '' && in_array($line, Industries::Array(), true)) {
            return $line;
        }

        foreach (static::SECTOR_RULES as $rule) {
            foreach ($rule['keywords'] as $keyword) {
                if (str_contains($line, $keyword)) {
                    return $rule['sector'];
                }
            }
        }

        return static::DEFAULT_SECTOR;
    }

    /** Canonical 17-sector list — the single authority is Industries::Array(). */
    public static function sectors(): array
    {
        return Industries::Array();
    }

    public function sectorColor(string $sector): string
    {
        return config('economic-map.sector_colors', [])[$sector]
            ?? config('economic-map.sector_fallback_color', '#6c757d');
    }

    /** Rows whose business address carries real coordinates. */
    public function geocodedRows(?array $rows = null): array
    {
        $rows ??= $this->businessList();

        return array_values(array_filter(
            $rows,
            fn (mixed $row): bool => is_array($row)
                && static::hasCoords($row['juridical']['latitude'] ?? null, $row['juridical']['longitude'] ?? null),
        ));
    }

    /** Group geocoded rows by normalized barangay. */
    protected function groupByBarangay(?array $rows = null): array
    {
        $groups = [];

        foreach ($this->geocodedRows($rows) as $row) {
            $raw = trim((string) ($row['juridical']['barangay'] ?? ''));
            $key = static::normalizePlace($raw);

            if ($key === '') {
                continue;
            }

            $groups[$key] ??= ['key' => $key, 'names' => [], 'rows' => []];
            $groups[$key]['names'][$raw] = ($groups[$key]['names'][$raw] ?? 0) + 1;
            $groups[$key]['rows'][] = $row;
        }

        foreach ($groups as $key => $group) {
            arsort($group['names']);
            $groups[$key]['barangay'] = (string) array_key_first($group['names']);
            $lats = array_map(fn (array $r): float => (float) $r['juridical']['latitude'], $group['rows']);
            $lngs = array_map(fn (array $r): float => (float) $r['juridical']['longitude'], $group['rows']);
            $groups[$key]['lat'] = array_sum($lats) / count($lats);
            $groups[$key]['lng'] = array_sum($lngs) / count($lngs);
        }

        return $groups;
    }

    protected function hotspotLevel(float $ratio): array
    {
        foreach (config('economic-map.hotspot_bands', []) as $band) {
            if ($ratio >= (float) $band['min']) {
                return ['level' => $band['label'], 'color' => $band['color']];
            }
        }

        return ['level' => 'Low', 'color' => '#ffc107'];
    }

    // ── Hotspot ───────────────────────────────────────────────────

    public function hotspots(): array
    {
        $groups = $this->groupByBarangay();
        $total = 0;
        $max = 1;

        foreach ($groups as $group) {
            $total += count($group['rows']);
            $max = max($max, count($group['rows']));
        }

        $ranked = [];

        foreach ($groups as $group) {
            $count = count($group['rows']);
            $new = count(array_filter($group['rows'], fn (array $r): bool => static::isNew($r)));

            $ranked[] = [
                'barangay' => $group['barangay'],
                'count' => $count,
                'new' => $new,
                'lat' => $group['lat'],
                'lng' => $group['lng'],
                ...$this->hotspotLevel($count / $max),
            ];
        }

        usort($ranked, fn (array $a, array $b): int => $b['count'] <=> $a['count']);

        return [
            'total' => $total,
            'barangay_count' => count($ranked),
            'top' => $ranked[0] ?? null,
            'ranked' => $ranked,
        ];
    }

    // ── Distribution ────────────────────────────────────────────

    public function distribution(): array
    {
        $groups = $this->groupByBarangay();
        $total = 0;
        $sectorTotals = array_fill_keys(static::sectors(), 0);

        $barangays = [];

        foreach ($groups as $group) {
            $categories = [];

            foreach ($group['rows'] as $row) {
                $sector = $this->categorizeIndustry($row['juridical']['line_of_industry'] ?? null);
                $categories[$sector] = ($categories[$sector] ?? 0) + 1;
                $sectorTotals[$sector] = ($sectorTotals[$sector] ?? 0) + 1;
                $total++;
            }

            arsort($categories);
            $dominant = (string) array_key_first($categories);

            $barangays[] = [
                'barangay' => $group['barangay'],
                'total' => count($group['rows']),
                'lat' => $group['lat'],
                'lng' => $group['lng'],
                'dominant' => $dominant,
                'dominant_color' => $this->sectorColor($dominant),
                'categories' => $categories,
            ];
        }

        usort($barangays, fn (array $a, array $b): int => $b['total'] <=> $a['total']);
        arsort($sectorTotals);

        $sectors = [];

        foreach ($sectorTotals as $name => $count) {
            $sectors[] = [
                'name' => $name,
                'count' => $count,
                'share' => $total > 0 ? round($count / $total * 100, 1) : 0.0,
                'color' => $this->sectorColor($name),
            ];
        }

        return [
            'total' => $total,
            'sectors' => $sectors,
            'barangays' => $barangays,
        ];
    }

    // ── Pins ────────────────────────────────────────────────────

    protected function pinRow(array $row): array
    {
        $juridical = $row['juridical'] ?? [];

        return [
            'name' => $juridical['name'] ?? '—',
            'entity_no' => $juridical['entity_no'] ?? null,
            'street' => $juridical['street'] ?? null,
            'barangay' => $juridical['barangay'] ?? null,
            'industry' => $juridical['line_of_industry'] ?? null,
            'sector' => $this->categorizeIndustry($juridical['line_of_industry'] ?? null),
            'msme_category' => $juridical['msme_category'] ?? null,
            'is_new' => static::isNew($row),
            'lat' => (float) ($juridical['latitude'] ?? 0),
            'lng' => (float) ($juridical['longitude'] ?? 0),
        ];
    }

    /** Geocoded businesses in one barangay (normalized match). */
    public function barangayBusinesses(string $barangay): array
    {
        $key = static::normalizePlace($barangay);

        $pins = [];

        foreach ($this->geocodedRows() as $row) {
            if (static::normalizePlace($row['juridical']['barangay'] ?? null) !== $key) {
                continue;
            }

            $pins[] = $this->pinRow($row);
        }

        return $pins;
    }

    /** Geocoded businesses in one canonical sector. */
    public function sectorBusinesses(string $sector): array
    {
        $pins = [];

        foreach ($this->geocodedRows() as $row) {
            if ($this->categorizeIndustry($row['juridical']['line_of_industry'] ?? null) !== $sector) {
                continue;
            }

            $pins[] = $this->pinRow($row);
        }

        return $pins;
    }

    // ── Search + summary ────────────────────────────────────────

    /** Barangay + street matches, max 30. Barangays resolve to averaged coords. */
    public function searchArea(string $query): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        $key = static::normalizePlace($query);
        $matches = [];

        foreach ($this->groupByBarangay() as $group) {
            if ($group['key'] === $key || str_contains($group['key'], $key)) {
                $matches[] = [
                    'type' => 'barangay',
                    'label' => $group['barangay'],
                    'barangay' => $group['barangay'],
                    'street' => null,
                    'lat' => $group['lat'],
                    'lng' => $group['lng'],
                ];
            }
        }

        foreach ($this->geocodedRows() as $row) {
            if (count($matches) >= 30) {
                break;
            }

            $street = trim((string) ($row['juridical']['street'] ?? ''));

            if ($street === '' || stripos($street, $query) === false) {
                continue;
            }

            $matches[] = [
                'type' => 'street',
                'label' => "{$street}, ".($row['juridical']['barangay'] ?? ''),
                'barangay' => $row['juridical']['barangay'] ?? null,
                'street' => $street,
                'lat' => (float) $row['juridical']['latitude'],
                'lng' => (float) $row['juridical']['longitude'],
            ];
        }

        return array_slice($matches, 0, 30);
    }

    public function areaSummary(string $barangay, ?string $street = null): array
    {
        $key = static::normalizePlace($barangay);
        $matched = [];

        foreach ($this->geocodedRows() as $row) {
            if (static::normalizePlace($row['juridical']['barangay'] ?? null) !== $key) {
                continue;
            }

            if (filled($street) && stripos((string) ($row['juridical']['street'] ?? ''), $street) === false) {
                continue;
            }

            $matched[] = $row;
        }

        $sizes = ['MICRO' => 0, 'SMALL' => 0, 'MEDIUM' => 0, 'LARGE' => 0, 'Unspecified' => 0];
        $industries = [];
        $sectors = [];
        $new = 0;

        foreach ($matched as $row) {
            $size = strtoupper(trim((string) ($row['juridical']['msme_category'] ?? '')));
            $bucket = in_array($size, ['MICRO', 'SMALL', 'MEDIUM', 'LARGE'], true) ? $size : 'Unspecified';
            $sizes[$bucket]++;

            if (static::isNew($row)) {
                $new++;
            }

            $industry = trim((string) ($row['juridical']['line_of_industry'] ?? ''));

            if ($industry !== '') {
                $industries[$industry] = ($industries[$industry] ?? 0) + 1;
            }

            $sector = $this->categorizeIndustry($industry ?: null);
            $sectors[$sector] = ($sectors[$sector] ?? 0) + 1;
        }

        arsort($industries);
        arsort($sectors);

        $topIndustries = [];

        foreach (array_slice($industries, 0, 3, true) as $name => $count) {
            $topIndustries[] = ['name' => $name, 'count' => $count];
        }

        $total = count($matched);
        $mix = [];

        foreach ($sectors as $name => $count) {
            $mix[] = [
                'name' => $name,
                'count' => $count,
                'share' => $total > 0 ? round($count / $total * 100, 1) : 0.0,
                'color' => $this->sectorColor($name),
            ];
        }

        return [
            'barangay' => $barangay,
            'street' => $street,
            'total' => $total,
            'new' => $new,
            'sizes' => $sizes,
            'top_industry' => array_key_first($industries),
            'top_industry_count' => $industries[array_key_first($industries)] ?? 0,
            'top_industries' => $topIndustries,
            'sector_mix' => $mix,
        ];
    }

    // ── Unset locations ─────────────────────────────────────────

    protected function unsetEntry(array $row, string $side): ?array
    {
        $address = $side === 'employer'
            ? ($row['employer'] ?? [])
            : ($row['juridical'] ?? []);

        if (static::hasCoords($address['latitude'] ?? null, $address['longitude'] ?? null)) {
            return null;
        }

        return [
            'side' => $side,
            'business_name' => $row['juridical']['name'] ?? '—',
            'address_id' => $address['address_id'] ?? null,
            'region' => $address['region'] ?? null,
            'province' => $address['province'] ?? null,
            'city' => $address['city'] ?? null,
            'barangay' => $address['barangay'] ?? null,
            'street' => $address['street'] ?? null,
            'subdivision' => $address['subdivision'] ?? null,
            'unit_bldg_no' => $address['upblb_num'] ?? null,
            'zip' => $address['zip'] ?? null,
        ];
    }

    /**
     * Addresses missing coordinates (null or zero lat/long),
     * business + employer sides as separate rows.
     */
    public function unsetLocations(?string $search = null): array
    {
        $entries = [];

        foreach ($this->businessList() as $row) {
            if (! is_array($row)) {
                continue;
            }

            foreach (['business', 'employer'] as $side) {
                $entry = $this->unsetEntry($row, $side);

                if ($entry !== null) {
                    $entries[] = $entry;
                }
            }
        }

        $search = trim((string) $search);

        if ($search !== '') {
            $entries = array_values(array_filter(
                $entries,
                fn (array $entry): bool => stripos(
                    implode(' ', [
                        $entry['business_name'],
                        $entry['street'],
                        $entry['barangay'],
                        $entry['city'],
                    ]),
                    $search
                ) !== false,
            ));
        }

        usort($entries, fn (array $a, array $b): int => strcmp(
            (string) $a['business_name'].(string) $a['side'],
            (string) $b['business_name'].(string) $b['side'],
        ));

        return $entries;
    }

    public function unsetCount(): int
    {
        return count($this->unsetLocations());
    }

    // ── Select options + viewport ───────────────────────────────

    /**
     * Canonical barangay options via AddressApiService, anchored off
     * live data (first resolvable row). Falls back to distinct values
     * from the dataset when the reference API is unreachable.
     */
    public function locationOptions(): array
    {
        try {
            $api = app(AddressApiService::class);

            foreach ($this->businessList() as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $juridical = $row['juridical'] ?? [];

                $resolved = $api->resolveAddress(
                    $juridical['region'] ?? null,
                    $juridical['province'] ?? null,
                    $juridical['city'] ?? null,
                    null,
                );

                $city = $resolved['city'] ?? null;
                $cityPsgc = is_array($city) ? ($city['psgc_id'] ?? null) : null;

                if (! filled($cityPsgc)) {
                    continue;
                }

                $names = collect($api->getBarangays($cityPsgc))
                    ->pluck('name')
                    ->filter(fn ($name): bool => filled($name))
                    ->unique()
                    ->sort(SORT_NATURAL | SORT_FLAG_CASE)
                    ->values()
                    ->all();

                if ($names !== []) {
                    return ['barangays' => $names];
                }
            }
        } catch (\Throwable) {
            // Fall through to the dataset-derived list below.
        }

        $names = [];

        foreach ($this->businessList() as $row) {
            $name = trim((string) ($row['juridical']['barangay'] ?? ''));

            if ($name !== '') {
                $names[$name] = true;
            }
        }

        $names = array_keys($names);
        sort($names, SORT_NATURAL | SORT_FLAG_CASE);

        return ['barangays' => $names];
    }

    /**
     * Center point for one barangay from already-geocoded siblings.
     * Null when the barangay is blank or nothing is located there yet.
     *
     * @return array{0: float, 1: float}|null [lat, lng]
     */
    public function barangayFocus(?string $barangay): ?array
    {
        $key = static::normalizePlace($barangay);

        if ($key === '') {
            return null;
        }

        $lats = [];
        $lngs = [];

        foreach ($this->geocodedRows() as $row) {
            if (static::normalizePlace($row['juridical']['barangay'] ?? null) !== $key) {
                continue;
            }

            $lats[] = (float) $row['juridical']['latitude'];
            $lngs[] = (float) $row['juridical']['longitude'];
        }

        if ($lats === []) {
            return null;
        }

        return [array_sum($lats) / count($lats), array_sum($lngs) / count($lngs)];
    }

    /** Data bounds for fitting the map viewport. Null when nothing is plotted. */    public function bounds(): ?array
    {
        $rows = $this->geocodedRows();

        if ($rows === []) {
            return null;
        }

        $lats = array_map(fn (array $r): float => (float) $r['juridical']['latitude'], $rows);
        $lngs = array_map(fn (array $r): float => (float) $r['juridical']['longitude'], $rows);

        [$minLat, $maxLat] = [min($lats), max($lats)];
        [$minLng, $maxLng] = [min($lngs), max($lngs)];

        // A single point (or near-identical points) has zero span, which
        // makes fitBounds zoom erratically — pad to a sane minimum window.
        if ($maxLat - $minLat < 0.05) {
            $mid = ($minLat + $maxLat) / 2;
            $minLat = $mid - 0.025;
            $maxLat = $mid + 0.025;
        }

        if ($maxLng - $minLng < 0.05) {
            $mid = ($minLng + $maxLng) / 2;
            $minLng = $mid - 0.025;
            $maxLng = $mid + 0.025;
        }

        return [$minLat, $minLng, $maxLat, $maxLng];
    }
}
