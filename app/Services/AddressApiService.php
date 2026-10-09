<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AddressApiService
{
    protected string $baseUrl = 'https://vamosmobile.app/api/addresses';

    public function getRegions(): array
    {
        return $this->getAllRegions();
    }

    public function getProvinces(string $regionPsgcId): array
    {
        $prefix = substr($regionPsgcId, 0, 2);

        return array_values(array_filter(
            $this->getAllProvinces(),
            fn (array $province): bool => substr($province['psgc_id'] ?? '', 0, 2) === $prefix
        ));
    }

    public function getCitiesAndMunicipalities(
        string $provincePsgcId,
    ): array {
        $provinces = $this->getAllProvinces();
        $places = $this->getAllCitiesAndMunicipalities();

        $province = collect($provinces)->first(
            fn (array $item): bool => ($item['psgc_id'] ?? '') === $provincePsgcId
        );

        if (! $province) {
            return [];
        }

        $provCorr = substr(
            $province['correspondence_code'] ?? '',
            0,
            4
        );

        $psgc5 = substr(
            $province['psgc_id'] ?? '',
            0,
            5
        );

        return array_values(array_filter(
            $places,
            function (array $item) use ($provCorr, $psgc5): bool {
                $corr4 = substr(
                    $item['correspondence_code'] ?? '',
                    0,
                    4
                );

                return $corr4 === $provCorr
                    || (
                        $provCorr === ''
                        && substr(
                            $item['psgc_id'] ?? '',
                            0,
                            5
                        ) === $psgc5
                    );
            }
        ));
    }

    public function getBarangays(string $cityPsgcId): array
    {
        return Cache::remember(
            "address_api.barangays.{$cityPsgcId}",
            now()->addHours(24),
            function () use ($cityPsgcId): array {
                $response = Http::acceptJson()
                    ->get("{$this->baseUrl}/barangay/{$cityPsgcId}");

                if ($response->failed()) {
                    return [];
                }

                return $response->json('data') ?? [];
            }
        );
    }

    public function resolveAddress(
        ?string $regionName,
        ?string $provinceName,
        ?string $cityName,
        ?string $barangayName,
    ): array {
        $regions = $this->getAllRegions();
        $provinces = $this->getAllProvinces();
        $places = $this->getAllCitiesAndMunicipalities();
        $region = null;

        if (filled($regionName)) {
            $region = collect($regions)->first(
                fn (array $item): bool => strcasecmp(
                    trim($item['name'] ?? ''),
                    trim($regionName)
                ) === 0
            );
        }

        if (! $region) {
            return [
                'region' => null,
                'province' => null,
                'city' => null,
                'barangay' => null,
            ];
        }

        $regionPsgcId = $region['psgc_id'] ?? '';
        $province = null;

        if (filled($provinceName)) {
            $province = collect($provinces)->first(
                fn (array $item): bool => substr(
                    $item['psgc_id'] ?? '',
                    0,
                    2
                ) === substr($regionPsgcId, 0, 2)
                    && strcasecmp(
                        trim($item['name'] ?? ''),
                        trim($provinceName)
                    ) === 0
            );
        }

        $city = null;

        if (filled($cityName)) {
            if ($province) {
                $provCorr = substr(
                    $province['correspondence_code'] ?? '',
                    0,
                    4
                );

                $psgc5 = substr(
                    $province['psgc_id'] ?? '',
                    0,
                    5
                );

                $city = collect($places)->first(
                    function (array $item) use (
                        $provCorr,
                        $psgc5,
                        $cityName
                    ): bool {
                        $corr4 = substr(
                            $item['correspondence_code'] ?? '',
                            0,
                            4
                        );

                        $belongsToProvince =
                            $corr4 === $provCorr
                            || (
                                $provCorr === ''
                                && substr(
                                    $item['psgc_id'] ?? '',
                                    0,
                                    5
                                ) === $psgc5
                            );

                        return $belongsToProvince
                            && strcasecmp(
                                trim($item['name'] ?? ''),
                                trim($cityName)
                            ) === 0;
                    }
                );
            } else {
                $regionPrefix = substr(
                    $regionPsgcId,
                    0,
                    2
                );

                $city = collect($places)->first(
                    fn (array $item): bool => substr(
                        $item['psgc_id'] ?? '',
                        0,
                        2
                    ) === $regionPrefix
                        && strcasecmp(
                            trim($item['name'] ?? ''),
                            trim($cityName)
                        ) === 0
                );
            }
        }

        $barangay = null;

        if ($city && filled($barangayName)) {
            $cityPsgcId = $city['psgc_id'] ?? '';

            if (filled($cityPsgcId)) {
                $barangays = $this->getBarangays($cityPsgcId);

                $barangay = collect($barangays)->first(
                    fn (array $item): bool => strcasecmp(
                        trim($item['name'] ?? ''),
                        trim($barangayName)
                    ) === 0
                );
            }
        }

        return [
            'region' => $region,
            'province' => $province,
            'city' => $city,
            'barangay' => $barangay,
        ];
    }

    public function resolveAddressByPsgcIds(
        ?string $regionPsgcId,
        ?string $provincePsgcId,
        ?string $cityPsgcId,
        ?string $barangayPsgcId,
    ): array {
        $region = null;

        if (filled($regionPsgcId)) {
            $region = collect($this->getAllRegions())
                ->first(
                    fn (array $item): bool => ($item['psgc_id'] ?? '') === $regionPsgcId
                );
        }

        $province = null;

        if (filled($provincePsgcId)) {
            $province = collect($this->getAllProvinces())
                ->first(
                    fn (array $item): bool => ($item['psgc_id'] ?? '') === $provincePsgcId
                );
        }

        $city = null;

        if (filled($cityPsgcId)) {
            $city = collect($this->getAllCitiesAndMunicipalities())
                ->first(
                    fn (array $item): bool => ($item['psgc_id'] ?? '') === $cityPsgcId
                );
        }

        $barangay = null;

        if (
            filled($cityPsgcId)
            && filled($barangayPsgcId)
        ) {
            $barangay = collect(
                $this->getBarangays($cityPsgcId)
            )->first(
                fn (array $item): bool => ($item['psgc_id'] ?? '') === $barangayPsgcId
            );
        }

        return [
            'region' => $region['name'] ?? null,
            'province' => $province['name'] ?? null,
            'city' => $city['name'] ?? null,
            'barangay' => $barangay['name'] ?? null,
        ];
    }

    private function getAllRegions(): array
    {
        return Cache::remember(
            'address_api.regions',
            now()->addHours(24),
            function (): array {
                $response = Http::acceptJson()
                    ->get("{$this->baseUrl}/region/");

                if ($response->failed()) {
                    return [];
                }

                return $response->json('data') ?? [];
            }
        );
    }

    private function getAllProvinces(): array
    {
        return Cache::remember(
            'address_api.provinces',
            now()->addHours(24),
            function (): array {
                $response = Http::acceptJson()
                    ->get("{$this->baseUrl}/province/");

                if ($response->failed()) {
                    return [];
                }

                return $response->json('data') ?? [];
            }
        );
    }

    private function getAllCitiesAndMunicipalities(): array
    {
        return Cache::remember(
            'address_api.cities_municipalities',
            now()->addHours(24),
            function (): array {
                $response = Http::acceptJson()
                    ->get("{$this->baseUrl}/municipality-city/");

                if ($response->failed()) {
                    return [];
                }

                return $response->json('data') ?? [];
            }
        );
    }

    public function getCitiesAndMunicipalitiesByRegion(
        string $regionPsgcId,
    ): array {
        $places = $this->getAllCitiesAndMunicipalities();

        $regionPrefix = substr($regionPsgcId, 0, 2);

        return array_values(array_filter(
            $places,
            fn (array $item): bool => substr(
                $item['psgc_id'] ?? '',
                0,
                2
            ) === $regionPrefix
        ));
    }
}
