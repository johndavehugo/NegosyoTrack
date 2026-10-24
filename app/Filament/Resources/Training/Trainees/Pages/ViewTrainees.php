<?php

namespace App\Filament\Resources\Training\Trainees\Pages;

use App\Filament\Resources\Training\Trainees\TraineesResource;
use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewTrainees extends ViewRecord
{
    protected static string $resource = TraineesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('viewTraining')
                ->label('View training')
                ->icon(Heroicon::AcademicCap)
                ->color('gray')
                ->visible(fn (): bool => filled($this->getRecord()->training_id))
                ->url(fn (): string => TrainingProgramResource::getUrl('view', [
                    'record' => $this->getRecord()->training_id,
                ])),

            Action::make('refreshScims')
                ->label('Refresh from SCIMS')
                ->icon(Heroicon::ArrowPath)
                ->color('gray')
                ->action(function (): void {
                    if ($this->getRecord()->refreshSnapshots()) {
                        Notification::make()
                            ->success()
                            ->title('Trainee details refreshed')
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->warning()
                        ->title('SCIMS details unavailable')
                        ->body('The local snapshot was left unchanged. Try again later.')
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }
}
