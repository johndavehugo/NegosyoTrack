<?php

namespace App\Filament\Resources\PriceMonitoring\Commodities;

use App\Filament\Resources\PriceMonitoring\Commodities\Pages\ManageCommodities;
use App\Models\PriceMonitoring\Commodity;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommoditiesResource extends Resource
{
    protected static ?string $model = Commodity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::RectangleStack;

    protected static ?string $recordTitleAttribute = 'commodity';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Price Monitoring';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_name')
                    ->required(),

                Select::make('brand_id')
                    ->label('Brand')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required()->unique(),
                    ]),

                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),

                Select::make('juridical_id')
                    ->label('Establishment')
                    ->relationship('juridical', 'name')
                    ->searchable()
                    ->preload(),

                Repeater::make('variants')
                    ->relationship('variants')
                    ->schema([
                        TextInput::make('unit_of_measure')
                            ->label('Unit / Size')
                            ->required(),
                        TextInput::make('srp')
                            ->label('SRP')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                    ])
                    ->columns(2)
                    ->addActionLabel('Add another unit/size')
                    ->defaultItems(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('commodity')
            ->columns([
                TextColumn::make('product_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->sortable()
                    ->label('Category'),
                TextColumn::make('juridical.name')
                    ->label('Establishment')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variants'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->color(Color::Yellow),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCommodities::route('/'),
        ];
    }
}