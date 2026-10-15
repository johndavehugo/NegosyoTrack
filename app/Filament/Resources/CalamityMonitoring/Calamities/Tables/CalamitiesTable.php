<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Tables;

use App\Filament\Resources\CalamityMonitoring\Calamities\CalamityResource;
use App\Models\CalamityMonitoring\Calamity;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
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

                TextColumn::make('incident_count')
                    ->label('Affected MSMEs')
                    ->counts('incident')
                    ->badge()
                    ->color('warning')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('incident_sum_estimated_cost_of_damages')
                    ->label('Total Damage')
                    ->sum('incident', 'estimated_cost_of_damages')
                    ->formatStateUsing(
                        fn ($state): string => '₱'.number_format((float) $state, 2)
                    )
                    ->alignEnd()
                    ->sortable(),
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
                    ]))
                    ->color(Color::Blue)
                    ->icon(Heroicon::DocumentText),
            ]);
        // ->toolbarActions([
        //     BulkActionGroup::make([
        //         DeleteBulkAction::make(),
        //     ]),
        // ]);
    }
}
