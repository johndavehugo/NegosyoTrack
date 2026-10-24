<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
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
                    'per_page' => 200,
                    'page' => 1,
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
        } catch (ConnectionException) {
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
     * Resolve an establishment's display name and location.
     *
     * SCIMS only returns address fields when include=address is requested.
     * Name-only callers share this cache through resolveEstablishmentName().
     *
     * @return array{name: string, address: ?string}|null
     */
    public function resolveEstablishment(?string $entityNo): ?array
    {
        if (blank($entityNo)) {
            return null;
        }

        $cacheKey = 'scims_est_details_v2_'.$entityNo;

        return Cache::remember($cacheKey, 900, function () use ($entityNo) {
            try {
                // Use the full URL directly — Http::baseUrl() drops the last path
                // segment when ->get() is called with a relative path, so we build
                // the complete URL ourselves to avoid that pitfall.
                $data = Http::timeout(5)
                    ->get("http://cictd.app/api/scims/juridical/{$entityNo}", [
                        'include' => 'address',
                        'expand' => 'details',
                    ])
                    ->json('data');

                if (! is_array($data) || blank($data['juri_name'] ?? null)) {
                    return null;
                }

                return [
                    'name' => $data['juri_name'],
                    'address' => $this->formatLocation($data),
                ];
            } catch (ConnectionException) {
                return null;
            }
        });
    }

    /**
     * Resolve a stored entity_no back to a display name.
     * Kept for Filament/admin callers that only need the name.
     */
    public function resolveEstablishmentName(?string $entityNo): ?string
    {
        return $this->resolveEstablishment($entityNo)['name'] ?? null;
    }

    /**
     * Build a short, consumer-friendly location line, e.g.
     * "Brgy. Rizal, City of San Carlos".
     */
    private function formatLocation(array $data): ?string
    {
        $barangay = trim((string) ($data['barangay'] ?? ''));

        if ($barangay !== '' && ! preg_match('/^(brgy\.?|barangay)\b/i', $barangay)) {
            $barangay = 'Brgy. '.$barangay;
        }

        $city = trim((string) ($data['city'] ?? ''))
            ?: trim((string) ($data['province'] ?? ''));

        $parts = array_filter([
            trim((string) ($data['upblb_num'] ?? '')),
            trim((string) ($data['subdivision'] ?? '')),
            trim((string) ($data['street'] ?? '')),
            $barangay,
            $city,
        ], fn (string $part) => $part !== '' && $part !== '.');

        return $parts === [] ? null : implode(', ', $parts);
    }
}
