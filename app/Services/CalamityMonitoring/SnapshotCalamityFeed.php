<?php

namespace App\Services\CalamityMonitoring;

use App\Services\EconomicMap\CalamityFeed;
use Illuminate\Support\Facades\DB;

/**
 * Calamity-module implementation of the Economic Map's calamity feed.
 *
 * Bound over the shared CalamityFeed in the service container, so incidents
 * are grouped by their SCIMS barangay snapshot without editing the shared
 * Economic Map class.
 */
class SnapshotCalamityFeed extends CalamityFeed
{
    /**
     * Damage + affected counts keyed by raw barangay name.
     *
     * Incidents carry the barangay snapshotted from SCIMS at creation time.
     * Incidents that predate the snapshot fall back to the supplied
     * entity_no => barangay map, then to "Unspecified".
     *
     * @param  array<string, string>  $entityBarangays  entity_no => barangay
     * @return array{by_barangay: array<string, array{damage: float, affected: int, incidents: int}>, total_damage: float, total_affected: int}
     */
    public function damageByBarangay(?int $calamityId = null, array $entityBarangays = []): array
    {
        $rows = DB::table('calamity_incidents')
            ->where('estimated_cost_of_damages', '>', 0)
            ->when($calamityId > 0, fn ($query) => $query->where('calamity_id', $calamityId))
            ->select('juridical_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(barangay), ''), '') as barangay")
            ->selectRaw('SUM(estimated_cost_of_damages) as damage')
            ->selectRaw('COUNT(*) as incidents')
            ->groupBy('juridical_id', 'barangay')
            ->get();

        $damage = [];
        $affected = [];

        foreach ($rows as $row) {
            $entityNo = trim((string) $row->juridical_id);

            $barangay = trim((string) $row->barangay)
                ?: (trim((string) ($entityBarangays[$entityNo] ?? '')) ?: 'Unspecified');

            $damage[$barangay] ??= ['damage' => 0.0, 'affected' => 0, 'incidents' => 0];
            $damage[$barangay]['damage'] += (float) $row->damage;
            $damage[$barangay]['incidents'] += (int) $row->incidents;

            $affected[$barangay][$entityNo] = true;
        }

        $totalDamage = 0.0;
        $totalAffected = 0;

        foreach ($damage as $barangay => $entry) {
            $damage[$barangay]['affected'] = count($affected[$barangay]);
            $totalDamage += $entry['damage'];
            $totalAffected += $damage[$barangay]['affected'];
        }

        return [
            'by_barangay' => $damage,
            'total_damage' => round($totalDamage, 2),
            'total_affected' => $totalAffected,
        ];
    }
}
