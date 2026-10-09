<?php

namespace App\Models\MsmeManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Address extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'region',
        'province',
        'city',
        'barangay',
        'street',
        'subdivision',
        'upblb_num',
        'longitude',
        'latitude',
        'zip',
    ];

    public function juridical(): HasOne
    {
        return $this->hasOne(Juridical::class);
    }

    public function employer(): HasOne
    {
        return $this->hasOne(Employer::class);
    }

    public function casts(): array
    {
        return [
            'longitude' => 'decimal:7',
            'latitude' => 'decimal:7',
        ];
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->upblb_num,
            $this->street,
            $this->subdivision,
            $this->barangay,
            $this->city,
            $this->province,
            $this->region,
            $this->zip,
        ])
            ->filter(fn ($value) => filled($value))
            ->join(', ');
    }
}
