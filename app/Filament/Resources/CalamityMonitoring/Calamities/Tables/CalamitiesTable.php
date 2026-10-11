<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Tables;

use App\Filament\Resources\CalamityMonitoring\Calamities\CalamityResource;
use App\Models\CalamityMonitoring\Calamity;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CalamitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('calamity_type')
                    ->searchable(),
                TextColumn::make('declaration_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('description'),
            ])
            ->filters([
                SelectFilter::make('calamity_type')
                    ->options([
                        'TYPHOON' => 'Typhoon',
                        'EARTHQUAKE' => 'Earthquake',
                        'LANDSLIDE' => 'Landslide',
                        'FLOOD' => 'Flood',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('manage-incidents')
                    ->url(fn (Calamity $record): string => CalamityResource::getUrl('manage-incidents', [
                        'record' => $record->id,
                    ])),
            ]);
        // ->toolbarActions([
        //     BulkActionGroup::make([
        //         DeleteBulkAction::make(),
        //     ]),
        // ]);
    }
}
