<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MsmeStatusDonutWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'msmeStatusDonut';

    protected static ?string $heading = 'Business Status Overview';

    protected static ?string $subheading = 'Data available once SCIMS aggregate endpoint is connected';

    protected static ?int $sort = 6;

    protected static ?int $contentHeight = 280;

    protected ?string $pollingInterval = null;

    protected static bool $deferLoading = true;

    protected function getOptions(): array
    {
        if (! $this->readyToLoad) {
            return [];
        }

        return [
            'chart' => [
                'type'       => 'donut',
                'height'     => 280,
                'fontFamily' => 'inherit',
            ],
            'series' => [0, 0],
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
                                'label'      => 'No data',
                                'fontFamily' => 'inherit',
                                'fontWeight' => 700,
                                'color'      => '#9ca3af',
                                'formatter'  => 'function() { return "—" }',
                            ],
                        ],
                    ],
                ],
            ],
            'dataLabels' => ['enabled' => false],
            'stroke'     => ['width' => 2],
            'tooltip'    => ['enabled' => false],
            'states'     => [
                'hover'  => ['filter' => ['type' => 'none']],
                'active' => ['filter' => ['type' => 'none']],
            ],
        ];
    }
}
