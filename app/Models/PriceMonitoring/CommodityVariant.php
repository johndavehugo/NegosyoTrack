<?php

namespace App\Models\PriceMonitoring;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CommodityVariant extends Model
{
    protected $fillable = ['commodity_id', 'unit_of_measure', 'srp', 'prevailing_price'];

    protected $casts = [
        'srp'             => 'decimal:2',
        'prevailing_price' => 'decimal:2',
    ];

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    /** Price logs written for this variant's parent commodity. */
    public function priceLogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            PriceLog::class,
            Commodity::class,
            'id',           // commodities.id
            'commodity_id', // price_logs.commodity_id
            'commodity_id', // commodity_variants.commodity_id
            'id'            // commodities.id
        );
    }
}