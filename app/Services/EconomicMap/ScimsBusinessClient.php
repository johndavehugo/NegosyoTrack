<?php

namespace App\Services\EconomicMap;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

/**
 * Map-owned SCIMS juridical client.
 *
 * The shared ScimsJuridicalApiService belongs to the MSME module, so the
 * Economic Map keeps its own client. This requests the address expansion the
 * map needs and paginates the same endpoint for the business directory.
 */
class ScimsBusinessClient
{
    protected const BASE_URL = 'https://cictd.app/api/scims';

    protected const INCLUDES = 'address';

    public function page(
        int $page,
        int $perPage,
        ?string $search = null,
        ?string $sortColumn = null,
        ?string $sortDirection = null,
    ): LengthAwarePaginator {
        $response = Http::baseUrl(static::BASE_URL)
            ->acceptJson()
            ->timeout(60)
            ->get('juridical', [
                'include' => static::INCLUDES,
                'expand' => 'details',
                'per_page' => $perPage,
                'page' => $page,
                'filter[search]' => $search,
                'sort_column' => $sortColumn,
                'sort_direction' => $sortDirection,
            ])
            ->throw()
            ->collect();

        return new LengthAwarePaginator(
            items: collect($response['data'])->keyBy('entity_no'),
            total: (int) ($response['meta']['total'] ?? 0),
            perPage: $perPage,
            currentPage: $page,
        );
    }
}
