<?php

use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Pages\CreateIncident;
use App\Models\CalamityMonitoring\Calamity;
use App\Models\CalamityMonitoring\Incident;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['app.env' => 'local']);

    Cache::flush();
    Storage::fake('public');
});

function fakeScimsRegistry(): void
{
    Http::fake([
        'cictd.app/api/scims/juridical*' => Http::response([
            'data' => [
                ['entity_no' => 'A', 'juri_name' => 'Alpha Store', 'barangay' => 'Bagonbon', 'street' => 'Proper', 'line_of_industry' => 'Retail'],
                ['entity_no' => 'B', 'juri_name' => 'Beta Shop', 'barangay' => 'Buluangan', 'street' => 'Poblacion', 'line_of_industry' => 'Retail'],
                ['entity_no' => 'C', 'juri_name' => 'Gamma Trading', 'barangay' => 'Bagonbon', 'street' => 'Proper', 'line_of_industry' => 'Food'],
            ],
            'meta' => ['total' => 3, 'last_page' => 1],
        ]),
    ]);
}

function createIncidentCalamity(): Calamity
{
    return Calamity::create([
        'name' => 'Typhoon Workflow Test',
        'calamity_type' => 'TYPHOON',
        'declaration_date' => '2026-10-14',
    ]);
}

it('builds damages for manually selected msmes, applies a bulk cost, and creates incidents', function () {
    fakeScimsRegistry();
    $calamity = createIncidentCalamity();

    $component = Livewire::test(CreateIncident::class, ['parentRecord' => $calamity])
        ->fillForm([
            'date_occurred' => '2026-10-14',
            'nature_of_damage' => 'PARTIAL',
            'status' => 'PENDING_VERIFICATION',
        ])
        ->set('data.juridical_ids', ['A', 'C']);

    $component->assertSet('data.juridical_ids', ['A', 'C']);

    $damages = collect($component->get('data.damages'))->values();

    expect($damages)->toHaveCount(2)
        ->and($damages->pluck('juridical_id')->all())->toBe(['A', 'C']);

    $component
        ->fillForm(['bulk_cost' => 1500])
        ->call('applyCostToAll');

    $damages = collect($component->get('data.damages'))->values();

    expect($damages)->toHaveCount(2)
        ->and($damages->pluck('juridical_id')->all())->toBe(['A', 'C'])
        ->and((float) $damages[0]['estimated_cost_of_damages'])->toBe(1500.0)
        ->and((float) $damages[1]['estimated_cost_of_damages'])->toBe(1500.0);

    $component
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Incident::query()->where('calamity_id', $calamity->id)->count())->toBe(2)
        ->and((float) Incident::query()->where('juridical_id', 'A')->value('estimated_cost_of_damages'))->toBe(1500.0);
});

it('skips msmes that already have an incident for the calamity', function () {
    fakeScimsRegistry();
    $calamity = createIncidentCalamity();

    Incident::create([
        'calamity_id' => $calamity->id,
        'juridical_id' => 'A',
        'juridical_name' => 'Alpha Store',
        'barangay' => 'Bagonbon',
        'date_occurred' => '2026-10-14',
        'nature_of_damage' => 'PARTIAL',
        'estimated_cost_of_damages' => 1000,
        'status' => 'PENDING_VERIFICATION',
    ]);

    $component = Livewire::test(CreateIncident::class, ['parentRecord' => $calamity])
        ->fillForm([
            'date_occurred' => '2026-10-14',
            'nature_of_damage' => 'PARTIAL',
            'status' => 'PENDING_VERIFICATION',
            'juridical_ids' => ['A', 'B'],
        ]);

    $component
        ->fillForm([
            'damages' => [
                [
                    'juridical_id' => 'A',
                    'juridical_name' => 'Alpha Store',
                    'barangay' => 'Bagonbon',
                    'estimated_cost_of_damages' => 100,
                ],
                [
                    'juridical_id' => 'B',
                    'juridical_name' => 'Beta Shop',
                    'barangay' => 'Buluangan',
                    'estimated_cost_of_damages' => 200,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Incident::query()->where('calamity_id', $calamity->id)->count())->toBe(2)
        ->and((float) Incident::query()->where('juridical_id', 'A')->value('estimated_cost_of_damages'))->toBe(1000.0)
        ->and((float) Incident::query()->where('juridical_id', 'B')->value('estimated_cost_of_damages'))->toBe(200.0);
});

it('removes a selected msme from the selection', function () {
    fakeScimsRegistry();
    $calamity = createIncidentCalamity();

    $component = Livewire::test(CreateIncident::class, ['parentRecord' => $calamity])
        ->fillForm([
            'juridical_ids' => ['A', 'B'],
        ]);

    $component->call('removeMsme', 'A');

    $damages = collect($component->get('data.damages'))->values();

    expect($component->get('data.juridical_ids'))->toBe(['B'])
        ->and($damages)->toHaveCount(1)
        ->and($damages[0]['juridical_id'])->toBe('B');
});

it('prevents duplicate incidents at the database level', function () {
    $calamity = createIncidentCalamity();

    Incident::create([
        'calamity_id' => $calamity->id,
        'juridical_id' => 'A',
        'juridical_name' => 'Alpha Store',
        'barangay' => 'Bagonbon',
        'date_occurred' => '2026-10-14',
        'nature_of_damage' => 'PARTIAL',
        'estimated_cost_of_damages' => 1000,
        'status' => 'PENDING_VERIFICATION',
    ]);

    expect(fn () => Incident::create([
        'calamity_id' => $calamity->id,
        'juridical_id' => 'A',
        'juridical_name' => 'Alpha Store',
        'barangay' => 'Bagonbon',
        'date_occurred' => '2026-10-14',
        'nature_of_damage' => 'TOTAL',
        'estimated_cost_of_damages' => 2000,
        'status' => 'VERIFIED',
    ]))->toThrow(QueryException::class);
});
