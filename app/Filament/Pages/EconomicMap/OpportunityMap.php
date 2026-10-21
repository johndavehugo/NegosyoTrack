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

    public array $ranked = [];

    public int $barangayCount = 0;

    public int $highVeryHigh = 0;

    public array $highlights = [];

    public array $howCalculated = [];

    public ?array $bounds = null;

    public bool $hasData = false;

    public function getHeading(): string
    {
        return 'San Carlos City Economic Opportunity Map';
    }

    public function getSubheading(): ?string
    {
        return 'Investment & Development Guidance — San Carlos City Negosyo Center';
    }

    public function mount(): void
    {
        $this->loadData();
    }

    protected function loadData(): void
    {
        $service = app(EconomicMapService::class);
        $data = $service->opportunities();

        $this->ranked = $data['ranked'];
        $this->barangayCount = $data['barangay_count'];
        $this->highVeryHigh = $data['high_very_high'];
        $this->highlights = $data['highlights'];
        $this->howCalculated = $data['how_calculated'];
        $this->bounds = $service->bounds();
        $this->hasData = count($this->ranked) > 0;
    }
}
