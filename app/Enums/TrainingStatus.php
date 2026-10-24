<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TrainingStatus: string implements HasColor, HasIcon, HasLabel
{
    case Completed = 'COMPLETED';
    case Ongoing = 'ONGOING';
    case Dropped = 'DROPPED';
    case Incomplete = 'INCOMPLETE';

    public function getLabel(): string
    {
        return match ($this) {
            self::Completed => 'Completed',
            self::Ongoing => 'Ongoing',
            self::Dropped => 'Dropped',
            self::Incomplete => 'Incomplete',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Completed => 'success',
            self::Ongoing => 'info',
            self::Dropped => 'danger',
            self::Incomplete => 'warning',
        };
    }

    public function getIcon(): string|BackedEnum|null
    {
        return match ($this) {
            self::Completed => Heroicon::CheckBadge,
            self::Ongoing => Heroicon::Clock,
            self::Dropped => Heroicon::XCircle,
            self::Incomplete => Heroicon::ExclamationTriangle,
        };
    }

    /**
     * Resolve a form/table state that may already be an enum or still a
     * string (for example when a Select field has just been filled).
     */
    public static function fromState(mixed $state): ?self
    {
        if ($state instanceof self) {
            return $state;
        }

        return is_string($state) ? self::tryFrom($state) : null;
    }
}
