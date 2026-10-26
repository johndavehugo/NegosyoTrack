<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;

/**
 * An action that can be mounted by a table row click without appearing in the
 * row's action column.
 *
 * Filament treats hidden actions as disabled, which prevents them from being
 * mounted. This action skips that hidden-to-disabled conversion while still
 * respecting explicit `disabled()` and authorization checks.
 */
class RowClickAction extends Action
{
    public function isDisabled(): bool
    {
        return $this->evaluate($this->isDisabled) || ! $this->isAuthorized();
    }
}
