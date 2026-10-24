<?php

namespace App\Models\Training;

use App\Enums\TrainingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * One trainee's personal completion record. The foreign key lives on the
 * trainees table (`training_details_id`), so a trainee either has no detail
 * row yet (still attending) or exactly one.
 */
class TrainingDetail extends Model
{
    protected $table = 'training_details';

    protected $fillable = [
        'graduated_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'graduated_at' => 'date',
            'status' => TrainingStatus::class,
        ];
    }

    /**
     * The trainee this completion record belongs to.
     */
    public function trainee(): HasOne
    {
        return $this->hasOne(Trainee::class, 'training_details_id', 'id');
    }
}
