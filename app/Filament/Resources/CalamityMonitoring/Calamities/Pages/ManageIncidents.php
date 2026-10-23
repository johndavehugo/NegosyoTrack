<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Pages;

use App\Filament\Resources\CalamityMonitoring\Calamities\CalamityResource;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\IncidentResource;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Schemas\IncidentForm;
use App\Filament\Widgets\IncidentStatsWidget;
use App\Models\CalamityMonitoring\Incident;
use App\Services\CalamityMonitoring\ReportExporter;
use App\Services\CalamityMonitoringService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ManageIncidents extends ManageRelatedRecords
{
    protected static string $resource = CalamityResource::class;

    protected static string $relationship = 'incident';

    protected static ?string $relatedResource = IncidentResource::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    /**
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            IncidentStatsWidget::class,
        ];
    }

    public function exportReport(string $format): BinaryFileResponse
    {
        return app(ReportExporter::class)->download(
            $this->getOwnerRecord(),
            $this->getTableQueryForExport(),
            $format,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getWidgetData(): array
    {
        return [
            'calamityId' => (string) $this->getRecord()->getKey(),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Calamity Information')
                    ->description('Details of the selected calamity.')
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->compact()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Calamity Name')
                            ->state(fn () => $this->getOwnerRecord()->name)
                            ->weight('bold')
                            ->placeholder('No name provided'),

                        TextEntry::make('calamity_type')
                            ->label('Calamity Type')
                            ->state(fn () => $this->getOwnerRecord()->calamity_type)
                            ->badge()
                            ->placeholder('Not specified'),

                        TextEntry::make('declaration_date')
                            ->label('Declaration Date')
                            ->state(fn () => $this->getOwnerRecord()->declaration_date)
                            ->date('M d, Y')
                            ->placeholder('Not specified'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->state(fn () => $this->getOwnerRecord()->description)
                            ->placeholder('No description provided')
                            ->columnSpanFull(),
                    ]),

                Section::make('Incident Records')
                    ->description('Monitor and manage incidents associated with this calamity.')
                    ->schema([
                        EmbeddedTable::make(),
                    ]),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return IncidentForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('incident')
            ->defaultSort('date_occurred', 'desc')
            ->columns([
                TextColumn::make('juridical_name')
                    ->label('Affected MSME')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->formatStateUsing(function (?string $state, Incident $record): string {
                        if (filled($state)) {
                            return $state;
                        }

                        return app(CalamityMonitoringService::class)
                            ->resolveEstablishmentName($record->juridical_id)
                            ?? $record->juridical_id;
                    })
                    ->tooltip(fn (Incident $record): ?string => $record->juridical_id),

                ImageColumn::make('photos')
                    ->label('Photo Evidence')
                    ->disk('public')
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->imageHeight(40)
                    ->placeholder('None')
                    ->toggleable(),

                TextColumn::make('date_occurred')
                    ->label('Date Occurred')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('nature_of_damage')
                    ->label('Damage')
                    ->formatStateUsing(fn (?string $state): string => Incident::natureLabel($state))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'PARTIAL' => 'warning',
                        'TOTAL' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('estimated_cost_of_damages')
                    ->label('Estimated Damage Cost')
                    ->formatStateUsing(
                        fn ($state): string => '₱'.number_format((float) $state, 2)
                    )
                    ->alignEnd()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),

                TextColumn::make('status')
                    ->label('Verification Status')
                    ->formatStateUsing(fn (?string $state): string => Incident::statusLabel($state))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'PENDING_VERIFICATION' => 'warning',
                        'VERIFIED' => 'success',
                        'AID_RELEASED' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('barangay')
                    ->label('Barangay')
                    ->searchable()
                    ->options(fn (): array => $this->getOwnerRecord()
                        ->incident()
                        ->whereNotNull('barangay')
                        ->where('barangay', '!=', '')
                        ->orderBy('barangay')
                        ->distinct()
                        ->pluck('barangay', 'barangay')
                        ->all()),

                SelectFilter::make('status')
                    ->label('Verification Status')
                    ->options(Incident::statusOptions()),

                SelectFilter::make('nature_of_damage')
                    ->label('Nature of Damage')
                    ->options(Incident::natureOptions()),

                Filter::make('date_occurred')
                    ->label('Date Occurred')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['from'] ?? null),
                            fn (Builder $query): Builder => $query->whereDate('date_occurred', '>=', $data['from']),
                        )
                        ->when(
                            filled($data['until'] ?? null),
                            fn (Builder $query): Builder => $query->whereDate('date_occurred', '<=', $data['until']),
                        )),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Create Incident'),

                Action::make('exportExcel')
                    ->label('Export Excel')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('gray')
                    ->action(fn (): BinaryFileResponse => $this->exportReport('xlsx')),

                Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('gray')
                    ->action(fn (): BinaryFileResponse => $this->exportReport('csv')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markVerified')
                        ->label('Mark as Verified')
                        ->icon(Heroicon::CheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'VERIFIED']))
                        ->successNotificationTitle('Selected incidents marked as verified.'),

                    BulkAction::make('markAidReleased')
                        ->label('Mark as Aid Released')
                        ->icon(Heroicon::Gift)
                        ->color('info')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'AID_RELEASED']))
                        ->successNotificationTitle('Selected incidents marked as aid released.'),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordActions([
                Action::make('viewPhotos')
                    ->label('View images')
                    ->icon(Heroicon::Photo)
                    ->color('gray')
                    ->visible(fn (Incident $record): bool => filled($record->photos))
                    ->modalHeading(fn (Incident $record): string => 'Photo Evidence: '.($record->juridical_name ?: $record->juridical_id))
                    ->modalDescription(fn (Incident $record): string => $record->barangay ?: 'Unspecified barangay')
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(function (Incident $record): View {
                        $photos = collect($record->photos ?? [])
                            ->filter(fn (string $photo): bool => Storage::disk('public')->exists($photo))
                            ->map(fn (string $photo): string => Storage::disk('public')->url($photo))
                            ->values()
                            ->all();

                        return view('filament.calamity-monitoring.incident-photos', [
                            'photos' => $photos,
                            'incident' => $record,
                        ]);
                    }),

                EditAction::make(),
            ]);
    }
}
