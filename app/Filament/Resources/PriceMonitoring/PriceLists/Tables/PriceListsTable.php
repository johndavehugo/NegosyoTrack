<?php

namespace App\Filament\Resources\PriceMonitoring\PriceLists\Tables;

use App\Models\PriceMonitoring\Agency;
use App\Models\PriceMonitoring\Category;
use App\Models\PriceMonitoring\CommodityVariant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\IconColumn;
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
                TextColumn::make('srp'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable()
                    ->label('Active')
                    ->color(fn ($state): string => $state ? 'success' : 'danger'),
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
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Select::make('is_active')
                            ->required()
                            ->options([
                                1 => 'Active',
                                0 => 'Inactive',
                            ]),
                    ]),
            ]);
    }
}