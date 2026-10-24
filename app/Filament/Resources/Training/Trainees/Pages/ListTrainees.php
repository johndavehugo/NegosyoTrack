<?php

namespace App\Filament\Resources\Training\Trainees\Pages;

use App\Filament\Resources\Training\Trainees\TraineesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainees extends ListRecords
{
    protected static string $resource = TraineesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
