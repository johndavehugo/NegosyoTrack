<?php

namespace App\Filament\Resources\Training\Trainees\Pages;

use App\Filament\Resources\Training\Trainees\TraineesResource;
use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use App\Models\Training\Trainee;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
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

            Action::make('markAsGraduate')
                ->label('Mark as graduate')
                ->icon(Heroicon::CheckBadge)
                ->color('success')
                ->visible(fn (): bool => ! $this->getRecord()->isGraduate())
                ->fillForm(function (): array {
                    /** @var Trainee $record */
                    $record = $this->getRecord();

                    return [
                        'graduated_at' => ($record->training?->graduated_at
                            ?? $record->training?->to_date
                            ?? now())->toDateString(),
                    ];
                })
                ->schema([
                    DatePicker::make('graduated_at')
                        ->label('Graduated on')
                        ->required()
                        ->helperText('Defaults to the training graduation date, then the training end date.'),
                ])
                ->action(function (array $data): void {
                    /** @var Trainee $record */
                    $record = $this->getRecord();
                    $record->markAsGraduate($data['graduated_at']);
                    $record->refresh();

                    Notification::make()
                        ->success()
                        ->title('Trainee marked as graduate')
                        ->send();
                }),

            Action::make('editGraduation')
                ->label('Edit graduation date')
                ->icon(Heroicon::CalendarDays)
                ->color('gray')
                ->visible(fn (): bool => $this->getRecord()->isGraduate())
                ->fillForm(function (): array {
                    /** @var Trainee $record */
                    $record = $this->getRecord();

                    return [
                        'graduated_at' => $record->trainingDetails?->graduated_at?->toDateString(),
                    ];
                })
                ->schema([
                    DatePicker::make('graduated_at')
                        ->label('Graduated on')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    /** @var Trainee $record */
                    $record = $this->getRecord();
                    $record->updateGraduationDate($data['graduated_at']);
                    $record->refresh();

                    Notification::make()
                        ->success()
                        ->title('Graduation date updated')
                        ->send();
                }),

            Action::make('removeGraduate')
                ->label('Remove from graduates')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->visible(fn (): bool => $this->getRecord()->isGraduate())
                ->requiresConfirmation()
                ->modalHeading('Remove graduate record')
                ->modalDescription('The trainee stays enrolled in the training, but their completion record and graduation date are deleted.')
                ->action(function (): void {
                    /** @var Trainee $record */
                    $record = $this->getRecord();
                    $record->removeGraduateRecord();
                    $record->refresh();

                    Notification::make()
                        ->success()
                        ->title('Graduate record removed')
                        ->send();
                }),

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
