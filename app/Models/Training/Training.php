<?php

namespace App\Models\Training;

use App\Enums\TrainingStatus;
use App\Services\Training\ScimsTrainingService;
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
        'entity_no',
        'business_name',
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
        // Keep the business name in step with the SCIMS entity_no. Only look
        // it up when the link changes; the explicit refresh actions handle
        // legacy records and earlier SCIMS outages.
        static::saving(function (Training $training): void {
            if (! $training->isDirty('entity_no')) {
                return;
            }

            if (blank($training->entity_no)) {
                $training->business_name = null;

                return;
            }

            $training->business_name = app(ScimsTrainingService::class)->businessName($training->entity_no);
        });

        // A training record owns its roster. Deleting it would either orphan
        // or fail on the trainees FK, so cancel the delete from the UI.
        static::deleting(fn (Training $training): bool => ! $training->trainees()->exists());
    }

    public function trainees(): HasMany
    {
        return $this->hasMany(Trainee::class, 'training_id', 'id');
    }

    /**
     * Pull the current business name from SCIMS and store it quietly.
     * Returns false when SCIMS has no match, leaving the snapshot untouched.
     */
    public function refreshBusinessName(): bool
    {
        if (blank($this->entity_no)) {
            return false;
        }

        $name = app(ScimsTrainingService::class)->businessName($this->entity_no);

        if (blank($name)) {
            return false;
        }

        $this->forceFill(['business_name' => $name])->saveQuietly();

        return true;
    }

    /**
     * Business name for display, with the SCIMS entity number as a fallback.
     */
    protected function businessDisplayName(): Attribute
    {
        return Attribute::get(function (): string {
            if (filled($this->business_name)) {
                return $this->business_name;
            }

            return filled($this->entity_no) ? $this->entity_no : '—';
        });
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
