<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Tables;

use App\Services\CalamityMonitoringService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;

class AffectedMsmeTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->records(function (
                CalamityMonitoringService $service,
                int $page,
                int $recordsPerPage,
                ?string $search,
                ?string $sortColumn,
                ?string $sortDirection,
            ): LengthAwarePaginator {
                return $service->paginateEstablishments(
                    search: $search,
                    page: $page,
                    recordsPerPage: $recordsPerPage,
                    sortColumn: $sortColumn,
                    sortDirection: $sortDirection,
                );
            })
            ->defaultSort('juri_name')
            ->columns([
                TextColumn::make('juri_name')
                    ->label('MSME')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('barangay')
                    ->label('Barangay')
                    ->searchable(),

                TextColumn::make('street')
                    ->label('Street')
                    ->searchable(),

                TextColumn::make('line_of_industry')
                    ->label('Line of Industry')
                    ->toggleable()
                    ->searchable(),
            ])
            ->searchable();
    }
}
