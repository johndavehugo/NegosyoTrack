<?php

namespace App\Filament\Resources\Training\TrainingPrograms\Tables;

use App\Models\Training\TrainingInfo;
use Filament\Actions\EditAction;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrainingProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('juridical.name')
                    ->label('Business Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('entity_no')
                    ->label('Entity No.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('training_program')
                    ->label('Training Program')
                    ->searchable()
                    ->sortable()
                    ->limit(45)
                    ->tooltip(fn (TrainingInfo $record): string => $record->training_program),

                TextColumn::make('training_type')
                    ->label('Type')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('from_date')
                    ->label('From')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('to_date')
                    ->label('To')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('hours_no')
                    ->label('Hours')
                    ->suffix(' hrs')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('conducted_by')
                    ->label('Conducted By')
                    ->searchable()
                    ->limit(35)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sector')
                    ->label('Sector')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('graduated_at')
                    ->label('Graduated')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): array => match ($state) {
                        'COMPLETED'  => Color::Green,
                        'ONGOING'    => Color::Amber,
                        'DROPPED'    => Color::Red,
                        'INCOMPLETE' => Color::Orange,
                        default      => Color::Gray,
                    })
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'COMPLETED'  => 'Completed',
                        'ONGOING'    => 'Ongoing',
                        'DROPPED'    => 'Dropped',
                        'INCOMPLETE' => 'Incomplete',
                    ]),

                SelectFilter::make('sector')
                    ->label('Sector')
                    ->options(fn () => TrainingInfo::whereNotNull('sector')
                        ->distinct()
                        ->pluck('sector', 'sector')
                        ->toArray()
                    ),

                Filter::make('from_date')
                    ->label('This Year')
                    ->query(fn (Builder $query) => $query->whereYear('from_date', now()->year)),
            ])

            ->recordActions([
                EditAction::make()
                    ->color(Color::Amber),
            ]);
    }
}
