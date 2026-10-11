<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IncidentForm
{
    public static function fields(): array
    {
        return [
            Select::make('juridical_id')
                ->relationship('juridical', 'name')
                ->label('Affected MSME')
                ->required(),

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
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::fields())
            ->columns(3);
    }
}
