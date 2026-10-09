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
     * Incident-level damage is attributed once to the main business's
     * barangay; per-business damage is attributed per affected business
     * (ported from the legacy risk_damage endpoint).
     *
     * @return array{by_barangay: array<string, array{damage: float, affected: int, incidents: int}>, total_damage: float, total_affected: int}
     */
    public function damageByBarangay(?int $calamityId = null): array
    {
        $label = "COALESCE(NULLIF(TRIM(a.barangay), ''), 'Unspecified')";

        $incidents = DB::table('calamity_incidents as ci')
            ->leftJoin('juridicals as j', 'j.id', '=', 'ci.juridical_id')
            ->leftJoin('addresses as a', 'j.address_id', '=', 'a.id')
            ->where('ci.estimated_cost_of_damages', '>', 0)
            ->when($calamityId > 0, fn ($query) => $query->where('ci.calamity_id', $calamityId))
            ->selectRaw("{$label} as barangay")
            ->selectRaw('SUM(ci.estimated_cost_of_damages) as damage')
            ->selectRaw('COUNT(DISTINCT ci.id) as incidents')
            ->groupBy('barangay')
            ->get();

        $businesses = DB::table('calamity_incident_businesses as ib')
            ->join('calamity_incidents as ci', 'ci.id', '=', 'ib.incident_id')
            ->leftJoin('juridicals as j', 'j.id', '=', 'ib.juridical_id')
            ->leftJoin('addresses as a', 'j.address_id', '=', 'a.id')
            ->where('ib.estimated_cost_of_damages', '>', 0)
            ->when($calamityId > 0, fn ($query) => $query->where('ci.calamity_id', $calamityId))
            ->selectRaw("{$label} as barangay")
            ->selectRaw('SUM(ib.estimated_cost_of_damages) as damage')
            ->selectRaw('COUNT(DISTINCT ib.juridical_id) as affected')
            ->groupBy('barangay')
            ->get();

        $damage = [];

        foreach ($incidents as $row) {
            $damage[$row->barangay] = [
                'damage' => (float) $row->damage,
                'affected' => 0,
                'incidents' => (int) $row->incidents,
            ];
        }

        foreach ($businesses as $row) {
            $damage[$row->barangay] ??= ['damage' => 0.0, 'affected' => 0, 'incidents' => 0];
            $damage[$row->barangay]['damage'] += (float) $row->damage;
            $damage[$row->barangay]['affected'] += (int) $row->affected;
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
