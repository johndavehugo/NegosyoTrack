<?php

namespace App\Filament\Resources\MsmeManagement\Juridicals\Pages;

use App\Filament\Resources\MsmeManagement\Juridicals\JuridicalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJuridical extends EditRecord
{
    protected static string $resource = JuridicalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
