<?php

namespace App\Filament\Widgets;

use App\Models\CalamityMonitoring\Calamity;
use App\Models\CalamityMonitoring\Incident;
use App\Models\MsmeManagement\Juridical;
use App\Models\PriceMonitoring\Commodity;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalMsme      = Juridical::count();
        $activeMsme     = Juridical::where('bus_status', 'ACTIVE')->count();
        $newThisMonth   = Juridical::where('registration_type', 'NEW')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $renewalThisMonth = Juridical::where('registration_type', 'RENEWAL')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $activeCalamities  = Calamity::count();
        $totalDamage       = Incident::sum('estimated_cost_of_damages');
        $affectedMsme      = Incident::distinct('juridical_id')->count('juridical_id');

        $totalCommodities  = Commodity::count();
        $activeCommodities = Commodity::where('is_active', 1)->count();

        $geocodedBarangays = DB::table('addresses')
            ->whereNotNull('barangay')
            ->where('barangay', '!=', '')
            ->whereNotNull('latitude')->where('latitude', '!=', 0)
            ->whereNotNull('longitude')->where('longitude', '!=', 0)
            ->distinct('barangay')
            ->count('barangay');

        $activePct = $totalMsme > 0 ? round($activeMsme / $totalMsme * 100, 1) : 0;

        return [
            Stat::make('Registered MSMEs', number_format($totalMsme))
                ->description(number_format($newThisMonth).' new · '.number_format($renewalThisMonth).' renewals this month')
                ->descriptionIcon(Heroicon::BuildingStorefront)
                ->color(Color::Red),

            Stat::make('Active Businesses', number_format($activeMsme))
                ->description("{$activePct}% of total registered")
                ->descriptionIcon(Heroicon::CheckBadge)
                ->color(Color::Green),

            Stat::make('Calamity Events', number_format($activeCalamities))
                ->description(number_format($affectedMsme).' affected MSMEs')
                ->descriptionIcon(Heroicon::CloudArrowDown)
                ->color(Color::Amber),

            Stat::make('Total Damage (₱)', '₱'.number_format($totalDamage, 2))
                ->description('Across all calamity incidents')
                ->descriptionIcon(Heroicon::CurrencyDollar)
                ->color(Color::Orange),

            Stat::make('Monitored Commodities', number_format($totalCommodities))
                ->description("{$activeCommodities} active · ".($totalCommodities - $activeCommodities).' inactive')
                ->descriptionIcon(Heroicon::ArchiveBox)
                ->color(Color::Blue),

            Stat::make('Barangays with MSMEs', number_format($geocodedBarangays))
                ->description('With geocoded business locations')
                ->descriptionIcon(Heroicon::MapPin)
                ->color(Color::Purple),
        ];
    }
}
