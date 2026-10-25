<?php

namespace App\Models\Training;

use App\Enums\TrainingStatus;
use App\Services\Training\ScimsTrainingService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
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

    /**
     * Only the SCIMS link and the local enrollment details are stored here.
     * Name, gender, birth date and mobile number belong to SCIMS.
     */
    protected $fillable = [
        'id',
        'api_entity_no',
        'training_id',
        'training_details_id',
        'contact_no',
    ];

    /**
     * Identity resolved from SCIMS for this model instance.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $scimsIdentity = null;

    protected bool $scimsIdentityLoaded = false;

    /**
     * Prefetch identities for hydrated result sets so a table page or export
     * makes one batched SCIMS request instead of one request per row.
     */
    public function newCollection(array $models = []): Collection
    {
        $collection = parent::newCollection($models);

        // Single-model temporary collections are created by loadMissing()
        // and loadCount(); prefetching there would make relation loading
        // call SCIMS for no reason.
        if (count($models) < 2) {
            return $collection;
        }

        $entityNos = [];

        foreach ($models as $model) {
            $entityNo = trim((string) $model->getAttribute('api_entity_no'));

            if ($entityNo !== '') {
                $entityNos[$entityNo] = true;
            }
        }

        if ($entityNos !== []) {
            app(ScimsTrainingService::class)->findIndividuals(array_keys($entityNos));
        }

        return $collection;
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
     * Match the local columns and relationships plus people whose SCIMS
     * identity matches the search term.
     */
    public function scopeSearchRecords(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
        $like = '%'.$escaped.'%';

        $entityNos = app(ScimsTrainingService::class)->searchIndividualEntityNumbers($search);

        return $query->where(function (Builder $query) use ($like, $entityNos): void {
            $query->where('api_entity_no', 'like', $like)
                ->orWhere('contact_no', 'like', $like)
                ->orWhereHas(
                    'training',
                    fn (Builder $training): Builder => $training->where('training_program', 'like', $like),
                );

            if ($entityNos !== []) {
                $query->orWhereIn('api_entity_no', $entityNos);
            }
        });
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
     * Re-read this person's identity from SCIMS. Nothing is written to the
     * local database; only the temporary cache entry is cleared.
     */
    public function refreshIdentity(): bool
    {
        if (blank($this->api_entity_no)) {
            return false;
        }

        $this->scimsIdentity = null;
        $this->scimsIdentityLoaded = false;

        $service = app(ScimsTrainingService::class);
        $service->forgetIndividual($this->api_entity_no);

        return is_array($this->identity());
    }

    /**
     * Raw SCIMS attributes for this person, resolved once per model instance.
     *
     * @return array<string, mixed>|null
     */
    protected function identity(): ?array
    {
        if ($this->scimsIdentityLoaded) {
            return $this->scimsIdentity;
        }

        $this->scimsIdentityLoaded = true;

        if (blank($this->api_entity_no)) {
            return $this->scimsIdentity = null;
        }

        return $this->scimsIdentity = app(ScimsTrainingService::class)
            ->findIndividual($this->api_entity_no);
    }

    /**
     * The SCIMS individual this trainee links to. Retained as an attribute
     * for callers that need the raw identity parts.
     *
     * @return Attribute<?array<string, mixed>, never>
     */
    protected function individual(): Attribute
    {
        return Attribute::get(function (): ?array {
            $attributes = $this->identity();

            if (! is_array($attributes)) {
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
     * Name for lists and titles, falling back to the entity number.
     *
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(function (): ?string {
            $name = ScimsTrainingService::formatName($this->identity());

            return $name !== '' ? $name : null;
        });
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function gender(): Attribute
    {
        return Attribute::get(function (): ?string {
            $gender = trim((string) ($this->identity()['gender'] ?? ''));

            return $gender !== '' ? $gender : null;
        });
    }

    /**
     * @return Attribute<?CarbonInterface, never>
     */
    protected function birthDate(): Attribute
    {
        return Attribute::get(function (): ?CarbonInterface {
            $birthDate = trim((string) ($this->identity()['birth_date'] ?? ''));

            if ($birthDate === '') {
                return null;
            }

            try {
                return Carbon::parse($birthDate);
            } catch (\Throwable) {
                return null;
            }
        });
    }

    /**
     * @return Attribute<?string, never>
     */
    protected function mobileNo(): Attribute
    {
        return Attribute::get(function (): ?string {
            $mobile = trim((string) ($this->identity()['mobile_no'] ?? ''));

            return $mobile !== '' ? $mobile : null;
        });
    }

    /**
     * Name for lists and titles, falling back to SCIMS and then the entity no.
     *
     * @return Attribute<string, never>
     */
    protected function displayName(): Attribute
    {
        return Attribute::get(fn (): string => $this->full_name ?: $this->api_entity_no);
    }

    /**
     * @return Attribute<?int, never>
     */
    protected function age(): Attribute
    {
        return Attribute::get(fn (): ?int => $this->birth_date?->age);
    }
}
