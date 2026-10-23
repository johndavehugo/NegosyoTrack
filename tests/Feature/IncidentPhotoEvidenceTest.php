<?php

use App\Filament\Resources\CalamityMonitoring\Calamities\Pages\ManageIncidents;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Pages\CreateIncident;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Pages\EditIncident;
use App\Models\CalamityMonitoring\Calamity;
use App\Models\CalamityMonitoring\Incident;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

function photoEvidenceFixture(): array
{
    $calamity = Calamity::create([
        'name' => 'Typhoon Photo Test',
        'calamity_type' => 'TYPHOON',
        'declaration_date' => '2026-10-14',
    ]);

    $incident = Incident::create([
        'calamity_id' => $calamity->id,
        'juridical_id' => 'ENTITY001',
        'juridical_name' => 'Alpha Store',
        'barangay' => 'Barangay I',
        'date_occurred' => '2026-10-14',
        'nature_of_damage' => 'PARTIAL',
        'estimated_cost_of_damages' => 100000,
        'status' => 'PENDING_VERIFICATION',
    ]);

    return [$calamity, $incident];
}

it('uploads photo evidence when editing an incident', function () {
    [$calamity, $incident] = photoEvidenceFixture();

    Livewire::test(EditIncident::class, [
        'parentRecord' => $calamity,
        'record' => $incident->id,
    ])
        ->fillForm([
            'photos' => [UploadedFile::fake()->image('damage.jpg')],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $incident->refresh();

    expect($incident->photos)->toHaveCount(1);

    Storage::disk('public')->assertExists($incident->photos[0]);
});

it('deletes photo files removed from an incident', function () {
    [$calamity, $incident] = photoEvidenceFixture();

    $photo = 'calamity-incidents/photos/removed.jpg';
    Storage::disk('public')->put($photo, 'image-content');
    $incident->update(['photos' => [$photo]]);

    Livewire::test(EditIncident::class, [
        'parentRecord' => $calamity,
        'record' => $incident->id,
    ])
        ->fillForm(['photos' => []])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($incident->refresh()->photos)->toBeEmpty();

    Storage::disk('public')->assertMissing($photo);
});

it('uploads separate photo evidence for each affected msme during bulk create', function () {
    [$calamity, $existingIncident] = photoEvidenceFixture();
    $existingIncident->delete();

    Http::fake([
        '*juridical?*' => Http::response([
            'data' => [
                [
                    'entity_no' => 'ENTITY001',
                    'juri_name' => 'Alpha Store',
                    'barangay' => 'Barangay I',
                ],
                [
                    'entity_no' => 'ENTITY002',
                    'juri_name' => 'Beta Shop',
                    'barangay' => 'Barangay II',
                ],
            ],
            'meta' => ['total' => 2],
        ]),
        '*juridical/*' => Http::response([
            'data' => [
                'entity_no' => 'ENTITY001',
                'juri_name' => 'Alpha Store',
                'barangay' => 'Barangay I',
            ],
        ]),
    ]);

    Livewire::test(CreateIncident::class, [
        'parentRecord' => $calamity,
    ])
        ->fillForm([
            'juridical_ids' => ['ENTITY001', 'ENTITY002'],
        ])
        ->fillForm([
            'damages' => [
                [
                    'juridical_id' => 'ENTITY001',
                    'juridical_name' => 'Alpha Store',
                    'barangay' => 'Barangay I',
                    'estimated_cost_of_damages' => 100000,
                    'photos' => [UploadedFile::fake()->image('alpha.jpg')],
                ],
                [
                    'juridical_id' => 'ENTITY002',
                    'juridical_name' => 'Beta Shop',
                    'barangay' => 'Barangay II',
                    'estimated_cost_of_damages' => 200000,
                    'photos' => [UploadedFile::fake()->image('beta.jpg')],
                ],
            ],
            'date_occurred' => '2026-10-14',
            'nature_of_damage' => 'PARTIAL',
            'status' => 'PENDING_VERIFICATION',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $incidents = Incident::query()
        ->where('calamity_id', $calamity->id)
        ->orderBy('juridical_id')
        ->get();

    expect($incidents)->toHaveCount(2)
        ->and($incidents[0]->photos)->toHaveCount(1)
        ->and($incidents[1]->photos)->toHaveCount(1)
        ->and($incidents[0]->photos[0])->not->toBe($incidents[1]->photos[0]);

    Storage::disk('public')->assertExists($incidents[0]->photos[0]);
    Storage::disk('public')->assertExists($incidents[1]->photos[0]);
});

it('renders photo evidence in the printable report', function () {
    [$calamity, $incident] = photoEvidenceFixture();

    $photo = 'calamity-incidents/photos/report.jpg';
    Storage::disk('public')->put($photo, 'image-content');
    $incident->update(['photos' => [$photo]]);

    $this->actingAs(User::factory()->create())
        ->get(route('calamity-monitoring.report', ['calamity' => $calamity]))
        ->assertOk()
        ->assertSee('Photo Evidence')
        ->assertSee('Alpha Store')
        ->assertSee(Storage::disk('public')->url($photo));
});

it('shows photo evidence on the manage incidents table', function () {
    [$calamity] = photoEvidenceFixture();

    $this->actingAs(User::factory()->create())
        ->get(ManageIncidents::getUrl(['record' => $calamity]))
        ->assertOk()
        ->assertSee('Photo Evidence');
});

it('shows a view images action and modal for photo evidence', function () {
    [$calamity, $incident] = photoEvidenceFixture();

    $photo = 'calamity-incidents/photos/modal.jpg';
    Storage::disk('public')->put($photo, 'image-content');
    $incident->update(['photos' => [$photo]]);

    Livewire::test(ManageIncidents::class, ['record' => $calamity->id])
        ->assertActionVisible(TestAction::make('viewPhotos')->table($incident))
        ->mountAction(TestAction::make('viewPhotos')->table($incident))
        ->assertMountedActionModalSee('Open full image')
        ->assertMountedActionModalSee(Storage::disk('public')->url($photo));
});

it('hides the view images action when there is no photo evidence', function () {
    [$calamity, $incident] = photoEvidenceFixture();

    Livewire::test(ManageIncidents::class, ['record' => $calamity->id])
        ->assertActionHidden(TestAction::make('viewPhotos')->table($incident));
});
