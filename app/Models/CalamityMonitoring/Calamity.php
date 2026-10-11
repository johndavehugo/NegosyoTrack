<?php

namespace App\Models\CalamityMonitoring;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calamity extends Model
{
    protected $fillable = ['id', 'name', 'calamity_type', 'declaration_date', 'description'];

    public function incident(): HasMany
    {
        return $this->hasMany(Incident::class);
    }
}
