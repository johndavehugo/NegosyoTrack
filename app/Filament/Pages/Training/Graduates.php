<?php

namespace App\Filament\Pages\Training;

use App\Enums\TrainingStatus;
use App\Filament\Resources\Training\Trainees\TraineesResource;
use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use App\Models\Training\Trainee;
use App\Models\Training\Training;
use App\Models\Training\TrainingDetail;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

                TextColumn::make('business')
                    ->label('Business')
                    ->state(fn (Trainee $record): string => $record->training?->business_display_name ?? '—')
                    ->wrap(),

                TextColumn::make('trainingDetails.graduated_at')
                    ->label('Graduated')
                    ->date('M d, Y')
                    ->description(fn (Trainee $record): ?string => $record->trainingDetails?->status?->getLabel())
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

                TextColumn::make('gender')
                    ->label('Gender')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('training.training_type')
                    ->label('Training type')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('trainingDetails.graduated_at', 'desc')
            ->recordUrl(fn (Trainee $record): string => TraineesResource::getUrl('view', ['record' => $record]))
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

                SelectFilter::make('training')
                    ->label('Training program')
                    ->relationship('training', 'training_program')
                    ->searchable(),

                SelectFilter::make('business')
                    ->label('Business')
                    ->options(fn (): array => Training::query()
                        ->whereIn('id', Trainee::query()->graduates()->select('training_id'))
                        ->whereNotNull('business_name')
                        ->orderBy('business_name')
                        ->pluck('business_name', 'entity_no')
                        ->all())
                    ->searchable()
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas(
                            'training',
                            fn (Builder $query): Builder => $query->where('entity_no', $data['value']),
                        )
                        : $query),
            ])
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
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Actions'),
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
