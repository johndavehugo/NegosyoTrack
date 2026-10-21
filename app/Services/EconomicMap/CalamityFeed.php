<?php

namespace App\Services\EconomicMap;

use Illuminate\Support\Facades\DB;

/**
 * Calamity data for the Economic Map.
 *
 * Table-backed today; when the calamity monitoring module ships its
 * own get-APIs, only these two methods change (same names, same shapes).
 */
class CalamityFeed
{
    /**
     * All calamity events, most recent first.
     *
     * @return list<array{id: int, name: string, calamity_type: string, declaration_date: string}>
     */
    public function calamities(): array
    {
        return DB::table('calamities')
            ->select(['id', 'name', 'calamity_type', 'declaration_date'])
            ->orderByDesc('declaration_date')
            ->orderBy('name')
            ->get()
            ->map(fn ($row): array => (array) $row)
            ->all();
    }

    /**
     * Damage + affected counts keyed by raw barangay name.
     *
     * Calamity data stays entirely in MySQL. Each incident's SCIMS entity
     * number is resolved to a barangay using the supplied map; legacy IDs
     * that no longer match a SCIMS business are grouped as "Unspecified".
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
            ->selectRaw('SUM(estimated_cost_of_damages) as damage')
            ->selectRaw('COUNT(DISTINCT id) as incidents')
            ->groupBy('juridical_id')
            ->get();

        $damage = [];

        foreach ($rows as $row) {
            $entityNo = trim((string) $row->juridical_id);
            $barangay = trim((string) ($entityBarangays[$entityNo] ?? '')) ?: 'Unspecified';

            $damage[$barangay] ??= ['damage' => 0.0, 'affected' => 0, 'incidents' => 0];
            $damage[$barangay]['damage'] += (float) $row->damage;
            $damage[$barangay]['affected']++;
            $damage[$barangay]['incidents'] += (int) $row->incidents;
        }

        $totalDamage = 0.0;
        $totalAffected = 0;

        foreach ($damage as $entry) {
            $totalDamage += $entry['damage'];
            $totalAffected += $entry['affected'];
        }

        return [
            'by_barangay' => $damage,
            'total_damage' => round($totalDamage, 2),
            'total_affected' => $totalAffected,
        ];
    }
}
