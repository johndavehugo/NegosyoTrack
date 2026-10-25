<?php

namespace App\Filament\Widgets;

use App\Models\CalamityMonitoring\Calamity;
use App\Services\CalamityMonitoring\ReportService;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class CalamityNatureDonutWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'calamityNatureDonut';

    protected static ?string $heading = 'Damage Classification';

    protected static ?string $subheading = 'Incident records by partial or total damage';

    protected static ?int $contentHeight = 280;

    protected ?string $pollingInterval = null;

    protected static bool $deferLoading = true;

    public ?string $calamityId = null;

    protected function getOptions(): array
    {
        if (! $this->readyToLoad || blank($this->calamityId)) {
            return [];
        }

        $calamity = Calamity::find($this->calamityId);

        if (! $calamity) {
            return $this->emptyOptions();
        }

        $rows = collect(app(ReportService::class)->build($calamity)['by_nature'])
            ->filter(fn (array $row): bool => $row['count'] > 0)
            ->values();

        if ($rows->sum('count') === 0) {
            return $this->emptyOptions();
        }

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 280,
                'fontFamily' => 'inherit',
                'toolbar' => ['show' => false],
            ],
            'series' => $rows->pluck('count')->all(),
            'labels' => $rows->pluck('label')->all(),
            'colors' => ['#F59E0B', '#DC2626'],
            'legend' => [
                'position' => 'bottom',
                'fontFamily' => 'inherit',
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '62%',
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'style' => ['fontFamily' => 'inherit'],
            ],
            'stroke' => ['width' => 2, 'colors' => ['#fff']],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyOptions(): array
    {
        return [
            'chart' => ['type' => 'donut', 'height' => 280],
            'series' => [],
            'labels' => [],
            'colors' => ['#F59E0B', '#DC2626'],
        ];
    }
}
