<?php

namespace App\Services;

use App\Support\Barangays;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Calamity Monitoring helpers - establishment lookups against SCIMS.
 *
 * Calamity incidents reference an MSME by its SCIMS entity number. Since
 * the local MSME tables were retired, names and barangays are read from
 * the SCIMS API and snapshotted onto each incident when it is saved.
 */
class CalamityMonitoringService
{
    protected const CACHE_TTL_SECONDS = 21600;

    public function __construct(protected ScimsJuridicalApiService $scims) {}

    /**
     * Live-search establishments from the cached SCIMS registry.
     *
     * @return array<string, string> entity_no => juri_name
     */
    public function searchEstablishments(?string $search): array
    {
        return $this->filterEstablishments(search: $search)
            ->take(50)
            ->mapWithKeys(fn (array $item): array => [
                $item['entity_no'] => $item['juri_name'] ?? $item['entity_no'],
            ])
            ->all();
    }

    /**
     * Paginated establishments for the affected-MSME table.
     *
     * @param  array<int, string>  $excluded
     */
    public function paginateEstablishments(
        ?string $search,
        int $page,
        int $recordsPerPage,
        ?string $sortColumn = null,
        ?string $sortDirection = null,
        ?string $barangay = null,
        ?string $industry = null,
        array $excluded = [],
    ): LengthAwarePaginator {
        $establishments = $this->filterEstablishments(
            search: $search,
            barangay: $barangay,
            industry: $industry,
            excluded: $excluded,
        );

        if (filled($sortColumn)) {
            $establishments = $establishments->sortBy(
                $sortColumn,
                SORT_NATURAL | SORT_FLAG_CASE,
                $sortDirection === 'desc',
            );
        }

        $total = $establishments->count();
        $lastPage = max(1, (int) ceil($total / max(1, $recordsPerPage)));
        $page = min(max(1, $page), $lastPage);

        return new LengthAwarePaginator(
            items: $establishments->forPage($page, $recordsPerPage)->keyBy('entity_no'),
            total: $total,
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }

    /**
     * Entity numbers for every establishment matching the current filters.
     *
     * @param  array<int, string>  $excluded
     * @return array<int, string>
     */
    public function matchingEstablishmentIds(
        ?string $barangay = null,
        ?string $search = null,
        ?string $industry = null,
        array $excluded = [],
    ): array {
        return $this->filterEstablishments(
            search: $search,
            barangay: $barangay,
            industry: $industry,
            excluded: $excluded,
        )
            ->pluck('entity_no')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $excluded
     */
    public function countEstablishments(
        ?string $barangay = null,
        ?string $search = null,
        ?string $industry = null,
        array $excluded = [],
    ): int {
        return $this->filterEstablishments(
            search: $search,
            barangay: $barangay,
            industry: $industry,
            excluded: $excluded,
        )->count();
    }

    /**
     * @return array<string, string>
     */
    public function industryOptions(): array
    {
        return $this->scims->getAllBusinesses()
            ->pluck('line_of_industry')
            ->filter(fn (mixed $value): bool => filled($value))
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->mapWithKeys(fn (string $value): array => [$value => $value])
            ->all();
    }

    /**
     * The official city barangays plus any additional barangays present in
     * the SCIMS registry (for example, newer or renamed barangays).
     *
     * @return array<string, string>
     */
    public function barangayOptions(): array
    {
        $options = Barangays::options();

        $this->scims->getAllBusinesses()
            ->pluck('barangay')
            ->filter(fn (mixed $value): bool => filled($value))
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->each(function (string $barangay) use (&$options): void {
                $options[$barangay] ??= $barangay;
            });

        return $options;
    }

    /**
     * @param  array<int, string>  $excluded
     * @return Collection<int, array<string, mixed>>
     */
    protected function filterEstablishments(
        ?string $search = null,
        ?string $barangay = null,
        ?string $industry = null,
        array $excluded = [],
    ): Collection {
        $establishments = $this->scims->getAllBusinesses();

        if (filled($barangay)) {
            $establishments = $establishments->where('barangay', $barangay);
        }

        if (filled($industry)) {
            $establishments = $establishments->where('line_of_industry', $industry);
        }

        if ($excluded !== []) {
            $establishments = $establishments->whereNotIn('entity_no', $excluded);
        }

        if (filled($search)) {
            $term = mb_strtolower(trim($search));

            $establishments = $establishments->filter(function (array $item) use ($term): bool {
                foreach (['juri_name', 'barangay', 'street', 'line_of_industry'] as $field) {
                    if (str_contains(mb_strtolower((string) ($item[$field] ?? '')), $term)) {
                        return true;
                    }
                }

                return false;
            });
        }

        return $establishments->values();
    }

    /**
     * Fetch a single establishment from the registry or SCIMS (cached).
     *
     * @return array<string, mixed>|null
     */
    public function findEstablishment(?string $entityNo): ?array
    {
        if (blank($entityNo)) {
            return null;
        }

        $registry = $this->scims->getAllBusinesses()->firstWhere('entity_no', $entityNo);

        if (is_array($registry)) {
            return $registry;
        }

        $cached = Cache::remember(
            $this->cacheKey($entityNo),
            static::CACHE_TTL_SECONDS,
            fn (): array => ['data' => $this->fetchEstablishment($entityNo)],
        );

        return $cached['data'] ?? null;
    }

    public function resolveEstablishmentName(?string $entityNo): ?string
    {
        return $this->findEstablishment($entityNo)['juri_name'] ?? null;
    }

    public function resolveBarangay(?string $entityNo): ?string
    {
        return $this->findEstablishment($entityNo)['barangay'] ?? null;
    }

    /**
     * Snapshot name + barangay for many establishments. The cached registry
     * answers the common case; only unknown entity numbers hit the API.
     *
     * @param  array<int, string>  $entityNos
     * @return array<string, array{name: string, barangay: ?string}>
     */
    public function snapshotEstablishments(array $entityNos): array
    {
        $entityNos = array_values(array_unique(array_filter($entityNos)));
        $snapshots = [];
        $missing = [];
        $registry = $this->scims->getAllBusinesses()->keyBy('entity_no');

        foreach ($entityNos as $entityNo) {
            $registryItem = $registry->get($entityNo);

            if (is_array($registryItem)) {
                $snapshots[$entityNo] = $this->snapshot($entityNo, $registryItem);

                continue;
            }

            $cached = Cache::get($this->cacheKey($entityNo));

            if (is_array($cached) && array_key_exists('data', $cached)) {
                $snapshots[$entityNo] = $this->snapshot($entityNo, $cached['data']);

                continue;
            }

            $missing[] = $entityNo;
        }

        if ($missing !== []) {
            $responses = Http::pool(function (Pool $pool) use ($missing): void {
                foreach ($missing as $entityNo) {
                    $pool->as($entityNo)
                        ->baseUrl(config('scims.base_url'))
                        ->timeout(15)
                        ->get('juridical/'.$entityNo, $this->establishmentIncludes());
                }
            });

            foreach ($missing as $entityNo) {
                $response = $responses[$entityNo] ?? null;
                $data = null;

                if ($response instanceof Response && $response->successful()) {
                    $data = $response->json('data');
                    $data = is_array($data) ? $data : null;
                }

                Cache::put($this->cacheKey($entityNo), ['data' => $data], static::CACHE_TTL_SECONDS);

                $snapshots[$entityNo] = $this->snapshot($entityNo, $data);
            }
        }

        return $snapshots;
    }

    /**
     * @return array{name: string, barangay: ?string}
     */
    protected function snapshot(string $entityNo, mixed $establishment): array
    {
        return [
            'name' => is_array($establishment) ? ($establishment['juri_name'] ?? $entityNo) : $entityNo,
            'barangay' => is_array($establishment) ? ($establishment['barangay'] ?? null) : null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function fetchEstablishment(string $entityNo): ?array
    {
        try {
            $response = Http::baseUrl(config('scims.base_url'))
                ->timeout(15)
                ->get('juridical/'.$entityNo, $this->establishmentIncludes());
        } catch (ConnectionException) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $data = $response->json('data');

        return is_array($data) ? $data : null;
    }

    /**
     * @return array<string, string>
     */
    protected function establishmentIncludes(): array
    {
        return [
            'include' => 'employees.information,organization,address,owner.information,employer',
            'expand' => 'details',
        ];
    }

    protected function cacheKey(string $entityNo): string
    {
        return 'calamity.scims.establishment.'.md5($entityNo);
    }
}
