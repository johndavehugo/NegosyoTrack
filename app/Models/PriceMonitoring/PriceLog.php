<?php

namespace App\Models\PriceMonitoring;

use Illuminate\Database\Eloquent\Model;

class PriceLog extends Model
{
    protected $fillable = [
        'commodity_id',
        'prevailing_price',
        'status',
        'monitored_by_agency_id',
        'monitored_at',
    ];

    protected function casts(): array
    {
        return [
            'prevailing_price'   => 'decimal:2',
            'monitored_at'       => 'datetime',
        ];
    }

    public function commodity()
    {
        return $this->belongsTo(Commodity::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'monitored_by_agency_id');
    }
}
