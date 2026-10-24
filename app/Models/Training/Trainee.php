<?php

namespace App\Models\Training;

use App\Services\Training\ScimsTrainingService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trainee extends Model
{
    protected $table = 'trainees';

    // trainees has no created_at / updated_at columns
    public $timestamps = false;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'api_entity_no',
        'training_id',
        'contact_no',
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class, 'training_id', 'id');
    }

    /**
     * The SCIMS individual this trainee links to, resolved by api_entity_no.
     * Contains keys: full_name, first_name, middle_name, last_name, gender,
     * birth_date, entity_no — or null when SCIMS is unreachable.
     */
    protected function individual(): Attribute
    {
        return Attribute::get(function (): ?array {
            $attributes = app(ScimsTrainingService::class)
                ->findIndividual($this->api_entity_no);

            if (is_null($attributes)) {
                return null;
            }

            return [
                'full_name' => ScimsTrainingService::formatName($attributes),
                'first_name' => $attributes['first_name'] ?? null,
                'middle_name' => $attributes['middle_name'] ?? null,
                'last_name' => $attributes['last_name'] ?? null,
                'gender' => $attributes['gender'] ?? null,
                'birth_date' => $attributes['birth_date'] ?? null,
                'entity_no' => $attributes['entity_no'] ?? $this->api_entity_no,
            ];
        });
    }
}
