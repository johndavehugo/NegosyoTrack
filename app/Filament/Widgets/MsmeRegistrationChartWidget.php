<?php

namespace App\Filament\Widgets;

use App\Models\MsmeManagement\Juridical;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MsmeRegistrationChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'msmeRegistrationChart';

    protected static ?string $heading = 'Monthly Business Registrations & Renewals';

    protected static ?string $subheading = 'New registrations vs. renewals per month — current year';

    protected static ?int $sort = 2;

    protected static ?int $contentHeight = 300;

    protected ?string $pollingInterval = null;

    protected static bool $deferLoading = true;

    protected function getOptions(): array
    {
        if (! $this->readyToLoad) {
            return [];
        }

        $year = now()->year;

        // Build month-indexed counts for New and Renewal
        $new     = array_fill(1, 12, 0);
        $renewal = array_fill(1, 12, 0);

        Juridical::selectRaw('MONTH(created_at) as month, registration_type, COUNT(*) as total')
            ->whereYear('created_at', $year)
            ->groupBy('month', 'registration_type')
            ->get()
            ->each(function ($row) use (&$new, &$renewal) {
                $m = (int) $row->month;
                if ($row->registration_type === 'NEW') {
                    $new[$m] = (int) $row->total;
                } elseif ($row->registration_type === 'RENEWAL') {
                    $renewal[$m] = (int) $row->total;
                }
            });

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                   'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        return [
            'chart' => [
                'type'    => 'bar',
                'height'  => 300,
                'stacked' => false,
                'toolbar' => ['show' => false],
                'fontFamily' => 'inherit',
            ],
            'series' => [
                [
                    'name' => 'New Registrations',
                    'data' => array_values($new),
                ],
                [
                    'name' => 'Renewals',
                    'data' => array_values($renewal),
                ],
            ],
            'xaxis' => [
                'categories' => $months,
                'labels'     => [
                    'style' => ['fontFamily' => 'inherit', 'fontWeight' => 600],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => ['fontFamily' => 'inherit'],
                ],
            ],
            'colors'      => ['#DC2626', '#1D4ED8'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius'      => 4,
                    'columnWidth'       => '55%',
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
                'borderColor' => '#f1f5f9',
                'strokeDashArray' => 4,
            ],
            'tooltip' => [
                'style' => ['fontFamily' => 'inherit'],
            ],
        ];
    }
}
