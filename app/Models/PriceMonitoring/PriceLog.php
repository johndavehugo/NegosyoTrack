<?php

namespace App\Models\PriceMonitoring;

use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceLog extends Model
{
    protected $table = 'price_logs';

=======

class PriceLog extends Model
{
>>>>>>> df62f001a3065ad8f6b20b54636559892424e680
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
<<<<<<< HEAD
            'prevailing_price' => 'decimal:2',
            'monitored_at'     => 'datetime',
        ];
    }

    public function commodity(): BelongsTo
=======
            'prevailing_price'   => 'decimal:2',
            'monitored_at'       => 'datetime',
        ];
    }

    public function commodity()
>>>>>>> df62f001a3065ad8f6b20b54636559892424e680
    {
        return $this->belongsTo(Commodity::class);
    }

<<<<<<< HEAD
    public function agency(): BelongsTo
=======
    public function agency()
>>>>>>> df62f001a3065ad8f6b20b54636559892424e680
    {
        return $this->belongsTo(Agency::class, 'monitored_by_agency_id');
    }
}
