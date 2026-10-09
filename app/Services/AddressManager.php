<?php

namespace App\Services;

use App\Filament\Resources\MsmeManagement\Juridicals\Pages\CreateJuridical;
use App\Filament\Resources\MsmeManagement\Juridicals\Pages\ViewBusiness;

class AddressManager
{
    public function __construct(
        protected AddressApiService $addressApiService,
    ) {}

    public function loadBusinessProvinces(
        ViewBusiness|CreateJuridical $page,
        string $regionPsgcId,
    ): void {        $page->businessProvinceOptions = collect(
            $this->addressApiService->getProvinces($regionPsgcId)
        )
            ->pluck('name', 'psgc_id')
            ->toArray();
    }

    public function loadBusinessCities(
        ViewBusiness|CreateJuridical $page,
        string $provincePsgcId,
    ): void {
        $page->businessCityOptions = collect(
            $this->addressApiService->getCitiesAndMunicipalities(
                $provincePsgcId
            )
        )
            ->pluck('name', 'psgc_id')
            ->toArray();
    }

    public function loadBusinessCitiesByRegion(
        ViewBusiness|CreateJuridical $page,
        string $regionPsgcId,
    ): void {
        $page->businessCityOptions = collect(
            $this->addressApiService->getCitiesAndMunicipalitiesByRegion(
                $regionPsgcId
            )
        )
            ->pluck('name', 'psgc_id')
            ->toArray();
    }

    public function loadBusinessBarangays(
        ViewBusiness|CreateJuridical $page,
        string $cityPsgcId,
    ): void {
        $page->businessBarangayOptions = collect(
            $this->addressApiService->getBarangays($cityPsgcId)
        )
            ->pluck('name', 'psgc_id')
            ->toArray();
    }

    public function loadEmployerProvinces(
        ViewBusiness|CreateJuridical $page,
        string $regionPsgcId,
    ): void {
        $page->employerProvinceOptions = collect(
            $this->addressApiService->getProvinces($regionPsgcId)
        )
            ->pluck('name', 'psgc_id')
            ->toArray();
    }

    public function loadEmployerCities(
        ViewBusiness|CreateJuridical $page,
        string $provincePsgcId,
    ): void {
        $page->employerCityOptions = collect(
            $this->addressApiService->getCitiesAndMunicipalities(
                $provincePsgcId
            )
        )
            ->pluck('name', 'psgc_id')
            ->toArray();
    }

    public function loadEmployerCitiesByRegion(
        ViewBusiness|CreateJuridical $page,
        string $regionPsgcId,
    ): void {
        $page->employerCityOptions = collect(
            $this->addressApiService->getCitiesAndMunicipalitiesByRegion(
                $regionPsgcId
            )
        )
            ->pluck('name', 'psgc_id')
            ->toArray();
    }

    public function loadEmployerBarangays(
        ViewBusiness|CreateJuridical $page,
        string $cityPsgcId,
    ): void {
        $page->employerBarangayOptions = collect(
            $this->addressApiService->getBarangays($cityPsgcId)
        )
            ->pluck('name', 'psgc_id')
            ->toArray();
    }

    public function loadBusinessAddressOptions(
        ViewBusiness $page,
    ): array {
        $address = $page->record->address;

        if (! $address) {
            return [
                'business_region' => null,
                'business_province' => null,
                'business_city' => null,
                'business_barangay' => null,
            ];
        }

        $resolved = $this->addressApiService->resolveAddress(
            $address->region,
            $address->province,
            $address->city,
            $address->barangay,
        );

        $region = $resolved['region'];
        $province = $resolved['province'];
        $city = $resolved['city'];
        $barangay = $resolved['barangay'];

        if (! $region) {
            return [
                'business_region' => null,
                'business_province' => null,
                'business_city' => null,
                'business_barangay' => null,
            ];
        }

        $page->businessProvinceOptions = collect(
            $this->addressApiService->getProvinces(
                $region['psgc_id']
            )
        )
            ->pluck('name', 'psgc_id')
            ->toArray();

        if ($province) {
            $page->businessCityOptions = collect(
                $this->addressApiService->getCitiesAndMunicipalities(
                    $province['psgc_id']
                )
            )
                ->pluck('name', 'psgc_id')
                ->toArray();
        } else {
            $page->businessCityOptions = collect(
                $this->addressApiService
                    ->getCitiesAndMunicipalitiesByRegion(
                        $region['psgc_id']
                    )
            )
                ->pluck('name', 'psgc_id')
                ->toArray();
        }

        if ($city) {
            $page->businessBarangayOptions = collect(
                $this->addressApiService->getBarangays(
                    $city['psgc_id']
                )
            )
                ->pluck('name', 'psgc_id')
                ->toArray();
        } else {
            $page->businessBarangayOptions = [];
        }

        return [
            'business_region' => $region['psgc_id'] ?? null,

            'business_province' => $province['psgc_id'] ?? null,

            'business_city' => $city['psgc_id'] ?? null,

            'business_barangay' => $barangay['psgc_id'] ?? null,
        ];
    }

