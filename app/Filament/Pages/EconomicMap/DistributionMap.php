<?php

namespace App\Filament\Pages\EconomicMap;

use App\Services\EconomicMapService;
use Filament\Support\Icons\Heroicon;

class DistributionMap extends BaseMapPage
{
    protected static ?string $navigationLabel = 'MSME Distribution Map';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::ChartPie;

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.economic-map.pages.distribution-map';

    public array $sectors = [];

    public array $barangays = [];

    public int $total = 0;

    public ?array $bounds = null;

    public bool $hasData = false;

    public ?string $activeSector = null;

    public array $sectorPins = [];

    public string $sectorFilter = '';

    public function getHeading(): string
    {
        return 'San Carlos City MSME Distribution Map';
    }

    public function getSubheading(): ?string
    {
        return 'MSME-based Economic Activity — San Carlos City Negosyo Center';
    }

    public function mount(): void
    {
        $this->loadData();
    }

    protected function loadData(): void
    {
        $service = app(EconomicMapService::class);
        $distribution = $service->distribution();

        $this->sectors = $distribution['sectors'];
        $this->barangays = $distribution['barangays'];
        $this->total = $distribution['total'];
        $this->bounds = $service->bounds();
        $this->hasData = $this->total > 0;
        $this->loadUnsetCount();
    }

    public function selectSector(string $sector): void
    {
        $this->activeSector = $sector;
        $this->sectorFilter = $sector;
        $this->sectorPins = app(EconomicMapService::class)->sectorBusinesses($sector);

        $this->dispatch('emap-sector-pins', [
            'sector' => $sector,
            'pins' => $this->sectorPins,
        ]);
    }

    public function updatedSectorFilter(): void
    {
        if (trim($this->sectorFilter) === '') {
            $this->clearSector();

            return;
        }

        $this->selectSector($this->sectorFilter);
    }

    public function clearSector(): void
    {
        $this->reset('activeSector', 'sectorPins', 'sectorFilter');
        $this->dispatch('emap-clear-pins');
    }
}
