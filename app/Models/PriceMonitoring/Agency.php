<?php

namespace App\Models\PriceMonitoring;

use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    protected $fillable = ['id', 'code', 'name', 'coverage'];

    public function categories()
    {
        return $this->hasOne(Category::class);
    }
}
