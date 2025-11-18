<?php

namespace Laravilt\Forms\Concerns;

use Closure;

trait HasHelperText
{
    protected string|Closure|null $helperText = null;

    /**
     * Set the helper text.
     */
    public function helperText(string|Closure|null $text): static
    {
        $this->helperText = $text;

        return $this;
    }

    /**
     * Get the helper text.
     */
    public function getHelperText(): ?string
    {
        return $this->evaluate($this->helperText);
    }

    /**
     * Alias for helperText().
     */
    public function hint(string|Closure|null $text): static
    {
        return $this->helperText($text);
    }
}
