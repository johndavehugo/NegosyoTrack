<?php

namespace App\Filament\Pages\EconomicMap;

use App\Services\EconomicMapService;
use Filament\Support\Icons\Heroicon;

class OpportunityMap extends BaseMapPage
{
    protected static ?string $navigationLabel = 'Economic Opportunity Map';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Sparkles;

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.economic-map.pages.opportunity-map';

    public int $geocodedCount = 0;

    public function getHeading(): string
    {
        return 'San Carlos City Economic Opportunity Map';
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
        $this->geocodedCount = count(app(EconomicMapService::class)->geocodedRows());
        $this->loadUnsetCount();
    }
}
