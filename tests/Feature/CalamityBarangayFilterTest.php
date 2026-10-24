<?php

use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Pages\CreateIncident;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Tables\AffectedMsmeTable;
use App\Models\CalamityMonitoring\Calamity;
use App\Models\User;
use App\Services\CalamityMonitoringService;
use App\Services\ScimsJuridicalApiService;
use App\Support\Barangays;
use Filament\Forms\Components\TableSelect\Livewire\TableSelectLivewireComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['app.env' => 'local']);
});

function scimsRegistryFixture(): array
{
    return [
        ['entity_no' => 'A', 'juri_name' => 'Alpha Store', 'barangay' => 'Bagonbon', 'street' => 'Proper', 'line_of_industry' => 'Retail'],
        ['entity_no' => 'B', 'juri_name' => 'Beta Shop', 'barangay' => 'Buluangan', 'street' => 'Poblacion', 'line_of_industry' => 'Retail'],
        ['entity_no' => 'C', 'juri_name' => 'Gamma Trading', 'barangay' => 'Bagonbon', 'street' => 'Proper', 'line_of_industry' => 'Food'],
    ];
}

it('lists the eighteen san carlos city barangays', function () {
    expect(Barangays::options())
        ->toHaveCount(18)
        ->toHaveKey('Barangay I')
        ->and(Barangays::options()['Barangay I'])->toBe('Barangay I (Poblacion)')
        ->and(Barangays::options()['San Juan'])->toBe('San Juan');
});

it('filters the cached scims registry by barangay, industry, and search', function () {
    $scims = Mockery::mock(ScimsJuridicalApiService::class);
    $scims->shouldReceive('getAllBusinesses')->twice()->andReturn(collect(scimsRegistryFixture()));

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
        industry: 'Food',
    );

    expect($filtered->total())->toBe(1)
        ->and($filtered->items())->toHaveKey('C');
});

it('returns matching ids, counts, and industry options from the cached registry', function () {
    $scims = Mockery::mock(ScimsJuridicalApiService::class);
    $scims->shouldReceive('getAllBusinesses')->times(3)->andReturn(collect(scimsRegistryFixture()));

    $service = new CalamityMonitoringService($scims);

    expect($service->matchingEstablishmentIds(barangay: 'Bagonbon', industry: 'Retail'))->toBe(['A'])
        ->and($service->countEstablishments(barangay: 'Bagonbon'))->toBe(2)
        ->and($service->industryOptions())->toBe([
            'Food' => 'Food',
            'Retail' => 'Retail',
        ]);
});

it('snapshots establishments from the cached registry without api calls', function () {
    Cache::flush();
    Cache::put('scims.juridical.businesses', scimsRegistryFixture(), now()->addHour());

    Http::fake();

    $snapshots = app(CalamityMonitoringService::class)->snapshotEstablishments(['A']);

    expect($snapshots)->toBe([
        'A' => ['name' => 'Alpha Store', 'barangay' => 'Bagonbon'],
    ]);

    Http::assertNothingSent();
});

it('applies the barangay filter and exclusions to the affected msme table', function () {
    $scims = Mockery::mock(ScimsJuridicalApiService::class);
    $scims->shouldReceive('getAllBusinesses')->twice()->andReturn(collect(scimsRegistryFixture()));

    app()->instance(ScimsJuridicalApiService::class, $scims);

    Livewire::test(TableSelectLivewireComponent::class, [
        'tableConfiguration' => base64_encode(AffectedMsmeTable::class),
        'tableArguments' => ['barangay' => 'Bagonbon'],
    ])
        ->assertCanSeeTableRecords(['A', 'C'])
        ->assertCanNotSeeTableRecords(['B']);

    Livewire::test(TableSelectLivewireComponent::class, [
        'tableConfiguration' => base64_encode(AffectedMsmeTable::class),
        'tableArguments' => ['barangay' => 'Bagonbon', 'excluded' => ['A']],
    ])
        ->assertCanSeeTableRecords(['C'])
        ->assertCanNotSeeTableRecords(['A', 'B']);
});

it('renders the filters and bulk actions on the create incident page', function () {
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
        ->assertSee('Bagonbon')
        ->assertSee('Apply filter')
        ->assertDontSee('Select all matching')
        ->assertSee('Clear selection')
        ->assertSee('Review selected');
});

it('includes registry barangays that are not in the official list', function () {
    $scims = Mockery::mock(ScimsJuridicalApiService::class);
    $scims->shouldReceive('getAllBusinesses')->once()->andReturn(collect([
        ['entity_no' => 'X', 'juri_name' => 'Macapso Store', 'barangay' => 'Macapso', 'street' => 'Proper', 'line_of_industry' => 'Retail'],
    ]));

    $options = (new CalamityMonitoringService($scims))->barangayOptions();

    expect($options)
        ->toHaveKey('Macapso')
        ->toHaveCount(19);
});

it('keeps the selected msmes when the table moves to another page', function () {
    $records = collect(range(1, 30))
        ->map(fn (int $index): array => [
            'entity_no' => 'E'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'juri_name' => 'MSME '.$index,
            'barangay' => 'Bagonbon',
            'street' => 'Proper',
            'line_of_industry' => 'Retail',
        ])
        ->all();

    $scims = Mockery::mock(ScimsJuridicalApiService::class);
    $scims->shouldReceive('getAllBusinesses')->andReturn(collect($records));

    app()->instance(ScimsJuridicalApiService::class, $scims);

    Livewire::test(TableSelectLivewireComponent::class, [
        'tableConfiguration' => base64_encode(AffectedMsmeTable::class),
        'tableArguments' => [],
    ])
        ->set('state', ['E01'])
        ->call('setPage', 2)
        ->assertSet('state', ['E01']);
});

it('applies the current filters when the apply button is used', function () {
    Cache::flush();
    Cache::put('scims.juridical.businesses', scimsRegistryFixture(), now()->addHour());

    Http::fake();

    $calamity = Calamity::create([
        'name' => 'Typhoon Filter Apply Test',
        'calamity_type' => 'TYPHOON',
        'declaration_date' => '2026-10-14',
    ]);

    Livewire::test(CreateIncident::class, ['parentRecord' => $calamity])
        ->set('data.juridical_ids', ['A'])
        ->fillForm(['barangay_filter' => 'Bagonbon'])
        ->call('applyFilters')
        ->assertSet('filterVersion', 1)
        ->assertSet('data.juridical_ids', ['A'])
        ->assertSee('Alpha Store')
        ->assertSee('Gamma Trading')
        ->assertDontSee('Beta Shop');
});
