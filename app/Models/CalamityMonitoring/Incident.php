<?php

namespace App\Models\CalamityMonitoring;

use App\Models\MsmeManagement\Juridical;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    protected $table = 'calamity_incidents';

    protected $fillable = ['id', 'juridical_id', 'calamity_id', 'date_occurred', 'nature_of_damage', 'estimated_cost_of_damages', 'remarks', 'status'];

    public function juridical(): BelongsTo
    {
        return $this->belongsTo(Juridical::class);
    }

    public function calamity(): BelongsTo
    {
        return $this->belongsTo(Calamity::class);
    }
}
