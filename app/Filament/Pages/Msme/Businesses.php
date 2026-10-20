<?php

namespace App\Filament\Pages\Msme;

use App\Services\ScimsJuridicalApiService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Filament\Pages\Msme\ViewBusiness;

class Businesses extends Page implements HasTable
{
    use InteractsWithTable;
    protected string $view = 'filament.pages.msme.businesses';
    protected static ?string $navigationLabel = 'Business List';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice2;
    public static function getNavigationGroup(): ?string
    {
        return 'Msme Management';
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->records(function (ScimsJuridicalApiService $scimsApi, int $page, int $recordsPerPage, ?string $search, ?string $sortColumn, ?string $sortDirection): LengthAwarePaginator {
                return $scimsApi->getBusiness(
                    page: $page,
                    recordsPerPage: $recordsPerPage,
                    search: $search,
                    sortColumn: $sortColumn,
                    sortDirection: $sortDirection
                );
            })
            ->columns([
                TextColumn::make('entity_no')
                    ->label('Entity #')
                    ->toggleable(),
                TextColumn::make('bin_etracs')
                    ->label('BIN')
                    ->toggleable(),
                TextColumn::make('juri_name')
                    ->sortable()
                    ->label('Name')
                    ->toggleable(),
                TextColumn::make('status')
                    ->toggleable()
                    ->badge()
                    ->color(fn(string $state): array => match ($state) {
                        'NEW' => Color::Blue,
                        'RENEW' => Color::Green,
                    })
                    ->icon(fn(string $state): Heroicon => match ($state) {
                        'NEW' => Heroicon::Sparkles,
                        'RENEW' => Heroicon::CheckBadge,
                    })
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'RENEW' => 'RENEW',
                        'NEW' => 'NEW',
                    ]),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->searchable()
            ->recordUrl(function ($record) {
                return ViewBusiness::getUrl(['entity_no' => $record['entity_no']]);
            });
    }
}
