<?php

namespace App\Services\Training;

use App\Enums\TrainingStatus;
use Illuminate\Support\Facades\DB;

/**
 * Resolves the local training graduates for a set of SCIMS people.
 *
 * Local graduation is a per-trainee fact, so only trainees whose own
 * training_details row is COMPLETED are returned.
 */
class LocalGraduateTrainings
{
    /**
     * @param  array<int, string|null>  $entityNos
     * @return array<string, list<array{attributes: array<string, mixed>}>>
     */
    public function forEntityNumbers(array $entityNos): array
    {
        $entityNos = collect($entityNos)
            ->map(fn (mixed $entityNo): string => trim((string) $entityNo))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($entityNos === []) {
            return [];
        }

        return DB::table('trainees as t')
            ->join('trainings as tr', 'tr.id', '=', 't.training_id')
            ->join('training_details as td', 'td.id', '=', 't.training_details_id')
            ->whereIn('t.api_entity_no', $entityNos)
            ->where('td.status', TrainingStatus::Completed->value)
            ->orderByDesc('td.graduated_at')
            ->orderByDesc('tr.to_date')
            ->get([
                't.api_entity_no',
                'tr.training_program',
                'tr.from_date',
                'tr.to_date',
                'tr.hours_no',
                'tr.training_type',
                'tr.conducted_by',
                'tr.sector',
                'tr.graduated_at as training_graduated_at',
                'td.graduated_at as detail_graduated_at',
            ])
            ->groupBy('api_entity_no')
            ->map(fn ($trainings): array => $trainings
                ->map(fn (object $training): array => [
                    'attributes' => [
                        'training_program' => $training->training_program,
                        'from_date' => $training->from_date,
                        'to_date' => $training->to_date,
                        'hours_no' => $training->hours_no,
                        'training_type' => $training->training_type,
                        'conducted_by' => $training->conducted_by,
                        'sector' => $training->sector,
                        'graduated_at' => $training->detail_graduated_at
                            ?? $training->training_graduated_at,
                        'status' => TrainingStatus::Completed->value,
                    ],
                ])
                ->values()
                ->all())
            ->all();
    }
}
