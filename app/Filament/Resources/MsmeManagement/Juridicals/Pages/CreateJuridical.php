<?php

namespace App\Filament\Resources\MsmeManagement\Juridicals\Pages;

use App\Filament\Resources\MsmeManagement\Juridicals\JuridicalResource;
use App\Services\AddressApiService;
use App\Services\AddressManager;
use App\Services\Industries;
use App\Services\ScimsApiService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class CreateJuridical extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;
    protected static string $resource = JuridicalResource::class;
    public array $businessRegionOptions = [];
    public array $businessProvinceOptions = [];
    public array $businessCityOptions = [];
    public array $businessBarangayOptions = [];
    public array $employerRegionOptions = [];
    public array $employerProvinceOptions = [];
    public array $employerCityOptions = [];
    public array $employerBarangayOptions = [];


    public function mount(): void
    {
        parent::mount();
        try {
            $regions = collect(app(AddressApiService::class)->getRegions())
                ->pluck('name', 'psgc_id')
                ->toArray();

            $this->businessRegionOptions = $regions;
            $this->employerRegionOptions = $regions;
        } catch (\Throwable) {
            $this->businessRegionOptions = [];
            $this->employerRegionOptions = [];
        }
    }

    protected function getRedirectUrl(): string
    {
        return JuridicalResource::getUrl('index');
    }

    public function getHeader(): ?View
    {
        return view('filament.resources.msme-management.juridicals.pages.create-juridical-header');
    }

    public string $scimsQuery = '';

    public array $scimsResults = [];

    public string $scimsSearchedFor = '';

    public int $scimsTotalCount = 0;

    public function updatedScimsQuery(): void
    {
        $this->refreshScimsResults();
    }

    public function searchScims(): void
    {
        $this->refreshScimsResults();
    }

    protected function refreshScimsResults(): void
    {
        $query = trim($this->scimsQuery);

        if (mb_strlen($query) < 2) {
            $this->reset('scimsResults', 'scimsSearchedFor', 'scimsTotalCount');

            return;
        }

        $rows = app(ScimsApiService::class)->searchBusinessByName($query);

        // Cap stored rows: a broad query can return hundreds of matches and
        // Livewire syncs every public prop per request (1MB cap). The full
        // count is kept separately for the "refine to narrow" hint.
        $this->scimsTotalCount = count($rows);
        $this->scimsResults = array_slice(array_values($rows), 0, 25);
        $this->scimsSearchedFor = $query;
    }

    public function selectScimsResult(int $index): void
    {
        $row = $this->scimsResults[$index] ?? null;

        if (! is_array($row)) {
            return;
        }

        $this->fillFromScims($row);
        $this->reset('scimsResults', 'scimsQuery', 'scimsSearchedFor', 'scimsTotalCount');
    }

    public function clearScimsResults(): void
    {
        $this->reset('scimsResults', 'scimsQuery', 'scimsSearchedFor', 'scimsTotalCount');
    }

    public function fillFromScims(array $row): void
    {
        $business = $row['business'] ?? [];
        $employer = $row['employer'] ?? [];
        $businessAddress = $business['address'] ?? [];
        $employerAddress = $employer['address'] ?? [];

        $addressManager = app(AddressManager::class);

        // Never inject a select value the field doesn't offer — an invalid
        // option hard-blocks required validation with no way for the user
        // to have caused it. Leave it blank for a manual pick instead.
        $industry = $business['line_of_industry'] ?? null;
        $industry = array_key_exists($industry, Industries::All()) ? $industry : null;

        $gender = $employer['gender'] ?? null;
        $gender = in_array($gender, ['Male', 'Female'], true) ? $gender : null;

        // SCIMS carries no special category — default it on fill so a
        // search-fill completes end to end (manual entries still choose).
        // Change or remove this line if that default ever becomes wrong.
        $specialCategory = 'None';

        $resolvedBusiness = $addressManager->preloadBusinessScimsAddress(
            $this,
            $businessAddress['region'] ?? null,
            $businessAddress['province'] ?? null,
            $businessAddress['city'] ?? null,
            $businessAddress['barangay'] ?? null,
        );
        $resolvedEmployer = $addressManager->preloadEmployerScimsAddress(
            $this,
            $employerAddress['region'] ?? null,
            $employerAddress['province'] ?? null,
            $employerAddress['city'] ?? null,
            $employerAddress['barangay'] ?? null,
        );
        // getRawState(), NOT getState(): getState() runs full validation first,
        // so calling it here would throw on the still-empty required fields
        // and abort the fill before a single value lands. Validation still
        // happens where it belongs — on wizard Next and Create submit.
        $this->form->fill(array_merge((array) $this->form->getRawState(), [
            // — Business —
            'name' => $business['name'] ?? null,
            'entity_no' => $business['entity_no'] ?? null,
            'date_reg' => $business['date_registered'] ?? null,
            'contact_no' => $business['contact_no'] ?? null,
            'contact_email' => $business['contact_email'] ?? null,
            'line_of_industry' => $industry,
            'business_upblb_num' => $businessAddress['unit_bldg_no'] ?? null,
            'business_street' => $businessAddress['street'] ?? null,
            'business_subdivision' => $businessAddress['subdivision'] ?? null,
            'business_zip' => $businessAddress['zip'] ?? null,
            ...$resolvedBusiness,

            // — Employer —
            'full_name' => $employer['full_name'] ?? null,
            'employer_entity_no' => $employer['entity_no'] ?? null,
            'gender' => $gender,
            'birth_date' => $employer['birth_date'] ?? null,
            'employer_contact_no' => $employer['contact_no'] ?? null,
            'employer_email' => $employer['email'] ?? null,
            'special_category' => $specialCategory,
            'employer_upblb_num' => $employerAddress['unit_bldg_no'] ?? null,
            'employer_street' => $employerAddress['street'] ?? null,
            'employer_subdivision' => $employerAddress['subdivision'] ?? null,
            'employer_zip' => $employerAddress['zip'] ?? null,
            ...$resolvedEmployer,
        ]));
    }
    protected function getSteps(): array
    {
        return [
            // ── Step 1: Juridical / Business ──────────────────────────
            Step::make('Business')
                ->description('Business details + business address')
                ->icon(Heroicon::BuildingOffice2)
                ->schema([
                    Section::make('Business Details')
                        ->description('Core business information')
                        ->icon(Heroicon::BuildingOffice2)
                        ->schema([
                            TextInput::make('name')
                                ->label('Business Name')
                                ->placeholder('e.g. ACME Trading Corp.')
                                ->prefixIcon(Heroicon::BuildingOffice2)
                                ->required()
                                ->maxLength(255),

                            TextInput::make('entity_no')
                                ->label('Entity No.')
                                ->placeholder('Auto-filled via Search from SCIMS')
                                ->prefixIcon(Heroicon::Hashtag)
                                ->readOnly()
                                ->required()
                                ->helperText('Use Search from SCIMS (top right) to autofill from SCIMS.'),

                            DatePicker::make('date_reg')
                                ->label('Date Registered')
                                ->prefixIcon(Heroicon::CalendarDays)
                                ->required(),

                            TextInput::make('contact_no')
                                ->label('Contact No.')
                                ->placeholder('09xx xxx xxxx')
                                ->prefixIcon(Heroicon::Phone)
                                ->tel(),

                            TextInput::make('contact_email')
                                ->label('E-mail')
                                ->placeholder('business@email.com')
                                ->prefixIcon(Heroicon::Envelope)
                                ->email(),

                            Select::make('line_of_industry')
                                ->label('Line of Industry')
                                ->options(Industries::All())
                                ->prefixIcon(Heroicon::Briefcase)
                                ->searchable()
                                ->required(),

                            TextInput::make('capitalization')
                                ->label('Capitalization')
                                ->placeholder('0.00')
                                ->prefix('₱')
                                ->extraInputAttributes([
                                    'oninput' => "this.value = this.value.replace(/[^0-9.,]/g, '')",
                                    'onblur' => "let val = parseFloat(this.value.replace(/,/g, '')); if (!isNaN(val)) { this.value = val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }",
                                ])
                                ->formatStateUsing(fn ($state) => filled($state)
                                    ? number_format((float) $state, 2, '.', ',')
                                    : null),
                        ])
                        ->columns(2),

                    Section::make('Business Address')
                        ->description('Where the business operates')
                        ->icon(Heroicon::MapPin)
                        ->schema([
                            Select::make('business_region')
                                ->label('Region')
                                ->options(fn (): array => $this->businessRegionOptions)
                                ->searchable()
                                ->live()
                                ->prefixIcon(Heroicon::GlobeAsiaAustralia)
                                ->afterStateUpdated(function ($state): void {
                                    $this->businessProvinceOptions = [];
                                    $this->businessCityOptions = [];
                                    $this->businessBarangayOptions = [];

                                    if (filled($state)) {
                                        app(AddressManager::class)->loadBusinessProvinces($this, $state);

                                        if (blank($this->businessProvinceOptions)) {
                                            app(AddressManager::class)->loadBusinessCitiesByRegion($this, $state);
                                        }
                                    }
                                }),

                            Select::make('business_province')
                                ->label('Province')
                                ->options(fn (): array => $this->businessProvinceOptions)
                                ->searchable()
                                ->live()
                                ->prefixIcon(Heroicon::Map)
                                ->placeholder('Select region first')
                                ->disabled(fn ($get): bool => blank($get('business_region')))
                                ->afterStateUpdated(function ($state, $get): void {
                                    $this->businessCityOptions = [];
                                    $this->businessBarangayOptions = [];

                                    if (filled($state)) {
                                        app(AddressManager::class)->loadBusinessCities($this, $state);

                                        return;
                                    }

                                    $region = $get('business_region');

                                    if (filled($region)) {
                                        app(AddressManager::class)->loadBusinessCitiesByRegion($this, $region);
                                    }
                                }),

                            Select::make('business_city')
                                ->label('City / Municipality')
                                ->options(fn (): array => $this->businessCityOptions)
                                ->searchable()
                                ->live()
                                ->prefixIcon(Heroicon::BuildingLibrary)
                                ->placeholder('Select province / region first')
                                ->disabled(fn ($get): bool => blank($get('business_region')))
                                ->afterStateUpdated(function ($state): void {
                                    $this->businessBarangayOptions = [];

                                    if (filled($state)) {
                                        app(AddressManager::class)->loadBusinessBarangays($this, $state);
                                    }
                                }),

                            Select::make('business_barangay')
                                ->label('Barangay')
                                ->options(fn (): array => $this->businessBarangayOptions)
                                ->searchable()
                                ->prefixIcon(Heroicon::HomeModern)
                                ->placeholder('Select city first')
                                ->disabled(fn ($get): bool => blank($get('business_city'))),

                            TextInput::make('business_upblb_num')
                                ->label('Unit / Bldg No.')
                                ->placeholder('Unit 201, Bldg A'),

                            TextInput::make('business_street')
                                ->label('Street')
                                ->placeholder('Rizal St.'),

                            TextInput::make('business_subdivision')
                                ->label('Subdivision')
                                ->placeholder('Greenfield Subd.'),

                            TextInput::make('business_zip')
                                ->label('ZIP Code')
                                ->placeholder('4200')
                                ->maxLength(10),
                        ])
                        ->columns(2),
                ]),

            // ── Step 2: Employer ─────────────────────────────────────
            Step::make('Employer')
                ->description('Owner details + employer address')
                ->icon(Heroicon::User)
                ->schema([
                    Section::make('Personal Details')
                        ->description('Employer identity')
                        ->icon(Heroicon::Identification)
                        ->schema([
                            TextInput::make('full_name')
                                ->label('Full Name')
                                ->placeholder('Juan D. Dela Cruz')
                                ->prefixIcon(Heroicon::User)
                                ->required()
                                ->maxLength(255),

                            TextInput::make('employer_entity_no')
                                ->label('Employer Entity No.')
                                ->placeholder('Auto-filled via Search from SCIMS')
                                ->prefixIcon(Heroicon::Hashtag)
                                ->readOnly()
                                ->required(),

                            Select::make('gender')
                                ->label('Gender')
                                ->options([
                                    'Male' => 'Male',
                                    'Female' => 'Female',
                                ])
                                ->prefixIcon(Heroicon::Users)
                                ->required(),

                            DatePicker::make('birth_date')
                                ->label('Date of Birth')
                                ->prefixIcon(Heroicon::Cake)
                                ->required(),

                            TextInput::make('employer_contact_no')
                                ->label('Contact No.')
                                ->placeholder('09xx xxx xxxx')
                                ->prefixIcon(Heroicon::Phone)
                                ->tel(),

                            TextInput::make('employer_email')
                                ->label('E-mail')
                                ->placeholder('owner@email.com')
                                ->prefixIcon(Heroicon::Envelope)
                                ->email(),

                            Select::make('special_category')
                                ->label('Special Category')
                                ->options([
                                    'None' => 'None',
                                    '4ps Beneficiary' => '4ps Beneficiary',
                                    'Solo Parent' => 'Solo Parent',
                                    'Person with Disability (PWD)' => 'Person with Disability (PWD)',
                                    'Young Entrepreneur' => 'Young Entrepreneur',
                                ])
                                ->required()
                                ->prefixIcon(Heroicon::Sparkles),
                        ])
                        ->columns(2),

                    Section::make('Employer Address')
                        ->description('Where the employer resides')
                        ->icon(Heroicon::MapPin)
                        ->schema([
                            Select::make('employer_region')
                                ->label('Region')
                                ->options(fn (): array => $this->employerRegionOptions)
                                ->searchable()
                                ->live()
                                ->prefixIcon(Heroicon::GlobeAsiaAustralia)
                                ->afterStateUpdated(function ($state): void {
                                    $this->employerProvinceOptions = [];
                                    $this->employerCityOptions = [];
                                    $this->employerBarangayOptions = [];

                                    if (filled($state)) {
                                        app(AddressManager::class)->loadEmployerProvinces($this, $state);

                                        if (blank($this->employerProvinceOptions)) {
                                            app(AddressManager::class)->loadEmployerCitiesByRegion($this, $state);
                                        }
                                    }
                                }),

                            Select::make('employer_province')
                                ->label('Province')
                                ->options(fn (): array => $this->employerProvinceOptions)
                                ->searchable()
                                ->live()
                                ->prefixIcon(Heroicon::Map)
                                ->placeholder('Select region first')
                                ->disabled(fn ($get): bool => blank($get('employer_region')))
                                ->afterStateUpdated(function ($state, $get): void {
                                    $this->employerCityOptions = [];
                                    $this->employerBarangayOptions = [];

                                    if (filled($state)) {
                                        app(AddressManager::class)->loadEmployerCities($this, $state);

                                        return;
                                    }

                                    $region = $get('employer_region');

                                    if (filled($region)) {
                                        app(AddressManager::class)->loadEmployerCitiesByRegion($this, $region);
                                    }
                                }),

                            Select::make('employer_city')
                                ->label('City / Municipality')
                                ->options(fn (): array => $this->employerCityOptions)
                                ->searchable()
                                ->live()
                                ->prefixIcon(Heroicon::BuildingLibrary)
                                ->placeholder('Select province / region first')
                                ->disabled(fn ($get): bool => blank($get('employer_region')))
                                ->afterStateUpdated(function ($state): void {
                                    $this->employerBarangayOptions = [];

                                    if (filled($state)) {
                                        app(AddressManager::class)->loadEmployerBarangays($this, $state);
                                    }
                                }),

                            Select::make('employer_barangay')
                                ->label('Barangay')
                                ->options(fn (): array => $this->employerBarangayOptions)
                                ->searchable()
                                ->prefixIcon(Heroicon::HomeModern)
                                ->placeholder('Select city first')
                                ->disabled(fn ($get): bool => blank($get('employer_city'))),

                            TextInput::make('employer_upblb_num')
                                ->label('Unit / Bldg No.')
                                ->placeholder('Blk 3 Lot 12'),

                            TextInput::make('employer_street')
                                ->label('Street')
                                ->placeholder('Mabini St.'),

                            TextInput::make('employer_subdivision')
                                ->label('Subdivision')
                                ->placeholder('Sunrise Village'),

                            TextInput::make('employer_zip')
                                ->label('ZIP Code')
                                ->placeholder('4200')
                                ->maxLength(10),
                        ])
                        ->columns(2),
                ]),
        ];
    }

   public function create(bool $another = false): void
{
    $data = $this->form->getState();

    $addressManager = app(AddressManager::class);

    $businessAddress = $addressManager->resolveSelectedAddress(
        $data['business_region'] ?? null,
        $data['business_province'] ?? null,
        $data['business_city'] ?? null,
        $data['business_barangay'] ?? null,
    );

    $employerAddress = $addressManager->resolveSelectedAddress(
        $data['employer_region'] ?? null,
        $data['employer_province'] ?? null,
        $data['employer_city'] ?? null,
        $data['employer_barangay'] ?? null,
    );

    try {
        $juridical = [
            'juri_entity_no' => $data['entity_no'] ?? null,
            'juri_name' => $data['name'] ?? null,
            'juri_date_reg' => $data['date_reg'] ?? null,
            'juri_capitalization' => str_replace(
                ',',
                '',
                $data['capitalization'] ?? '0.00'
            ),
            'juri_contact_no' => $data['contact_no'] ?? null,
            'juri_contact_email' => $data['contact_email'] ?? null,
            'juri_line_of_industry' => $data['line_of_industry'] ?? null,

            'juri_region' => $businessAddress['region'] ?? null,
            'juri_province' => $businessAddress['province'] ?? null,
            'juri_city' => $businessAddress['city'] ?? null,
            'juri_barangay' => $businessAddress['barangay'] ?? null,

            'juri_subdivision' => $data['business_subdivision'] ?? null,
            'juri_street' => $data['business_street'] ?? null,
            'juri_upblb_num' => $data['business_upblb_num'] ?? null,
            'juri_zip' => $data['business_zip'] ?? null,
        ];

        $employer = [
            'emp_entity_no' => $data['employer_entity_no'] ?? null,
            'emp_full_name' => $data['full_name'] ?? null,
            'emp_gender' => $data['gender'] ?? null,
            'emp_birth_date' => $data['birth_date'] ?? null,
            'emp_contact_no' => $data['employer_contact_no'] ?? null,
            'emp_email' => $data['employer_email'] ?? null,
            'emp_special_category' => $data['special_category'] ?? null,

            'emp_region' => $employerAddress['region'] ?? null,
            'emp_province' => $employerAddress['province'] ?? null,
            'emp_city' => $employerAddress['city'] ?? null,
            'emp_barangay' => $employerAddress['barangay'] ?? null,

            'emp_subdivision' => $data['employer_subdivision'] ?? null,
            'emp_street' => $data['employer_street'] ?? null,
            'emp_upblb_num' => $data['employer_upblb_num'] ?? null,
            'emp_zip' => $data['employer_zip'] ?? null,
        ];

        // Both shapes: the validator's nested-array rules resolve to FLAT
        // keys (proven: its errors name juri_*/emp_* with no prefix), while
        // the creation code reads $validated['juridical']['...'] NESTED.
        // Send both until the controller standardizes on dot notation —
        // then the flat copies come out and only the groups stay.
        $response = Http::acceptJson()->post(
            route('msme.store'),
            array_merge(
                $juridical,
                $employer,
                [
                    'juridical' => $juridical,
                    'employer' => $employer,
                ],
            ),
        );
    } catch (ConnectionException) {
        Notification::make()
            ->title('Could not reach the server.')
            ->danger()
            ->send();

        return;
    }

    $message = $response->json('message');

    if ($response->successful()) {
        Notification::make()
            ->title($message ?? 'Business created successfully.')
            ->success()
            ->send();

        $this->redirect(
            JuridicalResource::getUrl('index'),
            navigate: true,
        );

        return;
    }

    Notification::make()
        ->title($message ?? 'Failed to create business.')
        ->danger()
        ->send();
}
}
