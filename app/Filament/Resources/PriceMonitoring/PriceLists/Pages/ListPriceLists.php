<?php

namespace App\Filament\Resources\PriceMonitoring\PriceLists\Pages;

use App\Filament\Resources\PriceMonitoring\PriceLists\PriceListResource;
use App\Filament\Resources\PriceMonitoring\PriceLists\Widgets\PriceListOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPriceLists extends ListRecords
{
    protected static string $resource = PriceListResource::class;

     protected static ?string $title = 'Price Monitoring';

     protected function getHeaderWidgets(): array
    {
        return [    
            PriceListOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
