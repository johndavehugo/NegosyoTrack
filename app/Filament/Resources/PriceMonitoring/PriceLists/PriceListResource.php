<?php

namespace App\Filament\Resources\PriceMonitoring\PriceLists;

use App\Filament\Resources\PriceMonitoring\PriceLists\Pages\CreatePriceList;
use App\Filament\Resources\PriceMonitoring\PriceLists\Pages\EditPriceList;
use App\Filament\Resources\PriceMonitoring\PriceLists\Pages\ListPriceLists;
use App\Filament\Resources\PriceMonitoring\PriceLists\Schemas\PriceListForm;
use App\Filament\Resources\PriceMonitoring\PriceLists\Tables\PriceListsTable;
use App\Models\PriceMonitoring\Commodity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PriceListResource extends Resource
{
    protected static ?string $model = Commodity::class;
    protected static ?string $pluralModelLabel = 'Price Lists';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::PresentationChartLine;
    protected static ?string $recordTitleAttribute = 'price_list';
    protected static ?string $navigationLabel = 'Price List';
    protected static ?int $navigationSort = 1;
    public static function getNavigationGroup(): ?string
    {
        return 'Price Monitoring';
    }

    public static function form(Schema $schema): Schema
    {
        return PriceListForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PriceListsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPriceLists::route('/'),
            'create' => CreatePriceList::route('/create'),
            'edit' => EditPriceList::route('/{record}/edit'),
        ];
    }
}
