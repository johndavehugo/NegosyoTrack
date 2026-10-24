<?php

namespace App\Filament\Resources\Training\TrainingPrograms\Pages;

use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateTrainingProgram extends CreateRecord
{
    protected static string $resource = TrainingProgramResource::class;

    protected function getRedirectUrl(): string
    {
        return TrainingProgramResource::getUrl('view', [
            'record' => $this->getRecord(),
        ]);
    }

    /**
     * The primary key `id` is a varchar — not auto-incremented — so
     * generate the `trin-{uuid7}` value before Eloquent inserts.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id'] = 'trin-'.Str::uuid7();

        return $data;
    }
}
