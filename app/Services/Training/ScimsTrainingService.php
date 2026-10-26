<?php

namespace App\Services\Training;

use App\Services\ScimsJuridicalApiService;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
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

    /**
     * SCIMS caps result pages at 100 records, regardless of per_page.
     */
    protected const BATCH_PAGE_SIZE = 100;

    /**
     * Entity numbers sent per batch lookup. Kept well below URL limits.
     */
    protected const BATCH_SIZE = 50;

    /**
     * Safety cap for name searches so a broad term cannot walk SCIMS forever.
     */
    protected const SEARCH_MAX_PAGES = 20;

    protected const TIMEOUT_SECONDS = 15;

    /**
     * Request-level memo so repeated lookups (e.g. table columns) reuse the
     * same response without hitting the cache or SCIMS again.
     *
     * @var array<string, array<string, mixed>|null>
     */
    protected array $individualMemo = [];

    /**
     * @var array<string, array<string, mixed>|null>
     */
    protected array $businessMemo = [];

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
        $business = $this->findBusiness($entityNo);

        return is_array($business)
            ? $this->businessOptionLabel($business, $entityNo)
            : $entityNo;
    }

    /**
     * Business name for snapshotting onto a training record.
     */
    public function businessName(?string $entityNo): ?string
    {
        $business = $this->findBusiness($entityNo);
        $name = trim((string) ($business['juri_name'] ?? ''));

        return $name !== '' ? $name : null;
    }

    /**
     * Resolve one business from SCIMS. Null when missing or unreachable.
     *
     * @return array<string, mixed>|null
     */
    public function findBusiness(?string $entityNo): ?array
    {
        if (blank($entityNo)) {
            return null;
        }

        if (array_key_exists($entityNo, $this->businessMemo)) {
            return $this->businessMemo[$entityNo];
        }

        $cacheKey = 'scims-training:business-record:'.$entityNo;

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $this->businessMemo[$entityNo] = $cached;
        }

        try {
            $business = $this->juridical->getBusinessByID($entityNo);
        } catch (Throwable) {
            return null;
        }

        if (! is_array($business)) {
            return null;
        }

        Cache::put($cacheKey, $business, now()->addMinutes(self::CACHE_TTL_MINUTES));

        return $this->businessMemo[$entityNo] = $business;
    }

    /**
     * Trainee search used by the trainee form.
     *
     * @return array<string, string> entity_no => "Full Name (entity_no)"
     */
    public function individualOptions(string $search): array
    {
        $cacheKey = $this->cacheKey('individual-search', $search);

        $cached = $this->identityCache()->get($cacheKey);

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

        $this->identityCache()->put($cacheKey, $options, now()->addMinutes(self::CACHE_TTL_MINUTES));

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
        $entityNo = trim((string) $entityNo);

        if ($entityNo === '') {
            return null;
        }

        if (array_key_exists($entityNo, $this->individualMemo)) {
            return $this->individualMemo[$entityNo];
        }

        $cached = $this->identityCache()->get($this->individualCacheKey($entityNo));

        if (is_array($cached)) {
            return $this->individualMemo[$entityNo] = $cached;
        }

        try {
            $attributes = $this->get('individuals/'.rawurlencode($entityNo))['data']['attributes'] ?? null;
        } catch (Throwable) {
            return $this->individualMemo[$entityNo] = null;
        }

        if (! is_array($attributes)) {
            return $this->individualMemo[$entityNo] = null;
        }

        $this->identityCache()->put(
            $this->individualCacheKey($entityNo),
            $attributes,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
        );

        return $this->individualMemo[$entityNo] = $attributes;
    }

    /**
     * Resolve many people in as few SCIMS calls as possible.
     *
     * The result is keyed by entity_no. People SCIMS could not resolve are
     * omitted; failed requests are memoized for this request so a single
     * outage does not trigger one timeout per table row.
     *
     * @param  array<int, string|null>  $entityNos
     * @return array<string, array<string, mixed>>
     */
    public function findIndividuals(array $entityNos): array
    {
        $entityNos = $this->normalizeEntityNumbers($entityNos);

        if ($entityNos === []) {
            return [];
        }

        $resolved = [];
        $pending = [];

        foreach ($entityNos as $entityNo) {
            if (array_key_exists($entityNo, $this->individualMemo)) {
                if (is_array($this->individualMemo[$entityNo])) {
                    $resolved[$entityNo] = $this->individualMemo[$entityNo];
                }

                continue;
            }

            $cached = $this->identityCache()->get($this->individualCacheKey($entityNo));

            if (is_array($cached)) {
                $this->individualMemo[$entityNo] = $cached;
                $resolved[$entityNo] = $cached;

                continue;
            }

            $pending[] = $entityNo;
        }

        foreach (array_chunk($pending, self::BATCH_SIZE) as $chunk) {
            try {
                $fetched = $this->fetchIndividuals($chunk);
            } catch (Throwable) {
                foreach ($chunk as $entityNo) {
                    $this->individualMemo[$entityNo] = null;
                }

                continue;
            }

            foreach ($chunk as $entityNo) {
                $attributes = $fetched[$entityNo] ?? null;

                $this->individualMemo[$entityNo] = $attributes;

                if (! is_array($attributes)) {
                    continue;
                }

                $this->identityCache()->put(
                    $this->individualCacheKey($entityNo),
                    $attributes,
                    now()->addMinutes(self::CACHE_TTL_MINUTES),
                );

                $resolved[$entityNo] = $attributes;
            }
        }

        return $resolved;
    }

    /**
     * Drop the cached identity for one person so the next read is fresh.
     * Nothing is written back to the local database.
     */
    public function forgetIndividual(?string $entityNo): void
    {
        $entityNo = trim((string) $entityNo);

        if ($entityNo === '') {
            return;
        }

        unset($this->individualMemo[$entityNo]);

        $this->identityCache()->forget($this->individualCacheKey($entityNo));
    }

    /**
     * Entity numbers whose SCIMS identity matches the search term.
     *
     * SCIMS cannot combine filter[search] with filter[entity_no], so the
     * matching entity numbers are resolved first and used in a local whereIn.
     *
     * @return array<int, string>
     */
    public function searchIndividualEntityNumbers(string $search): array
    {
        $search = trim($search);

        if ($search === '') {
            return [];
        }

        $cacheKey = $this->cacheKey('individual-entity-search', $search);

        $cached = $this->identityCache()->get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $entityNos = [];
        $page = 1;

        try {
            do {
                $payload = $this->get('individuals', [
                    'filter[search]' => $search,
                    'per_page' => self::BATCH_PAGE_SIZE,
                    'page' => $page,
                ]);

                foreach ($payload['data'] ?? [] as $individual) {
                    $entityNo = $this->entityNumberFromPayload($individual);

                    if ($entityNo !== '') {
                        $entityNos[$entityNo] = true;
                    }
                }

                $lastPage = max(1, (int) ($payload['meta']['last_page'] ?? 1));
                $page++;
            } while ($page <= $lastPage && $page <= self::SEARCH_MAX_PAGES);
        } catch (Throwable) {
            // Do not cache a failed search. The table can still match the
            // local entity number and contact number columns.
            return [];
        }

        $entityNos = array_keys($entityNos);

        $this->identityCache()->put(
            $cacheKey,
            $entityNos,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
        );

        return $entityNos;
    }

    /**
     * Distinct genders for the given entity numbers, resolved from SCIMS.
     *
     * @param  array<int, string|null>  $entityNos
     * @return array<string, string>
     */
    public function genderOptions(array $entityNos): array
    {
        $genders = [];

        foreach ($this->findIndividuals($entityNos) as $attributes) {
            $gender = trim((string) ($attributes['gender'] ?? ''));

            if ($gender !== '') {
                $genders[$gender] = $gender;
            }
        }

        ksort($genders, SORT_NATURAL | SORT_FLAG_CASE);

        return $genders;
    }

    /**
     * Entity numbers from the given list whose SCIMS gender matches.
     *
     * @param  array<int, string|null>  $entityNos
     * @return array<int, string>
     */
    public function entityNumbersForGender(array $entityNos, ?string $gender): array
    {
        $gender = trim((string) $gender);

        if ($gender === '') {
            return $this->normalizeEntityNumbers($entityNos);
        }

        $matching = [];

        foreach ($this->findIndividuals($entityNos) as $entityNo => $attributes) {
            $resolvedGender = trim((string) ($attributes['gender'] ?? ''));

            if ($resolvedGender !== '' && mb_strtolower($resolvedGender) === mb_strtolower($gender)) {
                $matching[] = $entityNo;
            }
        }

        return $matching;
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
     * Fetch one chunk of entity numbers, following SCIMS pagination because
     * some records repeat once per contact detail.
     *
     * @param  array<int, string>  $entityNos
     * @return array<string, array<string, mixed>>
     */
    protected function fetchIndividuals(array $entityNos): array
    {
        $resolved = [];
        $page = 1;

        do {
            $payload = $this->get('individuals', [
                'filter[entity_no]' => implode(',', $entityNos),
                'per_page' => self::BATCH_PAGE_SIZE,
                'page' => $page,
            ]);

            foreach ($payload['data'] ?? [] as $individual) {
                $entityNo = $this->entityNumberFromPayload($individual);

                if ($entityNo === '' || isset($resolved[$entityNo])) {
                    continue;
                }

                $resolved[$entityNo] = $individual['attributes'] ?? [];
            }

            $lastPage = max(1, (int) ($payload['meta']['last_page'] ?? 1));
            $page++;
        } while ($page <= $lastPage);

        return $resolved;
    }

    /**
     * @param  array<int, string|null>  $entityNos
     * @return array<int, string>
     */
    protected function normalizeEntityNumbers(array $entityNos): array
    {
        $normalized = [];

        foreach ($entityNos as $entityNo) {
            $entityNo = trim((string) $entityNo);

            if ($entityNo !== '') {
                $normalized[$entityNo] = true;
            }
        }

        return array_keys($normalized);
    }

    /**
     * @param  array<string, mixed>  $individual
     */
    protected function entityNumberFromPayload(array $individual): string
    {
        $attributes = $individual['attributes'] ?? [];

        return trim((string) ($attributes['entity_no'] ?? $individual['id'] ?? ''));
    }

    protected function individualCacheKey(string $entityNo): string
    {
        return 'scims-training:individual:'.$entityNo;
    }

    /**
     * Identity data is read from SCIMS on demand, so its short-lived cache
     * deliberately avoids the database store configured for the rest of the
     * application. That keeps names and birth dates out of SQL.
     */
    protected function identityCache(): CacheRepository
    {
        return Cache::store(config('services.scims.cache_store', 'file'));
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
