<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Pages;

use App\Filament\Resources\CalamityMonitoring\Calamities\CalamityResource;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\IncidentResource;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Schemas\IncidentForm;
use App\Filament\Widgets\IncidentStatsWidget;
use App\Models\MsmeManagement\Address;
use BackedEnum;
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
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
                TextColumn::make('juridical.name')
                    ->label('Affected MSME')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date_occurred')
                    ->label('Date Occurred')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('nature_of_damage')
                    ->label('Damage')
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            'PARTIAL' => 'Partial Damage',
                            'TOTAL' => 'Total Damage',
                            default => str($state ?? 'Unknown')
                                ->replace('_', ' ')
                                ->title()
                                ->toString(),
                        }
                    )
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
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            'PENDING_VERIFICATION' => 'Pending Verification',
                            'VERIFIED' => 'Verified',
                            'AID_RELEASED' => 'Aid Released',
                            default => str($state ?? 'Unknown')
                                ->replace('_', ' ')
                                ->title()
                                ->toString(),
                        }
                    )
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
                    ->options(function (): array {
                        return Address::query()
                            ->whereHas(
                                'juridical.incident',
                                fn (Builder $query): Builder => $query->whereBelongsTo($this->getOwnerRecord()),
                            )
                            ->whereNotNull('barangay')
                            ->where('barangay', '!=', '')
                            ->orderBy('barangay')
                            ->distinct()
                            ->pluck('barangay', 'barangay')
                            ->all();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $query): Builder => $query->whereHas(
                                'juridical.address',
                                fn (Builder $query): Builder => $query->where('barangay', $data['value']),
                            ),
                        );
                    }),

                SelectFilter::make('status')
                    ->label('Verification Status')
                    ->options([
                        'PENDING_VERIFICATION' => 'Pending Verification',
                        'VERIFIED' => 'Verified',
                        'AID_RELEASED' => 'Aid Released',
                    ]),

                SelectFilter::make('nature_of_damage')
                    ->label('Nature of Damage')
                    ->options([
                        'PARTIAL' => 'Partial Damage',
                        'TOTAL' => 'Total Damage',
                    ]),

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
                EditAction::make(),
            ]);
    }
}
