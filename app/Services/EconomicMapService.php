<?php

namespace App\Services;

use App\Enums\Industries;
use App\Services\EconomicMap\CalamityFeed;
use App\Services\EconomicMap\ScimsBusinessClient;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * Economic Map data layer. No models — business and address data comes
 * from the SCIMS juridical API (cached), plus reference places via
 * AddressApiService.
 *
 * SCIMS exposes address text but no latitude/longitude, so aggregate maps
 * plot each barangay at a display-only centre from config/economic-map.php.
 * Individual pins are only rendered for rows with real coordinates.
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

    public function __construct(
        protected CalamityFeed $calamities,
        protected ScimsBusinessClient $scims,
    ) {}

    // ── Source ────────────────────────────────────────────────────

    protected function ttl(): int
    {
        return (int) config('economic-map.cache_ttl_seconds', 21600);
    }

    protected function listKey(): string
    {
        return config('economic-map.cache_prefix', 'economic_map').'.business_list';
    }

    protected function staleKey(): string
    {
        return $this->listKey().'.stale';
    }

    /**
     * Dedicated store for the large SCIMS snapshot.
     *
     * Defaults to a file-backed store; the database cache rejects the
     * multi-megabyte payload on some MySQL configurations.
     */
    protected function businessCache(): CacheRepository
    {
        return Cache::store(config('economic-map.business_cache_store', 'economic-map'));
    }

    /**
     * Cached, normalized SCIMS business rows.
     *
     * Web requests never page through SCIMS themselves — a full sync takes
     * dozens of API calls. The scheduled command/job owns that work, and this
     * method only reads the fresh snapshot or the last-good copy.
     */
    public function businessList(bool $refresh = false): array
    {
        if ($refresh) {
            $this->refresh();
        }

        $cached = $this->businessCache()->get($this->listKey());

        if (is_array($cached)) {
            return $cached;
        }

        $lastGood = $this->businessCache()->get($this->staleKey());

        return is_array($lastGood) ? $lastGood : [];
    }

    /**
     * Fetch every SCIMS page and replace the cached snapshot.
     *
     * @return int Number of businesses cached.
     */
    public function sync(): int
    {
        $rows = $this->fetchBusinesses();

        if ($rows === []) {
            throw new RuntimeException('SCIMS returned no business records.');
        }

        $this->storeBusinesses($rows);

        return count($rows);
    }

    /**
     * Page through the SCIMS juridical endpoint until the API's last page.
     *
     * The SCIMS endpoint caps each response at 100 rows, regardless of the
     * requested per-page value, so the map walks the paginator directly.
     */
    protected function fetchBusinesses(): array
    {
        $perPage = 100;
        $first = $this->scims->page(1, $perPage);
        $rows = collect($first->items())->values();
        $lastPage = $first->lastPage();

        for ($page = 2; $page <= $lastPage; $page++) {
            $rows = $rows->merge(
                collect($this->scims->page($page, $perPage)->items())->values(),
            );
        }

        return $rows
            ->filter(fn (mixed $row): bool => is_array($row))
            ->map(fn (array $row): array => $this->normalizeScimsRow($row))
            ->values()
            ->all();
    }

    protected function storeBusinesses(array $rows): void
    {
        $cache = $this->businessCache();

        $cache->put($this->listKey(), $rows, now()->addSeconds($this->ttl()));
        $cache->put($this->staleKey(), $rows, now()->addDays(7));
    }

    /**
     * Convert a flat SCIMS juridical row into the shape used by the maps.
     *
     * Only fields the Economic Map reads are retained. SCIMS returns several
     * dozen columns (owner, employer, contact, etc.); keeping them would make
     * the cached snapshot several times larger for no map benefit.
     */
    protected function normalizeScimsRow(array $row): array
    {
        return [
            'juridical' => [
                'entity_no' => $row['entity_no'] ?? null,
                'name' => $row['juri_name'] ?? $row['trade_name'] ?? null,
                'registration_type' => $row['status'] ?? null,
                'msme_category' => $row['juri_category'] ?? null,
                'line_of_industry' => $row['line_of_industry'] ?? null,
                'region' => $row['region'] ?? null,
                'province' => $row['province'] ?? null,
                'city' => $row['city'] ?? null,
                'barangay' => $row['barangay'] ?? null,
                'street' => $row['street'] ?? null,
                'longitude' => $row['longitude'] ?? null,
                'latitude' => $row['latitude'] ?? null,
            ],
        ];
    }

    public function refresh(): void
    {
        $this->businessCache()->forget($this->listKey());
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

    /** Every valid SCIMS business row, geocoded or not. */
    public function businessRows(?array $rows = null): array
    {
        $rows ??= $this->businessList();

        return array_values(array_filter($rows, 'is_array'));
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

    /**
     * Group every business row by normalized barangay.
     *
     * Coordinates are averaged when rows carry real lat/lng; otherwise the
     * display-only barangay centre from config is used so aggregate maps
     * still render the SCIMS counts.
     */
    protected function groupByBarangay(?array $rows = null): array
    {
        $groups = [];

        foreach ($this->businessRows($rows) as $row) {
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
            $barangay = trim((string) array_key_first($group['names']));
            $groups[$key]['barangay'] = $barangay;

            $located = array_values(array_filter(
                $group['rows'],
                fn (array $r): bool => static::hasCoords(
                    $r['juridical']['latitude'] ?? null,
                    $r['juridical']['longitude'] ?? null,
                ),
            ));

            if ($located !== []) {
                $lats = array_map(fn (array $r): float => (float) $r['juridical']['latitude'], $located);
                $lngs = array_map(fn (array $r): float => (float) $r['juridical']['longitude'], $located);
                $groups[$key]['lat'] = array_sum($lats) / count($lats);
                $groups[$key]['lng'] = array_sum($lngs) / count($lngs);
            } else {
                [$lat, $lng] = $this->barangayCentroid($barangay);
                $groups[$key]['lat'] = $lat;
                $groups[$key]['lng'] = $lng;
            }
        }

        return $groups;
    }

    /**
     * Display-only fallback centre for a barangay.
     *
     * @return array{0: float, 1: float}
     */
    protected function barangayCentroid(?string $barangay): array
    {
        $key = static::normalizePlace($barangay);
        $centroids = config('economic-map.barangay_centroids', []);
        $fallback = config('economic-map.city_center', [10.4849, 123.4199]);

        return $centroids[$key] ?? $fallback;
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

    protected function riskLevel(float $ratio): array
    {
        foreach (config('economic-map.risk_bands', []) as $band) {
            if ($ratio >= (float) $band['min']) {
                return ['level' => $band['label'], 'color' => $band['color']];
            }
        }

        return ['level' => 'Low', 'color' => '#28a745'];
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

    // ── Risk ──────────────────────────────────────────────────────

    /**
     * Per-barangay economic risk from LIVE data only: business exposure ×
     * recorded damage. No hazard ratings anywhere — those were static
     * LGU guesses and this module ships none. Damage merges by normalized
     * barangay name so variant spellings accumulate instead of splitting.
     */
    public function risk(?int $calamityId = null): array
    {
        $groups = $this->groupByBarangay();
        $entityBarangays = [];

        foreach ($this->businessRows() as $row) {
            $entityNo = trim((string) ($row['juridical']['entity_no'] ?? ''));
            $barangay = trim((string) ($row['juridical']['barangay'] ?? ''));

            if ($entityNo !== '' && $barangay !== '') {
                $entityBarangays[$entityNo] = $barangay;
            }
        }

        $damage = $this->calamities->damageByBarangay($calamityId, $entityBarangays);

        $mergedDamage = [];

        foreach ($damage['by_barangay'] as $rawName => $entry) {
            $key = static::normalizePlace($rawName);

            if ($key === '') {
                continue;
            }

            $mergedDamage[$key] ??= ['damage' => 0.0, 'affected' => 0, 'incidents' => 0];
            $mergedDamage[$key]['damage'] += (float) $entry['damage'];
            $mergedDamage[$key]['affected'] += (int) $entry['affected'];
            $mergedDamage[$key]['incidents'] += (int) $entry['incidents'];
        }

        $maxCount = 1;
        $maxDamage = 0.0;

        foreach ($groups as $key => $group) {
            $maxCount = max($maxCount, count($group['rows']));
            $maxDamage = max($maxDamage, (float) ($mergedDamage[$key]['damage'] ?? 0.0));
        }

        $barangays = [];
        $levels = ['Critical' => 0, 'High' => 0, 'Moderate' => 0, 'Low' => 0];

        foreach ($groups as $key => $group) {
            $count = count($group['rows']);
            $exposure = $count / $maxCount;
            $entry = $mergedDamage[$key] ?? ['damage' => 0.0, 'affected' => 0, 'incidents' => 0];
            $damageFactor = $maxDamage > 0 ? 1 + ($entry['damage'] / $maxDamage) : 1.0;

            $barangays[$key] = [
                'barangay' => $group['barangay'],
                'lat' => $group['lat'],
                'lng' => $group['lng'],
                'business_count' => $count,
                'exposure' => round($exposure, 4),
                'affected_count' => $entry['affected'],
                'total_damage' => round($entry['damage'], 2),
                'raw' => $exposure * $damageFactor,
            ];
        }

        $maxRaw = 0.0;

        foreach ($barangays as $entry) {
            $maxRaw = max($maxRaw, $entry['raw']);
        }

        foreach ($barangays as $key => $entry) {
            $score = $maxRaw > 0 ? $entry['raw'] / $maxRaw : 0.0;
            $barangays[$key]['score'] = round($score, 4);
            $barangays[$key] += $this->riskLevel($score);
            $levels[$barangays[$key]['level']]++;
        }

        $ranked = array_values($barangays);
        usort($ranked, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return [
            'barangays' => $ranked,
            'levels' => $levels,
            'assessed' => count($ranked),
            'calamity_id' => $calamityId,
            'total_damage' => $damage['total_damage'],
            'total_affected' => $damage['total_affected'],
            'calamities' => $this->calamities->calamities(),
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
        $groups = $this->groupByBarangay();
        $matches = [];

        foreach ($groups as $group) {
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

        $seen = [];

        foreach ($this->businessRows() as $row) {
            if (count($matches) >= 30) {
                break;
            }

            $street = trim((string) ($row['juridical']['street'] ?? ''));

            if ($street === '' || stripos($street, $query) === false) {
                continue;
            }

            $barangay = trim((string) ($row['juridical']['barangay'] ?? ''));
            $group = $groups[static::normalizePlace($barangay)] ?? null;

            if ($group === null) {
                continue;
            }

            $dedupeKey = static::normalizePlace($barangay).'|'.static::normalizePlace($street);

            if (isset($seen[$dedupeKey])) {
                continue;
            }

            $seen[$dedupeKey] = true;

            $matches[] = [
                'type' => 'street',
                'label' => "{$street}, {$barangay}",
                'barangay' => $barangay,
                'street' => $street,
                'lat' => $group['lat'],
                'lng' => $group['lng'],
            ];
        }

        return array_slice($matches, 0, 30);
    }

    public function areaSummary(string $barangay, ?string $street = null): array
    {
        $key = static::normalizePlace($barangay);
        $matched = [];

        foreach ($this->businessRows() as $row) {
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

    // ── Opportunity ──────────────────────────────────────────────

    /**
     * Fetch and cache barangay population for a city identified by PSGC.
     * Returns a normalized-key → int map so it can be joined with groupByBarangay().
     * Population strings from the API carry commas and spaces (" 2,189 ") —
     * those are stripped before casting.
     */
    protected function barangayPopulations(): array
    {
        $cacheKey = config('economic-map.cache_prefix', 'economic_map').'.barangay_populations';
        $ttl = $this->ttl();

        return Cache::remember($cacheKey, $ttl, function (): array {
            try {
                $api = app(AddressApiService::class);

                // Walk the business list to find the first city we can resolve to
                // a PSGC ID — same pattern used by locationOptions().
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

                    $cityPsgcId = is_array($resolved['city'] ?? null)
                        ? ($resolved['city']['psgc_id'] ?? null)
                        : null;

                    if (! filled($cityPsgcId)) {
                        continue;
                    }

                    $barangays = $api->getBarangays($cityPsgcId);

                    if (empty($barangays)) {
                        continue;
                    }

                    $map = [];

                    foreach ($barangays as $b) {
                        $name = trim((string) ($b['name'] ?? ''));

                        if ($name === '') {
                            continue;
                        }

                        // Strip commas/spaces: " 2,189 " → 2189
                        $pop = (int) preg_replace('/[^\d]/', '', (string) ($b['population'] ?? '0'));
                        $map[static::normalizePlace($name)] = $pop;
                    }

                    return $map;
                }
            } catch (\Throwable) {
                // Fall through — return empty map; scoring skips population factor.
            }

            return [];
        });
    }

    protected function opportunityLevel(float $score): array
    {
        foreach (config('economic-map.opportunity_bands', []) as $band) {
            if ($score >= (float) $band['min']) {
                return ['level' => $band['label'], 'color' => $band['color']];
            }
        }

        return ['level' => 'Low', 'color' => '#6b7280'];
    }

    /**
     * Per-barangay economic opportunity scores.
     *
     * Composite score (0–1) blends five signals:
     *   1. Commercial density   — businesses per 1 000 residents (or per-max if no population)
     *   2. Growth momentum      — share of new registrations vs barangay total
     *   3. Sector diversity     — normalised Shannon entropy of industry sectors
     *   4. Agriculture signal   — share of Agriculture/Fishing rows (higher = more ag. potential)
     *   5. Livelihood gap       — inverse commercial density (high pop, few businesses → gap)
     *
     * Each signal is normalised 0–1 relative to all assessed barangays, then
     * averaged with equal weights.  Highlights (best commercial, fastest growing,
     * etc.) are derived from the raw per-signal values before normalisation.
     */
    public function opportunities(): array
    {
        $groups = $this->groupByBarangay();
        $populations = $this->barangayPopulations();

        if (empty($groups)) {
            return [
                'ranked' => [],
                'barangay_count' => 0,
                'high_very_high' => 0,
                'highlights' => [],
                'how_calculated' => $this->opportunityHowCalculated(),
            ];
        }

        // ── Raw signal collection ───────────────────────────────

        $raw = [];

        foreach ($groups as $key => $group) {
            $count = count($group['rows']);
            $pop = $populations[$key] ?? 0;
            $newRows = count(array_filter($group['rows'], fn (array $r): bool => static::isNew($r)));

            // Sector breakdown
            $sectorCounts = [];

            foreach ($group['rows'] as $row) {
                $sector = $this->categorizeIndustry($row['juridical']['line_of_industry'] ?? null);
                $sectorCounts[$sector] = ($sectorCounts[$sector] ?? 0) + 1;
            }

            // Shannon entropy for diversity (0 = single sector, higher = more diverse)
            $entropy = 0.0;

            foreach ($sectorCounts as $sc) {
                $p = $sc / $count;
                $entropy -= $p * log($p);
            }

            // Agriculture + Fishing share
            $agCount = ($sectorCounts['AGRICULTURE'] ?? 0) + ($sectorCounts['FISHING'] ?? 0);

            // Commercial density: businesses per 1 000 pop (0 when no population data)
            $density = $pop > 0 ? ($count / $pop) * 1000 : 0.0;

            // Livelihood gap: high pop + few businesses → high gap
            // Expressed as pop per business (more = bigger gap); 0 when no pop data
            $gap = ($pop > 0 && $count > 0) ? $pop / $count : 0.0;

            $raw[$key] = [
                'barangay' => $group['barangay'],
                'lat' => $group['lat'],
                'lng' => $group['lng'],
                'count' => $count,
                'new' => $newRows,
                'population' => $pop,
                'density' => $density,
                'growth' => $count > 0 ? $newRows / $count : 0.0,
                'entropy' => $entropy,
                'ag_share' => $count > 0 ? $agCount / $count : 0.0,
                'gap' => $gap,
                'sector_counts' => $sectorCounts,
            ];
        }

        // ── Normalise each signal 0–1 across all barangays ─────

        $maxDensity = max(array_column($raw, 'density') ?: [1.0]);
        $maxGrowth = max(array_column($raw, 'growth') ?: [1.0]);
        $maxEntropy = max(array_column($raw, 'entropy') ?: [1.0]);
        $maxAgShare = max(array_column($raw, 'ag_share') ?: [1.0]);
        $maxGap = max(array_column($raw, 'gap') ?: [1.0]);

        $scored = [];

        foreach ($raw as $key => $r) {
            $nDensity = $maxDensity > 0 ? $r['density'] / $maxDensity : 0.0;
            $nGrowth = $maxGrowth > 0 ? $r['growth'] / $maxGrowth : 0.0;
            $nEntropy = $maxEntropy > 0 ? $r['entropy'] / $maxEntropy : 0.0;
            $nAgShare = $maxAgShare > 0 ? $r['ag_share'] / $maxAgShare : 0.0;
            $nGap = $maxGap > 0 ? $r['gap'] / $maxGap : 0.0;

            $score = ($nDensity + $nGrowth + $nEntropy + $nAgShare + $nGap) / 5.0;

            $scored[$key] = array_merge($r, [
                'score' => round($score, 4),
                'n_density' => round($nDensity, 4),
                'n_growth' => round($nGrowth, 4),
                'n_ag' => round($nAgShare, 4),
                'n_gap' => round($nGap, 4),
            ]);
        }

        // ── Attach level/color and rank ─────────────────────────

        $ranked = [];
        $highVeryHigh = 0;

        foreach ($scored as $r) {
            $level = $this->opportunityLevel($r['score']);
            $entry = array_merge($r, $level);
            $ranked[] = $entry;

            if (in_array($level['level'], ['High', 'Very High'], true)) {
                $highVeryHigh++;
            }
        }

        usort($ranked, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        // ── Highlights ──────────────────────────────────────────

        // Best commercial area: highest business density
        usort($scored, fn (array $a, array $b): int => $b['density'] <=> $a['density']);
        $bestCommercial = reset($scored)['barangay'] ?? '—';

        // Fastest growing: highest new-registration ratio
        usort($scored, fn (array $a, array $b): int => $b['growth'] <=> $a['growth']);
        $fastestGrowing = reset($scored)['barangay'] ?? '—';

        // Highest tourism potential: coastal/island — approximate via "SAN JUAN" or
        // first barangay with sector diversity (entropy) as proxy when no tourism data
        usort($scored, fn (array $a, array $b): int => $b['entropy'] <=> $a['entropy']);
        $highestTourism = reset($scored)['barangay'] ?? '—';

        // Highest agriculture potential: highest ag_share
        usort($scored, fn (array $a, array $b): int => $b['ag_share'] <=> $a['ag_share']);
        $highestAg = reset($scored)['barangay'] ?? '—';

        // Biggest livelihood gap: highest pop-per-business ratio
        usort($scored, fn (array $a, array $b): int => $b['gap'] <=> $a['gap']);
        $biggestGap = reset($scored)['barangay'] ?? '—';

        return [
            'ranked' => $ranked,
            'barangay_count' => count($ranked),
            'high_very_high' => $highVeryHigh,
            'highlights' => [
                ['icon' => '🏪', 'label' => 'Best commercial area',      'value' => $bestCommercial],
                ['icon' => '📈', 'label' => 'Fastest growing area',       'value' => $fastestGrowing],
                ['icon' => '🏝',  'label' => 'Highest tourism potential', 'value' => $highestTourism],
                ['icon' => '🌾', 'label' => 'Highest agriculture potential', 'value' => $highestAg],
                ['icon' => '🧑‍🤝‍🧑', 'label' => 'Biggest livelihood gap',  'value' => $biggestGap],
            ],
            'how_calculated' => $this->opportunityHowCalculated(),
        ];
    }

    protected function opportunityHowCalculated(): array
    {
        return [
            ['term' => 'Commercial Potential', 'detail' => 'MSME Concentration (SCIMS registry)'],
            ['term' => 'Growth Momentum',      'detail' => 'New Registrations'],
            ['term' => 'Tourism Potential',    'detail' => 'Sector Diversity (entropy proxy)'],
            ['term' => 'Agriculture Potential', 'detail' => 'Land & Production Capacity (Agri/Fishing share)'],
            ['term' => 'Livelihood Gap',        'detail' => 'High Population, Few Businesses'],
            ['term' => 'Population',            'detail' => 'Live from vamosmobile.app/api/addresses/barangay/'],
        ];
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
     * Center point for one barangay.
     *
     * @return array{0: float, 1: float}|null [lat, lng]
     */
    public function barangayFocus(?string $barangay): ?array
    {
        $key = static::normalizePlace($barangay);

        if ($key === '') {
            return null;
        }

        $group = $this->groupByBarangay()[$key] ?? null;

        return $group !== null ? [$group['lat'], $group['lng']] : null;
    }

    /** Barangay-centre bounds for fitting the map viewport. */
    public function bounds(): ?array
    {
        $groups = $this->groupByBarangay();

        if ($groups === []) {
            return null;
        }

        $lats = array_map(fn (array $group): float => (float) $group['lat'], $groups);
        $lngs = array_map(fn (array $group): float => (float) $group['lng'], $groups);

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
