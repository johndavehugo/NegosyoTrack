<?php

namespace App\Filament\Resources\PriceMonitoring\Categories\Pages;

use App\Filament\Resources\PriceMonitoring\Categories\CategoriesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
