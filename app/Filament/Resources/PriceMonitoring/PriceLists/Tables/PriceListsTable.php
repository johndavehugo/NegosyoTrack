<?php

namespace App\Filament\Resources\PriceMonitoring\PriceLists\Tables;

use App\Models\PriceMonitoring\Commodity;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PriceListsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->sortable()
                    ->label('Category')
                    ->limit(20)
                    ->tooltip(fn (Commodity $commodity) => $commodity->category->name),
                TextColumn::make('brand_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit_of_measure')
                    ->sortable(),
                TextColumn::make('category.agency.name')
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
                    ->relationship('category.agency', 'name')
                    ->multiple(),
                SelectFilter::make('category-filter')
                    ->label('Category')
                    ->relationship('category', 'name')
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
                                0 => 'Inactive'
                            ])
                    ]),
            ]);
            // ->toolbarActions([
            //     BulkActionGroup::make([
            //         DeleteBulkAction::make(),
            //     ]),
            // ]);
    }
}
