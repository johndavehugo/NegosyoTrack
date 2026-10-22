<?php

namespace App\Filament\Resources\PriceMonitoring\PriceLists\Widgets;

use App\Models\PriceMonitoring\CommodityVariant;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PriceListOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('', CommodityVariant::count())
                ->description('Monitored Items')
                ->descriptionIcon(Heroicon::ArchiveBox)
                ->color(Color::Blue),

            Stat::make('', CommodityVariant::whereNotNull('prevailing_price')->count())
                ->description('Has Prevailing Price')
                ->descriptionIcon(Heroicon::CheckBadge)
                ->color(Color::Green),

            Stat::make('', CommodityVariant::whereNull('prevailing_price')->count())
                ->description('Missing Prevailing Price')
                ->descriptionIcon(Heroicon::XCircle)
                ->color(Color::Red),
        ];
    }
}
