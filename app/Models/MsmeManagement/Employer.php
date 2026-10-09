<?php

namespace App\Models\MsmeManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employer extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'full_name',
        'entity_no',
        'gender',
        'birth_date',
        'contact_no',
        'email',
        'address_id',
        'special_category',
    ];

    public function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function juridical(): HasMany
    {
        return $this->hasMany(Juridical::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
