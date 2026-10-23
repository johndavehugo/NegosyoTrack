<?php

use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Tables\AffectedMsmeTable;
use App\Models\CalamityMonitoring\Calamity;
use App\Models\User;
use App\Services\CalamityMonitoringService;
use App\Services\ScimsJuridicalApiService;
use App\Support\Barangays;
use Filament\Forms\Components\TableSelect\Livewire\TableSelectLivewireComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['app.env' => 'local']);
});

it('lists the eighteen san carlos city barangays', function () {
    expect(Barangays::options())
        ->toHaveCount(18)
        ->toHaveKey('Barangay I')
        ->and(Barangays::options()['Barangay I'])->toBe('Barangay I (Poblacion)')
        ->and(Barangays::options()['San Juan'])->toBe('San Juan');
});

it('filters the cached scims registry by barangay and search', function () {
    $scims = Mockery::mock(ScimsJuridicalApiService::class);
    $scims->shouldReceive('getAllBusinesses')->twice()->andReturn(collect([
        ['entity_no' => 'A', 'juri_name' => 'Alpha Store', 'barangay' => 'Bagonbon', 'street' => 'Proper', 'line_of_industry' => 'Retail'],
        ['entity_no' => 'B', 'juri_name' => 'Beta Shop', 'barangay' => 'Buluangan', 'street' => 'Poblacion', 'line_of_industry' => 'Retail'],
        ['entity_no' => 'C', 'juri_name' => 'Gamma Trading', 'barangay' => 'Bagonbon', 'street' => 'Proper', 'line_of_industry' => 'Food'],
    ]));

    $service = new CalamityMonitoringService($scims);

    $paginator = $service->paginateEstablishments(
        search: null,
        page: 1,
        recordsPerPage: 10,
        sortColumn: 'juri_name',
        sortDirection: 'asc',
        barangay: 'Bagonbon',
    );

    expect($paginator->total())->toBe(2)
        ->and($paginator->items())->toHaveKeys(['A', 'C']);

    $filtered = $service->paginateEstablishments(
        search: 'gamma',
        page: 1,
        recordsPerPage: 10,
        sortColumn: 'juri_name',
        sortDirection: 'asc',
        barangay: 'Bagonbon',
    );

    expect($filtered->total())->toBe(1)
        ->and($filtered->items())->toHaveKey('C');
});

it('applies the barangay filter to the affected msme table', function () {
    $scims = Mockery::mock(ScimsJuridicalApiService::class);
    $scims->shouldReceive('getAllBusinesses')->once()->andReturn(collect([
        ['entity_no' => 'A', 'juri_name' => 'Alpha Store', 'barangay' => 'Bagonbon', 'street' => 'Proper', 'line_of_industry' => 'Retail'],
        ['entity_no' => 'B', 'juri_name' => 'Beta Shop', 'barangay' => 'Buluangan', 'street' => 'Poblacion', 'line_of_industry' => 'Retail'],
        ['entity_no' => 'C', 'juri_name' => 'Gamma Trading', 'barangay' => 'Bagonbon', 'street' => 'Proper', 'line_of_industry' => 'Food'],
    ]));

    app()->instance(ScimsJuridicalApiService::class, $scims);

    Livewire::test(TableSelectLivewireComponent::class, [
        'tableConfiguration' => base64_encode(AffectedMsmeTable::class),
    ])
        ->filterTable('barangay', 'Bagonbon')
        ->assertCanSeeTableRecords(['A', 'C'])
        ->assertCanNotSeeTableRecords(['B']);
});

it('renders the barangay filter on the create incident page', function () {
    Http::fake([
        'cictd.app/api/scims/juridical*' => Http::response([
            'data' => [],
            'meta' => ['total' => 0, 'last_page' => 1],
        ]),
    ]);

    $calamity = Calamity::create([
        'name' => 'Typhoon Filter Test',
        'calamity_type' => 'TYPHOON',
        'declaration_date' => '2026-10-14',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('filament.negosyotrack.resources.calamity-monitoring.calamities.incident.create', [
            'calamity' => $calamity,
        ]))
        ->assertOk()
        ->assertSee('Barangay')
        ->assertSee('Bagonbon');
});
