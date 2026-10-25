<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CalamityDamageChartWidget;
use App\Filament\Widgets\DashboardStatsWidget;
use App\Filament\Widgets\IndustryDistributionChartWidget;
use App\Filament\Widgets\MsmeCategoryDonutWidget;
use App\Filament\Widgets\MsmeRegistrationChartWidget;
use App\Filament\Widgets\MsmeStatusDonutWidget;
use App\Filament\Widgets\TrainingStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            DashboardStatsWidget::class,
            MsmeRegistrationChartWidget::class,
            MsmeCategoryDonutWidget::class,
            IndustryDistributionChartWidget::class,
            CalamityDamageChartWidget::class,
            TrainingStatsWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'sm'  => 1,
            'md'  => 2,
            'xl'  => 3,
        ];
    }
}
