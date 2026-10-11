<?php

namespace App\Filament\Widgets;

use App\Models\MsmeManagement\Juridical;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MsmeCategoryDonutWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'msmeCategoryDonut';

    protected static ?string $heading = 'MSME Category Breakdown';

    protected static ?string $subheading = 'Micro, Small, Medium & Large — by capitalization';

    protected static ?int $sort = 3;

    protected static ?int $contentHeight = 300;

    protected ?string $pollingInterval = null;

    protected static bool $deferLoading = true;

    protected function getOptions(): array
    {
        if (! $this->readyToLoad) {
            return [];
        }

        $counts = Juridical::selectRaw('category, COUNT(*) as total')
            ->whereNotNull('category')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $labels = ['MICRO', 'SMALL', 'MEDIUM', 'LARGE'];
        $series = array_map(fn ($l) => (int) ($counts[$l] ?? 0), $labels);
        // Use blue shades matching the existing badge colors in the system
        $colors = ['#3B82F6', '#2563EB', '#1D4ED8', '#1E3A8A'];

        return [
            'chart' => [
                'type'       => 'donut',
                'height'     => 300,
                'fontFamily' => 'inherit',
            ],
            'series' => $series,
            'labels' => $labels,
            'colors' => $colors,
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
                                'show'      => true,
                                'label'     => 'Total',
                                'fontFamily' => 'inherit',
                                'fontWeight' => 700,
                                'color'     => '#0f172a',
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
            'tooltip' => [
                'style' => ['fontFamily' => 'inherit'],
            ],
        ];
    }
}
