<?php

namespace App\Filament\Pages\Msme;

use App\Services\ScimsJuridicalApiService;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;

class Employees extends Page implements HasTable
{
    use InteractsWithTable;
    protected static bool $shouldRegisterNavigation = false;
    public string $entity_no;
    protected static ?string $slug = 'business/{entity_no}/employees';
    protected string $view = 'filament.pages.msme.employees';
    public function getBreadcrumbs(): array
    {
        return [
            Businesses::getUrl() => 'Businesses',
            ViewBusiness::getUrl(['entity_no' => $this->entity_no,]) => 'View Business',
            'Employees',
        ];
    }

    public function mount(string $entity_no): void
    {
        $this->entity_no = $entity_no;
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->records(function (ScimsJuridicalApiService $scimsApi, int $page, int $recordsPerPage, ?string $search): LengthAwarePaginator {
                return $scimsApi->getEmployees(
                    entity_no: $this->entity_no,
                    page: $page,
                    recordsPerPage: $recordsPerPage,
                    search: $search
                );
            })
            ->columns([
                TextColumn::make('entity_no')
                    ->label('Entity #')
                    ->toggleable(),
                TextColumn::make('employee_fullname')
                    ->label('Name')
                    ->toggleable(),
                TextColumn::make('work_status')
                    ->label('Work Status')
                    ->default('Not Specified')
                    ->toggleable(),
                TextColumn::make('work_type')
                    ->label('Work Type')
                    ->toggleable(),
                TextColumn::make('employment_id')
                    ->label('Employment ID')
                    ->toggleable(isToggledHiddenByDefault: True),
                TextColumn::make('work_id')
                    ->label('Work ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('work_category')
                    ->label('Work Category')
                    ->toggleable()
                    ->default('Not Specified'),
                TextColumn::make('position_title')
                    ->label('Position')
                    ->toggleable(),
                TextColumn::make('work_nature')
                    ->label('Nature of Work')
                    ->toggleable(),
            ])
            ->filters([

            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->searchable();
        
    }
}
