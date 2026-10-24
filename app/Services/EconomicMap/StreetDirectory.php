<?php

namespace App\Services\EconomicMap;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Searchable directory of San Carlos City streets and places.
 *
 * OpenStreetMap supplies accurately positioned streets, subdivisions and
 * hamlets. SCIMS address text supplies the street, sitio and subdivision
 * names that businesses actually use, including names OSM has not mapped yet.
 * Entries without an OSM position are anchored to their barangay centre and
 * marked as approximate, so the picker never silently pretends to have an
 * exact coordinate.
 */
class StreetDirectory
{
    protected const DATA_VERSION = 1;

    protected const CITY_AREA_ID = 3600145727;

    protected const OVERPASS_URL = 'https://overpass-api.de/api/interpreter';

    protected const ROAD_TYPES = [
        'motorway',
        'motorway_link',
        'trunk',
        'trunk_link',
        'primary',
        'primary_link',
        'secondary',
        'secondary_link',
        'tertiary',
        'tertiary_link',
        'unclassified',
        'residential',
        'living_street',
        'service',
        'road',
        'track',
    ];

    /**
     * Centroids from neighbouring municipalities that appear in some SCIMS
     * addresses. They stay available for address lookups, but must not pull
     * OpenStreetMap geometry out of San Carlos City.
     *
     * @var array<int, string>
     */
    protected const NON_CITY_BARANGAYS = ['macapso'];

    /**
     * Street names that exist in SCIMS addresses but are not yet named as
     * highways in OSM. `anchor` refers to an OSM subdivision/place name whose
     * centre is used when the street itself has no known geometry.
     *
     * @var array<string, array{label: string, barangay: string, anchor?: string, aliases?: array<int, string>}>
     */
    protected const SCIMS_ONLY_STREETS = [
        'absenthi' => ['label' => 'Absenthi Street', 'barangay' => 'Barangay II'],
        'aguinaldo' => ['label' => 'Aguinaldo Street', 'barangay' => 'Barangay II', 'anchor' => 'don juan'],
        'azucena' => ['label' => 'Azucena Street', 'barangay' => 'Barangay II', 'anchor' => 'san julio'],
        'caimito' => ['label' => 'Caimito Street', 'barangay' => 'Barangay II', 'anchor' => 'san julio'],
        'campanilla' => ['label' => 'Campanilla Street', 'barangay' => 'Barangay II', 'anchor' => 'san julio'],
        'chico' => ['label' => 'Chico Street', 'barangay' => 'Barangay II', 'anchor' => 'san julio'],
        'cona' => ['label' => 'Cona Street', 'barangay' => 'Barangay II'],
        'emerald' => ['label' => 'Emerald Street', 'barangay' => 'Barangay II', 'anchor' => "teacher's village"],
        'jasmin' => ['label' => 'Jasmin Street', 'barangay' => 'Barangay II', 'anchor' => 'san julio'],
        'laurel' => ['label' => 'Laurel Street', 'barangay' => 'Barangay II', 'anchor' => 'don juan'],
        'mapa' => ['label' => 'Mapa Street', 'barangay' => 'Barangay II', 'anchor' => 'don juan'],
        'nangka' => ['label' => 'Nangka Street', 'barangay' => 'Barangay II', 'anchor' => 'san julio'],
        'onyx' => ['label' => 'Onyx Street', 'barangay' => 'Barangay II', 'anchor' => "teacher's village"],
        'pearl' => ['label' => 'Pearl Street', 'barangay' => 'Barangay II', 'anchor' => "teacher's village"],
        'pres garcia' => ['label' => 'Pres. Garcia Street', 'barangay' => 'Barangay II', 'anchor' => 'don juan'],
        'pres osmena' => ['label' => 'Pres. Osmeña Street', 'barangay' => 'Barangay II', 'anchor' => 'don juan', 'aliases' => ['osmina', 'osmena']],
        'pres quezon' => ['label' => 'Pres. Quezon Street', 'barangay' => 'Barangay II', 'anchor' => 'don juan'],
        'pres quirino' => ['label' => 'Pres. Quirino Street', 'barangay' => 'Barangay II', 'anchor' => 'don juan'],
        'recoletos' => ['label' => 'Recoletos Street', 'barangay' => 'Barangay I'],
        'ruby' => ['label' => 'Ruby Street', 'barangay' => 'Barangay II', 'anchor' => "teacher's village"],
        'rum' => ['label' => 'Rum Street', 'barangay' => 'Barangay II'],
        'santol' => ['label' => 'Santol Street', 'barangay' => 'Barangay II', 'anchor' => 'san julio'],
        'sapphire' => ['label' => 'Sapphire Street', 'barangay' => 'Barangay II', 'anchor' => "teacher's village"],
        'silver' => ['label' => 'Silver Street', 'barangay' => 'Barangay II', 'anchor' => "teacher's village"],
        'velasco lim' => ['label' => 'Velasco-Lim Street', 'barangay' => 'Barangay I', 'anchor' => 'st vincent', 'aliases' => ['velascolim', 'velasco lim st']],
        '5th' => ['label' => '5th Street', 'barangay' => 'Barangay Rizal', 'anchor' => 'villa consuelo'],
        'mars' => ['label' => 'Mars Street', 'barangay' => 'Palampas', 'anchor' => 'south villa 1'],
    ];

