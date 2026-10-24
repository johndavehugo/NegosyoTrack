<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
class ScimsJuridicalApiService
{

    public function getBusiness(?int $page, ?int $recordsPerPage, ?string $search, ?string $sortColumn, ?string $sortDirection)
    {
        $response = Http::baseUrl('http://cictd.app/api/scims')
            ->get('juridical?include=employees.information,organization,address,owner.information,employer&expand=details', [
                'per_page' => $recordsPerPage,
                'page' => $page,
                'filter[search]' => $search,
                'sort_column' => $sortColumn,
                'sort_direction' => $sortDirection
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
        $response = Http::baseUrl('http://cictd.app/api/scims/juridical')
            ->get("$entity_no?include=employees.information,organization,address,owner.information,employer&expand=details")
            ->json('data');
        return $response;
    }

    public function getEmployees(string $entity_no, int $page = 1, int $recordsPerPage = 10, ?string $search = null): LengthAwarePaginator {
        $response = collect(Http::baseUrl('http://cictd.app/api/scims/juridical')
                ->get("$entity_no?include=employees.information,organization,address,owner.information,employer&expand=details")
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
            return !empty($part) && trim($part) !== '.' && trim($part) !== '';
        });

        // 3. Sew them back together cleanly with a comma and a space separator
        return implode(', ', $cleanParts);
    }

}