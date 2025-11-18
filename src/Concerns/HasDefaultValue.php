<?php

namespace Laravilt\Forms\Concerns;

trait HasDefaultValue
{
    protected mixed $default = null;

    /**
     * Set the default value.
     */
    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }

    /**
     * Get the default value.
     */
    public function getDefaultValue(): mixed
    {
        return $this->evaluate($this->default);
    }
}
