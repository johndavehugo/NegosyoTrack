<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CalamityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('calamity_type')
                    ->required()
                    ->options([
                        'TYPHOON' => 'Typhoon',
                        'EARTHQUAKE' => 'Earthquake',
                        'LANDSLIDE' => 'Landslide',
                        'FLOOD' => 'Flood',
                    ]),
                DatePicker::make('declaration_date')
                    ->required()
                    ->format('Y/m/d'),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
