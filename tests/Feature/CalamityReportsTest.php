<?php

use App\Filament\Pages\CalamityMonitoring\Reports;
use App\Filament\Resources\CalamityMonitoring\Calamities\CalamityResource;
use App\Filament\Resources\CalamityMonitoring\Calamities\Pages\ManageIncidents;
use App\Filament\Widgets\CalamityDamageByBarangayChartWidget;
use App\Filament\Widgets\CalamityNatureDonutWidget;
use App\Filament\Widgets\CalamityStatusDonutWidget;
use App\Models\CalamityMonitoring\Calamity;
use App\Models\CalamityMonitoring\Incident;
use App\Models\User;
use App\Services\CalamityMonitoring\ReportExporter;
use App\Services\CalamityMonitoring\ReportService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['app.env' => 'local']);
});

function calamityReportFixture(): array
{
    $calamity = Calamity::create([
        'name' => 'Typhoon Report Test',
        'calamity_type' => 'TYPHOON',
        'declaration_date' => '2026-10-14',
    ]);

    Incident::create([
        'calamity_id' => $calamity->id,
        'juridical_id' => 'ENTITY001',
        'juridical_name' => 'Alpha Store',
        'barangay' => 'Barangay II',
        'date_occurred' => '2026-10-14',
        'nature_of_damage' => 'TOTAL',
        'estimated_cost_of_damages' => 1000000,
        'status' => 'VERIFIED',
    ]);

    Incident::create([
        'calamity_id' => $calamity->id,
        'juridical_id' => 'ENTITY002',
        'juridical_name' => 'Beta Shop',
        'barangay' => 'Barangay II',
        'date_occurred' => '2026-10-14',
        'nature_of_damage' => 'PARTIAL',
        'estimated_cost_of_damages' => 500000,
        'status' => 'PENDING_VERIFICATION',
    ]);

    Incident::create([
        'calamity_id' => $calamity->id,
        'juridical_id' => 'ENTITY003',
        'juridical_name' => 'Gamma Trading',
        'barangay' => 'Barangay IV',
        'date_occurred' => '2026-10-14',
        'nature_of_damage' => 'TOTAL',
        'estimated_cost_of_damages' => 250000,
        'status' => 'AID_RELEASED',
    ]);

    return [$calamity, Incident::query()->where('calamity_id', $calamity->id)];
}

it('builds the calamity report summary and barangay breakdown', function () {
    [$calamity] = calamityReportFixture();

    $report = app(ReportService::class)->build($calamity);

    expect($report['summary']['incidents'])->toBe(3)
        ->and($report['summary']['affected'])->toBe(3)
        ->and($report['summary']['total_damage'])->toBe(1750000.0)
        ->and($report['summary']['verified'])->toBe(1)
        ->and($report['summary']['pending'])->toBe(1)
        ->and($report['summary']['aid_released'])->toBe(1)
        ->and($report['summary']['partial'])->toBe(1)
        ->and($report['summary']['total'])->toBe(2)
        ->and($report['by_barangay'])->toHaveCount(2);

    $barangayTwo = collect($report['by_barangay'])->firstWhere('barangay', 'Barangay II');

    expect($barangayTwo['affected'])->toBe(2)
        ->and($barangayTwo['incidents'])->toBe(2)
        ->and($barangayTwo['damage'])->toBe(1500000.0)
        ->and($barangayTwo['share'])->toBe(85.7);
});

it('renders the printable damage assessment report', function () {
    [$calamity] = calamityReportFixture();

    $this->actingAs(User::factory()->create())
        ->get(route('calamity-monitoring.report', ['calamity' => $calamity]))
        ->assertOk()
        ->assertSee('MSME Calamity Damage Assessment Report')
        ->assertSee('Typhoon Report Test')
        ->assertSee('Alpha Store')
        ->assertSee('Barangay II')
        ->assertSee('₱1,750,000.00');
});

