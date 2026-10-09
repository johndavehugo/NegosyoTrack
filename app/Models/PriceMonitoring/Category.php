<?php

namespace App\Models\PriceMonitoring;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'commodity_categories';

    protected $fillable = ['id', 'name', 'agency_id'];

    public function commodities()
    {
        return $this->hasMany(Commodity::class);
    }
    
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
