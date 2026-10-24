<?php

namespace App\Filament\Resources\Training\Trainees\Tables;

use App\Filament\Resources\Training\Trainees\TraineesResource;
use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use App\Models\Training\Trainee;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                TextColumn::make('full_name')
                    ->label('Trainee')
                    ->state(fn (Trainee $record): string => $record->display_name)
                    ->description(fn (Trainee $record): string => $record->api_entity_no)
                    ->searchable(['full_name', 'api_entity_no', 'contact_no'])
                    ->sortable()
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
                    ->searchable()
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
            ->defaultSort('full_name')
            ->recordUrl(fn (Trainee $record): string => TraineesResource::getUrl('view', ['record' => $record]))
            ->filters([
                SelectFilter::make('training')
                    ->relationship('training', 'training_program')
                    ->label('Training')
                    ->searchable(),

                SelectFilter::make('gender')
                    ->label('Gender')
                    ->options(fn (): array => Trainee::query()
                        ->whereNotNull('gender')
                        ->distinct()
                        ->orderBy('gender')
                        ->pluck('gender', 'gender')
                        ->all()),

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

                    Action::make('viewTraining')
                        ->label('View training')
                        ->icon(Heroicon::AcademicCap)
                        ->visible(fn (Trainee $record): bool => filled($record->training_id))
                        ->url(fn (Trainee $record): string => TrainingProgramResource::getUrl('view', ['record' => $record->training_id])),

                    Action::make('refreshScims')
                        ->label('Refresh from SCIMS')
                        ->icon(Heroicon::ArrowPath)
                        ->action(function (Trainee $record): void {
                            if ($record->refreshSnapshots()) {
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
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('refreshScims')
                        ->label('Refresh from SCIMS')
                        ->icon(Heroicon::ArrowPath)
                        ->requiresConfirmation()
                        ->modalHeading('Refresh trainee details from SCIMS')
                        ->modalDescription('Names, genders, birth dates and mobile numbers are updated from SCIMS. Records without a SCIMS match keep their current details.')
                        ->action(function (Collection $records): void {
                            $refreshed = 0;
                            $failed = 0;

                            foreach ($records as $record) {
                                $record->refreshSnapshots() ? $refreshed++ : $failed++;
                            }

                            $notification = $refreshed > 0
                                ? Notification::make()->success()->title($refreshed.' trainee(s) refreshed')
                                : Notification::make()->warning()->title('SCIMS details unavailable');

                            if ($failed > 0) {
                                $notification->body($failed.' trainee(s) kept their existing details.');
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
