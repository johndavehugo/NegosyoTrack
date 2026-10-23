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
     * Barangay is snapshotted onto each incident from SCIMS when it is
     * created, so this reads `calamity_incidents` only — no joins to the
     * retired local MSME tables.
     *
     * @return array{by_barangay: array<string, array{damage: float, affected: int, incidents: int}>, total_damage: float, total_affected: int}
     */
    public function damageByBarangay(?int $calamityId = null): array
    {
        $label = "COALESCE(NULLIF(TRIM(barangay), ''), 'Unspecified')";

        $rows = DB::table('calamity_incidents')
            ->where('estimated_cost_of_damages', '>', 0)
            ->when($calamityId > 0, fn ($query) => $query->where('calamity_id', $calamityId))
            ->selectRaw("{$label} as barangay")
            ->selectRaw('SUM(estimated_cost_of_damages) as damage')
            ->selectRaw('COUNT(DISTINCT juridical_id) as affected')
            ->selectRaw('COUNT(*) as incidents')
            ->groupBy('barangay')
            ->get();

        $damage = [];
        $totalDamage = 0.0;
        $totalAffected = 0;

        foreach ($rows as $row) {
            $damage[$row->barangay] = [
                'damage' => (float) $row->damage,
                'affected' => (int) $row->affected,
                'incidents' => (int) $row->incidents,
            ];

            $totalDamage += (float) $row->damage;
            $totalAffected += (int) $row->affected;
        }

        return [
            'by_barangay' => $damage,
            'total_damage' => round($totalDamage, 2),
            'total_affected' => $totalAffected,
        ];
    }
}
