<?php

namespace Laravilt\Forms\Concerns;

use Closure;

trait HasPlaceholder
{
    protected string|Closure|null $placeholder = null;

    /**
     * Set the placeholder text.
     */
    public function placeholder(string|Closure|null $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Get the placeholder text.
     */
    public function getPlaceholder(): ?string
    {
        return $this->evaluate($this->placeholder);
    }
}
