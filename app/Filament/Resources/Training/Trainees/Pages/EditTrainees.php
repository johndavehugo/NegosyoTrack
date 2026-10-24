<?php

namespace App\Filament\Resources\Training\Trainees\Pages;

use App\Filament\Resources\Training\Trainees\TraineesResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTrainees extends EditRecord
{
    protected static string $resource = TraineesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return TraineesResource::getUrl('view', [
            'record' => $this->getRecord(),
        ]);
    }
}
