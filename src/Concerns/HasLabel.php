<?php

namespace Laravilt\Forms\Concerns;

use Closure;

trait HasLabel
{
    protected string|Closure|null $label = null;

    /**
     * Set the field label.
     */
    public function label(string|Closure|null $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the field label.
     */
    public function getLabel(): ?string
    {
        return $this->evaluate($this->label) ?? $this->generateLabelFromName();
    }

    /**
     * Generate a label from the field name.
     */
    protected function generateLabelFromName(): string
    {
        if (! isset($this->name)) {
            return '';
        }

        return (string) str($this->name)
            ->afterLast('.')
            ->kebab()
            ->replace(['-', '_'], ' ')
            ->ucfirst();
    }
}
