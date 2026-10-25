<?php

namespace App\Filament\Pages\Msme;

use App\Models\Msme\Negosyo;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Businesses extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.msme.businesses';

    protected static ?string $navigationLabel = 'Business List';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice2;

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            return Cache::remember('total_businesses', 300, function () {

                $response = Http::timeout(5)->get('https://cictd.app/api/scims/juridical');

                if ($response && $response->successful()) {
                    return $response->json('meta.total');
                }

                return null;
            });
        } catch (\Throwable $e) {
            logger()->error('Badge API Breakdown: '.$e->getMessage());

            return null;
        }
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Total Registered Businesses';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Msme Management';
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->records(function (ScimsJuridicalApiService $scimsApi, int $page, int $recordsPerPage, ?string $search, ?string $sortColumn, ?string $sortDirection): LengthAwarePaginator {
                $businesses = $scimsApi->getBusiness(
                    page: $page,
                    recordsPerPage: $recordsPerPage,
                    search: $search,
                    sortColumn: $sortColumn,
                    sortDirection: $sortDirection
                );

                $negosyos = Negosyo::query()
                    ->whereIn(
                        'api_entity_no',
                        $businesses->getCollection()->pluck('entity_no')->filter()->values()->all()
                    )
                    ->get()
                    ->keyBy('api_entity_no');

                $businesses->setCollection(
                    $businesses->getCollection()->map(function (array $business) use ($negosyos): array {
                        $business['category'] = $negosyos->get($business['entity_no'] ?? '')?->category ?: null;

                        return $business;
                    })
                );

                return $businesses;
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
                    ->color(fn (string $state): array => match ($state) {
                        'NEW' => Color::Blue,
                        'RENEW' => Color::Green,
                    })
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        'NEW' => Heroicon::Sparkles,
                        'RENEW' => Heroicon::CheckBadge,
                    })
                    ->alignCenter(),
                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->placeholder('uncategorized')
                    ->color(fn (?string $state): array => match ($state) {
                        'MICRO' => Color::Green,
                        'SMALL' => Color::Blue,
                        'MEDIUM' => Color::Amber,
                        default => Color::Gray,
                    })
                    ->alignCenter()
                    ->toggleable(),
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
