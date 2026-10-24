<?php

namespace App\Filament\Resources\Training\Trainees\Pages;

use App\Filament\Resources\Training\Trainees\TraineesResource;
use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateTrainees extends CreateRecord
{
    protected static string $resource = TraineesResource::class;

    protected function getRedirectUrl(): string
    {
        $trainingId = $this->getRecord()->training_id;

        return filled($trainingId)
            ? TrainingProgramResource::getUrl('view', ['record' => $trainingId])
            : TraineesResource::getUrl('index');
    }

    /**
     * The trainees.id primary key is a varchar — not auto-incremented —
     * so generate it before Eloquent inserts.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id'] = 'tren-'.Str::uuid7();

        return $data;
    }
}
