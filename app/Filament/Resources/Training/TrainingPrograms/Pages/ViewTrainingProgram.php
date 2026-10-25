<?php

namespace App\Filament\Resources\Training\TrainingPrograms\Pages;

use App\Filament\Resources\Training\Trainees\TraineesResource;
use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
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

            Action::make('refreshBusiness')
                ->label('Refresh business')
                ->icon(Heroicon::ArrowPath)
                ->color('gray')
                ->visible(fn (): bool => filled($this->getRecord()->entity_no))
                ->action(function (): void {
                    if ($this->getRecord()->refreshBusinessName()) {
                        Notification::make()
                            ->success()
                            ->title('Business details refreshed')
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->warning()
                        ->title('SCIMS details unavailable')
                        ->body('The business name could not be refreshed. Try again later.')
                        ->send();
                }),

            DeleteAction::make()
                ->disabled(fn (): bool => $this->getRecord()->trainees()->exists())
                ->tooltip(fn (): ?string => $this->getRecord()->trainees()->exists()
                    ? 'Remove or reassign this record\'s trainees first.'
                    : null),
        ];
    }
}
