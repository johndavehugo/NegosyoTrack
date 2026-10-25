<?php

namespace App\Filament\Resources\Training\Trainees\Tables;

use App\Filament\Resources\Training\Trainees\TraineesResource;
use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use App\Models\Training\Trainee;
use App\Services\Training\ScimsTrainingService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TraineesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_name')
                    ->label('Trainee')
                    ->description(fn (Trainee $record): string => $record->api_entity_no)
                    ->weight('semibold')
                    ->limit(45),

                TextColumn::make('training.training_program')
                    ->label('Training')
                    ->description(fn (Trainee $record): string => $record->training?->schedule_label ?? '—')
                    ->searchable()
                    ->sortable()
                    ->limit(45)
                    ->url(fn (Trainee $record): ?string => $record->training
                        ? TrainingProgramResource::getUrl('view', ['record' => $record->training])
                        : null),

                TextColumn::make('trainingDetails.graduated_at')
                    ->label('Graduated')
                    ->date('M d, Y')
                    ->placeholder('—')
                    ->icon(fn (Trainee $record): ?Heroicon => $record->isGraduate() ? Heroicon::CheckBadge : null)
                    ->color(fn (Trainee $record): string => $record->isGraduate() ? 'success' : 'gray')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('gender')
                    ->label('Gender')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('age')
                    ->label('Age')
                    ->state(fn (Trainee $record): ?int => $record->age)
                    ->suffix(' yrs')
                    ->placeholder('—')
                    ->alignCenter(),

                TextColumn::make('contact_no')
                    ->label('Contact')
                    ->icon(Heroicon::Phone)
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->placeholder('—'),

                TextColumn::make('birth_date')
                    ->label('Birth date')
                    ->date('M d, Y')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('mobile_no')
                    ->label('SCIMS mobile')
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->searchable()
            ->searchUsing(fn (Builder $query, string $search): Builder => $query->searchRecords($search))
            ->searchPlaceholder('Search name, entity no., contact, or training')
            ->defaultSort('api_entity_no')
            ->recordUrl(fn (Trainee $record): string => TraineesResource::getUrl('view', ['record' => $record]))
            ->filters([
                SelectFilter::make('training')
                    ->relationship('training', 'training_program')
                    ->label('Training')
                    ->searchable(),

                SelectFilter::make('gender')
                    ->label('Gender')
                    ->options(fn (): array => app(ScimsTrainingService::class)->genderOptions(
                        Trainee::query()->pluck('api_entity_no')->all(),
                    ))
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereIn(
                            'api_entity_no',
                            app(ScimsTrainingService::class)->entityNumbersForGender(
                                (clone $query)->pluck('api_entity_no')->all(),
                                $data['value'],
                            ),
                        )
                        : $query),

                Filter::make('missing_contact')
                    ->label('Missing contact number')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where(
                        fn (Builder $query) => $query
                            ->whereNull('contact_no')
                            ->orWhere('contact_no', ''),
                    )),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    EditAction::make(),

                    Action::make('markAsGraduate')
                        ->label('Mark as graduate')
                        ->icon(Heroicon::CheckBadge)
                        ->color('success')
                        ->visible(fn (Trainee $record): bool => ! $record->isGraduate())
                        ->fillForm(fn (Trainee $record): array => [
                            'graduated_at' => ($record->training?->graduated_at
                                ?? $record->training?->to_date
                                ?? now())->toDateString(),
                        ])
                        ->schema([
                            DatePicker::make('graduated_at')
                                ->label('Graduated on')
                                ->required()
                                ->helperText('Defaults to the training graduation date, then the training end date.'),
                        ])
                        ->action(function (Trainee $record, array $data): void {
                            $record->markAsGraduate($data['graduated_at']);

                            Notification::make()
                                ->success()
                                ->title('Trainee marked as graduate')
                                ->send();
                        }),

                    Action::make('editGraduation')
                        ->label('Edit graduation date')
                        ->icon(Heroicon::CalendarDays)
                        ->visible(fn (Trainee $record): bool => $record->isGraduate())
                        ->fillForm(fn (Trainee $record): array => [
                            'graduated_at' => $record->trainingDetails?->graduated_at?->toDateString(),
                        ])
                        ->schema([
                            DatePicker::make('graduated_at')
                                ->label('Graduated on')
                                ->required(),
                        ])
                        ->action(function (Trainee $record, array $data): void {
                            $record->updateGraduationDate($data['graduated_at']);

                            Notification::make()
                                ->success()
                                ->title('Graduation date updated')
                                ->send();
                        }),

                    Action::make('removeGraduate')
                        ->label('Remove from graduates')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->visible(fn (Trainee $record): bool => $record->isGraduate())
                        ->requiresConfirmation()
                        ->modalHeading('Remove graduate record')
                        ->modalDescription('The trainee stays enrolled in the training, but their completion record and graduation date are deleted.')
                        ->action(function (Trainee $record): void {
                            $record->removeGraduateRecord();

                            Notification::make()
                                ->success()
                                ->title('Graduate record removed')
                                ->send();
                        }),

                    Action::make('viewTraining')
                        ->label('View training')
                        ->icon(Heroicon::AcademicCap)
                        ->visible(fn (Trainee $record): bool => filled($record->training_id))
                        ->url(fn (Trainee $record): string => TrainingProgramResource::getUrl('view', ['record' => $record->training_id])),

                    Action::make('refreshScims')
                        ->label('Refresh from SCIMS')
                        ->icon(Heroicon::ArrowPath)
                        ->action(function (Trainee $record): void {
                            if ($record->refreshIdentity()) {
                                Notification::make()
                                    ->success()
                                    ->title('Trainee details reloaded from SCIMS')
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->warning()
                                ->title('SCIMS details unavailable')
                                ->body('Nothing was changed locally because identity data is not stored here. Try again later.')
                                ->send();
                        }),

                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markAsGraduate')
                        ->label('Mark as graduates')
                        ->icon(Heroicon::CheckBadge)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Mark selected trainees as graduates')
                        ->modalDescription('Graduation dates default to each training\'s graduation date, then its end date, then today. Trainees already marked are skipped.')
                        ->action(function (Collection $records): void {
                            $marked = 0;
                            $skipped = 0;

                            foreach ($records as $record) {
                                /** @var Trainee $record */
                                if ($record->isGraduate()) {
                                    $skipped++;

                                    continue;
                                }

                                $record->markAsGraduate();
                                $marked++;
                            }

                            $notification = $marked > 0
                                ? Notification::make()->success()->title($marked.' trainee(s) marked as graduates')
                                : Notification::make()->warning()->title('No trainees were marked');

                            if ($skipped > 0) {
                                $notification->body($skipped.' trainee(s) were already graduates.');
                            }

                            $notification->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('refreshScims')
                        ->label('Refresh from SCIMS')
                        ->icon(Heroicon::ArrowPath)
                        ->requiresConfirmation()
                        ->modalHeading('Refresh trainee details from SCIMS')
                        ->modalDescription('Names, genders, birth dates and mobile numbers are re-read from SCIMS. Nothing is written to the local database.')
                        ->action(function (Collection $records): void {
                            $refreshed = 0;
                            $failed = 0;

                            foreach ($records as $record) {
                                $record->refreshIdentity() ? $refreshed++ : $failed++;
                            }

                            $notification = $refreshed > 0
                                ? Notification::make()->success()->title($refreshed.' trainee(s) reloaded from SCIMS')
                                : Notification::make()->warning()->title('SCIMS details unavailable');

                            if ($failed > 0) {
                                $notification->body($failed.' trainee(s) could not be resolved from SCIMS.');
                            }

                            $notification->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::UserGroup)
            ->emptyStateHeading('No trainees yet')
            ->emptyStateDescription('Enroll a person in a training record to build its roster.')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('New trainee'),
            ]);
    }
}
