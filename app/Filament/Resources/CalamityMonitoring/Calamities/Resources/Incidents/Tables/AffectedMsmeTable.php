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
                ?string $sortColumn,
                ?string $sortDirection,
            ) use ($table): LengthAwarePaginator {
                $arguments = $table->getArguments();

                return $service->paginateEstablishments(
                    search: $arguments['search'] ?? null,
                    page: $page,
                    recordsPerPage: $recordsPerPage,
                    sortColumn: $sortColumn ?? 'juri_name',
                    sortDirection: $sortDirection ?? 'asc',
                    barangay: $arguments['barangay'] ?? null,
                    industry: $arguments['industry'] ?? null,
                    excluded: $arguments['excluded'] ?? [],
                );
            })
            ->defaultSort('juri_name')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->emptyStateHeading('No MSMEs match the current filters.')
            ->emptyStateDescription('Try a different barangay, industry, or search term.')
            ->columns([
                TextColumn::make('juri_name')
                    ->label('MSME')
                    ->sortable()
                    ->wrap(),

                TextColumn::make('barangay')
                    ->label('Barangay')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('line_of_industry')
                    ->label('Line of Industry')
                    ->sortable()
                    ->toggleable()
                    ->wrap(),

                TextColumn::make('street')
                    ->label('Street')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
            ]);
    }
}
