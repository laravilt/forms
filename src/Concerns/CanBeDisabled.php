<?php

namespace Laravilt\Forms\Concerns;

use Closure;

trait CanBeDisabled
{
    protected bool|Closure $disabled = false;

    /**
     * Mark the field as disabled.
     */
    public function disabled(bool|Closure $condition = true): static
    {
        $this->disabled = $condition;

        return $this;
    }

    /**
     * Check if the field is disabled.
     */
    public function isDisabled(): bool
    {
        return $this->evaluate($this->disabled);
    }
}
