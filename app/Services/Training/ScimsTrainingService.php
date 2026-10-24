<?php

namespace App\Services\Training;

use App\Services\ScimsJuridicalApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * SCIMS integration for the Training module.
 *
 * ScimsJuridicalApiService belongs to the MSME module and is left untouched,
 * so this service wraps it for business lookups and reads the SCIMS
 * individuals endpoint directly for trainees. All Training screens use
 * this service instead of calling SCIMS (or the shared service) directly.
 */
class ScimsTrainingService
{
    protected const CACHE_TTL_MINUTES = 30;

    protected const SEARCH_LIMIT = 50;

    protected const TIMEOUT_SECONDS = 15;

    /**
     * Request-level memo so repeated lookups (e.g. table columns) reuse the
     * same response without hitting the cache or SCIMS again.
     *
     * @var array<string, array<string, mixed>|null>
     */
    protected array $individualMemo = [];

    public function __construct(
        protected ScimsJuridicalApiService $juridical,
    ) {}

    /**
     * Business search used by the training program form.
     *
     * @return array<string, string> entity_no => "Business Name (entity_no)"
     */
    public function businessOptions(string $search): array
    {
        $cacheKey = $this->cacheKey('business-search', $search);

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $businesses = $this->juridical->getBusiness(
                page: 1,
                recordsPerPage: self::SEARCH_LIMIT,
                search: $search,
                sortColumn: null,
                sortDirection: null,
            );
        } catch (Throwable) {
            // SCIMS is unavailable — return no options and let the next
            // keystroke retry; failures are not cached.
            return [];
        }

        $options = [];

        foreach ($businesses as $business) {
            $entityNo = trim((string) ($business['entity_no'] ?? ''));

            if ($entityNo === '') {
                continue;
            }

            $options[$entityNo] = $this->businessOptionLabel($business, $entityNo);
        }

        Cache::put($cacheKey, $options, now()->addMinutes(self::CACHE_TTL_MINUTES));

        return $options;
    }

    /**
     * Label for an already-selected business, falling back to the entity_no
     * when SCIMS cannot resolve it.
     */
    public function businessLabel(string $entityNo): string
    {
        $cacheKey = 'scims-training:business:'.$entityNo;

        $cached = Cache::get($cacheKey);

        if (is_string($cached)) {
            return $cached;
        }

        try {
            $business = $this->juridical->getBusinessByID($entityNo);
        } catch (Throwable) {
            return $entityNo;
        }

        if (! is_array($business)) {
            return $entityNo;
        }

        $label = $this->businessOptionLabel($business, $entityNo);

        Cache::put($cacheKey, $label, now()->addMinutes(self::CACHE_TTL_MINUTES));

        return $label;
    }

    /**
     * Trainee search used by the trainee form.
     *
     * @return array<string, string> entity_no => "Full Name (entity_no)"
     */
    public function individualOptions(string $search): array
    {
        $cacheKey = $this->cacheKey('individual-search', $search);

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $individuals = $this->get('individuals', [
                'filter[search]' => $search,
                'per_page' => self::SEARCH_LIMIT,
                'page' => 1,
            ])['data'] ?? [];
        } catch (Throwable) {
            return [];
        }

        $options = [];

        foreach ($individuals as $individual) {
            $attributes = $individual['attributes'] ?? [];
            $entityNo = trim((string) ($attributes['entity_no'] ?? $individual['id'] ?? ''));

            // SCIMS can return the same person more than once (one row per
            // contact detail), so keep only the first entry per entity_no.
            if ($entityNo === '' || isset($options[$entityNo])) {
                continue;
            }

            $options[$entityNo] = $this->individualOptionLabel($attributes, $entityNo);
        }

        Cache::put($cacheKey, $options, now()->addMinutes(self::CACHE_TTL_MINUTES));

        return $options;
    }

    /**
     * Label for an already-selected trainee, falling back to the entity_no
     * when SCIMS cannot resolve it.
     */
    public function individualLabel(string $entityNo): string
    {
        $attributes = $this->findIndividual($entityNo);

        return is_array($attributes)
            ? $this->individualOptionLabel($attributes, $entityNo)
            : $entityNo;
    }

    /**
     * Resolve one person from SCIMS. Null when missing or unreachable.
     *
     * @return array<string, mixed>|null
     */
    public function findIndividual(?string $entityNo): ?array
    {
        if (blank($entityNo)) {
            return null;
        }

        if (array_key_exists($entityNo, $this->individualMemo)) {
            return $this->individualMemo[$entityNo];
        }

        $cacheKey = 'scims-training:individual:'.$entityNo;

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $this->individualMemo[$entityNo] = $cached;
        }

        try {
            $attributes = $this->get('individuals/'.rawurlencode($entityNo))['data']['attributes'] ?? null;
        } catch (Throwable) {
            return null;
        }

        if (! is_array($attributes)) {
            return null;
        }

        Cache::put($cacheKey, $attributes, now()->addMinutes(self::CACHE_TTL_MINUTES));

        return $this->individualMemo[$entityNo] = $attributes;
    }

    /**
     * "FIRST MIDDLE LAST SUFFIX", skipping blanks and filler values such as
     * "N/A" that SCIMS stores in some records.
     *
     * @param  array<string, mixed>|null  $individual
     */
    public static function formatName(?array $individual): string
    {
        $parts = array_filter(
            [
                $individual['first_name'] ?? null,
                $individual['middle_name'] ?? null,
                $individual['last_name'] ?? null,
                $individual['suffix'] ?? null,
            ],
            fn ($part): bool => is_string($part)
                && trim($part) !== ''
                && ! in_array(mb_strtoupper(trim($part)), ['N/A', 'NA', 'NONE', '-'], true),
        );

        return implode(' ', array_map(
            fn (string $part): string => trim($part),
            $parts,
        ));
    }

    /**
     * @param  array<string, mixed>  $business
     */
    protected function businessOptionLabel(array $business, string $entityNo): string
    {
        $name = trim((string) ($business['juri_name'] ?? ''));

        return $name !== '' ? $name.' ('.$entityNo.')' : $entityNo;
    }

    /**
     * @param  array<string, mixed>  $individual
     */
    protected function individualOptionLabel(array $individual, string $entityNo): string
    {
        $name = self::formatName($individual);

        return $name !== '' ? $name.' ('.$entityNo.')' : $entityNo;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    protected function get(string $path, array $query = []): array
    {
        $payload = Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->timeout(self::TIMEOUT_SECONDS)
            ->get($path, $query)
            ->throw()
            ->json();

        return is_array($payload) ? $payload : [];
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('services.scims.base_url', 'https://cictd.app/api/scims'), '/');
    }

    protected function cacheKey(string $scope, string $value): string
    {
        return 'scims-training:'.$scope.':'.mb_strtolower(trim($value));
    }
}
