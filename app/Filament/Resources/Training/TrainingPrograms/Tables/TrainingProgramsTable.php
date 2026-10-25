<?php

namespace App\Filament\Resources\Training\TrainingPrograms\Tables;

use App\Enums\TrainingStatus;
use App\Filament\Resources\Training\Trainees\TraineesResource;
use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use App\Models\Training\Training;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrainingProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('training_program')
                    ->label('Training Program')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap()
                    ->description(fn (Training $record): string => collect([
                        $record->training_type,
                        $record->conducted_by,
                    ])->filter()->join(' · '))
                    ->limit(70),

                TextColumn::make('from_date')
                    ->label('Schedule')
                    ->state(fn (Training $record): string => $record->schedule_label)
                    ->description(fn (Training $record): string => $record->hours_no.' training hours')
                    ->sortable()
                    ->wrap(),

                TextColumn::make('trainees_count')
                    ->label('Trainees')
                    ->counts('trainees')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->url(fn (Training $record): string => TrainingProgramResource::getUrl('view', ['record' => $record])),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?TrainingStatus $state): string => $state?->getLabel() ?? '—')
                    ->icon(fn (?TrainingStatus $state) => $state?->getIcon())
                    ->sortable(),

                TextColumn::make('sector')
                    ->label('Sector')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('graduated_at')
                    ->label('Graduated')
                    ->date('M d, Y')
                    ->placeholder('Not graduated')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Recorded')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('from_date', 'desc')
            ->recordUrl(fn (Training $record): string => TrainingProgramResource::getUrl('view', ['record' => $record]))
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(TrainingStatus::class),

                SelectFilter::make('training_type')
                    ->label('Training type')
                    ->searchable()
                    ->options(fn (): array => Training::query()
                        ->whereNotNull('training_type')
                        ->distinct()
                        ->orderBy('training_type')
                        ->pluck('training_type', 'training_type')
                        ->all()),

                SelectFilter::make('sector')
                    ->label('Sector')
                    ->searchable()
                    ->options(fn (): array => Training::query()
                        ->whereNotNull('sector')
                        ->distinct()
                        ->orderBy('sector')
                        ->pluck('sector', 'sector')
                        ->all()),

                SelectFilter::make('year')
                    ->label('Year')
                    ->options(fn (): array => Training::query()
                        ->selectRaw('YEAR(from_date) as year')
                        ->distinct()
                        ->orderByDesc('year')
                        ->pluck('year', 'year')
                        ->mapWithKeys(fn ($year): array => [(string) $year => (string) $year])
                        ->all())
                    ->query(function (Builder $query, array $data): Builder {
                        $year = $data['value'] ?? null;

                        return filled($year)
                            ? $query->whereYear('from_date', $year)
                            : $query;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    EditAction::make(),

                    Action::make('roster')
                        ->label('View trainee roster')
                        ->icon(Heroicon::UserGroup)
                        ->url(fn (Training $record): string => TrainingProgramResource::getUrl('view', ['record' => $record])),

                    Action::make('addTrainee')
                        ->label('Add trainee')
                        ->icon(Heroicon::UserPlus)
                        ->url(fn (Training $record): string => TraineesResource::getUrl('create', ['training_id' => $record->getKey()])),

                    DeleteAction::make()
                        ->disabled(fn (Training $record): bool => $record->trainees()->exists())
                        ->tooltip(fn (Training $record): ?string => $record->trainees()->exists()
                            ? 'Remove or reassign this record\'s trainees first.'
                            : null),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Actions'),
            ])
            ->emptyStateIcon(Heroicon::AcademicCap)
            ->emptyStateHeading('No training records yet')
            ->emptyStateDescription('Create your first training record, then add the people who attended it.')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('New training record'),
            ]);
    }
}
