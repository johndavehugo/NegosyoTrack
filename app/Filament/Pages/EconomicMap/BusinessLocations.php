<?php

namespace App\Filament\Pages\EconomicMap;

use App\Filament\Pages\Msme\ViewBusiness;
use App\Models\Msme\Location;
use App\Services\EconomicMap\ScimsBusinessClient;
use App\Services\EconomicMap\StreetDirectory;
use App\Services\EconomicMapService;
use App\Services\ScimsJuridicalApiService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class BusinessLocations extends BaseMapPage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Business Locations';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::MapPin;

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.economic-map.pages.business-locations';

    public ?string $pickerEntityNo = null;

    public ?array $pickerEntry = null;

    public ?array $pickerBounds = null;

    public ?array $pickerFocus = null;

    public ?array $pickerSearchFocus = null;

    public bool $pickerExistingOutsideCity = false;

    public ?string $pickerFocusLabel = null;

    /** @var array<int, string>|null */
    protected ?array $locatedEntityNos = null;

    public function getHeading(): string
    {
        return 'San Carlos City Business Locations';
    }

    public function getSubheading(): ?string
    {
        return 'Business and address records from the SCIMS registry. Set coordinates once and they become map pins.';
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

    /**
     * A business is located when its SCIMS entity number exists in the
     * `locations` table. Coordinates are irrelevant here: the row's
     * presence is the source of truth.
     */
    public function hasLocation(?string $entityNo): bool
    {
        $entityNo = trim((string) $entityNo);

        if ($entityNo === '') {
            return false;
        }

        return in_array($entityNo, $this->locatedEntityNos(), true);
    }

    /**
     * @return array<int, string>
     */
    protected function locatedEntityNos(): array
    {
        return $this->locatedEntityNos ??= Location::query()
            ->pluck('api_entity_no')
            ->map(fn (mixed $entityNo): string => trim((string) $entityNo))
            ->all();
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

                TextColumn::make('location_status')
                    ->label('Location')
                    ->toggleable()
                    ->badge()
                    ->state(fn (array $record): string => $this->hasLocation($record['entity_no'] ?? null) ? 'Set' : 'Unset')
                    ->icon(fn (string $state): Heroicon => $state === 'Set' ? Heroicon::MapPin : Heroicon::ExclamationTriangle)
                    ->color(fn (string $state): array => $state === 'Set' ? Color::Green : Color::Amber),

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
            ->recordActions([
                Action::make('setLocation')
                    ->label(fn (array $record): string => $this->hasLocation($record['entity_no'] ?? null)
                        ? 'Update location'
                        : 'Set location')
                    ->icon(Heroicon::MapPin)
                    ->color(Color::Blue)
                    ->action(fn (array $record) => $this->openPicker($record)),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->searchable()
            ->recordUrl(fn (array $record): string => ViewBusiness::getUrl([
                'entity_no' => $record['entity_no'],
            ]));
    }

    public function openPicker(array $record): void
    {
        $entityNo = trim((string) ($record['entity_no'] ?? ''));

        if ($entityNo === '') {
            Notification::make()
                ->title('No entity number')
                ->body('This SCIMS record cannot be linked to a location.')
                ->warning()
                ->send();

            return;
        }

        $location = Location::query()->where('api_entity_no', $entityNo)->first();
        $service = app(EconomicMapService::class);
        $barangay = $record['barangay'] ?? null;
        $barangayLabel = trim((string) $barangay);
        $barangayCenter = $service->barangayCenter($barangay);

        $hasExistingLocation = $location !== null
            && $location->latitude !== null
            && $location->longitude !== null;

        $existingLat = $hasExistingLocation ? (float) $location->latitude : null;
        $existingLng = $hasExistingLocation ? (float) $location->longitude : null;

        $existingIsLocal = $existingLat !== null
            && $existingLng !== null
            && $this->withinCityBounds($existingLat, $existingLng);

        $cityBounds = config('economic-map.city_bounds', []);

        $this->pickerEntityNo = $entityNo;
        $this->pickerExistingOutsideCity = $hasExistingLocation && ! $existingIsLocal;
        $this->pickerEntry = [
            'business_name' => $record['juri_name'] ?? $record['trade_name'] ?? '—',
            'entity_no' => $entityNo,
            'street' => $record['street'] ?? null,
            'barangay' => $barangay,
            'city' => $record['city'] ?? null,
            'latitude' => $existingIsLocal ? $existingLat : null,
            'longitude' => $existingIsLocal ? $existingLng : null,
        ];
        $this->pickerBounds = [
            (float) ($cityBounds['min_lat'] ?? 10.25),
            (float) ($cityBounds['min_lng'] ?? 123.10),
            (float) ($cityBounds['max_lat'] ?? 10.70),
            (float) ($cityBounds['max_lng'] ?? 123.55),
        ];
        $this->pickerFocus = $existingIsLocal
            ? [$existingLat, $existingLng]
            : $barangayCenter;
        $this->pickerSearchFocus = $barangayCenter;
        $this->pickerFocusLabel = $existingIsLocal
            ? $entityNo
            : ($barangayLabel !== '' ? $barangayLabel : 'San Carlos City');
    }

    public function closePicker(): void
    {
        $this->reset(
            'pickerEntityNo',
            'pickerEntry',
            'pickerBounds',
            'pickerFocus',
            'pickerSearchFocus',
            'pickerExistingOutsideCity',
            'pickerFocusLabel',
        );
    }

    /**
     * Configured barangay centres the picker can resolve locally.
     *
     * Nominatim's OSM data for San Carlos City names the poblacion
     * barangays ambiguously, so a query such as "Barangay I" returns
     * Barangay II/V instead. These entries let the picker match the
     * barangay by name before it asks the external geocoder.
     *
     * @return array<int, array{name: string, lat: float, lng: float}>
     */
    public function pickerBarangays(): array
    {
        return collect(config('economic-map.barangay_centroids', []))
            ->map(function (array $coordinates, string $key): array {
                $label = preg_match('/^(i|ii|iii|iv|v|vi)$/', $key) === 1
                    ? 'Barangay '.strtoupper($key)
                    : 'Barangay '.Str::title($key);

                return [
                    'name' => $label,
                    'lat' => (float) ($coordinates[0] ?? 0),
                    'lng' => (float) ($coordinates[1] ?? 0),
                ];
            })
            ->filter(fn (array $place): bool => $place['lat'] != 0.0 && $place['lng'] != 0.0)
            ->values()
            ->all();
    }

    /**
     * Searchable streets, subdivisions, sitios and puroks built from OSM and
     * the SCIMS address snapshot. The picker ranks these locally so users do
     * not have to wait for an external geocoder for every street.
     *
     * @return array<int, array<string, mixed>>
     */
    public function pickerStreets(): array
    {
        return app(StreetDirectory::class)->pickerEntries();
    }

    public function saveLocation(mixed $lat = null, mixed $lng = null): void
    {
        if (! is_numeric($lat) || ! is_numeric($lng)) {
            Notification::make()
                ->title('Coordinates required')
                ->body('Click the map or type both latitude and longitude.')
                ->warning()
                ->send();

            return;
        }

        $lat = (float) $lat;
        $lng = (float) $lng;

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            Notification::make()
                ->title('Coordinates out of range')
                ->body('Latitude must be within -90..90 and longitude within -180..180.')
                ->warning()
                ->send();

            return;
        }

        if (! $this->withinCityBounds($lat, $lng)) {
            Notification::make()
                ->title('Outside San Carlos City')
                ->body('This map only accepts locations inside San Carlos City. Search for the place or click within the city.')
                ->warning()
                ->send();

            return;
        }

        if ($this->pickerEntityNo === null) {
            Notification::make()
                ->title('No business selected')
                ->warning()
                ->send();

            return;
        }

        $location = Location::query()->firstOrNew([
            'api_entity_no' => $this->pickerEntityNo,
        ]);

        if (! $location->exists) {
            $location->id = (string) Str::uuid();
        }

        $location->latitude = $lat;
        $location->longitude = $lng;
        $location->save();

        $this->locatedEntityNos = null;

        Notification::make()
            ->title('Location saved')
            ->body("Coordinates linked to {$this->pickerEntityNo}.")
            ->success()
            ->send();

        $this->closePicker();
    }

    protected function withinCityBounds(float $lat, float $lng): bool
    {
        $bounds = config('economic-map.city_bounds');

        if (! is_array($bounds)
            || ! isset($bounds['min_lat'], $bounds['max_lat'], $bounds['min_lng'], $bounds['max_lng'])) {
            return true;
        }

        return $lat >= (float) $bounds['min_lat']
            && $lat <= (float) $bounds['max_lat']
            && $lng >= (float) $bounds['min_lng']
            && $lng <= (float) $bounds['max_lng'];
    }

    protected function loadData(): void
    {
        //
    }
}
