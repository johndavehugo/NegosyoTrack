<?php

namespace App\Filament\Widgets;

use App\Models\Training\Training;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TrainingStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 7;

    protected static bool $deferLoading = true;

    protected function getStats(): array
    {
        $total      = Training::count();
        $completed  = Training::where('status', 'COMPLETED')->count();
        $ongoing    = Training::where('status', 'ONGOING')->count();
        $thisYear   = Training::whereYear('from_date', now()->year)->count();
        $businesses = Training::distinct('entity_no')->count('entity_no');

        return [
            Stat::make('Training Records', number_format($total))
                ->description(number_format($thisYear).' this year')
                ->descriptionIcon(Heroicon::AcademicCap)
                ->color(Color::Indigo),

            Stat::make('Completed Trainings', number_format($completed))
                ->description(number_format($ongoing).' ongoing')
                ->descriptionIcon(Heroicon::CheckBadge)
                ->color(Color::Green),

            Stat::make('Businesses Trained', number_format($businesses))
                ->description('Unique MSMEs with training records')
                ->descriptionIcon(Heroicon::BuildingStorefront)
                ->color(Color::Teal),
        ];
    }
}
