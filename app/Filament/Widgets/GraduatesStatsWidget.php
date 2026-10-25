<?php

namespace App\Filament\Widgets;

use App\Models\Training\Trainee;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class GraduatesStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $total = Trainee::query()->graduates()->count();

        $thisYear = Trainee::query()
            ->graduates()
            ->whereHas(
                'trainingDetails',
                fn (Builder $query): Builder => $query->whereYear('graduated_at', now()->year),
            )
            ->count();

        $programs = Trainee::query()
            ->graduates()
            ->distinct('training_id')
            ->count('training_id');

        return [
            Stat::make('Total Graduates', number_format($total))
                ->description('Across all training records')
                ->descriptionIcon(Heroicon::CheckBadge)
                ->color(Color::Green),

            Stat::make('Graduated This Year', number_format($thisYear))
                ->description(now()->year.' completions')
                ->descriptionIcon(Heroicon::CalendarDays)
                ->color(Color::Indigo),

            Stat::make('Training Programs', number_format($programs))
                ->description('Programs with at least one graduate')
                ->descriptionIcon(Heroicon::AcademicCap)
                ->color(Color::Teal),
        ];
    }
}
