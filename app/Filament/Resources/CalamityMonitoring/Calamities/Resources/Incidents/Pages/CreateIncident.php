<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Pages;

use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\IncidentResource;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Tables\AffectedMsmeTable;
use App\Models\MsmeManagement\Juridical;
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
                    ->description('Filter by barangay or street to narrow the list, select every affected MSME, then enter the estimated cost of damage for each.')
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
                                $existing = collect($get('damages') ?? [])
                                    ->mapWithKeys(fn (array $item): array => [
                                        (string) ($item['juridical_id'] ?? '') => $item['estimated_cost_of_damages'] ?? null,
                                    ]);

                                $set('damages', collect($state ?? [])
                                    ->map(fn ($id): array => [
                                        'juridical_id' => $id,
                                        'estimated_cost_of_damages' => $existing->get((string) $id),
                                    ])
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

                                Placeholder::make('juridical_label')
                                    ->label('Affected MSME')
                                    ->content(fn (Get $get): string => Juridical::find($get('juridical_id'))?->name ?? '—'),

                                TextInput::make('estimated_cost_of_damages')
                                    ->label('Estimated Cost of Damage')
                                    ->prefix('₱')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),
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
                'estimated_cost_of_damages' => $damage['estimated_cost_of_damages'],
            ]);
        }

        if ($last === null) {
            throw ValidationException::withMessages([
                'data.juridical_ids' => 'Select at least one affected MSME.',
            ]);
        }

        return $last;
    }
}