    /**
     * Canonical subdivision names used in SCIMS addresses. Entries already
     * present in OSM are matched by key and receive these aliases instead of
     * being duplicated.
     *
     * @var array<string, array{label: string, barangay: string, aliases?: array<int, string>}>
     */
    protected const SUBDIVISIONS = [
        'campo siete' => ['label' => 'Campo Siete', 'barangay' => 'Barangay V'],
        'citihomes' => ['label' => 'Citihomes Subdivision', 'barangay' => 'Barangay V'],
        'don juan' => ['label' => 'Don Juan Subdivision', 'barangay' => 'Barangay II'],
        'fatima village' => ['label' => 'Fatima Village', 'barangay' => 'Barangay Rizal'],
        'fatima village phase 1' => ['label' => 'Fatima Village Phase 1', 'barangay' => 'Barangay Rizal'],
        'fatima village phase 3' => ['label' => 'Fatima Village Phase 3', 'barangay' => 'Barangay Rizal'],
        'fatima village phase 4' => ['label' => 'Fatima Village Phase 4', 'barangay' => 'Barangay Rizal'],
        'fatima village phase 5' => ['label' => 'Fatima Village Phase 5', 'barangay' => 'Barangay Rizal'],
        'greenville' => ['label' => 'Greenville', 'barangay' => 'Barangay Rizal'],
        'margarita' => ['label' => 'Margarita Subdivision', 'barangay' => 'Barangay II'],
        'metroville' => ['label' => 'Metroville', 'barangay' => 'Palampas'],
        'newtown' => ['label' => 'Newtown Subdivision', 'barangay' => 'Barangay I'],
        'rovirih heights' => ['label' => 'Rovirih Heights', 'barangay' => 'Barangay Rizal'],
        'rovirin heights' => ['label' => 'Rovirih Heights', 'barangay' => 'Barangay Rizal'],
        'san juan village' => ['label' => 'San Juan Village', 'barangay' => 'Barangay Rizal'],
        'san julio' => ['label' => 'San Julio Subdivision', 'barangay' => 'Barangay II'],
        'sancaville' => ['label' => 'SanCaVille', 'barangay' => 'Barangay Rizal'],
        'south villa 1' => ['label' => 'South Villa 1', 'barangay' => 'Palampas'],
        'south villa 2' => ['label' => 'South Villa 2', 'barangay' => 'Palampas'],
        'south villa 3' => ['label' => 'South Villa 3', 'barangay' => 'Palampas'],
        'st charles' => ['label' => 'St. Charles Subdivision', 'barangay' => 'Barangay I'],
        'st vincent' => ['label' => 'St. Vincent Subdivision', 'barangay' => 'Barangay I'],
        "teacher's village" => ['label' => "Teacher's Village", 'barangay' => 'Barangay II'],
        'teachers village' => ['label' => "Teacher's Village", 'barangay' => 'Barangay II'],
        'villa consuelo' => ['label' => 'Villa Consuelo Alger', 'barangay' => 'Barangay Rizal'],
        'villa consuelo alger' => ['label' => 'Villa Consuelo Alger', 'barangay' => 'Barangay Rizal'],
        'villarante' => ['label' => 'Villarante Village', 'barangay' => 'Barangay I'],
    ];

    /**
     * Common SCIMS spellings that should point at an OSM street.
     *
     * @var array<string, array<int, string>>
     */
    protected const STREET_ALIASES = [
        'a ylagan' => ['ylagan', 'ylagan st', 'ylagan street', 'a ylagan st', 'yalgan'],
        'a ylagan extension' => ['ylagan extension', 'ylagan ext', 'ylagan ext.', 'yalgan ext'],
        'p burgos' => ['burgos', 'burgos st', 'burgos street', 'burgost'],
        's carmona' => ['carmona', 'carmona st', 'carmona street'],
        'v gustilo' => ['gustilo', 'gustillo', 'gustilo st', 'gustillo st'],
        'f c ledesma' => ['fc ledesma', 'f.c. ledesma', 'ledesma ave', 'ledesma avenue', 'national highway', 'f.c. ledesma ave'],
        'c l ledesma' => ['cl ledesma', 'c.l. ledesma', 'ledesma ave', 'ledesma avenue', 'c.l. ledesma ave'],
        'eco tourism' => ['eco tourism', 'ecotourism highway', 'national highway', 'ecozone national highway', 'san carlos ecozone national highway'],
        'atienza' => ['attienza', 'atienza ave', 'atienza avenue'],
        'euzkara' => ['east euzkara', 'euzkara ave', 'euzkara avenue'],
        'buluangan' => ['bulangan', 'bulangan road'],
        'valdivia' => ['valdevia', 'valdevia st', 'vadidivia', 'validvia', 'valdivia st'],
        'azcona' => ['ascona', 'azocena', 'azcona st'],
        'medina' => ['medina st'],
        'locsin' => ['locsin st', 'locsin street'],
        'broce' => ['broce st', 'broce street'],
        'rizal' => ['rizal st', 'rizal street'],
        'm h del pilar' => ['del pilar', 'mh del pilar'],
        'j v ledesma' => ['jv ledesma', 'j.v. ledesma'],
        'e a antonio' => ['ea antonio', 'e.a. antonio'],
        'r magsaysay' => ['magsaysay', 'magsaysay st'],
        'j abad santos' => ['abad santos', 'j. abad santos'],
        'o quisumbing' => ['quisumbing', 'o. quisumbing'],
        'l y apuhin sr' => ['apuhin', 'l.y. apuhin'],
        'h c rigor' => ['hc rigor', 'h.c. rigor'],
    ];

