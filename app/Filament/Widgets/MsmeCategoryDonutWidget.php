<?php

namespace App\Filament\Widgets;

use App\Services\DashboardMetricsService;
use Filament\Support\RawJs;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MsmeCategoryDonutWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'msmeCategoryDonut';

    protected static ?string $heading = 'MSME Category Breakdown';

    protected static ?string $subheading = 'Micro, Small, and Medium from saved capitalization';

    protected static ?int $sort = 3;

    protected static ?int $contentHeight = 300;

    protected ?string $pollingInterval = null;

    protected static bool $deferLoading = true;

    protected ?array $categoryMetricsCache = null;

    protected function getOptions(): array
    {
        if (! $this->readyToLoad) {
            return [];
        }

        $metrics = $this->categoryMetrics();
        $hasData = $metrics['total'] > 0;

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
                'fontFamily' => 'inherit',
            ],
            'series' => $hasData ? $metrics['series'] : [],
            'labels' => $metrics['labels'],
            'colors' => ['#22C55E', '#3B82F6', '#F59E0B'],
            'legend' => [
                'position' => 'bottom',
                'fontFamily' => 'inherit',
                'fontWeight' => 600,
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '65%',
                        'labels' => [
                            'show' => true,
                            'total' => [
                                'show' => $hasData,
                                'label' => 'Total',
                                'fontFamily' => 'inherit',
                                'fontWeight' => 700,
                                'color' => '#6b7280',
                            ],
                        ],
                    ],
                ],
            ],
            'dataLabels' => ['enabled' => false],
            'stroke' => ['width' => 2],
            'tooltip' => [
                'enabled' => true,
                'style' => ['fontFamily' => 'inherit'],
            ],
            'states' => [
                'hover' => ['filter' => ['type' => 'none']],
                'active' => ['filter' => ['type' => 'none']],
            ],
            'noData' => [
                'text' => $hasData ? '' : 'No classified businesses yet',
                'align' => 'center',
                'verticalAlign' => 'middle',
                'style' => [
                    'color' => '#9ca3af',
                    'fontSize' => '13px',
                ],
            ],
        ];
    }

    /**
     * Add a JavaScript total formatter because chart option arrays cannot
     * carry PHP closures through JSON encoding.
     */
    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make('{
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            total: {
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                }
                            }
                        }
                    }
                }
            }
        }');
    }

    /**
     * @return array{labels: array<int, string>, series: array<int, int>, total: int}
     */
    protected function categoryMetrics(): array
    {
        return $this->categoryMetricsCache ??= app(DashboardMetricsService::class)
            ->msmeCategoryBreakdown();
    }
}
