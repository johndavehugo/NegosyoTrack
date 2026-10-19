<?php

namespace App\Filament\Widgets;

use App\Services\EconomicMap\CalamityFeed;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class CalamityDamageChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'calamityDamageChart';

    protected static ?string $heading = 'Calamity Damage by Barangay';

    protected static ?string $subheading = 'Estimated cost of damages (₱) per affected barangay';

    protected static ?int $sort = 5;

    protected static ?int $contentHeight = 280;

    protected ?string $pollingInterval = null;

    protected static bool $deferLoading = true;

    protected function getOptions(): array
    {
        if (! $this->readyToLoad) {
            return [];
        }

        /** @var CalamityFeed $feed */
        $feed   = app(CalamityFeed::class);
        $damage = $feed->damageByBarangay();

        $byBarangay = collect($damage['by_barangay'])
            ->filter(fn ($v) => $v['damage'] > 0)
            ->sortByDesc('damage')
            ->take(10);

        if ($byBarangay->isEmpty()) {
            return [
                'chart'  => ['type' => 'bar', 'height' => 280],
                'series' => [['name' => 'Damage (₱)', 'data' => []]],
                'xaxis'  => ['categories' => []],
                'colors' => ['#DC2626'],
            ];
        }

        $labels = $byBarangay->keys()
            ->map(fn ($n) => strlen($n) > 20 ? substr($n, 0, 18).'…' : $n)
            ->values()
            ->toArray();

        $data = $byBarangay->pluck('damage')
            ->map(fn ($v) => round((float) $v, 2))
            ->values()
            ->toArray();

        return [
            'chart' => [
                'type'       => 'bar',
                'height'     => 280,
                'fontFamily' => 'inherit',
                'toolbar'    => ['show' => false],
            ],
            'series' => [
                [
                    'name' => 'Damage (₱)',
                    'data' => $data,
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
                'labels'     => [
                    'style' => ['fontFamily' => 'inherit', 'fontSize' => '11px'],
                ],
            ],
            'yaxis' => [
                'labels' => ['style' => ['fontFamily' => 'inherit']],
            ],
            'colors'      => ['#B91C1C'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'columnWidth'  => '55%',
                    'borderRadiusApplication' => 'end',
                ],
            ],
            'dataLabels' => ['enabled' => false],
            'grid' => [
                'borderColor'     => '#f1f5f9',
                'strokeDashArray' => 4,
            ],
            'tooltip' => [
                'style' => ['fontFamily' => 'inherit'],
            ],
        ];
    }
}