    /**
     * Common SCIMS spellings for the same sitio or purok. Keeping the best
     * label and all raw spellings as aliases prevents near-duplicate results
     * such as "Sitio Pmahawan" and "Sitio Pamahawan".
     *
     * @var array<string, string>
     */
    protected const LOCALITY_CANONICAL = [
        'pamahawan' => 'Pamahawan',
        'pmahawan' => 'Pamahawan',
        'panoolan' => 'Panoolan',
        'pano lan' => 'Panoolan',
        'pano oaln' => 'Panoolan',
        'pano olan' => 'Panoolan',
        'pano ulan' => 'Panoolan',
        'gui ob' => 'Gui-ob',
        'gui 0b' => 'Gui-ob',
        'sto nino' => 'Sto. Niño',
        'sto nno' => 'Sto. Niño',
        'iliranan' => 'Iliranan',
        'ilaranan' => 'Iliranan',
        'lliranan' => 'Iliranan',
        'haguimitan' => 'Haguimitan',
        'haguinitan' => 'Haguimitan',
        'cabayasan' => 'Cabayasan',
        'cabay asan' => 'Cabayasan',
        'kabay asan' => 'Cabayasan',
        'tinorongan' => 'Tinorongan',
        'tinurungan' => 'Tinorongan',
        'tongo' => 'Tongo',
        'tonggo' => 'Tongo',
        'gemelina' => 'Gemelina',
        'gemilina' => 'Gemelina',
        'ilang ilang' => 'Ilang-Ilang',
        'ilabg ilang' => 'Ilang-Ilang',
        'lauan' => 'Lauan',
        'laua an' => 'Lauan',
        'tindalo' => 'Tindalo',
        'tindao' => 'Tindalo',
        'tindalo villarante' => 'Tindalo Villarante',
        'tindato villarante' => 'Tindalo Villarante',
    ];

    /** @var array<int, array<string, mixed>>|null */
    protected ?array $entries = null;

    /** @var array<string, array{0: float, 1: float}> */
    protected array $barangayCenters = [];

    /** @var array<string, string> */
    protected array $barangayLabels = [];

    public function __construct()
    {
        $this->bootBarangays();
    }

    public function path(): string
    {
        return resource_path('data/san-carlos-streets.json');
    }

    public function exists(): bool
    {
        return is_file($this->path());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        if ($this->entries !== null) {
            return $this->entries;
        }

        if (! $this->exists()) {
            return $this->entries = [];
        }

        $data = json_decode((string) file_get_contents($this->path()), true);
        $entries = is_array($data) ? ($data['entries'] ?? []) : [];

        return $this->entries = array_values(array_filter(
            is_array($entries) ? $entries : [],
            fn (mixed $entry): bool => is_array($entry)
                && isset($entry['name'], $entry['lat'], $entry['lng']),
        ));
    }

    /**
     * Compact entries embedded in the location picker.
     *
     * @return array<int, array<string, mixed>>
     */
    public function pickerEntries(): array
    {
        return array_map(fn (array $entry): array => [
            'name' => $entry['name'],
            'key' => $entry['key'] ?? self::key($entry['name']),
            'aliases' => array_values($entry['aliases'] ?? []),
            'label' => $entry['label'] ?? $this->label($entry),
            'barangay' => $entry['barangay'] ?? null,
            'lat' => (float) $entry['lat'],
            'lng' => (float) $entry['lng'],
            'kind' => $entry['kind'] ?? 'street',
            'precision' => $entry['precision'] ?? 'street',
            'source' => $entry['source'] ?? 'osm',
        ], $this->all());
    }

    /**
     * Search the local directory. Used by tests and as a server-side fallback;
     * the picker performs the same ranked matching in JavaScript.
     *
     * @param  array{0: float, 1: float}|null  $focus
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, ?array $focus = null, int $limit = 8): array
    {
        $needle = self::key($query);
        $normalized = self::normalize($query);
        $prefix = null;

        if (preg_match('/^(purok|prk|po|bo|barrio|sitio|so)\b/', trim($normalized), $matches) === 1) {
            $prefix = $matches[1];
        }

        $allowedKinds = $prefix === null
            ? null
            : (in_array($prefix, ['purok', 'prk', 'po', 'bo', 'barrio'], true)
                ? ['purok']
                : ['locality', 'place', 'subdivision']);
        $strictShort = $prefix !== null && strlen($needle) < 2;

        if ($needle === '') {
            return [];
        }

        $matches = [];

        foreach ($this->all() as $entry) {
            if ($allowedKinds !== null && ! in_array($entry['kind'] ?? 'street', $allowedKinds, true)) {
                continue;
            }

            $score = $this->matchScore($needle, $entry, $strictShort);

            if ($score === null) {
                continue;
            }

            $matches[] = [
                'score' => $score,
                'distance' => $this->distanceTo($entry, $focus),
                'entry' => $entry,
            ];
        }

        usort($matches, function (array $a, array $b): int {
            return [$a['score'], $a['distance'], $a['entry']['name']]
                <=> [$b['score'], $b['distance'], $b['entry']['name']];
        });

        return array_map(fn (array $match): array => [
            ...$match['entry'],
            'label' => $match['entry']['label'] ?? $this->label($match['entry']),
        ], array_slice($matches, 0, max(1, $limit)));
    }

    /**
     * Refresh the static directory from OpenStreetMap and cached SCIMS rows.
     *
     * @param  array<int, array<string, mixed>>  $scimsRows
     */
    public function refresh(array $scimsRows = []): int
    {
        $elements = $this->fetchOverpass(<<<'Q'
[out:json][timeout:180];
area(3600145727)->.a;
(
  way["highway"]["name"](area.a);
  node["place"](area.a);
  way["place"](area.a);
  relation["place"](area.a);
  way["landuse"~"residential|allotments|neighbourhood"]["name"](area.a);
  relation["landuse"~"residential|allotments|neighbourhood"]["name"](area.a);
);
out tags center;
Q);

        $roads = array_values(array_filter($elements, fn (array $element): bool => ($element['type'] ?? null) === 'way' && isset($element['tags']['highway'])));
        $places = array_values(array_filter($elements, fn (array $element): bool => in_array($element['type'] ?? null, ['node', 'way', 'relation'], true) && isset($element['tags']['place'])));
        $subdivisions = array_values(array_filter($elements, fn (array $element): bool => in_array($element['type'] ?? null, ['way', 'relation'], true) && isset($element['tags']['name']) && isset($element['tags']['landuse'])));

        $entries = $this->buildEntries($roads, $places, $subdivisions, $scimsRows);

        $path = $this->path();
        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException("Unable to create {$directory}.");
        }

