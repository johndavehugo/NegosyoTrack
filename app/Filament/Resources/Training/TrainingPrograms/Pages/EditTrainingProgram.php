<?php

namespace App\Filament\Resources\Training\TrainingPrograms\Pages;

use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTrainingProgram extends EditRecord
{
    protected static string $resource = TrainingProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            DeleteAction::make()
                ->disabled(fn (): bool => $this->getRecord()->trainees()->exists())
                ->tooltip(fn (): ?string => $this->getRecord()->trainees()->exists()
                    ? 'Remove or reassign this record\'s trainees first.'
                    : null),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return TrainingProgramResource::getUrl('view', [
            'record' => $this->getRecord(),
        ]);
    }
}
