<?php

namespace App\Observers\PriceMonitoring;

use App\Models\PriceMonitoring\CommodityVariant;
use App\Models\PriceMonitoring\PriceLog;

class CommodityVariantObserver
{
    /**
     * Log a price_log entry whenever prevailing_price is set or updated.
     */
    public function saved(CommodityVariant $variant): void
    {
        // Only write a log entry when prevailing_price actually changed and is not null.
        if (
            $variant->prevailing_price !== null
            && $variant->wasChanged('prevailing_price')
        ) {
            // Resolve the agency that owns this commodity's category.
            $agencyId = $variant->commodity?->category?->agency_id;

            PriceLog::create([
                'commodity_id'          => $variant->commodity_id,
                'prevailing_price'      => $variant->prevailing_price,
                'status'                => 'ACTIVE',
                'monitored_by_agency_id' => $agencyId,
                'monitored_at'          => now(),
            ]);
        }
    }
}
