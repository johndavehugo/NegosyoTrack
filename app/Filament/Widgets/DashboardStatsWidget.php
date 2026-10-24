<?php

namespace App\Filament\Widgets;

use App\Models\CalamityMonitoring\Calamity;
use App\Models\CalamityMonitoring\Incident;
use App\Models\PriceMonitoring\Commodity;
use App\Models\Training\Training;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // ── MSME — from SCIMS API (no local table) ────────────────
        $msmeStat = Stat::make('Registered MSMEs', 'Live from SCIMS')
            ->description('View in Business List ↗')
            ->descriptionIcon(Heroicon::BuildingStorefront)
            ->color(Color::Red);

        // ── Calamity ──────────────────────────────────────────────
        $calamityCount = 0;
        $affectedMsme  = 0;
        $totalDamage   = 0;

        try {
            if (Schema::hasTable('calamities')) {
                $calamityCount = Calamity::count();
            }
            if (Schema::hasTable('calamity_incidents')) {
                $affectedMsme = Incident::distinct('juridical_id')->count('juridical_id');
                $totalDamage  = Incident::sum('estimated_cost_of_damages');
            }
        } catch (Throwable) {}

        $calamityStat = Schema::hasTable('calamities')
            ? Stat::make('Calamity Events', number_format($calamityCount))
                ->description(number_format($affectedMsme).' affected businesses')
                ->descriptionIcon(Heroicon::CloudArrowDown)
                ->color(Color::Amber)
            : Stat::make('Calamity Events', '—')
                ->description('Module pending setup')
                ->descriptionIcon(Heroicon::CloudArrowDown)
                ->color(Color::Gray);

        $damageStat = Schema::hasTable('calamity_incidents')
            ? Stat::make('Total Damage (₱)', '₱'.number_format($totalDamage, 2))
                ->description('Estimated across all calamities')
                ->descriptionIcon(Heroicon::CurrencyDollar)
                ->color(Color::Orange)
            : Stat::make('Total Damage (₱)', '—')
                ->description('Module pending setup')
                ->descriptionIcon(Heroicon::CurrencyDollar)
                ->color(Color::Gray);

        // ── Commodities ───────────────────────────────────────────
        $totalCommodities  = 0;
        $activeCommodities = 0;

        try {
            if (Schema::hasTable('commodities')) {
                $totalCommodities  = Commodity::count();
                $activeCommodities = Commodity::where('is_active', 1)->count();
            }
        } catch (Throwable) {}

        $commodityStat = Schema::hasTable('commodities')
            ? Stat::make('Monitored Commodities', number_format($totalCommodities))
                ->description("{$activeCommodities} active · ".($totalCommodities - $activeCommodities).' inactive')
                ->descriptionIcon(Heroicon::ArchiveBox)
                ->color(Color::Blue)
            : Stat::make('Monitored Commodities', '—')
                ->description('Module pending setup')
                ->descriptionIcon(Heroicon::ArchiveBox)
                ->color(Color::Gray);

        // ── Training ──────────────────────────────────────────────
        $trainingTotal = 0;
        $trainingYear  = 0;

        try {
            $trainingTotal = Training::count();
            $trainingYear  = Training::whereYear('from_date', now()->year)->count();
        } catch (Throwable) {}

        $trainingStat = Stat::make('Training Records', number_format($trainingTotal))
            ->description(number_format($trainingYear).' this year')
            ->descriptionIcon(Heroicon::AcademicCap)
            ->color(Color::Indigo);

        return [
            $msmeStat,
            $calamityStat,
            $damageStat,
            $commodityStat,
            $trainingStat,
        ];
    }
}
