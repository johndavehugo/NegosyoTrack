<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Tables;

use App\Models\MsmeManagement\Address;
use App\Models\MsmeManagement\Juridical;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AffectedMsmeTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Juridical::query())
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('MSME')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('address.barangay')
                    ->label('Barangay')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('address.street')
                    ->label('Street')
                    ->searchable(),

                TextColumn::make('line_of_industry')
                    ->label('Line of Industry')
                    ->toggleable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('barangay')
                    ->label('Barangay')
                    ->searchable()
                    ->options(fn (): array => Address::query()
                        ->whereHas('juridical')
                        ->whereNotNull('barangay')
                        ->where('barangay', '!=', '')
                        ->orderBy('barangay')
                        ->distinct()
                        ->pluck('barangay', 'barangay')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->whereHas(
                            'address',
                            fn (Builder $query): Builder => $query->where('barangay', $data['value']),
                        ),
                    )),

                SelectFilter::make('street')
                    ->label('Street')
                    ->searchable()
                    ->options(fn (): array => Address::query()
                        ->whereHas('juridical')
                        ->whereNotNull('street')
                        ->where('street', '!=', '')
                        ->orderBy('street')
                        ->distinct()
                        ->pluck('street', 'street')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        filled($data['value'] ?? null),
                        fn (Builder $query): Builder => $query->whereHas(
                            'address',
                            fn (Builder $query): Builder => $query->where('street', $data['value']),
                        ),
                    )),
            ]);
    }
}
