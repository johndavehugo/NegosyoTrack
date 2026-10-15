<?php

namespace App\Filament\Resources\PriceMonitoring\PriceLists\Schemas;

use App\Models\PriceMonitoring\Commodity;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PriceListForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('commodity_id')
                    ->label('Commodity')
                    ->options(fn () => Commodity::pluck('product_name', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('unit_of_measure')
                    ->label('Unit / Size')
                    ->required(),
                TextInput::make('srp')
                    ->label('SRP')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
                TextInput::make('prevailing_price')
                    ->label('Prevailing Price')
                    ->numeric()
                    ->minValue(0),
            ]);
    }
}
