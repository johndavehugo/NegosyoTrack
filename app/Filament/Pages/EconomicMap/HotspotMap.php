<?php

namespace App\Filament\Pages\EconomicMap;

use App\Services\EconomicMapService;
use Filament\Support\Icons\Heroicon;

class HotspotMap extends BaseMapPage
{
    protected static ?string $navigationLabel = 'Economic Hotspot Map';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Fire;

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.economic-map.pages.hotspot-map';

    public array $ranked = [];

    public int $total = 0;

    public ?array $top = null;

    public int $barangayCount = 0;

    public ?array $bounds = null;

    public bool $hasData = false;

    public array $barangayOptions = [];

    public function getHeading(): string
    {
        return 'San Carlos City Economic Hotspot Map';
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
        $hotspots = $service->hotspots();

        $this->ranked = $hotspots['ranked'];
        $this->total = $hotspots['total'];
        $this->top = $hotspots['top'];
        $this->barangayCount = $hotspots['barangay_count'];
        $this->bounds = $service->bounds();
        $this->hasData = $this->total > 0;
        $this->barangayOptions = $service->locationOptions()['barangays'];
        $this->loadUnsetCount();
    }
}
