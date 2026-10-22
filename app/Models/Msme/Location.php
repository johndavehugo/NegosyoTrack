<?php

namespace App\Models\Msme;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'api_entity_no', 'longitude', 'latitude'];
}
