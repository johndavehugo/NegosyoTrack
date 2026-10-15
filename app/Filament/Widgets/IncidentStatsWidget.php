<?php

namespace App\Filament\Widgets;

use App\Models\CalamityMonitoring\Incident;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IncidentStatsWidget extends StatsOverviewWidget
{
    public ?string $calamityId = null;

    protected function getStats(): array
    {
        $query = Incident::query();

        if (filled($this->calamityId)) {
            $query->where('calamity_id', $this->calamityId);
        }

        $records = (clone $query)->count();
        $affectedMsme = (clone $query)->distinct('juridical_id')->count('juridical_id');
        $totalDamage = (clone $query)->sum('estimated_cost_of_damages');
        $pending = (clone $query)->where('status', 'PENDING_VERIFICATION')->count();

        return [
            Stat::make('Affected MSMEs', number_format($affectedMsme))
                ->description(number_format($records).' incident record(s)')
                ->descriptionIcon(Heroicon::BuildingStorefront)
                ->color(Color::Amber),

            Stat::make('Total Damage (₱)', '₱'.number_format((float) $totalDamage, 2))
                ->description('Estimated cost of damages')
                ->descriptionIcon(Heroicon::CurrencyDollar)
                ->color(Color::Red),

            Stat::make('Pending Verification', number_format($pending))
                ->description('Awaiting verification')
                ->descriptionIcon(Heroicon::Clock)
                ->color(Color::Orange),
        ];
    }
}
