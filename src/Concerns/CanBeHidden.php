<?php

namespace Laravilt\Forms\Concerns;

use Closure;

trait CanBeHidden
{
    protected bool|Closure $hidden = false;

    protected bool|Closure $visible = true;

    /**
     * Hide the field.
     */
    public function hidden(bool|Closure $condition = true): static
    {
        $this->hidden = $condition;

        return $this;
    }

    /**
     * Show/hide the field based on a condition.
     */
    public function visible(bool|Closure $condition = true): static
    {
        $this->visible = $condition;

        return $this;
    }

    /**
     * Check if the field is hidden.
     */
    public function isHidden(): bool
    {
        if ($this->evaluate($this->hidden)) {
            return true;
        }

        return ! $this->evaluate($this->visible);
    }

    /**
     * Check if the field is visible.
     */
    public function isVisible(): bool
    {
        return ! $this->isHidden();
    }
}
