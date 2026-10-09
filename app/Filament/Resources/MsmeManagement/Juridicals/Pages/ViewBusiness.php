<?php

namespace App\Filament\Resources\MsmeManagement\Juridicals\Pages;

use App\Filament\Resources\MsmeManagement\Juridicals\JuridicalResource;
use App\Filament\Resources\MsmeManagement\Juridicals\Schemas\AddressForm;
use App\Filament\Resources\MsmeManagement\Juridicals\Schemas\BusinessForm;
use App\Filament\Resources\MsmeManagement\Juridicals\Schemas\JuridicalModal;
use App\Filament\Resources\MsmeManagement\Juridicals\Schemas\PersonalForm;
use App\Services\AddressApiService;
use App\Services\AddressManager;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Attributes\Url;

class ViewBusiness extends Page implements HasSchemas
{
    use InteractsWithRecord;
    use InteractsWithSchemas;

    #[Url]
    public string $section = 'menu';

    public array $personalData = [];

    public array $businessData = [];

    public array $addressData = [];

    public array $businessRegionOptions = [];

    public array $businessProvinceOptions = [];

    public array $businessCityOptions = [];

    public array $businessBarangayOptions = [];

    public array $employerRegionOptions = [];

    public array $employerProvinceOptions = [];

    public array $employerCityOptions = [];

    public array $employerBarangayOptions = [];

    protected static string $resource = JuridicalResource::class;

    protected string $view = 'filament.resources.msme-management.juridicals.pages.view-business';

    public function getHeading(): string
    {
        return '';
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            JuridicalResource::getUrl('index') => 'Businesses',
            static::getUrl(['record' => $this->record]) => 'View Business',
        ];

        if ($this->section === 'personal') {
            $breadcrumbs[] = 'Personal Information';
        }

        if ($this->section === 'business') {
            $breadcrumbs[] = 'Business Information';
        }

        if ($this->section === 'address') {
            $breadcrumbs[] = 'Address Information';
        }

        return $breadcrumbs;
    }

    public function mount(int|string $record, AddressApiService $addressApiService): void
    {
        $regions = collect(
            $addressApiService->getRegions()
        )->pluck('name', 'psgc_id')->toArray();

        $this->businessRegionOptions = $regions;
        $this->employerRegionOptions = $regions;
        $this->record = $this->resolveRecord($record);

        $this->personalForm->fill([
            'entity_no' => $this->record->employer->entity_no,
            'full_name' => $this->record->employer->full_name,
            'gender' => $this->record->employer->gender,
            'birth_date' => $this->record->employer->birth_date,
            'contact_no' => $this->record->employer->contact_no,
            'email' => $this->record->employer->email,
            'special_category' => $this->record->employer->special_category,
        ]);

        $this->businessForm->fill([
            'name' => $this->record->name,
            'entity_no' => $this->record->entity_no,
            'category' => $this->record->category,
            'date_reg' => $this->record->date_reg,
            'contact_no' => $this->record->contact_no,
            'contact_email' => $this->record->contact_email,
            'line_of_industry' => $this->record->line_of_industry,
            'capitalization' => $this->record->capitalization,
        ]);

        $businessAddressState =
        $this->getAddressManager()
            ->loadBusinessAddressOptions($this);

        $employerAddressState =
            $this->getAddressManager()
                ->loadEmployerAddressOptions($this);

        $this->addressForm->fill([
            // Business Address
            'business_region' => $businessAddressState['business_region'] ?? null,
            'business_province' => $businessAddressState['business_province'] ?? null,
            'business_city' => $businessAddressState['business_city'] ?? null,
            'business_barangay' => $businessAddressState['business_barangay'] ?? null,
            'business_street' => $this->record->address->street,
            'business_subdivision' => $this->record->address->subdivision,
            'business_upblb_num' => $this->record->address->upblb_num,
            'business_zip' => $this->record->address->zip,

            // Employer Address
            'employer_region' => $employerAddressState['employer_region'] ?? null,
            'employer_province' => $employerAddressState['employer_province'] ?? null,
            'employer_city' => $employerAddressState['employer_city'] ?? null,
            'employer_barangay' => $employerAddressState['employer_barangay'] ?? null,
            'employer_street' => $this->record->employer->address->street,
            'employer_subdivision' => $this->record->employer->address->subdivision,
            'employer_upblb_num' => $this->record->employer->address->upblb_num,
            'employer_zip' => $this->record->employer->address->zip,
        ]);

        $this->getAddressManager()->loadBusinessAddressOptions($this);
        $this->getAddressManager()->loadEmployerAddressOptions($this);
    }

    protected function getHeaderActions(): array
    {
        return [
            JuridicalModal::StatusAction()
                ->visible(fn (): bool => $this->section === 'business')
                ->after(function (): void {
                    $this->record->refresh();
                }),

            JuridicalModal::renewAction()
                ->visible(fn (): bool => $this->section === 'business')
                ->after(function (): void {
                    $this->record->refresh();
                }),
        ];
    }

    public function showSection(string $section): void
    {
        $this->section = $section;
    }

    public function personalForm(Schema $schema): Schema
    {
        return PersonalForm::make($schema)
            ->statePath('personalData');
    }

    public function businessForm(Schema $schema): Schema
    {
        return BusinessForm::make(
            $schema,
            fn () => $this->record,
        )->statePath('businessData');
    }

    public function addressForm(Schema $schema): Schema
    {
        return AddressForm::make(
            $schema,
            $this,
        )->statePath('addressData');
    }

    public function getAddressManager(): AddressManager
    {
        return app(AddressManager::class);
    }
}
