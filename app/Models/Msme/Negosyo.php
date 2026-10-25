<?php

namespace App\Models\Msme;

use Illuminate\Database\Eloquent\Model;

class Negosyo extends Model
{
    protected $table = 'negosyos';

    protected $primaryKey = 'id';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'api_entity_no',
        'special_category',
        'capitalization',
    ];

    protected function casts(): array
    {
        return [
            'capitalization' => 'decimal:2',
        ];
    }
}
