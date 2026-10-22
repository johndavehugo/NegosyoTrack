<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Price Monitoring helpers — establishment lookups against SCIMS API.
 * Self-contained: does not depend on ScimsJuridicalApiService.
 */
class PriceMonitoringService
{
    /**
     * Live-search establishments from SCIMS.
     * Returns entity_no => juri_name map.
     */
    public function searchEstablishments(string $search): array
    {
        try {
            $response = Http::timeout(8)
                ->baseUrl('http://cictd.app/api/scims')
                ->get('juridical', [
                    'per_page'       => 200,
                    'page'           => 1,
                    'filter[search]' => $search,
                ]);

            if ($response->failed()) {
                return [];
            }

            return collect($response->json('data', []))
                ->mapWithKeys(fn (array $item): array => [
                    $item['entity_no'] => $item['juri_name'],
                ])
                ->all();
        } catch (\Illuminate\Http\Client\ConnectionException) {
            return [];
        }
    }

    /**
     * Resolve multiple entity_nos to names in a single API call.
     * Returns entity_no => juri_name map. Missing ones are excluded.
     *
     * @param  string[]  $entityNos
     * @return array<string, string>
     */
    public function resolveEstablishmentNames(array $entityNos): array
    {
        if (empty($entityNos)) {
            return [];
        }

        // The list endpoint supports a search param; pass a broad empty search
        // so it returns everything, then filter client-side by our entity_nos.
        // We cache by the sorted key set so repeated page loads don't re-hit the API.
        $cacheKey = 'scims_est_batch_' . md5(implode(',', $entityNos));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($entityNos) {
            $response = Http::timeout(8)
                ->baseUrl('http://cictd.app/api/scims')
                ->get('juridical', [
                    'per_page'       => 200,
                    'page'           => 1,
                    'filter[search]' => '',
                ]);

            if ($response->failed()) {
                return [];
            }

            $lookup = collect($response->json('data', []))
                ->mapWithKeys(fn (array $item): array => [
                    $item['entity_no'] => $item['juri_name'],
                ])
                ->all();

            // Return only the entity_nos we actually need
            return array_intersect_key($lookup, array_flip($entityNos));
        });
    }

    /**
     * Resolve a stored entity_no back to a display name.
     * Cached for 5 minutes to avoid repeated API calls.
     */
    public function resolveEstablishmentName(?string $entityNo): ?string
    {
        if (blank($entityNo)) {
            return null;
        }

        $cacheKey = 'scims_est_' . $entityNo;

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($entityNo) {
            try {
                $data = Http::timeout(5)
                    ->baseUrl('http://cictd.app/api/scims/juridical')
                    ->get("{$entityNo}?include=&expand=details")
                    ->json('data');

                return $data['juri_name'] ?? null;
            } catch (\Illuminate\Http\Client\ConnectionException) {
                return null;
            }
        });
    }
}
