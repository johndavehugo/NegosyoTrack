<?php

namespace App\Filament\Pages\Training;

use App\Enums\TrainingStatus;
use App\Filament\Resources\Training\Trainees\TraineesResource;
use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use App\Filament\Widgets\GraduatesStatsWidget;
use App\Models\Training\Trainee;
use App\Models\Training\Training;
use App\Models\Training\TrainingDetail;
use App\Services\Training\GraduatesExporter;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Graduates extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.training.graduates';

    protected static ?string $title = 'Graduates';

    protected static ?string $navigationLabel = 'Graduates';

    protected static ?string $slug = 'training/graduates';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CheckBadge;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Training & Capacity Building';
    }

    public static function getNavigationBadge(): ?string
    {
        $total = Trainee::query()->graduates()->count();

        return $total > 0 ? (string) $total : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Graduates across all training records';
    }

    /**
     * @return array<class-string<GraduatesStatsWidget>>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            GraduatesStatsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int
    {
        return 1;
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportGraduates')
                ->label('Export')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->schema([
                    Select::make('format')
                        ->label('File format')
                        ->options([
                            'csv' => 'CSV (spreadsheet)',
                            'xlsx' => 'Excel (XLSX)',
                        ])
                        ->default('csv')
                        ->required(),
                ])
                ->action(fn (array $data) => app(GraduatesExporter::class)->download(
                    $this->getFilteredTableQuery() ?? Trainee::query()->graduates(),
                    $data['format'],
                )),

            Action::make('openTrainees')
                ->label('Mark graduates')
                ->icon(Heroicon::UserPlus)
                ->url(TraineesResource::getUrl('index')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Trainee::query()
                    ->graduates()
                    ->with(['training', 'trainingDetails'])
            )
            ->columns([
                TextColumn::make('full_name')
                    ->label('Graduate')
                    ->state(fn (Trainee $record): string => $record->display_name)
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

                TextColumn::make('trainingDetails.graduated_at')
                    ->label('Graduated')
                    ->date('M d, Y')
                    ->description(fn (Trainee $record): ?string => $record->training?->training_type)
                    ->icon(Heroicon::CheckBadge)
                    ->color('success')
                    ->sortable(),

                TextColumn::make('contact_no')
                    ->label('Contact')
                    ->icon(Heroicon::Phone)
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->placeholder('—'),

                TextColumn::make('training.sector')
                    ->label('Sector')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('gender')
                    ->label('Gender')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('trainingDetails.graduated_at', 'desc')
            ->recordUrl(fn (Trainee $record): string => TraineesResource::getUrl('view', ['record' => $record]))
            ->searchPlaceholder('Search name, entity no., or contact')
            ->persistSearchInSession()
            ->filters([
                SelectFilter::make('graduation_year')
                    ->label('Graduation year')
                    ->options(fn (): array => TrainingDetail::query()
                        ->where('status', TrainingStatus::Completed->value)
                        ->whereNotNull('graduated_at')
                        ->orderByDesc('graduated_at')
                        ->pluck('graduated_at')
                        ->map(fn ($date): string => $date->format('Y'))
                        ->unique()
                        ->mapWithKeys(fn (string $year): array => [$year => $year])
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas(
                            'trainingDetails',
                            fn (Builder $query): Builder => $query->whereYear('graduated_at', $data['value']),
                        )
                        : $query),

                Filter::make('graduated_between')
                    ->label('Graduation date range')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['from'] ?? null),
                            fn (Builder $query): Builder => $query->whereHas(
                                'trainingDetails',
                                fn (Builder $query): Builder => $query->whereDate('graduated_at', '>=', $data['from']),
                            ),
                        )
                        ->when(
                            filled($data['until'] ?? null),
                            fn (Builder $query): Builder => $query->whereHas(
                                'trainingDetails',
                                fn (Builder $query): Builder => $query->whereDate('graduated_at', '<=', $data['until']),
                            ),
                        ))
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['from'] ?? null)) {
                            $indicators[] = 'From '.Carbon::parse($data['from'])->toFormattedDateString();
                        }

                        if (filled($data['until'] ?? null)) {
                            $indicators[] = 'Until '.Carbon::parse($data['until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),

                SelectFilter::make('training')
                    ->label('Training program')
                    ->relationship('training', 'training_program')
                    ->searchable(),

                SelectFilter::make('training_type')
                    ->label('Training type')
                    ->searchable()
                    ->options(fn (): array => Training::query()
                        ->whereNotNull('training_type')
                        ->distinct()
                        ->orderBy('training_type')
                        ->pluck('training_type', 'training_type')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas(
                            'training',
                            fn (Builder $query): Builder => $query->where('training_type', $data['value']),
                        )
                        : $query),

                SelectFilter::make('sector')
                    ->label('Sector')
                    ->searchable()
                    ->options(fn (): array => Training::query()
                        ->whereNotNull('sector')
                        ->distinct()
                        ->orderBy('sector')
                        ->pluck('sector', 'sector')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas(
                            'training',
                            fn (Builder $query): Builder => $query->where('sector', $data['value']),
                        )
                        : $query),

                SelectFilter::make('gender')
                    ->label('Gender')
                    ->options(fn (): array => Trainee::query()
                        ->whereNotNull('gender')
                        ->distinct()
                        ->orderBy('gender')
                        ->pluck('gender', 'gender')
                        ->all()),
            ])
            ->persistFiltersInSession()
            ->recordActions([
                ActionGroup::make([
                    Action::make('viewTrainee')
                        ->label('View trainee')
                        ->icon(Heroicon::User)
                        ->url(fn (Trainee $record): string => TraineesResource::getUrl('view', ['record' => $record])),

                    Action::make('viewTraining')
                        ->label('View training')
                        ->icon(Heroicon::AcademicCap)
                        ->visible(fn (Trainee $record): bool => filled($record->training_id))
                        ->url(fn (Trainee $record): string => TrainingProgramResource::getUrl('view', [
                            'record' => $record->training_id,
                        ])),

                    Action::make('editGraduation')
                        ->label('Edit graduation date')
                        ->icon(Heroicon::CalendarDays)
                        ->visible(fn (Trainee $record): bool => $record->isGraduate())
                        ->fillForm(fn (Trainee $record): array => [
                            'graduated_at' => $record->trainingDetails?->graduated_at?->toDateString(),
                        ])
                        ->schema(fn (Trainee $record): array => [
                            DatePicker::make('graduated_at')
                                ->label('Graduated on')
                                ->required()
                                ->minDate($record->training?->to_date?->toDateString())
                                ->helperText($record->training?->to_date
                                    ? 'Must be on or after the training end date ('.$record->training->to_date->format('M d, Y').').'
                                    : null),
                        ])
                        ->action(function (Trainee $record, array $data): void {
                            $minimum = $record->training?->to_date;

                            if ($minimum && Carbon::parse($data['graduated_at'])->lt($minimum)) {
                                Notification::make()
                                    ->danger()
                                    ->title('Invalid graduation date')
                                    ->body('The graduation date cannot be earlier than the training end date ('.$minimum->format('M d, Y').').')
                                    ->send();

                                return;
                            }

                            $record->updateGraduationDate($data['graduated_at']);

                            Notification::make()
                                ->success()
                                ->title('Graduation date updated')
                                ->send();
                        }),

                    Action::make('refreshScims')
                        ->label('Refresh from SCIMS')
                        ->icon(Heroicon::ArrowPath)
                        ->action(function (Trainee $record): void {
                            if ($record->refreshSnapshots()) {
                                Notification::make()
                                    ->success()
                                    ->title('Graduate details refreshed')
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->warning()
                                ->title('SCIMS details unavailable')
                                ->body('The local snapshot was left unchanged. Try again later.')
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
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('exportSelected')
                        ->label('Export selected')
                        ->icon(Heroicon::ArrowDownTray)
                        ->schema([
                            Select::make('format')
                                ->label('File format')
                                ->options([
                                    'csv' => 'CSV (spreadsheet)',
                                    'xlsx' => 'Excel (XLSX)',
                                ])
                                ->default('csv')
                                ->required(),
                        ])
                        ->action(fn (Collection $records, array $data) => app(GraduatesExporter::class)->download(
                            Trainee::query()->whereKey($records->pluck('id')->all()),
                            $data['format'],
                        ))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('setGraduationDate')
                        ->label('Set graduation date')
                        ->icon(Heroicon::CalendarDays)
                        ->schema([
                            DatePicker::make('graduated_at')
                                ->label('Graduated on')
                                ->required(),
                        ])
                        ->modalHeading('Set graduation date for selected graduates')
                        ->modalDescription('Records whose training ended after the chosen date are skipped.')
                        ->action(function (Collection $records, array $data): void {
                            $date = Carbon::parse($data['graduated_at']);
                            $updated = 0;
                            $skipped = 0;

                            foreach ($records as $record) {
                                /** @var Trainee $record */
                                if (! $record->isGraduate()) {
                                    $skipped++;

                                    continue;
                                }

                                $trainingEnd = $record->training?->to_date;

                                if ($trainingEnd && $date->lt($trainingEnd)) {
                                    $skipped++;

                                    continue;
                                }

                                $record->updateGraduationDate($date->toDateString())
                                    ? $updated++
                                    : $skipped++;
                            }

                            $notification = $updated > 0
                                ? Notification::make()->success()->title($updated.' graduation date(s) updated')
                                : Notification::make()->warning()->title('No graduation dates were updated');

                            if ($skipped > 0) {
                                $notification->body($skipped.' record(s) were skipped because the date was before the training end date.');
                            }

                            $notification->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('removeGraduates')
                        ->label('Remove from graduates')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Remove selected graduates')
                        ->modalDescription('Their completion records and graduation dates are deleted. The trainees stay enrolled in their trainings.')
                        ->action(function (Collection $records): void {
                            $removed = 0;

                            foreach ($records as $record) {
                                /** @var Trainee $record */
                                if ($record->removeGraduateRecord()) {
                                    $removed++;
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title($removed.' graduate record(s) removed')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->emptyStateIcon(Heroicon::CheckBadge)
            ->emptyStateHeading('No graduates yet')
            ->emptyStateDescription('Mark a trainee as a graduate from the Trainees page to list them here.')
            ->emptyStateActions([
                Action::make('openTrainees')
                    ->label('Open trainees')
                    ->icon(Heroicon::UserGroup)
                    ->url(TraineesResource::getUrl('index')),
            ]);
    }
}
