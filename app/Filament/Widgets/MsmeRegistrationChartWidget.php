<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MsmeRegistrationChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'msmeRegistrationChart';

    protected static ?string $heading = 'Monthly Business Registrations & Renewals';

    protected static ?string $subheading = 'Data available once SCIMS aggregate endpoint is connected';

    protected static ?int $sort = 2;

    protected static ?int $contentHeight = 300;

    protected ?string $pollingInterval = null;

    protected static bool $deferLoading = true;

    protected function getOptions(): array
    {
        if (! $this->readyToLoad) {
            return [];
        }

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                   'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        return [
            'chart' => [
                'type'       => 'bar',
                'height'     => 300,
                'stacked'    => false,
                'toolbar'    => ['show' => false],
                'fontFamily' => 'inherit',
            ],
            'series' => [
                ['name' => 'New Registrations', 'data' => array_fill(0, 12, 0)],
                ['name' => 'Renewals',           'data' => array_fill(0, 12, 0)],
            ],
            'xaxis' => [
                'categories' => $months,
                'labels'     => ['style' => ['fontFamily' => 'inherit', 'fontWeight' => 600]],
            ],
            'yaxis' => [
                'min'    => 0,
                'labels' => ['style' => ['fontFamily' => 'inherit']],
            ],
            'colors'      => ['#DC2626', '#1D4ED8'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius'            => 4,
                    'columnWidth'             => '55%',
                    'borderRadiusApplication' => 'end',
                ],
            ],
            'dataLabels' => ['enabled' => false],
            'legend'     => [
                'position'   => 'top',
                'fontFamily' => 'inherit',
                'fontWeight' => 600,
            ],
            'grid' => [
                'borderColor'     => '#f1f5f9',
                'strokeDashArray' => 4,
            ],
            'tooltip' => ['enabled' => false],
            'noData'  => [
                'text'          => 'Awaiting SCIMS data',
                'align'         => 'center',
                'verticalAlign' => 'middle',
                'style'         => [
                    'color'    => '#9ca3af',
                    'fontSize' => '13px',
                ],
            ],
        ];
    }
}
