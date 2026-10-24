<?php

namespace App\Filament\Widgets;

use App\Models\CalamityMonitoring\Calamity;
use App\Services\CalamityMonitoring\ReportService;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class CalamityDamageByBarangayChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'calamityDamageByBarangayChart';

    protected static ?string $heading = 'Damage by Barangay';

    protected static ?string $subheading = 'Estimated cost of damages for this calamity';

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

        $rows = collect(app(ReportService::class)->build($calamity)['by_barangay'])
            ->filter(fn (array $row): bool => $row['damage'] > 0)
            ->sortByDesc('damage')
            ->take(10);

        if ($rows->isEmpty()) {
            return $this->emptyOptions();
        }

        $labels = $rows->pluck('barangay')
            ->map(fn (string $name): string => strlen($name) > 20 ? substr($name, 0, 18).'…' : $name)
            ->values()
            ->all();

        $data = $rows->pluck('damage')
            ->map(fn ($value): float => round((float) $value, 2))
            ->values()
            ->all();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 280,
                'fontFamily' => 'inherit',
                'toolbar' => ['show' => false],
            ],
            'series' => [
                [
                    'name' => 'Damage (₱)',
                    'data' => $data,
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
                'labels' => [
                    'style' => ['fontFamily' => 'inherit', 'fontSize' => '11px'],
                ],
            ],
            'yaxis' => [
                'labels' => ['style' => ['fontFamily' => 'inherit']],
            ],
            'colors' => ['#B91C1C'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'columnWidth' => '55%',
                    'borderRadiusApplication' => 'end',
                ],
            ],
            'dataLabels' => ['enabled' => false],
            'grid' => [
                'borderColor' => '#f1f5f9',
                'strokeDashArray' => 4,
            ],
            'tooltip' => [
                'style' => ['fontFamily' => 'inherit'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyOptions(): array
    {
        return [
            'chart' => ['type' => 'bar', 'height' => 280],
            'series' => [['name' => 'Damage (₱)', 'data' => []]],
            'xaxis' => ['categories' => []],
            'colors' => ['#B91C1C'],
        ];
    }
}
