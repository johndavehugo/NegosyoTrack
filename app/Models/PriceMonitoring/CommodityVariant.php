<?php

namespace App\Models\PriceMonitoring;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CommodityVariant extends Model
{
    public const SRP_FLAG_THRESHOLD_PERCENT = 10.0;

    protected $fillable = ['commodity_id', 'unit_of_measure', 'srp', 'prevailing_price'];

    protected $casts = [
        'srp' => 'decimal:2',
        'prevailing_price' => 'decimal:2',
    ];

    public function priceIncreasePercent(): ?float
    {
        if ($this->srp === null || $this->prevailing_price === null) {
            return null;
        }

        $srp = (float) $this->srp;

        if ($srp <= 0.0) {
            return null;
        }

        return (((float) $this->prevailing_price - $srp) / $srp) * 100;
    }

    public function isOverSrp(): bool
    {
        $increase = $this->priceIncreasePercent();

        return $increase !== null
            && round($increase, 4) > static::SRP_FLAG_THRESHOLD_PERCENT;
    }

    public function scopeOverSrp(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        return $query
            ->whereNotNull("{$table}.srp")
            ->where("{$table}.srp", '>', 0)
            ->whereNotNull("{$table}.prevailing_price")
            ->whereRaw(
                "{$table}.prevailing_price > {$table}.srp * ?",
                [1 + static::SRP_FLAG_THRESHOLD_PERCENT / 100],
            );
    }

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
