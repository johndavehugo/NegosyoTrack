<?php

namespace App\Models\PriceMonitoring;

use Illuminate\Database\Eloquent\Model;

class CommodityVariant extends Model
{
    protected $fillable = ['commodity_id', 'unit_of_measure', 'srp', 'is_active'];

    public function commodity()
    {
        return $this->belongsTo(Commodity::class);
    }
}