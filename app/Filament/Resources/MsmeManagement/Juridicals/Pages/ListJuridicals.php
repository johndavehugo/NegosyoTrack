<?php

namespace App\Filament\Resources\MsmeManagement\Juridicals\Pages;

use App\Filament\Resources\MsmeManagement\Juridicals\JuridicalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJuridicals extends ListRecords
{
    protected static string $resource = JuridicalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
