<?php

namespace App\Models\CalamityMonitoring;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Incident extends Model
{
    public const MAX_ESTIMATED_COST = 9999999999999.99;

    protected $table = 'calamity_incidents';

    protected $fillable = ['juridical_id', 'juridical_name', 'barangay', 'calamity_id', 'date_occurred', 'nature_of_damage', 'estimated_cost_of_damages', 'remarks', 'status', 'photos'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'date_occurred' => 'date',
            'estimated_cost_of_damages' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Incident $incident): void {
            $removed = array_diff(
                $incident->getOriginal('photos') ?? [],
                $incident->photos ?? [],
            );

            if ($removed !== []) {
                Storage::disk('public')->delete(array_values($removed));
            }
        });

        static::deleted(function (Incident $incident): void {
            $photos = $incident->photos ?? [];

            if ($photos !== []) {
                Storage::disk('public')->delete($photos);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'PENDING_VERIFICATION' => 'Pending Verification',
            'VERIFIED' => 'Verified',
            'AID_RELEASED' => 'Aid Released',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function natureOptions(): array
    {
        return [
            'PARTIAL' => 'Partial Damage',
            'TOTAL' => 'Total Damage',
        ];
    }

    public static function statusLabel(?string $status): string
    {
        return static::statusOptions()[$status]
            ?? str($status ?? 'Unknown')->replace('_', ' ')->title()->toString();
    }

    public static function natureLabel(?string $nature): string
    {
        return static::natureOptions()[$nature]
            ?? str($nature ?? 'Unknown')->replace('_', ' ')->title()->toString();
    }

    public function calamity(): BelongsTo
    {
        return $this->belongsTo(Calamity::class);
    }
}
