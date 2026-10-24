<?php

namespace App\Filament\Resources\Training\Trainees\Tables;

use App\Models\Training\Trainee;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TraineesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    // Display name comes from the SCIMS individuals API,
                    // resolved by api_entity_no; falls back to the entity_no.
                    ->state(fn (Trainee $record): string => $record->individual['full_name'] ?? $record->api_entity_no)
                    ->searchable(query: fn ($query, $search) => $query->where('api_entity_no', 'like', "%{$search}%"))
                    ->limit(45),

                TextColumn::make('api_entity_no')
                    ->label('Entity No.')
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('individual.gender')
                    ->label('Gender')
                    ->state(fn (Trainee $record): string => $record->individual['gender'] ?? '—'),

                TextColumn::make('individual.birth_date')
                    ->label('Birth Date')
                    // null (not a dash) when SCIMS is unavailable — `date()`
                    // would otherwise try to parse the placeholder.
                    ->state(fn (Trainee $record): ?string => $record->individual['birth_date'] ?? null)
                    ->placeholder('—')
                    ->date('M d, Y'),

                TextColumn::make('training.training_program')
                    ->label('Training')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('contact_no')
                    ->label('Contact No.')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('training')
                    ->relationship('training', 'training_program')
                    ->label('Training'),
            ])
            ->recordActions([
                EditAction::make()
                    ->color(Color::Amber),

                DeleteAction::make()
                    ->color(Color::Red),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
