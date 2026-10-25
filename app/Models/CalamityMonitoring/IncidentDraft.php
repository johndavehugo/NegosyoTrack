<?php

namespace App\Models\CalamityMonitoring;

use Illuminate\Database\Eloquent\Model;

class IncidentDraft extends Model
{
    protected $table = 'calamity_incident_drafts';

    protected $fillable = [
        'draft_key',
        'user_id',
        'calamity_id',
        'juridical_id',
        'juridical_name',
        'barangay',
        'estimated_cost_of_damages',
        'photos',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'estimated_cost_of_damages' => 'decimal:2',
        ];
    }
}
