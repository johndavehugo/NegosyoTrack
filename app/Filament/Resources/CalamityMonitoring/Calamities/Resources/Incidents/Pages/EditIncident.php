<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Pages;

use App\Filament\Resources\CalamityMonitoring\Calamities\CalamityResource;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\IncidentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIncident extends EditRecord
{
    protected static string $resource = IncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return CalamityResource::getUrl('manage-incidents', [
            'record' => $this->getRecord()->calamity_id,
        ]);
    }
}
