<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ScimsApiService
{
    protected string $baseUrl = 'https://vamosmobile.app/api/juridical/business';

    /**
     * Search businesses by name.
     *
     * Returns mapped `business` / `employer` entries with clean names —
     * only the fields we care about. Empty array when the query
     * is blank or the API fails.
     */
    public function searchBusinessByName(string $name): array
    {
        $name = trim($name);

        if ($name === '') {
            return [];
        }

        try {
            $response = Http::acceptJson()->get(
                "{$this->baseUrl}/businessname/".urlencode($name)
            );
        } catch (ConnectionException) {
            return [];
        }

        if ($response->failed()) {
            return [];
        }

        $data = $response->json('data', []);

        if (! is_array($data)) {
            return [];
        }

        if (! array_is_list($data)) {
            $data = [$data];
        }

        return array_values(array_map(
            fn (array $row): array => $this->mapBusinessRow($row),
            array_filter($data, fn (mixed $row): bool => is_array($row)),
        ));
    }

    /**
     * Map one raw registry row to clean `business` / `employer` names.
     */
    public function mapBusinessRow(array $row): array
    {
        return [
            'business' => [
                'name' => $this->clean($row['juri_name'] ?? $row['trade_name'] ?? null),
                'entity_no' => $this->clean($row['entity_no'] ?? null),
                'date_registered' => $this->date($row['juri_date_reg'] ?? null),
                'contact_no' => $this->clean($row['contact_no'] ?? null),
                'contact_email' => $this->clean($row['contact_email'] ?? null),
                'line_of_industry' => $this->clean($row['line_of_industry'] ?? null),
                'address' => [
                    'region' => $this->clean($row['juri_region'] ?? null),
                    'province' => $this->clean($row['juri_province'] ?? null),
                    'city' => $this->clean($row['juri_city'] ?? null),
                    'barangay' => $this->clean($row['juri_barangay'] ?? null),
                    'unit_bldg_no' => $this->clean($row['juri_upblb_num'] ?? null),
                    'street' => $this->clean($row['juri_street'] ?? null),
                    'subdivision' => $this->clean($row['juri_subdivision'] ?? null),
                    'zip' => $this->clean($row['juri_zip'] ?? null),
                ],
            ],
            'employer' => [
                'full_name' => $this->clean($row['juri_employer'] ?? null),
                'entity_no' => $this->clean($row['employer_entity_no'] ?? null),
                'gender' => $this->clean($row['employer_gender'] ?? null),
                'birth_date' => $this->date($row['employer_birth_date'] ?? null),
                'contact_no' => $this->clean($row['employer_contact'] ?? null),
                'email' => $this->clean($row['employer_email'] ?? null),
                'address' => [
                    'region' => $this->clean($row['emp_region'] ?? null),
                    'province' => $this->clean($row['emp_province'] ?? null),
                    'city' => $this->clean($row['emp_city'] ?? null),
                    'barangay' => $this->clean($row['emp_barangay'] ?? null),
                    'unit_bldg_no' => $this->clean($row['emp_upblb_num'] ?? null),
                    'street' => $this->clean($row['emp_street'] ?? null),
                    'subdivision' => $this->clean($row['emp_subdivision'] ?? null),
                    'zip' => $this->clean($row['emp_zip'] ?? null),
                ],
            ],
        ];
    }

    protected function clean(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return filled($value) && $value !== '.' ? (string) $value : null;
    }

    protected function date(mixed $value): ?string
    {
        $date = substr((string) $value, 0, 10);

        return $date !== '' ? $date : null;
    }
}
