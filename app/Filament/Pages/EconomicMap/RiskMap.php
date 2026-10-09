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

    public int $calamityCount = 0;

    public float $totalDamage = 0.0;

    public int $totalAffected = 0;

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
        $feed = $service->calamityFeed();

        $this->calamityCount = count($feed->calamities());

        $damage = $feed->damageByBarangay();
        $this->totalDamage = $damage['total_damage'];
        $this->totalAffected = $damage['total_affected'];

        $this->loadUnsetCount();
    }
}
