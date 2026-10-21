<?php

namespace App\Models\Training;

use App\Models\MsmeManagement\Juridical;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingInfo extends Model
{
    protected $table = 'training_info';

    protected $primaryKey = 'training_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'training_id',
        'entity_no',
        'training_program',
        'from_date',
        'to_date',
        'hours_no',
        'training_type',
        'conducted_by',
        'sector',
        'graduated_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'from_date'    => 'date',
            'to_date'      => 'date',
            'graduated_at' => 'date',
            'hours_no'     => 'integer',
        ];
    }

    public function juridical(): BelongsTo
    {
        return $this->belongsTo(Juridical::class, 'entity_no', 'entity_no');
    }
}
