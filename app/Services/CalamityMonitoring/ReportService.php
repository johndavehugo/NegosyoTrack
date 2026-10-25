<?php

namespace App\Services\CalamityMonitoring;

use App\Models\CalamityMonitoring\Calamity;
use App\Models\CalamityMonitoring\Incident;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Build the complete damage assessment report for a calamity.
     *
     * @return array<string, mixed>
     */
    public function build(Calamity $calamity): array
    {
        $incidents = Incident::query()
            ->where('calamity_id', $calamity->getKey())
            ->orderByRaw("COALESCE(NULLIF(TRIM(barangay), ''), 'Unspecified')")
            ->orderBy('juridical_name')
            ->get();

        return $this->fromIncidents($calamity, $incidents);
    }

    /**
     * Build the report from an already filtered incident collection.
     *
     * @param  Collection<int, Incident>  $incidents
     * @return array<string, mixed>
     */
    public function fromIncidents(Calamity $calamity, Collection $incidents): array
    {
        $totalDamage = round((float) $incidents->sum('estimated_cost_of_damages'), 2);

        $byBarangay = $incidents
            ->groupBy(fn (Incident $incident): string => $this->barangay($incident))
            ->map(fn (Collection $group, string $barangay): array => [
                'barangay' => $barangay,
                'affected' => $group->pluck('juridical_id')->filter()->unique()->count(),
                'incidents' => $group->count(),
                'damage' => round((float) $group->sum('estimated_cost_of_damages'), 2),
            ])
            ->sortBy('barangay')
            ->values()
            ->map(fn (array $row): array => [
                ...$row,
                'share' => $totalDamage > 0
                    ? round($row['damage'] / $totalDamage * 100, 1)
                    : 0.0,
            ])
            ->all();

        $byStatus = collect(Incident::statusOptions())
            ->map(fn (string $label, string $status): array => [
                'status' => $status,
                'label' => $label,
                'count' => $incidents->where('status', $status)->count(),
                'damage' => round((float) $incidents->where('status', $status)->sum('estimated_cost_of_damages'), 2),
            ])
            ->values()
            ->all();

        $byNature = collect(Incident::natureOptions())
            ->map(fn (string $label, string $nature): array => [
                'nature' => $nature,
                'label' => $label,
                'count' => $incidents->where('nature_of_damage', $nature)->count(),
                'damage' => round((float) $incidents->where('nature_of_damage', $nature)->sum('estimated_cost_of_damages'), 2),
            ])
            ->values()
            ->all();

        return [
            'calamity' => $calamity,
            'incidents' => $incidents,
            'by_barangay' => $byBarangay,
            'by_status' => $byStatus,
            'by_nature' => $byNature,
            'summary' => [
                'affected' => $incidents->pluck('juridical_id')->filter()->unique()->count(),
                'incidents' => $incidents->count(),
                'total_damage' => $totalDamage,
                'average_damage' => $incidents->isNotEmpty()
                    ? round($totalDamage / $incidents->count(), 2)
                    : 0.0,
                'verified' => $incidents->where('status', 'VERIFIED')->count(),
                'pending' => $incidents->where('status', 'PENDING_VERIFICATION')->count(),
                'aid_released' => $incidents->where('status', 'AID_RELEASED')->count(),
                'partial' => $incidents->where('nature_of_damage', 'PARTIAL')->count(),
                'total' => $incidents->where('nature_of_damage', 'TOTAL')->count(),
            ],
            'generated_at' => now(),
        ];
    }

    protected function barangay(Incident $incident): string
    {
        $barangay = trim((string) $incident->barangay);

        return $barangay !== '' ? $barangay : 'Unspecified';
    }
}
