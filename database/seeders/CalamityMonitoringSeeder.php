<?php

namespace Database\Seeders;

use App\Models\CalamityMonitoring\Calamity;
use App\Models\CalamityMonitoring\Incident;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CalamityMonitoringSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['Bagyong Amihan', 'TYPHOON', '2026-01-12'],
            ['Bagyong Betty', 'TYPHOON', '2026-02-03'],
            ['Bagyong Chedeng', 'TYPHOON', '2026-03-18'],
            ['Bagyong Dodong', 'TYPHOON', '2026-04-22'],
            ['Bagyong Egay', 'TYPHOON', '2026-05-09'],
            ['Magnitude 5.6 Earthquake', 'EARTHQUAKE', '2026-06-14'],
            ['Magnitude 6.1 Earthquake', 'EARTHQUAKE', '2026-07-02'],
            ['Barangay I Flash Flood', 'FLOOD', '2026-07-19'],
            ['Barangay II River Flood', 'FLOOD', '2026-08-05'],
            ['Barangay III Coastal Flood', 'FLOOD', '2026-08-21'],
            ['Barangay IV Hillside Landslide', 'LANDSLIDE', '2026-09-03'],
            ['Barangay V Landslide', 'LANDSLIDE', '2026-09-17'],
            ['Bagyong Falcon', 'TYPHOON', '2026-10-01'],
            ['Bagyong Goring', 'TYPHOON', '2026-10-12'],
            ['Barangay VI Flooding', 'FLOOD', '2026-11-06'],
            ['Magnitude 5.2 Earthquake', 'EARTHQUAKE', '2026-11-20'],
            ['Barangay II Slope Collapse', 'LANDSLIDE', '2026-12-02'],
            ['Bagyong Hanna', 'TYPHOON', '2026-12-15'],
            ['Citywide Flooding', 'FLOOD', '2026-12-24'],
            ['Barangay III Landslide', 'LANDSLIDE', '2026-12-28'],
        ];

        $businesses = $this->businesses();

        if ($businesses->count() < 5) {
            throw new RuntimeException('Unable to load enough businesses from the SCIMS businesses module.');
        }

        DB::transaction(function () use ($records, $businesses): void {
            Incident::query()
                ->where('juridical_id', 'like', 'DEMO%')
                ->orWhere('juridical_id', 'like', 'SYN%')
                ->delete();

            foreach ($records as [$name, $type, $date]) {
                Calamity::firstOrCreate(
                    ['name' => $name],
                    [
                        'calamity_type' => $type,
                        'declaration_date' => $date,
                        'description' => 'Sample calamity record for reporting and chart testing.',
                    ],
                );
            }

            $cursor = 0;

            foreach (Calamity::query()->orderBy('id')->get() as $calamity) {
                $used = $calamity->incident()->pluck('juridical_id')->all();
                $count = count($used);

                while ($count < 5) {
                    $business = $businesses[$cursor % $businesses->count()];
                    $cursor++;

                    if (in_array($business['entity_no'], $used, true)) {
                        continue;
                    }

                    Incident::create([
                        'calamity_id' => $calamity->getKey(),
                        'juridical_id' => $business['entity_no'],
                        'juridical_name' => $business['juri_name'],
                        'barangay' => $business['barangay'] ?? null,
                        'date_occurred' => $calamity->declaration_date,
                        'nature_of_damage' => ($calamity->getKey() + $count) % 3 === 0 ? 'TOTAL' : 'PARTIAL',
                        'estimated_cost_of_damages' => 50000 + ((($calamity->getKey() * 37) + ($count * 83)) % 40) * 25000,
                        'remarks' => 'Sample incident sourced from the SCIMS businesses module.',
                        'status' => ['PENDING_VERIFICATION', 'VERIFIED', 'AID_RELEASED'][($calamity->getKey() + $count) % 3],
                    ]);

                    $used[] = $business['entity_no'];
                    $count++;
                }
            }
        });
    }

    protected function businesses(): Collection
    {
        $businesses = collect();

        for ($page = 1; $page <= 2; $page++) {
            $response = Http::baseUrl('https://cictd.app/api/scims')
                ->timeout(30)
                ->get('juridical', [
                    'per_page' => 100,
                    'page' => $page,
                    'include' => 'address',
                    'expand' => 'details',
                    'sort_column' => 'juri_name',
                    'sort_direction' => 'asc',
                ]);

            if ($response->failed()) {
                throw new RuntimeException('Unable to load businesses from the SCIMS businesses module.');
            }

            $businesses = $businesses->merge($response->json('data', []));
        }

        return $businesses
            ->filter(fn (mixed $business): bool => is_array($business)
                && filled($business['entity_no'] ?? null)
                && filled($business['juri_name'] ?? null))
            ->unique('entity_no')
            ->values();
    }
}
