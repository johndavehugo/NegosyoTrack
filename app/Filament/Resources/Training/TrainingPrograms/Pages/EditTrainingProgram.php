<?php

namespace App\Filament\Resources\Training\TrainingPrograms\Pages;

use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrainingProgram extends EditRecord
{
    protected static string $resource = TrainingProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return TrainingProgramResource::getUrl('index');
    }
}