it('renders the manage incidents page with export actions', function () {
    [$calamity] = calamityReportFixture();

    $this->actingAs(User::factory()->create())
        ->get(ManageIncidents::getUrl(['record' => $calamity]))
        ->assertOk()
        ->assertDontSee('Print report')
        ->assertSee('Export Excel')
        ->assertSee('Export CSV')
        ->assertDontSee('CalamityDamageByBarangayChartWidget')
        ->assertDontSee('CalamityStatusDonutWidget')
        ->assertDontSee('CalamityNatureDonutWidget');
});

it('does not show print report actions on the calamities list', function () {
    calamityReportFixture();

    $this->actingAs(User::factory()->create())
        ->get(CalamityResource::getUrl())
        ->assertOk()
        ->assertDontSee('Print report');
});

it('loads the calamity chart widgets', function () {
    [$calamity] = calamityReportFixture();

    foreach ([
        CalamityDamageByBarangayChartWidget::class,
        CalamityStatusDonutWidget::class,
        CalamityNatureDonutWidget::class,
    ] as $widget) {
        Livewire::test($widget, ['calamityId' => (string) $calamity->id])
            ->call('loadWidget')
            ->assertOk();
    }
});

it('renders the calamity reports sidebar page', function () {
    [$calamity] = calamityReportFixture();

    expect(Reports::getNavigationGroup())->toBe('Calamity Monitoring')
        ->and(Reports::getNavigationLabel())->toBe('Reports');

    $this->actingAs(User::factory()->create())
        ->get(Reports::getUrl())
        ->assertOk()
        ->assertSee('Calamity Reports')
        ->assertDontSee('Report filters')
        ->assertSee('CalamityDamageByBarangayChartWidget')
        ->assertSee('CalamityStatusDonutWidget')
        ->assertSee('CalamityNatureDonutWidget')
        ->assertSee('View report')
        ->assertSee('Excel')
        ->assertSee('CSV')
        ->assertSee('Typhoon Report Test');
});

it('defaults the reports page charts to the latest calamity', function () {
    [$calamity] = calamityReportFixture();

    Livewire::test(Reports::class)
        ->assertSet('calamityId', (string) $calamity->id);
});

it('downloads a report from the calamity reports page', function () {
    [$calamity] = calamityReportFixture();

    Livewire::test(Reports::class)
        ->callAction(TestAction::make('exportExcel')->table($calamity))
        ->assertFileDownloaded();
});

it('downloads csv and xlsx reports from the manage incidents page', function () {
    [$calamity] = calamityReportFixture();

    Livewire::test(ManageIncidents::class, ['record' => $calamity->id])
        ->callAction(TestAction::make('exportExcel')->table())
        ->assertFileDownloaded();

    Livewire::test(ManageIncidents::class, ['record' => $calamity->id])
        ->callAction(TestAction::make('exportCsv')->table())
        ->assertFileDownloaded();
});

it('exports the filtered incident query', function () {
    [$calamity, $query] = calamityReportFixture();

    $response = app(ReportExporter::class)->download(
        $calamity,
        $query->where('status', 'VERIFIED'),
        'csv',
    );

    $contents = file_get_contents($response->getFile()->getPathname());

    expect($contents)->toContain('Alpha Store')
        ->toContain('ENTITY001')
        ->not->toContain('Beta Shop');
});

it('exports a multi-sheet xlsx report', function () {
    [$calamity, $query] = calamityReportFixture();

    $response = app(ReportExporter::class)->download($calamity, $query, 'xlsx');

    $reader = new XlsxReader;
    $reader->open($response->getFile()->getPathname());

    $sheetNames = [];
    $contents = [];

    foreach ($reader->getSheetIterator() as $sheet) {
        $sheetNames[] = $sheet->getName();

        foreach ($sheet->getRowIterator() as $row) {
            $contents[] = implode('|', array_map(
                fn ($cell): string => (string) $cell->getValue(),
                $row->getCells(),
            ));
        }
    }

    $reader->close();

    expect($sheetNames)->toBe(['Summary', 'Barangay Summary', 'Incident Details'])
        ->and(implode("\n", $contents))
        ->toContain('Alpha Store')
        ->toContain('Barangay II')
        ->toContain('1750000');
});
