<?php

namespace App\Filament\Pages\EconomicMap;

use App\Filament\Pages\Msme\ViewBusiness;
use App\Services\EconomicMap\ScimsBusinessClient;
use App\Services\ScimsJuridicalApiService;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;

class BusinessLocations extends BaseMapPage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Business Locations';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::MapPin;

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.economic-map.pages.business-locations';

    public function getHeading(): string
    {
        return 'San Carlos City Business Locations';
    }

    public function getSubheading(): ?string
    {
        return 'Business and address records from the SCIMS registry.';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public static function addressLine(array $record): string
    {
        return app(ScimsJuridicalApiService::class)->formatAddress(
            upblb: $record['upblb_num'] ?? null,
            subdivision: $record['subdivision'] ?? null,
            street: $record['street'] ?? null,
            barangay: $record['barangay'] ?? null,
            city: $record['city'] ?? null,
            province: $record['province'] ?? null,
            region: $record['region'] ?? null,
        ) ?: '—';
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->records(function (ScimsBusinessClient $scimsApi, int $page, int $recordsPerPage, ?string $search, ?string $sortColumn, ?string $sortDirection): LengthAwarePaginator {
                return $scimsApi->page(
                    page: $page,
                    perPage: $recordsPerPage,
                    search: $search,
                    sortColumn: $sortColumn,
                    sortDirection: $sortDirection,
                );
            })
            ->columns([
                TextColumn::make('entity_no')
                    ->label('Entity No.')
                    ->toggleable(),

                TextColumn::make('juri_name')
                    ->label('Business Name')
                    ->toggleable()
                    ->limit(35)
                    ->tooltip(fn (array $record): ?string => $record['juri_name'] ?? null),

                TextColumn::make('address')
                    ->label('Address')
                    ->toggleable()
                    ->limit(50)
                    ->tooltip(fn (array $record): string => static::addressLine($record))
                    ->state(fn (array $record): string => static::addressLine($record)),

                TextColumn::make('barangay')
                    ->label('Barangay')
                    ->toggleable(),

                TextColumn::make('line_of_industry')
                    ->label('Industry')
                    ->toggleable()
                    ->limit(32)
                    ->tooltip(fn (array $record): ?string => $record['line_of_industry'] ?? null),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(fn (?string $state): Heroicon => match ($state) {
                        'NEW' => Heroicon::Sparkles,
                        'RENEW' => Heroicon::CheckBadge,
                        default => Heroicon::QuestionMarkCircle,
                    })
                    ->color(fn (?string $state): array => match ($state) {
                        'NEW' => Color::Blue,
                        'RENEW' => Color::Green,
                        default => Color::Gray,
                    }),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->searchable()
            ->recordUrl(fn (array $record): string => ViewBusiness::getUrl([
                'entity_no' => $record['entity_no'],
            ]));
    }

    protected function loadData(): void
    {
        //
    }
}
