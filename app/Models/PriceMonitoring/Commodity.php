<?php

namespace App\Models\PriceMonitoring;

use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    protected $fillable = ['id', 'category_id', 'product_name', 'brand_name', 'unit_of_measure', 'srp', 'establishments', 'is_active'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
