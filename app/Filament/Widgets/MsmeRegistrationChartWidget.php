<?php

namespace App\Filament\Widgets;

use App\Services\DashboardMetricsService;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MsmeRegistrationChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'msmeRegistrationChart';

    protected static ?string $heading = 'Monthly Business Registrations & Renewals';

    protected static ?int $sort = 2;

    protected static ?int $contentHeight = 300;

    protected ?string $pollingInterval = null;

    protected static bool $deferLoading = true;

    protected ?array $registrationMetricsCache = null;

    protected function getOptions(): array
    {
        if (! $this->readyToLoad) {
            return [];
        }

        $metrics = $this->registrationMetrics();
        $hasData = $metrics['total'] > 0;

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
                'stacked' => false,
                'toolbar' => ['show' => false],
                'fontFamily' => 'inherit',
            ],
            'series' => $hasData ? [
                ['name' => 'New Registrations', 'data' => $metrics['new_registrations']],
                ['name' => 'Renewals', 'data' => $metrics['renewals']],
            ] : [],
            'xaxis' => [
                'categories' => $metrics['labels'],
                'labels' => ['style' => ['fontFamily' => 'inherit', 'fontWeight' => 600]],
            ],
            'yaxis' => [
                'min' => 0,
                'labels' => ['style' => ['fontFamily' => 'inherit']],
            ],
            'colors' => ['#2563EB', '#16A34A'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'columnWidth' => '55%',
                    'borderRadiusApplication' => 'end',
                ],
            ],
            'dataLabels' => ['enabled' => false],
            'legend' => [
                'position' => 'top',
                'fontFamily' => 'inherit',
                'fontWeight' => 600,
            ],
            'grid' => [
                'borderColor' => '#f1f5f9',
                'strokeDashArray' => 4,
            ],
            'tooltip' => [
                'enabled' => true,
                'shared' => true,
                'intersect' => false,
                'style' => ['fontFamily' => 'inherit'],
            ],
            'noData' => [
                'text' => $hasData ? '' : 'No registration data for '.$metrics['year'],
                'align' => 'center',
                'verticalAlign' => 'middle',
                'style' => [
                    'color' => '#9ca3af',
                    'fontSize' => '13px',
                ],
            ],
        ];
    }

    protected function getSubheading(): string
    {
        return 'New vs. renewal registrations for '.now()->year;
    }

    /**
     * @return array{year: int, labels: array<int, string>, new_registrations: array<int, int>, renewals: array<int, int>, total: int}
     */
    protected function registrationMetrics(): array
    {
        return $this->registrationMetricsCache ??= app(DashboardMetricsService::class)
            ->monthlyRegistrations();
    }
}
