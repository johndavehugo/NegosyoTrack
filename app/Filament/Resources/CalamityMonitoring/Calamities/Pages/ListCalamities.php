<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Pages;

use App\Filament\Resources\CalamityMonitoring\Calamities\CalamityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCalamities extends ListRecords
{
    protected static string $resource = CalamityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