        $payload = [
            'version' => self::DATA_VERSION,
            'generated_at' => now()->toIso8601String(),
            'source' => 'OpenStreetMap contributors (ODbL) + SCIMS address data',
            'count' => count($entries),
            'entries' => $entries,
        ];

        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            throw new RuntimeException('Unable to encode the street directory.');
        }

        $temporary = $path.'.tmp';

        if (file_put_contents($temporary, $encoded.PHP_EOL) === false) {
            throw new RuntimeException("Unable to write {$temporary}.");
        }

        if (! rename($temporary, $path)) {
            @unlink($temporary);

            throw new RuntimeException("Unable to replace {$path}.");
        }

        $this->entries = $entries;

        return count($entries);
    }

    public static function normalize(?string $value): string
    {
        $value = Str::ascii(mb_strtolower(trim((string) $value)));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', (string) $value));
    }

    /**
     * Name without its street/place type, used for matching. "Rizal St." and
     * "Rizal Street" both become "rizal"; "Sitio Trozo" becomes "trozo".
     */
    public static function key(?string $value): string
    {
        $value = self::normalize($value);

        if ($value === '') {
            return '';
        }

        $value = preg_replace('/^(barangay|brgy|sitio|so|purok|prk|po|bo|barrio)\s+/', '', $value);
        $value = preg_replace('/\b(saint)\b/', 'st', (string) $value);
        $value = preg_replace('/\b(santa)\b/', 'sta', (string) $value);
        $value = preg_replace('/\b(santo)\b/', 'sto', (string) $value);
        $value = preg_replace('/\s+(street|st|avenue|ave|road|rd|highway|hwy|drive|dr|lane|blvd|boulevard|extension|ext|subdivision|subd|village|vill)$/', '', (string) $value);

        return trim(preg_replace('/\s+/', ' ', (string) $value));
    }

    protected function bootBarangays(): void
    {
        foreach ((array) config('economic-map.barangay_centroids', []) as $key => $coordinates) {
            $key = (string) $key;
            $label = preg_match('/^(i|ii|iii|iv|v|vi)$/', $key) === 1
                ? 'Barangay '.strtoupper($key)
                : 'Barangay '.Str::title($key);

            if (! is_array($coordinates) || count($coordinates) < 2) {
                continue;
            }

            $this->barangayCenters[$key] = [(float) $coordinates[0], (float) $coordinates[1]];
            $this->barangayLabels[$key] = $label;
        }
    }

    protected function label(array $entry): string
    {
        $label = $entry['name'] ?? '';

        if (filled($entry['barangay'] ?? null)) {
            $label .= ', '.$entry['barangay'];
        }

        return $label;
    }

    protected function distanceTo(array $entry, ?array $focus): float
    {
        if (! is_array($focus) || count($focus) < 2) {
            return 0.0;
        }

        $lat = (float) $focus[0];
        $lng = (float) $focus[1];

        return ((float) $entry['lat'] - $lat) ** 2 + ((float) $entry['lng'] - $lng) ** 2;
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    protected function matchScore(string $needle, array $entry, bool $strictShort = false): ?int
    {
        $candidates = array_filter(array_merge(
            [(string) ($entry['key'] ?? self::key($entry['name'] ?? ''))],
            array_map('strval', (array) ($entry['aliases'] ?? [])),
        ));

        $tokens = array_values(array_filter(explode(' ', $needle)));
        $best = null;

        foreach ($candidates as $candidate) {
            $candidate = self::key($candidate);

            if ($candidate === '') {
                continue;
            }

            if ($strictShort && $candidate !== $needle && ! str_starts_with($candidate, $needle.' ')) {
                continue;
            }

            if ($candidate === $needle) {
                $score = 0;
            } elseif (str_starts_with($candidate, $needle.' ')) {
                $score = 1;
            } elseif (str_contains($candidate, ' '.$needle.' ') || str_ends_with($candidate, ' '.$needle)) {
                $score = 2;
            } else {
                $candidateTokens = explode(' ', $candidate);
                $score = count($tokens) > 1 && array_diff($tokens, $candidateTokens) === []
                    ? 3
                    : null;
            }

            if ($score !== null) {
                $best = $best === null ? $score : min($best, $score);
            }

            if ($best === 0) {
                break;
            }
        }

        return $best;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchOverpass(string $query): array
    {
        $url = self::OVERPASS_URL.'?data='.rawurlencode($query);

        if (function_exists('curl_init')) {
            $lastError = 'Unknown cURL error.';

            for ($attempt = 1; $attempt <= 2; $attempt++) {
                $handle = curl_init($url);

                curl_setopt_array($handle, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_USERAGENT => 'NegosyoTrack/1.0 street-directory (local government economic map)',
                    CURLOPT_CONNECTTIMEOUT => 30,
                    CURLOPT_TIMEOUT => 180,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_ENCODING => '',
                ]);

                $body = curl_exec($handle);
                $error = curl_error($handle);
                $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
                curl_close($handle);

                if (is_string($body) && $status >= 200 && $status < 300) {
                    $payload = json_decode($body, true);

                    if (is_array($payload) && is_array($payload['elements'] ?? null)) {
                        return $payload['elements'];
                    }

                    $lastError = 'Overpass returned an unexpected payload.';
                } else {
                    $lastError = "Overpass returned HTTP {$status} {$error}";
                }

                if ($attempt < 2) {
                    sleep(2);
                }
            }

            throw new RuntimeException('Unable to fetch OpenStreetMap data: '.trim($lastError));
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'NegosyoTrack/1.0 street-directory (local government economic map)',
            ])
                ->connectTimeout(30)
                ->timeout(180)
                ->retry(2, 1500)
                ->get(self::OVERPASS_URL, ['data' => $query]);

            if (! $response->successful()) {
                throw new RuntimeException("Overpass returned HTTP {$response->status()}.");
            }

            $elements = $response->json('elements', []);

            if (! is_array($elements)) {
                throw new RuntimeException('Overpass returned an unexpected payload.');
            }

            return $elements;
        } catch (Throwable $exception) {
            throw new RuntimeException('Unable to fetch OpenStreetMap data: '.$exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $roads
     * @param  array<int, array<string, mixed>>  $places
     * @param  array<int, array<string, mixed>>  $subdivisions
     * @param  array<int, array<string, mixed>>  $scimsRows
     * @return array<int, array<string, mixed>>
     */
    protected function buildEntries(array $roads, array $places, array $subdivisions, array $scimsRows): array
    {
        $entries = [];
        $subdivisionCenters = [];
        $placeCenters = [];

        foreach ($subdivisions as $subdivision) {
            $point = $this->point($subdivision);
            $name = trim((string) ($subdivision['tags']['name'] ?? ''));

            if ($point === null || $name === '') {
                continue;
            }

            $name = $this->canonicalSubdivisionName($name);
            $key = self::key($name);
            $subdivisionCenters[$key] = $point;

            // Canonical subdivisions are added once below with their checked
            // barangay and aliases. Keeping the raw OSM duplicates would make
            // one subdivision appear two or three times in search results.
            if ($this->isCanonicalSubdivision($key)) {
                continue;
            }

            $this->addEntry($entries, [
                'name' => $name,
                'key' => $key,
                'aliases' => $this->aliasesFor($name, 'subdivision'),
                'lat' => $point[0],
                'lng' => $point[1],
                'barangay' => $this->nearestBarangay($point[0], $point[1]),
                'kind' => 'subdivision',
                'precision' => 'subdivision',
                'source' => 'osm',
            ]);
        }

        foreach ($places as $place) {
            $point = $this->point($place);
            $tags = $place['tags'] ?? [];
            $name = trim((string) ($tags['name'] ?? ''));

            if ($point === null || $name === '' || ! in_array($tags['place'] ?? '', ['village', 'hamlet', 'suburb', 'quarter', 'neighbourhood', 'locality'], true)) {
                continue;
            }

            $key = self::key($name);
            $barangay = $this->nearestBarangay($point[0], $point[1]);
            $placeCenters[$key.'|'.$barangay] = $point;
            $this->addEntry($entries, [
                'name' => $name,
                'key' => $key,
                'aliases' => $this->aliasesFor($name, $tags['place']),
                'lat' => $point[0],
                'lng' => $point[1],
                'barangay' => $barangay,
                'kind' => $tags['place'] === 'neighbourhood' ? 'locality' : 'place',
                'precision' => 'place',
                'source' => 'osm',
            ]);
        }

        $roadGroups = [];

        foreach ($roads as $road) {
            $tags = $road['tags'] ?? [];
            $name = trim((string) ($tags['name'] ?? ''));
            $highway = (string) ($tags['highway'] ?? '');

            if ($name === '' || ! in_array($highway, self::ROAD_TYPES, true) || $this->isNotAStreet($name)) {
                continue;
            }

            $point = $this->point($road);

            if ($point === null) {
                continue;
            }

            $name = $this->canonicalStreetName($name);
            $key = self::key($name);
            $barangay = $this->nearestBarangay($point[0], $point[1]);
            $groupKey = $key.'|'.$barangay;

            $roadGroups[$groupKey]['name'] = $name;
            $roadGroups[$groupKey]['key'] = $key;
            $roadGroups[$groupKey]['points'][] = $point;
            $roadGroups[$groupKey]['barangay'] = $barangay;
            $roadGroups[$groupKey]['types'][$highway] = true;
        }

        foreach ($roadGroups as $group) {
            $points = $group['points'];
            $lat = array_sum(array_column($points, 0)) / count($points);
            $lng = array_sum(array_column($points, 1)) / count($points);

            $this->addEntry($entries, [
                'name' => $group['name'],
                'key' => $group['key'],
                'aliases' => $this->aliasesFor($group['name'], 'street'),
                'lat' => round($lat, 8),
                'lng' => round($lng, 8),
                'barangay' => $group['barangay'],
                'kind' => 'street',
                'precision' => 'street',
                'source' => 'osm',
            ]);
        }

        $this->addLocalPlaces($entries);
        $this->addCanonicalSubdivisions($entries, $subdivisionCenters);
        $this->addScimsOnlyStreets($entries, $subdivisionCenters);
        $this->addScimsLocalities($entries, $placeCenters, $scimsRows);

        return collect($entries)
            ->sortBy(fn (array $entry): string => ($entry['kind'] ?? '').'|'.($entry['name'] ?? '').'|'.($entry['barangay'] ?? ''))
            ->values()
            ->all();
    }

    protected function isNotAStreet(string $name): bool
    {
        return preg_match('/\b(trail|wharf|pier|bridge|river|peak|boundary|construction|reef)\b/i', $name) === 1;
    }

    protected function canonicalStreetName(string $name): string
    {
        return match (self::key($name)) {
            'valdevia' => 'Valdivia Street',
            'bulangan' => 'Buluangan Road',
            default => trim($name),
        };
    }

    protected function canonicalSubdivisionName(string $name): string
    {
        return match (self::key($name)) {
            'new town subdivistion', 'new town subdivision' => 'Newtown Subdivision',
            default => trim($name),
        };
    }

    protected function isCanonicalSubdivision(string $key): bool
    {
        foreach (array_keys(self::SUBDIVISIONS) as $canonicalKey) {
            if (self::key($canonicalKey) === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    protected function point(array $element): ?array
    {
        if (isset($element['lat'], $element['lon'])) {
            return [(float) $element['lat'], (float) $element['lon']];
        }

        if (isset($element['center']['lat'], $element['center']['lon'])) {
            return [(float) $element['center']['lat'], (float) $element['center']['lon']];
        }

        return null;
    }

    protected function nearestBarangay(float $lat, float $lng): ?string
    {
        $nearest = null;
        $distance = INF;

        foreach ($this->barangayCenters as $key => [$centerLat, $centerLng]) {
            if (in_array($key, self::NON_CITY_BARANGAYS, true)) {
                continue;
            }

            $candidate = (($centerLat - $lat) ** 2) + (($centerLng - $lng) ** 2);

            if ($candidate < $distance) {
                $distance = $candidate;
                $nearest = $this->barangayLabels[$key] ?? null;
            }
        }

        return $nearest;
    }

    protected function normalizeBarangay(?string $value): ?string
    {
        $key = self::normalize($value);
        $key = preg_replace('/^(barangay|brgy)\s+/', '', $key);
        $key = preg_replace_callback(
            '/\b([1-6])\b/',
            fn (array $matches): string => ['i', 'ii', 'iii', 'iv', 'v', 'vi'][(int) $matches[1] - 1],
            (string) $key,
        );

        if ($key === null || $key === '') {
            return null;
        }

        return $this->barangayLabels[$key] ?? null;
    }

    /**
     * @return array<int, string>
     */
    protected function aliasesFor(string $name, string $kind): array
    {
        $aliases = [];
        $key = self::key($name);
        $normalized = self::normalize($name);

        if ($key !== '') {
            $aliases[] = $key;
        }

        if ($normalized !== '') {
            $aliases[] = $normalized;
        }

        $suffix = match ($kind) {
            'street', 'road', 'highway' => 'street',
            'subdivision', 'village' => 'subdivision',
            'purok' => 'purok',
            'sitio' => 'sitio',
            default => '',
        };

        if ($suffix !== '' && $key !== '') {
            $aliases[] = $key.' '.$suffix;
        }

        if (isset(self::STREET_ALIASES[$key])) {
            $aliases = array_merge($aliases, self::STREET_ALIASES[$key]);
        }

        // "P. Burgos" can be found as "Burgos"; "F. C. Ledesma" as "Ledesma".
        $tokens = explode(' ', $key);

        if (count($tokens) > 1) {
            $leading = 0;

            while (isset($tokens[$leading]) && mb_strlen($tokens[$leading]) === 1) {
                $leading++;
            }

            if ($leading > 0 && $leading < count($tokens)) {
                $aliases[] = implode(' ', array_slice($tokens, $leading));
            }
        }

        return array_values(array_unique(array_filter($aliases)));
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     */
    protected function addEntry(array &$entries, array $entry): void
    {
        $key = ($entry['key'] ?? self::key($entry['name'])).'|'.($entry['barangay'] ?? '').'|'.($entry['kind'] ?? 'street');

        if (isset($entries[$key])) {
            $priority = ['barangay' => 1, 'place' => 2, 'subdivision' => 3, 'street' => 4];
            $sourcePriority = ['config' => 0, 'scims' => 1, 'osm' => 2];
            $newPrecision = $priority[$entry['precision'] ?? 'barangay'] ?? 0;
            $oldPrecision = $priority[$entries[$key]['precision'] ?? 'barangay'] ?? 0;
            $newSource = $sourcePriority[$entry['source'] ?? ''] ?? -1;
            $oldSource = $sourcePriority[$entries[$key]['source'] ?? ''] ?? -1;

            if ($newPrecision > $oldPrecision || ($newPrecision === $oldPrecision && $newSource > $oldSource)) {
                $entry['aliases'] = array_values(array_unique(array_merge(
                    $entries[$key]['aliases'] ?? [],
                    $entry['aliases'] ?? [],
                )));
                $entries[$key] = $entry;
            } else {
                $entries[$key]['aliases'] = array_values(array_unique(array_merge(
                    $entries[$key]['aliases'] ?? [],
                    $entry['aliases'] ?? [],
                )));
            }

            return;
        }

        $entries[$key] = $entry;
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     */
    protected function addLocalPlaces(array &$entries): void
    {
        foreach ((array) config('economic-map.local_places', []) as $name => $place) {
            if (! is_array($place)) {
                continue;
            }

            $lat = (float) ($place['lat'] ?? ($place[0] ?? 0));
            $lng = (float) ($place['lng'] ?? ($place[1] ?? 0));

            if ($lat === 0.0 && $lng === 0.0) {
                continue;
            }

            $label = trim((string) ($place['label'] ?? $name));
            $directoryName = trim((string) $name);

            $this->addEntry($entries, [
                'name' => $directoryName !== '' ? $directoryName : $label,
                'key' => self::key($directoryName !== '' ? $directoryName : $label),
                'aliases' => $this->aliasesFor($directoryName !== '' ? $directoryName : $label, 'subdivision'),
                'lat' => $lat,
                'lng' => $lng,
                'barangay' => $this->nearestBarangay($lat, $lng),
                'kind' => 'subdivision',
                'precision' => 'subdivision',
                'source' => 'config',
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     * @param  array<string, array{0: float, 1: float}>  $subdivisionCenters
     */
    protected function addCanonicalSubdivisions(array &$entries, array $subdivisionCenters): void
    {
        foreach (self::SUBDIVISIONS as $key => $subdivision) {
            $point = $this->exactAnchorPoint($key, $subdivisionCenters);
            $anchored = $point !== null;

            if ($point === null) {
                $point = $this->centerForBarangay($subdivision['barangay']);
            }

            $this->addEntry($entries, [
                'name' => $subdivision['label'],
                'key' => self::key($subdivision['label']),
                'aliases' => array_values(array_unique(array_merge(
                    [$key],
                    $this->aliasesFor($subdivision['label'], 'subdivision'),
                    $subdivision['aliases'] ?? [],
                ))),
                'lat' => $point[0],
                'lng' => $point[1],
                'barangay' => $subdivision['barangay'],
                'kind' => 'subdivision',
                'precision' => $anchored ? 'subdivision' : 'barangay',
                'source' => $anchored ? 'osm' : 'config',
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     * @param  array<string, array{0: float, 1: float}>  $subdivisionCenters
     */
    protected function addScimsOnlyStreets(array &$entries, array $subdivisionCenters): void
    {
        $existing = collect($entries)
            ->filter(fn (array $entry): bool => ($entry['kind'] ?? null) === 'street')
            ->keyBy(fn (array $entry): string => (string) ($entry['key'] ?? ''));

        foreach (self::SCIMS_ONLY_STREETS as $key => $street) {
            if ($existing->has($key)) {
                $this->addEntry($entries, [
                    'name' => $existing[$key]['name'],
                    'key' => $existing[$key]['key'],
                    'aliases' => array_values(array_unique(array_merge(
                        $existing[$key]['aliases'] ?? [],
                        $this->aliasesFor($street['label'], 'street'),
                        $street['aliases'] ?? [],
                    ))),
                    'lat' => $existing[$key]['lat'],
                    'lng' => $existing[$key]['lng'],
                    'barangay' => $existing[$key]['barangay'],
                    'kind' => 'street',
                    'precision' => 'street',
                    'source' => 'osm',
                ]);

                continue;
            }

            $point = isset($street['anchor']) ? $this->anchorPoint($street['anchor'], $subdivisionCenters) : null;
            $anchored = $point !== null;

            if ($point === null) {
                $point = $this->centerForBarangay($street['barangay']);
            }

            $this->addEntry($entries, [
                'name' => $street['label'],
                'key' => $key,
                'aliases' => array_values(array_unique(array_merge(
                    $this->aliasesFor($street['label'], 'street'),
                    $street['aliases'] ?? [],
                ))),
                'lat' => $point[0],
                'lng' => $point[1],
                'barangay' => $street['barangay'],
                'kind' => 'street',
                'precision' => $anchored ? 'subdivision' : 'barangay',
                'source' => 'scims',
            ]);
        }
    }

    /**
     * Pull sitio/purok names out of SCIMS street values so rural businesses can
     * be searched even when their address has no named street.
     *
     * @param  array<int, array<string, mixed>>  $entries
     * @param  array<string, array{0: float, 1: float}>  $placeCenters
     * @param  array<int, array<string, mixed>>  $scimsRows
     */
    protected function addScimsLocalities(array &$entries, array $placeCenters, array $scimsRows): void
    {
        $localities = [];

        foreach ($scimsRows as $row) {
            $juridical = $row['juridical'] ?? [];
            $raw = trim((string) ($juridical['street'] ?? ''));
            $barangay = $this->normalizeBarangay($juridical['barangay'] ?? null);

            if ($raw === '' || $barangay === null) {
                continue;
            }

            $upper = mb_strtoupper($raw);
            $upper = str_replace(['Ñ', '?'], ['N', 'N'], $upper);
            $upper = preg_replace('/[^A-Z0-9]+/', ' ', $upper);
            $upper = trim(preg_replace('/\s+/', ' ', (string) $upper));

            $kind = null;
            $name = null;

            if (preg_match('/^(?:SO|SITIO)\s+(.+)$/', $upper, $matches) === 1) {
                $kind = 'locality';
                $name = $matches[1];
            } elseif (preg_match('/^(?:PO|PUROK|PRK)\s+(.+)$/', $upper, $matches) === 1) {
                $kind = 'purok';
                $name = $matches[1];
            } elseif (preg_match('/^(?:BO|BARRIO)\s+(.+)$/', $upper, $matches) === 1) {
                $kind = 'purok';
                $name = $matches[1];
            }

            if ($kind === null || $name === null) {
                continue;
            }

            $name = preg_replace('/\b(BRGY|BARANGAY)\b.*$/', '', $name);
            $name = preg_replace('/\b(PROPER|VILL|VILLAGE|SUBD|SUBDIVISION)\b/', ' ', (string) $name);
            $name = trim(preg_replace('/\s+/', ' ', (string) $name));

            if ($name === '' || preg_match('/^\d+$/', $name) === 1) {
                continue;
            }

            $canonicalName = self::LOCALITY_CANONICAL[self::normalize($name)] ?? null;

            if ($canonicalName === null) {
                $canonicalName = preg_match('/^(I|II|III|IV|V|VI)$/', $name) === 1
                    ? $name
                    : Str::title($name);
            }

            $label = ($kind === 'purok' ? 'Purok ' : 'Sitio ').$canonicalName;
            $key = self::key($label);
            $localityKey = $kind.'|'.$key.'|'.$barangay;

            if (! isset($localities[$localityKey])) {
                $localities[$localityKey] = [
                    'name' => $label,
                    'key' => $key,
                    'barangay' => $barangay,
                    'kind' => $kind,
                    'raws' => [],
                ];
            }

            $localities[$localityKey]['raws'][] = self::normalize($raw);
        }

        foreach ($localities as $locality) {
            $point = $placeCenters[$locality['key'].'|'.$locality['barangay']] ?? null;
            $precision = 'place';

            if ($point === null) {
                $point = $this->centerForBarangay($locality['barangay']);
                $precision = 'barangay';
            }

            $rawAliases = [];

            foreach (array_unique($locality['raws']) as $raw) {
                $raw = preg_replace('/\b(brgy|barangay)\b.*$/', '', $raw);
                $raw = trim((string) $raw);

                if ($raw === '') {
                    continue;
                }

                $rawAliases[] = $raw;
                $rawAliases[] = self::key($raw);
            }

            $this->addEntry($entries, [
                'name' => $locality['name'],
                'key' => $locality['key'],
                'aliases' => array_values(array_unique(array_merge(
                    $this->aliasesFor($locality['name'], $locality['kind'] === 'purok' ? 'purok' : 'sitio'),
                    $rawAliases,
                ))),
                'lat' => $point[0],
                'lng' => $point[1],
                'barangay' => $locality['barangay'],
                'kind' => $locality['kind'],
                'precision' => $precision,
                'source' => 'scims',
            ]);
        }
    }

    /**
     * @param  array<string, array{0: float, 1: float}>  $subdivisionCenters
     * @return array{0: float, 1: float}|null
     */
    protected function exactAnchorPoint(string $key, array $subdivisionCenters): ?array
    {
        $needle = self::key($key);

        foreach ($subdivisionCenters as $centerKey => $point) {
            if (self::key($centerKey) === $needle) {
                return $point;
            }
        }

        return null;
    }

    /**
     * @param  array<string, array{0: float, 1: float}>  $subdivisionCenters
     * @return array{0: float, 1: float}|null
     */
    protected function anchorPoint(string $key, array $subdivisionCenters): ?array
    {
        $point = $this->exactAnchorPoint($key, $subdivisionCenters);

        if ($point !== null) {
            return $point;
        }

        $needle = self::key($key);

        // Exact keys always win. The fallback substring match is only for
        // shortened SCIMS names such as "Villa Consuelo" vs "Villa Consuelo
        // Alger"; without this pass, "Fatima Village" would incorrectly use
        // the centre of a numbered phase.
        foreach ($subdivisionCenters as $centerKey => $point) {
            if (str_contains(self::key($centerKey), $needle)) {
                return $point;
            }
        }

        return null;
    }

    /**
     * @return array{0: float, 1: float}
     */
    protected function centerForBarangay(string $barangay): array
    {
        $key = self::normalize($barangay);
        $key = preg_replace('/^(barangay|brgy)\s+/', '', $key);

        return $this->barangayCenters[$key]
            ?? config('economic-map.city_center', [10.4849, 123.4199]);
    }
}
