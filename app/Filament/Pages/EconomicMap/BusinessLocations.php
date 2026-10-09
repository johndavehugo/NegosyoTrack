<?php

namespace App\Filament\Pages\EconomicMap;

use App\Models\MsmeManagement\Address;
use App\Models\MsmeManagement\Employer;
use App\Models\MsmeManagement\Juridical;
use App\Services\EconomicMapService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class BusinessLocations extends BaseMapPage implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Business Locations';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::MapPin;

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.economic-map.pages.business-locations';

    public ?string $pickerAddressId = null;

    public ?array $pickerEntry = null;

    public ?array $pickerBounds = null;

    public ?array $pickerFocus = null;

    public ?string $pickerFocusLabel = null;

    public function getHeading(): string
    {
        return 'San Carlos City Business Locations';
    }

    public function getSubheading(): ?string
    {
        return 'All businesses with live location status — set coordinates without leaving the module.';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public static function hasCoords(mixed $lat, mixed $lng): bool
    {
        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return false;
        }

        return (float) $lat != 0.0 && (float) $lng != 0.0;
    }

    public static function addressLine(Juridical $record): string
    {
        return collect([
            $record->address?->street,
            $record->address?->barangay,
            $record->address?->city,
        ])->filter()->join(', ') ?: '—';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Juridical::with(['address', 'employer']))
            ->columns([
                TextColumn::make('entity_no')
                    ->label('Entity No.')
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Business Name')
                    ->sortable()
                    ->toggleable()
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn (Juridical $record): ?string => $record->name),

                TextColumn::make('category')
                    ->label('Category')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        'MICRO' => Heroicon::Minus,
                        'SMALL' => Heroicon::Bars2,
                        'MEDIUM' => Heroicon::Bars3,
                        'LARGE' => Heroicon::Bars4
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'MICRO' => 'blue-badge-1',
                        'SMALL' => 'blue-badge-2',
                        'MEDIUM' => 'blue-badge-3',
                        'LARGE' => 'blue-badge-4'
                    }),

                TextColumn::make('address_line')
                    ->label('Address')
                    ->toggleable()
                    ->limit(40)
                    ->tooltip(fn (Juridical $record): string => static::addressLine($record))
                    ->state(fn (Juridical $record): string => static::addressLine($record)),

                TextColumn::make('location_status')
                    ->label('Location')
                    ->toggleable()
                    ->badge()
                    ->state(fn (Juridical $record): string => static::hasCoords(
                        $record->address?->latitude,
                        $record->address?->longitude,
                    ) ? 'Set' : 'Unset')
                    ->icon(fn (string $state): Heroicon => $state === 'Set' ? Heroicon::MapPin : Heroicon::ExclamationTriangle)
                    ->color(fn (string $state): array => $state === 'Set'
                        ? Color::Green
                        : Color::Amber),

                TextColumn::make('bus_status')
                    ->label('Status')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        'ACTIVE' => Heroicon::Play,
                        'INACTIVE' => Heroicon::XMark
                    })
                    ->color(fn (string $state): array => match ($state) {
                        'ACTIVE' => Color::Green,
                        'INACTIVE' => Color::Red
                    }),

                TextColumn::make('registration_type')
                    ->label('Registration Type')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('location')
                    ->label('Location status')
                    ->options([
                        'set' => 'Set',
                        'unset' => 'Unset',
                    ])
                    ->query(function (Builder $query, array $data): void {
                        if (($data['value'] ?? null) === 'set') {
                            $query->whereHas('address', fn (Builder $address) => $address
                                ->whereNotNull('latitude')->where('latitude', '!=', 0)
                                ->whereNotNull('longitude')->where('longitude', '!=', 0));
                        }

                        if (($data['value'] ?? null) === 'unset') {
                            $query->where(function (Builder $query): void {
                                $query->whereDoesntHave('address')
                                    ->orWhereHas('address', fn (Builder $address) => $address
                                        ->where(fn (Builder $q) => $q
                                            ->whereNull('latitude')->orWhere('latitude', 0)
                                            ->orWhereNull('longitude')->orWhere('longitude', 0)));
                            });
                        }
                    }),

                SelectFilter::make('category')
                    ->label('Filter by Category')
                    ->options([
                        'MICRO' => 'Micro',
                        'SMALL' => 'Small',
                        'MEDIUM' => 'Medium',
                        'LARGE' => 'Large',
                    ]),

                Filter::make('check_bus_status')
                    ->query(fn (Builder $query) => $query->where('bus_status', 'ACTIVE'))
                    ->label('Only show active Businesses'),
            ])
            ->actions([
                Action::make('setLocation')
                    ->label('Set location')
                    ->icon(Heroicon::MapPin)
                    ->color(Color::Blue)
                    ->action(fn (Juridical $record): mixed => $record->address_id
                        ? $this->openPicker($record->address_id)
                        : Notification::make()
                            ->title('No address linked')
                            ->body('This business has no address to locate.')
                            ->warning()
                            ->send()),
            ]);
    }

    public function openPicker(int|string|null $addressId): void
    {
        if (! filled($addressId)) {
            Notification::make()
                ->title('No address linked')
                ->body('This record has no address to locate.')
                ->warning()
                ->send();

            return;
        }

        $address = Address::find($addressId);

        if (! $address) {
            Notification::make()
                ->title('Address not found')
                ->warning()
                ->send();

            return;
        }

        $juridical = Juridical::where('address_id', $address->id)->first();
        $employer = Employer::where('address_id', $address->id)->first();

        $this->pickerAddressId = $address->id;
        $this->pickerEntry = [
            'business_name' => $juridical?->name
                ?? $employer?->juridical()->first()?->name
                ?? '—',
            'side' => $juridical ? 'business' : 'employer',
            'street' => $address->street,
            'barangay' => $address->barangay,
            'city' => $address->city,
        ];
        $this->pickerBounds = app(EconomicMapService::class)->bounds();
        $this->pickerFocus = app(EconomicMapService::class)->barangayFocus($address->barangay);
        $this->pickerFocusLabel = $this->pickerFocus ? trim((string) $address->barangay) : null;
    }

    public function closePicker(): void
    {
        $this->reset('pickerAddressId', 'pickerEntry', 'pickerBounds', 'pickerFocus', 'pickerFocusLabel');
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

        // The address endpoint validates decimal:10,7 / decimal:11,7,
        // so send strings padded to exactly 7 decimal places.
        $payload = [
            'latitude' => number_format($lat, 7, '.', ''),
            'longitude' => number_format($lng, 7, '.', ''),
        ];

        try {
            $response = Http::acceptJson()->patch(
                route('msme.location', ['address' => $this->pickerAddressId]),
                $payload,
            );
        } catch (ConnectionException) {
            Notification::make()
                ->title('Could not reach the server')
                ->danger()
                ->send();

            return;
        }

        if ($response->failed()) {
            Notification::make()
                ->title('Location not saved')
                ->body($response->json('message') ?? 'The address endpoint rejected the coordinates.')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title($response->json('message') ?? 'Location saved.')
            ->success()
            ->send();

        app(EconomicMapService::class)->refresh();

        $this->closePicker();
    }

    protected function loadData(): void
    {
        //
    }
}
