<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Schemas;

use App\Models\CalamityMonitoring\Incident;
use App\Services\CalamityMonitoringService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class IncidentForm
{
    public static function fields(): array
    {
        return [
            Select::make('juridical_id')
                ->label('Affected MSME')
                ->searchable()
                ->required()
                ->placeholder('Search an establishment')
                ->getSearchResultsUsing(
                    fn (string $search): array => app(CalamityMonitoringService::class)->searchEstablishments($search)
                )
                ->getOptionLabelUsing(
                    fn (?string $value): ?string => app(CalamityMonitoringService::class)->resolveEstablishmentName($value) ?? $value
                )
                ->live()
                ->afterStateUpdated(function (?string $state, Set $set): void {
                    $establishment = app(CalamityMonitoringService::class)->findEstablishment($state);

                    $set('juridical_name', $establishment['juri_name'] ?? $state);
                    $set('barangay', $establishment['barangay'] ?? null);
                }),

            Hidden::make('juridical_name'),

            Hidden::make('barangay'),

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

            TextInput::make('estimated_cost_of_damages')
                ->label('Estimated Cost of Damages')
                ->numeric()
                ->minValue(0)
                ->maxValue(Incident::MAX_ESTIMATED_COST)
                ->prefix('₱')
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

            Section::make('Photo Evidence')
                ->description('Attach up to 5 photos showing the damage to this MSME.')
                ->schema([
                    static::photoEvidenceField(),
                ])
                ->columns(1)
                ->columnSpanFull(),
        ];
    }

    public static function photoEvidenceField(): FileUpload
    {
        return FileUpload::make('photos')
            ->label('Photos')
            ->multiple()
            ->image()
            ->acceptedFileTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ])
            ->disk('public')
            ->visibility('public')
            ->directory('calamity-incidents/photos')
            ->maxFiles(5)
            ->maxSize(5120)
            ->reorderable()
            ->appendFiles()
            ->openable()
            ->downloadable()
            ->panelLayout('grid')
            ->imagePreviewHeight('140')
            ->columnSpanFull();
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::fields())
            ->columns(3);
    }
}
