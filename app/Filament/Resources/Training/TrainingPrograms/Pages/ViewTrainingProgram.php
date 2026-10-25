<?php

namespace App\Filament\Resources\Training\TrainingPrograms\Pages;

use App\Filament\Resources\Training\Trainees\TraineesResource;
use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewTrainingProgram extends ViewRecord
{
    protected static string $resource = TrainingProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('addTrainee')
                ->label('Add trainee')
                ->icon(Heroicon::UserPlus)
                ->color('gray')
                ->url(fn (): string => TraineesResource::getUrl('create', [
                    'training_id' => $this->getRecord()->getKey(),
                ])),

            DeleteAction::make()
                ->disabled(fn (): bool => $this->getRecord()->trainees()->exists())
                ->tooltip(fn (): ?string => $this->getRecord()->trainees()->exists()
                    ? 'Remove or reassign this record\'s trainees first.'
                    : null),
        ];
    }
}
