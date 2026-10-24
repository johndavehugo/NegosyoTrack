<?php

namespace App\Models\PriceMonitoring;

use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    protected $fillable = ['id', 'code', 'name', 'coverage'];

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function priceLogs()
    {
        return $this->hasMany(PriceLog::class, 'monitored_by_agency_id');
    }
}
