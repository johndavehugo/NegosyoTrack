<?php

namespace App\Models\PriceMonitoring;

use App\Models\MsmeManagement\Juridical;
use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    protected $fillable = ['id', 'category_id', 'brand_id', 'juridical_id', 'product_name', 'is_active'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

<<<<<<< HEAD
    public function priceLogs()
    {
        return $this->hasMany(PriceLog::class)
            ->orderBy('monitored_at');
    }
}
=======
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function juridical()
    {
        return $this->belongsTo(Juridical::class);
    }

    public function variants()
    {
        return $this->hasMany(CommodityVariant::class);
    }
}
>>>>>>> df62f001a3065ad8f6b20b54636559892424e680