    public function loadEmployerAddressOptions(
        ViewBusiness $page,
    ): array {
        $address = $page->record->employer->address;

        if (! $address) {
            return [
                'employer_region' => null,
                'employer_province' => null,
                'employer_city' => null,
                'employer_barangay' => null,
            ];
        }

        $resolved = $this->addressApiService->resolveAddress(
            $address->region,
            $address->province,
            $address->city,
            $address->barangay,
        );

        $region = $resolved['region'];
        $province = $resolved['province'];
        $city = $resolved['city'];
        $barangay = $resolved['barangay'];

        if (! $region) {
            return [
                'employer_region' => null,
                'employer_province' => null,
                'employer_city' => null,
                'employer_barangay' => null,
            ];
        }

        $page->employerProvinceOptions = collect(
            $this->addressApiService->getProvinces(
                $region['psgc_id']
            )
        )
            ->pluck('name', 'psgc_id')
            ->toArray();

        if ($province) {
            $page->employerCityOptions = collect(
                $this->addressApiService->getCitiesAndMunicipalities(
                    $province['psgc_id']
                )
            )
                ->pluck('name', 'psgc_id')
                ->toArray();
        } else {
            $page->employerCityOptions = collect(
                $this->addressApiService
                    ->getCitiesAndMunicipalitiesByRegion(
                        $region['psgc_id']
                    )
            )
                ->pluck('name', 'psgc_id')
                ->toArray();
        }

        if ($city) {
            $page->employerBarangayOptions = collect(
                $this->addressApiService->getBarangays(
                    $city['psgc_id']
                )
            )
                ->pluck('name', 'psgc_id')
                ->toArray();
        } else {
            $page->employerBarangayOptions = [];
        }

        return [
            'employer_region' => $region['psgc_id'] ?? null,

            'employer_province' => $province['psgc_id'] ?? null,

            'employer_city' => $city['psgc_id'] ?? null,

            'employer_barangay' => $barangay['psgc_id'] ?? null,
        ];
    }

    public function resolveSelectedAddress(
        ?string $regionPsgcId,
        ?string $provincePsgcId,
        ?string $cityPsgcId,
        ?string $barangayPsgcId,
    ): array {
        return $this->addressApiService->resolveAddressByPsgcIds(
            $regionPsgcId,
            $provincePsgcId,
            $cityPsgcId,
            $barangayPsgcId,
        );
    }

    /**
     * One-shot SCIMS fill for the business address: resolve the four names
     * to dropdown (psgc) ids and preload every child dropdown through the
     * same loaders the cascading selects use. Returns form-state keys.
     */
    public function preloadBusinessScimsAddress(
        ViewBusiness|CreateJuridical $page,
        mixed $region,
        mixed $province,
        mixed $city,
        mixed $barangay,
    ): array {
        return $this->preloadScimsAddress($page, $region, $province, $city, $barangay, 'business');
    }

    /**
     * One-shot SCIMS fill for the employer address. See preloadBusinessScimsAddress().
     */
    public function preloadEmployerScimsAddress(
        ViewBusiness|CreateJuridical $page,
        mixed $region,
        mixed $province,
        mixed $city,
        mixed $barangay,
    ): array {
        return $this->preloadScimsAddress($page, $region, $province, $city, $barangay, 'employer');
    }

    protected function preloadScimsAddress(
        ViewBusiness|CreateJuridical $page,
        mixed $region,
        mixed $province,
        mixed $city,
        mixed $barangay,
        string $prefix,
    ): array {
        $empty = [
            "{$prefix}_region" => null,
            "{$prefix}_province" => null,
            "{$prefix}_city" => null,
            "{$prefix}_barangay" => null,
        ];

        $side = ucfirst($prefix);
        $provinceProp = "{$prefix}ProvinceOptions";
        $cityProp = "{$prefix}CityOptions";
        $barangayProp = "{$prefix}BarangayOptions";

        try {
            $resolved = $this->addressApiService->resolveAddress(
                is_string($region) ? $region : null,
                is_string($province) ? $province : null,
                is_string($city) ? $city : null,
                is_string($barangay) ? $barangay : null,
            );

            $id = fn (mixed $part): ?string => is_array($part)
                ? (string) ($part['psgc_id'] ?? null) ?: null
                : null;

            $regionId = $id($resolved['region'] ?? null);
            $provinceId = $id($resolved['province'] ?? null);
            $cityId = $id($resolved['city'] ?? null);
            $barangayId = $id($resolved['barangay'] ?? null);

            if (! $regionId) {
                return $empty;
            }

            $this->{"load{$side}Provinces"}($page, $regionId);

            if (blank($page->{$provinceProp}) || blank($provinceId)) {
                $this->{"load{$side}CitiesByRegion"}($page, $regionId);
            } else {
                $this->{"load{$side}Cities"}($page, $provinceId);
            }

            if (filled($cityId)) {
                $this->{"load{$side}Barangays"}($page, $cityId);
            }

            return [
                "{$prefix}_region" => $regionId,
                "{$prefix}_province" => $provinceId,
                "{$prefix}_city" => $cityId,
                "{$prefix}_barangay" => $barangayId,
            ];
        } catch (\Throwable) {
            return $empty;
        }
    }
}
