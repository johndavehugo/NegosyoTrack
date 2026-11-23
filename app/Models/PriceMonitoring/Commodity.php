<?php

namespace App\Models\PriceMonitoring;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Commodity extends Model
{
    protected $fillable = ['id', 'category_id', 'brand_id', 'juridical_id', 'product_name', 'image_path', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleted(function (Commodity $commodity): void {
            if ($commodity->image_path) {
                Storage::disk('public')->delete($commodity->image_path);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(CommodityVariant::class);
    }

    public function priceLogs(): HasMany
    {
        return $this->hasMany(PriceLog::class);
    }
}