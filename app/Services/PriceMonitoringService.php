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
     * Resolve multiple entity_nos to names.
     * Fetches each one individually (cached per entity) so the lookup
     * works regardless of total record count in SCIMS.
     *
     * @param  string[]  $entityNos
     * @return array<string, string>
     */
    public function resolveEstablishmentNames(array $entityNos): array
    {
        if (empty($entityNos)) {
            return [];
        }

        $result = [];
        foreach ($entityNos as $entityNo) {
            $name = $this->resolveEstablishmentName($entityNo);
            if ($name !== null) {
                $result[$entityNo] = $name;
            }
        }

        return $result;
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
                // Use the full URL directly — Http::baseUrl() drops the last path
                // segment when ->get() is called with a relative path, so we build
                // the complete URL ourselves to avoid that pitfall.
                $data = Http::timeout(5)
                    ->get("http://cictd.app/api/scims/juridical/{$entityNo}", [
                        'include' => '',
                        'expand'  => 'details',
                    ])
                    ->json('data');

                return $data['juri_name'] ?? null;
            } catch (\Illuminate\Http\Client\ConnectionException) {
                return null;
            }
        });
    }
}
