<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Calamity Monitoring helpers — establishment lookups against SCIMS.
 *
 * Calamity incidents reference an MSME by its SCIMS entity number. Since
 * the local MSME tables were retired, names and barangays are read from
 * the SCIMS API and snapshotted onto each incident when it is saved.
 */
class CalamityMonitoringService
{
    protected const BASE_URL = 'http://cictd.app/api/scims';

    protected const CACHE_TTL_SECONDS = 21600;

    public function __construct(protected ScimsJuridicalApiService $scims) {}

    /**
     * Live-search establishments from SCIMS.
     *
     * @return array<string, string> entity_no => juri_name
     */
    public function searchEstablishments(?string $search): array
    {
        $parameters = [
            'per_page' => 200,
            'page' => 1,
        ];

        if (filled($search)) {
            $parameters['filter[search]'] = $search;
        }

        try {
            $response = Http::baseUrl(static::BASE_URL)
                ->timeout(15)
                ->get('juridical', $parameters);
        } catch (ConnectionException) {
            return [];
        }

        if ($response->failed()) {
            return [];
        }

        return collect($response->json('data', []))
            ->filter(fn (mixed $item): bool => is_array($item) && filled($item['entity_no'] ?? null))
            ->mapWithKeys(fn (array $item): array => [
                $item['entity_no'] => $item['juri_name'] ?? $item['entity_no'],
            ])
            ->all();
    }

    /**
     * Paginated establishments for the affected-MSME table.
     */
    public function paginateEstablishments(
        ?string $search,
        int $page,
        int $recordsPerPage,
        ?string $sortColumn = null,
        ?string $sortDirection = null,
        ?string $barangay = null,
    ): LengthAwarePaginator {
        if (filled($barangay)) {
            return $this->paginateEstablishmentsByBarangay(
                barangay: $barangay,
                search: $search,
                page: $page,
                recordsPerPage: $recordsPerPage,
                sortColumn: $sortColumn,
                sortDirection: $sortDirection,
            );
        }

        $parameters = [
            'per_page' => $recordsPerPage,
            'page' => $page,
            'include' => 'address',
            'expand' => 'details',
        ];

        if (filled($search)) {
            $parameters['filter[search]'] = $search;
        }

        if (filled($sortColumn)) {
            $parameters['sort_column'] = $sortColumn;
            $parameters['sort_direction'] = $sortDirection ?? 'asc';
        }

        try {
            $response = Http::baseUrl(static::BASE_URL)
                ->timeout(15)
                ->get('juridical', $parameters);
        } catch (ConnectionException) {
            return new LengthAwarePaginator(collect(), 0, $recordsPerPage, $page);
        }

        if ($response->failed()) {
            return new LengthAwarePaginator(collect(), 0, $recordsPerPage, $page);
        }

        return new LengthAwarePaginator(
            items: collect($response->json('data', []))->keyBy('entity_no'),
            total: (int) $response->json('meta.total', 0),
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }

    /**
     * Filter the cached SCIMS registry locally because the API has no
     * barangay filter.
     */
    protected function paginateEstablishmentsByBarangay(
        string $barangay,
        ?string $search,
        int $page,
        int $recordsPerPage,
        ?string $sortColumn,
        ?string $sortDirection,
    ): LengthAwarePaginator {
        $establishments = $this->scims->getAllBusinesses()
            ->filter(fn (array $item): bool => ($item['barangay'] ?? null) === $barangay);

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

        if (filled($sortColumn)) {
            $establishments = $establishments->sortBy(
                $sortColumn,
                SORT_NATURAL | SORT_FLAG_CASE,
                $sortDirection === 'desc',
            );
        }

        $establishments = $establishments->values();

        return new LengthAwarePaginator(
            items: $establishments->forPage($page, $recordsPerPage)->keyBy('entity_no'),
            total: $establishments->count(),
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }

    /**
     * Fetch a single establishment from SCIMS (cached).
     *
     * @return array<string, mixed>|null
     */
    public function findEstablishment(?string $entityNo): ?array
    {
        if (blank($entityNo)) {
            return null;
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
     * Snapshot name + barangay for many establishments. Uncached
     * establishments are fetched in parallel to keep selection fast.
     *
     * @param  array<int, string>  $entityNos
     * @return array<string, array{name: string, barangay: ?string}>
     */
    public function snapshotEstablishments(array $entityNos): array
    {
        $entityNos = array_values(array_unique(array_filter($entityNos)));
        $snapshots = [];
        $missing = [];

        foreach ($entityNos as $entityNo) {
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
                        ->baseUrl(static::BASE_URL)
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
            $response = Http::baseUrl(static::BASE_URL)
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
