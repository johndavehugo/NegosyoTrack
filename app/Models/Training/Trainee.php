<?php

namespace App\Models\Training;

use App\Enums\TrainingStatus;
use App\Services\Training\ScimsTrainingService;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
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
        'full_name',
        'gender',
        'birth_date',
        'mobile_no',
        'training_id',
        'training_details_id',
        'contact_no',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // Snapshot the SCIMS identity when a trainee is first enrolled or
        // relinked. A failed lookup keeps whatever was stored before.
        static::saving(function (Trainee $trainee): void {
            if (! $trainee->isDirty('api_entity_no')) {
                return;
            }

            $trainee->copyIdentityFromScims();
        });
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class, 'training_id', 'id');
    }

    /**
     * The trainee's personal completion record (status + graduation date).
     */
    public function trainingDetails(): BelongsTo
    {
        return $this->belongsTo(TrainingDetail::class, 'training_details_id', 'id');
    }

    /**
     * Trainees with a completed personal completion record.
     */
    public function scopeGraduates(Builder $query): Builder
    {
        return $query->whereHas(
            'trainingDetails',
            fn (Builder $query): Builder => $query->where('status', TrainingStatus::Completed->value),
        );
    }

    /**
     * Whether this trainee's own record marks them as a graduate.
     */
    public function isGraduate(): bool
    {
        return $this->trainingDetails?->status === TrainingStatus::Completed;
    }

    /**
     * Create or update this trainee's completion record and mark them as a
     * graduate. Without an explicit date the training-level graduation date
     * is used, then its end date, then today.
     */
    public function markAsGraduate(CarbonInterface|string|null $graduatedAt = null): TrainingDetail
    {
        $graduatedAt ??= $this->training?->graduated_at
            ?? $this->training?->to_date
            ?? now();

        $detail = $this->trainingDetails ?? new TrainingDetail;

        $detail->fill([
            'status' => TrainingStatus::Completed,
            'graduated_at' => $graduatedAt,
        ])->save();

        if ($this->training_details_id !== $detail->id) {
            $this->forceFill(['training_details_id' => $detail->id])->save();
        }

        $this->setRelation('trainingDetails', $detail);

        return $detail;
    }

    /**
     * Change the graduation date without touching the completion status.
     */
    public function updateGraduationDate(CarbonInterface|string $graduatedAt): bool
    {
        $detail = $this->trainingDetails;

        if (! $detail) {
            return false;
        }

        $detail->update(['graduated_at' => $graduatedAt]);

        return true;
    }

    /**
     * Delete the completion record. The foreign key unlinks the trainee, so
     * they stay enrolled in the training but leave the graduates list.
     */
    public function removeGraduateRecord(): bool
    {
        $detail = $this->trainingDetails;

        if (! $detail) {
            return false;
        }

        $detail->delete();

        $this->unsetRelation('trainingDetails');
        $this->forceFill(['training_details_id' => null])->saveQuietly();

        return true;
    }

    /**
     * Update the local SCIMS snapshot. Returns false when SCIMS is
     * unreachable or the person cannot be found.
     */
    public function refreshSnapshots(): bool
    {
        if (! $this->copyIdentityFromScims()) {
            return false;
        }

        $this->saveQuietly();

        return true;
    }

    /**
     * Copy name, gender, birth date and mobile number from SCIMS.
     */
    public function copyIdentityFromScims(): bool
    {
        $individual = app(ScimsTrainingService::class)->findIndividual($this->api_entity_no);

        if (! is_array($individual)) {
            return false;
        }

        $name = ScimsTrainingService::formatName($individual);

        if ($name !== '') {
            $this->full_name = $name;
        }

        if (filled($individual['gender'] ?? null)) {
            $this->gender = $individual['gender'];
        }

        if (filled($individual['birth_date'] ?? null)) {
            $this->birth_date = $individual['birth_date'];
        }

        $mobile = trim((string) ($individual['mobile_no'] ?? ''));

        if ($mobile !== '') {
            $this->mobile_no = $mobile;
        }

        return true;
    }

    /**
     * The SCIMS individual this trainee links to, resolved by api_entity_no.
     * Used as a fallback for records created before local snapshots existed.
     *
     * @return array<string, mixed>|null
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
                'mobile_no' => $attributes['mobile_no'] ?? null,
                'entity_no' => $attributes['entity_no'] ?? $this->api_entity_no,
            ];
        });
    }

    /**
     * Name for lists and titles, falling back to SCIMS and then the entity no.
     */
    protected function displayName(): Attribute
    {
        return Attribute::get(fn (): string => filled($this->full_name)
            ? $this->full_name
            : ($this->individual['full_name'] ?? $this->api_entity_no));
    }

    protected function age(): Attribute
    {
        return Attribute::get(fn (): ?int => $this->birth_date?->age);
    }
}
