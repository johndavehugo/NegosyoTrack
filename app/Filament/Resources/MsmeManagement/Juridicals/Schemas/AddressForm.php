<?php

namespace App\Filament\Resources\MsmeManagement\Juridicals\Schemas;

use App\Filament\Resources\MsmeManagement\Juridicals\Pages\ViewBusiness;
use App\Models\MsmeManagement\Juridical;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class AddressForm {
    public static function make(Schema $schema, ViewBusiness $page,): Schema {
        return $schema
            ->components([
                Tabs::make()
                    ->tabs([
                        Tab::make('Business Address')
                            ->schema([
                                Select::make('business_region')
                                    ->label('Region')
                                    ->options(fn () => $page->businessRegionOptions)
                                    ->live()
                                    ->afterStateUpdated(function ($state) use ($page): void {
                                        $page->businessProvinceOptions = [];
                                        $page->businessCityOptions = [];
                                        $page->businessBarangayOptions = [];

                                        if (filled($state)) {
                                            $page->getAddressManager()
                                                ->loadBusinessProvinces($page, $state);
                                            if (blank($page->businessProvinceOptions)) {
                                                    $page->getAddressManager()
                                                        ->loadBusinessCitiesByRegion($page,$state);
                                            }
                                        }
                                    }),

                                Select::make('business_province')
                                    ->label('Province')
                                    ->options(fn () => $page->businessProvinceOptions)
                                    ->live()
                                    ->disabled(fn ($get): bool => blank($get('business_region')))
                                    ->afterStateUpdated(
                                        function ($state) use ($page): void {
                                            $page->businessCityOptions = [];
                                            $page->businessBarangayOptions = [];

                                            if (filled($state)) {
                                                $page->getAddressManager()
                                                    ->loadBusinessCities($page, $state);
                                                return;
                                            }
                                            $region = $page->addressData['business_region'] ?? null;

                                            if (filled($region)) {
                                                $page->getAddressManager()
                                                    ->loadBusinessCitiesByRegion($page, $region);
                                            }
                                        }),

                                Select::make('business_city')
                                    ->label('City / Municipality')
                                    ->options(fn () => $page->businessCityOptions)
                                    ->live()
                                    ->disabled(fn ($get): bool => blank($get('business_region')))
                                    ->afterStateUpdated(function ($state) use ($page): void {
                                        $page->businessBarangayOptions = [];

                                        if (filled($state)) {
                                            $page->getAddressManager()
                                                ->loadBusinessBarangays($page, $state);
                                        }
                                    }),

                                Select::make('business_barangay')
                                    ->label('Barangay')
                                    ->options(fn () => $page->businessBarangayOptions)
                                    ->disabled(fn ($get): bool => blank($get('business_city'))),

                                TextInput::make('business_street')
                                    ->label('Street'),

                                TextInput::make('business_subdivision')
                                    ->label('Subdivision'),

                                TextInput::make('business_upblb_num')
                                    ->label('Unit / Building No.'),

                                TextInput::make('business_zip')
                                    ->label('ZIP Code'),
                            ])
                            ->columns(2),

                        Tab::make('Employer Address')
                            ->schema([
                                Select::make('employer_region')
                                    ->label('Region')
                                    ->options(fn () => $page->employerRegionOptions)
                                    ->live()
                                    ->afterStateUpdated(function ($state) use ($page): void {
                                        $page->employerProvinceOptions = [];
                                        $page->employerCityOptions = [];
                                        $page->employerBarangayOptions = [];

                                        if (filled($state)) {
                                            $page->getAddressManager()
                                                ->loadEmployerProvinces($page,$state);
                                            if (blank($page->employerProvinceOptions)) {
                                                $page->getAddressManager()
                                                    ->loadEmployerCitiesByRegion($page, $state);
                                            }
                                        }
                                    }),

                                Select::make('employer_province')
                                    ->label('Province')
                                    ->options(fn () => $page->employerProvinceOptions)
                                    ->live()
                                    ->disabled(fn ($get): bool => blank($get('employer_region')))
                                    ->afterStateUpdated(function ($state) use ($page): void {
                                        $page->employerCityOptions = [];
                                        $page->employerBarangayOptions = [];

                                        if (filled($state)) {
                                            $page->getAddressManager()
                                                ->loadEmployerCities($page, $state);
                                            return;
                                        }

                                        $region = $page->addressData['employer_region'] ?? null;

                                        if (filled($region)) {
                                            $page->getAddressManager()
                                                ->loadEmployerCitiesByRegion($page, $region);
                                        }
                                    }),

                                Select::make('employer_city')
                                    ->label('City / Municipality')
                                    ->options(fn () => $page->employerCityOptions)
                                    ->live()
                                    ->disabled(fn ($get): bool => blank($get('employer_region')))
                                    ->afterStateUpdated(function ($state) use ($page): void {
                                        $page->employerBarangayOptions = [];

                                        if (filled($state)) {
                                            $page->getAddressManager()
                                                ->loadEmployerBarangays($page, $state);
                                        }
                                    }),

                                Select::make('employer_barangay')
                                    ->label('Barangay')
                                    ->options(fn () => $page->employerBarangayOptions)
                                    ->disabled(fn ($get): bool => blank($get('employer_city'))),

                                TextInput::make('employer_street')
                                    ->label('Street'),

                                TextInput::make('employer_subdivision')
                                    ->label('Subdivision'),

                                TextInput::make('employer_upblb_num')
                                    ->label('Unit / Building No.'),

                                TextInput::make('employer_zip')
                                    ->label('ZIP Code'),
                            ])
                            ->columns(2),
                    ])
                    ->contained(),

                Actions::make([
                    Action::make('save')
                        ->label('Save')
                        ->color('success')
                        ->icon(Heroicon::Check)
                        ->action(function (Juridical $record, $livewire,): void {
                            $data = $livewire->addressData;
                            $addressManager = $livewire->getAddressManager();

                            $businessAddress = $addressManager
                                ->resolveSelectedAddress(
                                    $data['business_region'] ?? null,
                                    $data['business_province'] ?? null,
                                    $data['business_city'] ?? null,
                                    $data['business_barangay'] ?? null,
                                );

                            $employerAddress = $addressManager
                                ->resolveSelectedAddress(
                                    $data['employer_region'] ?? null,
                                    $data['employer_province'] ?? null,
                                    $data['employer_city'] ?? null,
                                    $data['employer_barangay'] ?? null,
                                );

                            try {
                                $juriResponse = Http::acceptJson()->put(
                                    route('msme.address.update', ['address' => $record->address->id,]),
                                        [
                                            'region' => $businessAddress['region'],
                                            'province' => $businessAddress['province'],
                                            'city' => $businessAddress['city'],
                                            'barangay' => $businessAddress['barangay'],
                                            'subdivision' => $data['business_subdivision'] ?? null,
                                            'street' => $data['business_street'] ?? null,
                                            'upblb_num' => $data['business_upblb_num'] ?? null,
                                            'zip' => $data['business_zip'] ?? null,
                                        ]
                                );

                                $juriMessage = $juriResponse->json('message');

                                $empResponse = Http::acceptJson()->put(
                                    route('msme.address.update', ['address' => $record->employer->address->id,]),
                                        [
                                            'region' => $employerAddress['region'],
                                            'province' => $employerAddress['province'],
                                            'city' => $employerAddress['city'],
                                            'barangay' => $employerAddress['barangay'],
                                            'subdivision' => $data['employer_subdivision'] ?? null,
                                            'street' => $data['employer_street'] ?? null,
                                            'upblb_num' => $data['employer_upblb_num'] ?? null,
                                            'zip' => $data['employer_zip'] ?? null,
                                        ]
                                );

                                $empMessage = $empResponse->json('message');

                            } catch (ConnectionException) {
                                Notification::make()
                                    ->title('Could not reach the server')
                                    ->danger()
                                    ->send();
                                    return;
                            }

                            if ($juriResponse->successful() && $empResponse->successful()) {
                                Notification::make()
                                    ->title('Address updated successfully')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Update Failed')
                                    ->body(($juriMessage). ' ' . $empMessage)
                                    ->danger()
                                    ->send();
                            }
                        }),
                ])
                    ->alignEnd(),
            ]);
    }
}
