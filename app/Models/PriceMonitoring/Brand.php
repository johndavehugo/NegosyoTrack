<?php

namespace App\Models\PriceMonitoring;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name'];

    public function commodities()
    {
        return $this->hasMany(Commodity::class);
    }
}