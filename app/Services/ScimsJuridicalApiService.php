<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ScimsJuridicalApiService
{
    protected const ALL_BUSINESSES_CACHE_KEY = 'scims.juridical.businesses';

    protected const ALL_BUSINESSES_STALE_CACHE_KEY = 'scims.juridical.businesses.stale';

    protected const ALL_BUSINESSES_CACHE_TTL_HOURS = 25;

    protected const ALL_BUSINESSES_STALE_CACHE_TTL_DAYS = 7;

    protected ?Collection $allBusinesses = null;

    protected bool $usedStaleCache = false;

    public function getBusiness(?int $page, ?int $recordsPerPage, ?string $search, ?string $sortColumn, ?string $sortDirection)
    {
        $response = Http::baseUrl(config('scims.base_url'))
            ->get('juridical?include=employees.information,organization,address,owner.information,employer&expand=details', [
                'per_page' => $recordsPerPage,
                'page' => $page,
                'filter[search]' => $search,
                'sort_column' => $sortColumn,
                'sort_direction' => $sortDirection,
            ])
            ->collect();

        return new LengthAwarePaginator(
            items: collect($response['data'])->keyBy('entity_no'),
            total: $response['meta']['total'],
            perPage: $recordsPerPage,
            currentPage: $page
        );
    }

    public function getBusinessByID(string $entity_no)
    {
        $response = Http::baseUrl(config('scims.base_url'))
            ->get("juridical/$entity_no?include=employees.information,organization,address,owner.information,employer&expand=details")
            ->json('data');

        return $response;
    }

    /**
     * Fetch every juridical record from SCIMS, cached for local filtering.
     *
     * @return Collection<int, array{entity_no: string, juri_name: string, barangay: ?string, street: ?string, line_of_industry: ?string}>
     */
    public function getAllBusinesses(): Collection
    {
        if ($this->allBusinesses instanceof Collection) {
            return $this->allBusinesses;
        }

        $this->usedStaleCache = false;

        $cached = Cache::get(static::ALL_BUSINESSES_CACHE_KEY);

        if (is_array($cached) && $cached !== []) {
            return $this->allBusinesses = collect($cached);
        }

        $businesses = $this->fetchAllBusinesses();

        if ($businesses->isNotEmpty()) {
            Cache::put(
                static::ALL_BUSINESSES_CACHE_KEY,
                $businesses->all(),
                now()->addHours(static::ALL_BUSINESSES_CACHE_TTL_HOURS),
            );

            Cache::put(
                static::ALL_BUSINESSES_STALE_CACHE_KEY,
                $businesses->all(),
                now()->addDays(static::ALL_BUSINESSES_STALE_CACHE_TTL_DAYS),
            );

            return $this->allBusinesses = $businesses;
        }

        $stale = Cache::get(static::ALL_BUSINESSES_STALE_CACHE_KEY);

        if (is_array($stale) && $stale !== []) {
            $this->usedStaleCache = true;

            return $this->allBusinesses = collect($stale);
        }

        return $this->allBusinesses = $businesses;
    }

    /**
     * @return Collection<int, array{entity_no: string, juri_name: string, barangay: ?string, street: ?string, line_of_industry: ?string}>
     */
    public function refreshAllBusinesses(): Collection
    {
        Cache::forget(static::ALL_BUSINESSES_CACHE_KEY);

        $this->allBusinesses = null;

        return $this->getAllBusinesses();
    }

    public function usedStaleCache(): bool
    {
        return $this->usedStaleCache;
    }

    /**
     * @return Collection<int, array{entity_no: string, juri_name: string, barangay: ?string, street: ?string, line_of_industry: ?string}>
     */
    protected function fetchAllBusinesses(): Collection
    {
        $parameters = [
            'per_page' => 100,
            'include' => 'address',
            'expand' => 'details',
        ];

        try {
            $first = Http::baseUrl(config('scims.base_url'))
                ->timeout(30)
                ->get('juridical', ['page' => 1, ...$parameters]);

            if ($first->failed()) {
                return collect();
            }

            $lastPage = (int) $first->json('meta.last_page', 1);

            $responses = $lastPage > 1
                ? Http::pool(function (Pool $pool) use ($lastPage, $parameters): void {
                    foreach (range(2, $lastPage) as $page) {
                        $pool->as((string) $page)
                            ->baseUrl(config('scims.base_url'))
                            ->timeout(30)
                            ->get('juridical', ['page' => $page, ...$parameters]);
                    }
                })
                : [];
        } catch (\Throwable) {
            return collect();
        }

        return collect([$first, ...array_values($responses)])
            ->filter(fn (mixed $response): bool => $response instanceof Response && $response->successful())
            ->flatMap(fn (Response $response): array => $response->json('data', []))
            ->filter(fn (mixed $item): bool => is_array($item) && filled($item['entity_no'] ?? null))
            ->map(fn (array $item): array => [
                'entity_no' => $item['entity_no'],
                'juri_name' => $item['juri_name'] ?? $item['entity_no'],
                'barangay' => $item['barangay'] ?? null,
                'street' => $item['street'] ?? null,
                'line_of_industry' => $item['line_of_industry'] ?? null,
            ])
            ->values();
    }

    public function getEmployees(string $entity_no, int $page = 1, int $recordsPerPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $response = collect(Http::baseUrl(config('scims.base_url'))
            ->get("juridical/$entity_no?include=employees.information,organization,address,owner.information,employer&expand=details")
            ->json('data.employees', []));

        if (filled($search)) {
            $term = mb_strtolower(trim($search));

            $response = $response->filter(function (array $employee) use ($term) {
                foreach ([
                    'entity_no',
                    'employee_fullname',
                    'position_title',
                    'work_status',
                ] as $field) {
                    if (
                        str_contains(
                            mb_strtolower($employee[$field] ?? ''),
                            $term
                        )
                    ) {
                        return true;
                    }
                }

                return false;
            });
        }

        $total = $response->count();
        $items = $response
            ->forPage($page, $recordsPerPage)
            ->keyBy('entity_no');

        return new LengthAwarePaginator(
            items: $items,
            total: $total,
            perPage: $recordsPerPage,
            currentPage: $page,
            options: [
                'path' => request()->url(),
            ],
        );
    }

    public function formatAddress(
        ?string $upblb = null,
        ?string $subdivision = null,
        ?string $street = null,
        ?string $barangay = null,
        ?string $city = null,
        ?string $province = null,
        ?string $region = null
    ): string {
        // 1. Pack all inputs into a sequential array list
        $rawParts = [$upblb, $subdivision, $street, $barangay, $city, $province, $region];

        // 2. Clean the list: removes nulls, spaces, or empty strings ("")
        $cleanParts = array_filter($rawParts, function ($part) {
            return ! empty($part) && trim($part) !== '.' && trim($part) !== '';
        });

        // 3. Sew them back together cleanly with a comma and a space separator
        return implode(', ', $cleanParts);
    }
}
