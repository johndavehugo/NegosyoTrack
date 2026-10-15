<?php

namespace App\Filament\Resources\PriceMonitoring\PriceLists\Tables;

use App\Models\PriceMonitoring\Agency;
use App\Models\PriceMonitoring\Category;
use App\Models\PriceMonitoring\CommodityVariant;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PriceListsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('commodity.product_name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('commodity.brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('commodity.category.name')
                    ->sortable()
                    ->label('Category')
                    ->limit(20)
                    ->tooltip(fn (CommodityVariant $record) => $record->commodity->category->name ?? null),
                TextColumn::make('unit_of_measure')
                    ->label('Unit / Size')
                    ->sortable(),
                TextColumn::make('commodity.category.agency.name')
                    ->sortable()
                    ->label('Agency'),
                TextColumn::make('srp')
                    ->label('SRP')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('prevailing_price')
                    ->label('Prevailing Price')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('agency-filter')
                    ->label('Agency')
                    ->options(fn () => Agency::pluck('name', 'id'))
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['values'] ?? null,
                            fn ($q, $values) => $q->whereHas(
                                'commodity.category',
                                fn ($q2) => $q2->whereIn('agency_id', $values)
                            )
                        );
                    })
                    ->multiple(),
                SelectFilter::make('category-filter')
                    ->label('Category')
                    ->options(fn () => Category::pluck('name', 'id'))
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['values'] ?? null,
                            fn ($q, $values) => $q->whereHas(
                                'commodity',
                                fn ($q2) => $q2->whereIn('category_id', $values)
                            )
                        );
                    })
                    ->multiple(),
            ])
            ->recordUrl(null)
            ->recordAction('edit_price_list')
            ->actions([
                EditAction::make('edit_price_list')
                    ->color(Color::Yellow)
                    ->modal()
                    ->schema([
                        TextInput::make('srp')
                            ->label('SRP')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('prevailing_price')
                            ->label('Prevailing Price')
                            ->numeric()
                            ->minValue(0),
                    ]),
            ]);
    }
}
