<?php

namespace App\Filament\Widgets;

use App\Services\EconomicMapService;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class IndustryDistributionChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'industryDistributionChart';

    protected static ?string $heading = 'MSME Industry Distribution';

    protected static ?string $subheading = 'Top sectors by number of registered businesses';

    protected static ?int $sort = 4;

    protected static ?int $contentHeight = 320;

    protected ?string $pollingInterval = null;

    protected static bool $deferLoading = true;

    protected function getOptions(): array
    {
        if (! $this->readyToLoad) {
            return [];
        }

        /** @var EconomicMapService $service */
        $service = app(EconomicMapService::class);
        $distribution = $service->distribution();

        // Top 8 sectors by count
        $sectors = collect($distribution['sectors'])
            ->filter(fn ($s) => $s['count'] > 0)
            ->take(8);

        $labels = $sectors->pluck('name')
            ->map(fn ($n) => strlen($n) > 30 ? substr($n, 0, 27).'...' : $n)
            ->values()
            ->toArray();

        $data = $sectors->pluck('count')->values()->toArray();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 320,
                'fontFamily' => 'inherit',
                'toolbar' => ['show' => false],
            ],
            'series' => [
                [
                    'name' => 'Businesses',
                    'data' => $data,
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
                'labels' => [
                    'style' => ['fontFamily' => 'inherit', 'fontSize' => '11px'],
                    'trim' => true,
                ],
            ],
            'yaxis' => [
                'labels' => ['style' => ['fontFamily' => 'inherit']],
            ],
            'colors' => ['#DC2626'],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => true,
                    'borderRadius' => 4,
                    'distributed' => true,
                    'barHeight' => '65%',
                    'dataLabels' => ['position' => 'top'],
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'offsetX' => 14,
                'style' => [
                    'fontFamily' => 'inherit',
                    'fontWeight' => 700,
                    'colors' => ['#0f172a'],
                ],
            ],
            'legend' => ['show' => false],
            'grid' => [
                'borderColor' => '#f1f5f9',
                'strokeDashArray' => 4,
            ],
            'tooltip' => ['style' => ['fontFamily' => 'inherit']],
        ];
    }
}