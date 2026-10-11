<?php

namespace App\Filament\Widgets;

use App\Models\MsmeManagement\Juridical;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MsmeStatusDonutWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'msmeStatusDonut';

    protected static ?string $heading = 'Business Status Overview';

    protected static ?string $subheading = 'Active vs. Inactive businesses';

    protected static ?int $sort = 6;

    protected static ?int $contentHeight = 280;

    protected ?string $pollingInterval = null;

    protected static bool $deferLoading = true;

    protected function getOptions(): array
    {
        if (! $this->readyToLoad) {
            return [];
        }

        $active   = Juridical::where('bus_status', 'ACTIVE')->count();
        $inactive = Juridical::where('bus_status', 'INACTIVE')->count();

        return [
            'chart' => [
                'type'       => 'donut',
                'height'     => 280,
                'fontFamily' => 'inherit',
            ],
            'series' => [$active, $inactive],
            'labels' => ['Active', 'Inactive'],
            'colors' => ['#16A34A', '#DC2626'],
            'legend' => [
                'position'   => 'bottom',
                'fontFamily' => 'inherit',
                'fontWeight' => 600,
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '65%',
                        'labels' => [
                            'show'  => true,
                            'total' => [
                                'show'       => true,
                                'label'      => 'Total',
                                'fontFamily' => 'inherit',
                                'fontWeight' => 700,
                                'color'      => '#0f172a',
                            ],
                        ],
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'style'   => ['fontFamily' => 'inherit', 'fontWeight' => 700],
            ],
            'stroke' => ['width' => 2],
            'tooltip' => ['style' => ['fontFamily' => 'inherit']],
        ];
    }
}
