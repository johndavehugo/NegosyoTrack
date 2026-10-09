<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Pages;

use App\Filament\Resources\CalamityMonitoring\Calamities\CalamityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCalamity extends EditRecord
{
    protected static string $resource = CalamityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
