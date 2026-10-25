<?php

namespace App\Filament\Pages\Msme;

use App\Filament\Schemas\Msme\BusinessForm;
use App\Filament\Schemas\Msme\EmployeesTable;
use App\Filament\Schemas\Msme\OwnerForm;
use App\Models\Msme\Negosyo;
use App\Services\ScimsJuridicalApiService;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ViewBusiness extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'business/{entity_no}/details';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $business;

    public ?array $ownerData;

    public ?array $businessData;

    protected string $view = 'filament.pages.msme.view-business';

    public function getHeading(): string
    {
        return '';
    }

    public string $section = 'menu';

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            Businesses::getUrl() => 'Businesses',
            static::getUrl(['entity_no' => $this->business['entity_no']]) => 'View Business',
        ];

        if ($this->section === 'owner') {
            $breadcrumbs[] = 'Owner Information';
        }

        if ($this->section === 'business') {
            $breadcrumbs[] = 'Business Information';
        }

        return $breadcrumbs;
    }

    public function mount(string $entity_no, ScimsJuridicalApiService $scimsApi)
    {
        $this->business = $scimsApi->getBusinessByID($entity_no);

        if (empty($this->business)) {
            abort(404);
        }

        $negosyo = Negosyo::query()
            ->where('api_entity_no', $entity_no)
            ->first();

        $this->business['full_business_address'] = $scimsApi->formatAddress(
            upblb: $this->business['upblb_num'],
            subdivision: $this->business['subdivision'],
            street: $this->business['street'],
            barangay: $this->business['barangay'],
            city: $this->business['city'],
            province: $this->business['province'],
            region: $this->business['region'],
        );

        $this->ownerForm->fill([
            'owner_fullname' => $this->business['owner_fullname'],
            'owner_mobile' => $this->business['owner_mobile'],
            'owner_telno' => $this->business['owner_telno'],
            'owner_email' => $this->business['owner_email'],
        ]);

        $this->businessForm->fill([
            'juri_name' => $this->business['juri_name'],
            'entity_no' => $this->business['entity_no'],
            'bin_etracs' => $this->business['bin_etracs'],
            'organization' => $this->business['organization'],
            'juri_date_reg' => $this->business['juri_date_reg'],
            'line_of_industry' => $this->business['line_of_industry'],
            'nature_of_business' => $this->business['nature_of_business'],
            'region' => $this->business['region'],
            'province' => $this->business['province'],
            'city' => $this->business['city'],
            'barangay' => $this->business['barangay'],
            'street' => $this->business['street'],
            'subdivision' => $this->business['subdivision'],
            'upblb_num' => $this->business['upblb_num'],
            'zip' => $this->business['zip'],
            'status' => $this->business['status'],
            'type_organization' => $this->business['type_organization'],
            'total_workforce' => $this->business['total_workforce'],
            'special_category' => $negosyo?->special_category ?: 'None',
            'capitalization' => $negosyo?->capitalization,
        ]);
    }

    public function saveBusiness(): void
    {
        $data = $this->businessForm->validate();
        $data = $data['businessData'] ?? $data;

        $negosyo = Negosyo::firstOrNew([
            'api_entity_no' => $this->business['entity_no'],
        ]);

        if (! $negosyo->exists) {
            $negosyo->id = 'neg-'.Str::uuid();
        }

        $negosyo->fill([
            'special_category' => $data['special_category'],
            'capitalization' => $data['capitalization'],
        ])->save();

        Notification::make()
            ->title('Business information saved')
            ->success()
            ->send();
    }

    public function showSection(string $section): void
    {
        if ($section === 'employees') {
            $this->redirect(Employees::getUrl([
                'entity_no' => $this->business['entity_no'],
            ]));

            return;
        }
        $this->section = $section;
    }

    protected function getForms(): array
    {
        return [
            'ownerForm',
            'businessForm',
        ];
    }

    protected function getTables(): array
    {
        return [
            'employeesTable',
        ];
    }

    public function ownerForm(Schema $schema)
    {
        return $schema
            ->components([
                Form::make()
                    ->schema(OwnerForm::make())])
            ->statePath('ownerData');
    }

    public function businessForm(Schema $schema)
    {
        return $schema
            ->components([
                Form::make()
                    ->schema([
                        ...BusinessForm::make(),
                        Actions::make([
                            Action::make('saveBusiness')
                                ->label('Save')
                                ->icon(Heroicon::OutlinedCheck)
                                ->action(fn () => $this->saveBusiness()),
                        ])
                            ->alignment(Alignment::End)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('businessData');
    }

    public function employeesTable(Table $table)
    {
        $table
            ->deferLoading()
            ->columns(EmployeesTable::make());
    }
}
