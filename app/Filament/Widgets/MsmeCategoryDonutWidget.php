<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MsmeCategoryDonutWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'msmeCategoryDonut';

    protected static ?string $heading = 'MSME Category Breakdown';

    protected static ?string $subheading = 'Data available once SCIMS aggregate endpoint is connected';

    protected static ?int $sort = 3;

    protected static ?int $contentHeight = 300;

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
                'height'     => 300,
                'fontFamily' => 'inherit',
            ],
            'series' => [0, 0, 0, 0],
            'labels' => ['Micro', 'Small', 'Medium', 'Large'],
            'colors' => ['#3B82F6', '#2563EB', '#1D4ED8', '#1E3A8A'],
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
