<?php

namespace App\Filament\Resources\Training\TrainingPrograms\Pages;

use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTrainingProgram extends CreateRecord
{
    protected static string $resource = TrainingProgramResource::class;

    protected function getRedirectUrl(): string
    {
        return TrainingProgramResource::getUrl('index');
    }
}
