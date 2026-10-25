<?php

namespace App\Models\Training;

use App\Enums\TrainingStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Training extends Model
{
    protected $table = 'trainings';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
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
            'status' => TrainingStatus::class,
        ];
    }

    protected static function booted(): void
    {
        // A training record owns its roster. Deleting it would either orphan
        // or fail on the trainees FK, so cancel the delete from the UI.
        static::deleting(fn (Training $training): bool => ! $training->trainees()->exists());
    }

    public function trainees(): HasMany
    {
        return $this->hasMany(Trainee::class, 'training_id', 'id');
    }

    /**
     * "Jan 05, 2026 – Feb 10, 2026" — used by tables and search results.
     */
    protected function scheduleLabel(): Attribute
    {
        return Attribute::get(fn (): string => sprintf(
            '%s – %s',
            $this->from_date?->format('M d, Y') ?? '—',
            $this->to_date?->format('M d, Y') ?? '—',
        ));
    }

    protected function durationInDays(): Attribute
    {
        return Attribute::get(function (): ?int {
            if (! $this->from_date || ! $this->to_date) {
                return null;
            }

            return (int) $this->from_date->diffInDays($this->to_date) + 1;
        });
    }
}
