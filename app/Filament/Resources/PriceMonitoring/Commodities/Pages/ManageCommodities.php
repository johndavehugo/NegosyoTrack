<?php

namespace App\Filament\Resources\PriceMonitoring\Commodities\Pages;

use App\Filament\Resources\PriceMonitoring\Commodities\CommoditiesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCommodities extends ManageRecords
{
    protected static string $resource = CommoditiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
