<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Pages;

use App\Filament\Resources\CalamityMonitoring\Calamities\CalamityResource;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\IncidentResource;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Schemas\IncidentForm;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Tables\AffectedMsmeTable;
use App\Models\CalamityMonitoring\Incident;
use App\Services\CalamityMonitoringService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TableSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateIncident extends CreateRecord
{
    protected static string $resource = IncidentResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Affected MSMEs')
                    ->description('Search the SCIMS registry to narrow the list, select every affected MSME, then enter the estimated cost of damage and attach photo evidence for each.')
                    ->columns(2)
                    ->schema([
                        TableSelect::make('juridical_ids')
                            ->label('Select affected MSMEs')
                            ->multiple()
                            ->tableConfiguration(AffectedMsmeTable::class)
                            ->required()
                            ->live()
                            ->columnSpanFull()
                            ->afterStateUpdated(function (?array $state, Get $get, Set $set): void {
                                $state = array_values($state ?? []);

                                $existing = collect($get('damages') ?? [])
                                    ->keyBy(fn (array $item): string => (string) ($item['juridical_id'] ?? ''));

                                $snapshots = app(CalamityMonitoringService::class)
                                    ->snapshotEstablishments($state);

                                $set('damages', collect($state)
                                    ->map(function ($id) use ($existing, $snapshots): array {
                                        $current = $existing->get((string) $id);

                                        return [
                                            'juridical_id' => $id,
                                            'juridical_name' => $snapshots[$id]['name'] ?? $id,
                                            'barangay' => $snapshots[$id]['barangay'] ?? null,
                                            'estimated_cost_of_damages' => is_array($current)
                                                ? ($current['estimated_cost_of_damages'] ?? null)
                                                : null,
                                            'photos' => is_array($current)
                                                ? ($current['photos'] ?? [])
                                                : [],
                                        ];
                                    })
                                    ->values()
                                    ->all());
                            }),

                        Repeater::make('damages')
                            ->hiddenLabel()
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(2)
                            ->schema([
                                Hidden::make('juridical_id'),

                                Hidden::make('juridical_name'),

                                Hidden::make('barangay'),

                                Placeholder::make('juridical_label')
                                    ->label('Affected MSME')
                                    ->content(fn (Get $get): string => $get('juridical_name')
                                        ?: ($get('juridical_id') ?? '—')),

                                TextInput::make('estimated_cost_of_damages')
                                    ->label('Estimated Cost of Damage')
                                    ->prefix('�,�')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(Incident::MAX_ESTIMATED_COST)
                                    ->required(),

                                IncidentForm::photoEvidenceField(),
                            ])
                            ->hidden(fn (Get $get): bool => blank($get('juridical_ids')))
                            ->columnSpanFull(),
                    ]),

                Section::make('Incident Details')
                    ->description('These details are applied to every affected MSME you selected above.')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('date_occurred')
                            ->label('Date of Occurrence')
                            ->required(),

                        Select::make('nature_of_damage')
                            ->label('Nature of Damage')
                            ->options([
                                'PARTIAL' => 'Partial Damage',
                                'TOTAL' => 'Total Damage',
                            ])
                            ->required(),

                        Select::make('status')
                            ->label('Verification Status')
                            ->options([
                                'PENDING_VERIFICATION' => 'Pending Verification',
                                'VERIFIED' => 'Verified',
                                'AID_RELEASED' => 'Aid Released',
                            ])
                            ->default('PENDING_VERIFICATION')
                            ->required(),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $calamity = $this->getParentRecord();

        $shared = [
            'date_occurred' => $data['date_occurred'],
            'nature_of_damage' => $data['nature_of_damage'],
            'status' => $data['status'],
            'remarks' => $data['remarks'] ?? null,
        ];

        $last = null;

        foreach ($data['damages'] ?? [] as $damage) {
            $last = $calamity->incident()->create([
                ...$shared,
                'juridical_id' => $damage['juridical_id'],
                'juridical_name' => $damage['juridical_name'] ?? $damage['juridical_id'],
                'barangay' => $damage['barangay'] ?? null,
                'estimated_cost_of_damages' => $damage['estimated_cost_of_damages'],
                'photos' => $damage['photos'] ?? [],
            ]);
        }

        if ($last === null) {
            throw ValidationException::withMessages([
                'data.juridical_ids' => 'Select at least one affected MSME.',
            ]);
        }

        return $last;
    }

    protected function getRedirectUrl(): string
    {
        return CalamityResource::getUrl('manage-incidents', [
            'record' => $this->getParentRecord(),
        ]);
    }
}
