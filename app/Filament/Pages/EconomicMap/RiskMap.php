<?php

namespace App\Filament\Pages\EconomicMap;

use App\Services\EconomicMapService;
use Filament\Support\Icons\Heroicon;

class RiskMap extends BaseMapPage
{
    protected static ?string $navigationLabel = 'Economic Risk Map';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::ExclamationTriangle;

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.economic-map.pages.risk-map';

    public array $barangays = [];

    public array $levels = [];

    public int $assessed = 0;

    public float $totalDamage = 0.0;

    public int $totalAffected = 0;

    public array $calamities = [];

    public ?string $selectedCalamity = null;

    public ?array $bounds = null;

    public bool $hasData = false;

    public function getHeading(): string
    {
        return 'San Carlos City Economic Risk Map';
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
        $risk = $service->risk(filled($this->selectedCalamity) ? (int) $this->selectedCalamity : null);

        $this->barangays = $risk['barangays'];
        $this->levels = $risk['levels'];
        $this->assessed = $risk['assessed'];
        $this->totalDamage = $risk['total_damage'];
        $this->totalAffected = $risk['total_affected'];
        $this->calamities = $risk['calamities'];
        $this->bounds = $service->bounds();
        $this->hasData = $this->assessed > 0;
        $this->loadUnsetCount();
    }

    public function updatedSelectedCalamity(): void
    {
        $this->loadData();
        $this->dispatch('emap-data-refreshed');
    }

    public function selectedCalamityName(): ?string
    {
        if (! $this->selectedCalamity) {
            return null;
        }

        foreach ($this->calamities as $calamity) {
            if ((string) ($calamity['id'] ?? '') === (string) $this->selectedCalamity) {
                return $calamity['name'] ?? null;
            }
        }

        return null;
    }
}
