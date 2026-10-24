<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $table = 'trainings';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
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
            'from_date' => 'date',
            'to_date' => 'date',
            'graduated_at' => 'date',
            'hours_no' => 'integer',
        ];
    }
}
