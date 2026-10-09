<?php

namespace App\Models\MsmeManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Juridical extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'employer_id',
        'address_id',
        'name',
        'entity_no',
        'date_reg',
        'capitalization',
        'line_of_industry',
        'registration_type',
        'bus_status',
        'contact_no',
        'contact_email',
        'employer_id',
        'address_id',
    ];

    protected function casts(): array
    {
        return [
            'capitalization' => 'decimal:2',
            'date_reg' => 'date',
        ];
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
