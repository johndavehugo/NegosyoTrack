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
        $response = Http::baseUrl('http://cictd.app/api/scims')
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
    }

    /**
     * Resolve a stored entity_no back to a display name.
     */
    public function resolveEstablishmentName(?string $entityNo): ?string
    {
        if (blank($entityNo)) {
            return null;
        }

        $data = Http::baseUrl('http://cictd.app/api/scims/juridical')
            ->get("{$entityNo}?include=&expand=details")
            ->json('data');

        return $data['juri_name'] ?? null;
    }
}
