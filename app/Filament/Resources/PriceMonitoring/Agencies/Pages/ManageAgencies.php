<?php

namespace App\Filament\Resources\PriceMonitoring\Agencies\Pages;

use App\Filament\Resources\PriceMonitoring\Agencies\AgenciesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAgencies extends ManageRecords
{
    protected static string $resource = AgenciesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
