<?php

namespace App\Filament\Widgets;

use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;
use Throwable;

class IncidentStatsWidget extends StatsOverviewWidget
{
    public ?string $calamityId = null;

    // This widget is used inside Calamity pages, not on the main Dashboard.
    // Returning false from the static check prevents it appearing via discoverWidgets().
    public static function canView(): bool
    {
        return false;
    }

    protected function getStats(): array
    {
        // Guard — calamity_incidents table may not exist yet
        if (! Schema::hasTable('calamity_incidents')) {
            return [
                Stat::make('Affected MSMEs', '—')
                    ->description('Calamity data not available yet')
                    ->descriptionIcon(Heroicon::BuildingStorefront)
                    ->color(Color::Amber),

                Stat::make('Total Damage (₱)', '—')
                    ->description('Calamity data not available yet')
                    ->descriptionIcon(Heroicon::CurrencyDollar)
                    ->color(Color::Red),

                Stat::make('Pending Verification', '—')
                    ->description('Calamity data not available yet')
                    ->descriptionIcon(Heroicon::Clock)
                    ->color(Color::Orange),
            ];
        }

        try {
            $query = \App\Models\CalamityMonitoring\Incident::query();

            if (filled($this->calamityId)) {
                $query->where('calamity_id', $this->calamityId);
            }

            $records      = (clone $query)->count();
            $affectedMsme = (clone $query)->distinct('juridical_id')->count('juridical_id');
            $totalDamage  = (clone $query)->sum('estimated_cost_of_damages');
            $pending      = (clone $query)->where('status', 'PENDING_VERIFICATION')->count();
        } catch (Throwable) {
            $records = $affectedMsme = $totalDamage = $pending = 0;
        }

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
