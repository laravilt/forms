<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Key-Value Component
 *
 * A field for entering key-value pairs with support for:
 * - Adding/removing pairs
 * - Custom key/value labels
 * - Key/value validation
 * - Reordering
 */
class KeyValue extends Field
{
    protected string $view = 'laravilt-forms::components.fields.key-value';

    /**
     * Label for the key column.
     */
    protected string|Closure $keyLabel = 'Key';

    /**
     * Label for the value column.
     */
    protected string|Closure $valueLabel = 'Value';

    /**
     * Placeholder for key input.
     */
    protected string|Closure|null $keyPlaceholder = null;

    /**
     * Placeholder for value input.
     */
    protected string|Closure|null $valuePlaceholder = null;

    /**
     * Whether keys should be unique.
     */
    protected bool|Closure $uniqueKeys = true;

    /**
     * Whether the key-value pairs can be reordered.
     */
    protected bool|Closure $reorderable = false;

    /**
     * Minimum number of pairs.
     */
    protected int|Closure|null $minPairs = null;

    /**
     * Maximum number of pairs.
     */
    protected int|Closure|null $maxPairs = null;

    /**
     * Set the key column label.
     */
    public function keyLabel(string|Closure $label): static
    {
        $this->keyLabel = $label;

        return $this;
    }

    /**
     * Set the value column label.
     */
    public function valueLabel(string|Closure $label): static
    {
        $this->valueLabel = $label;

        return $this;
    }

    /**
     * Set the key placeholder.
     */
    public function keyPlaceholder(string|Closure|null $placeholder): static
    {
        $this->keyPlaceholder = $placeholder;

        return $this;
    }

    /**
     * Set the value placeholder.
     */
    public function valuePlaceholder(string|Closure|null $placeholder): static
    {
        $this->valuePlaceholder = $placeholder;

        return $this;
    }

    /**
     * Allow duplicate keys.
     */
    public function allowDuplicateKeys(bool|Closure $condition = true): static
    {
        $this->uniqueKeys = ! $condition;

        return $this;
    }

    /**
     * Make reorderable.
     */
    public function reorderable(bool|Closure $condition = true): static
    {
        $this->reorderable = $condition;

        return $this;
    }

    /**
     * Set minimum number of pairs.
     */
    public function minPairs(int|Closure $min): static
    {
        $this->minPairs = $min;

        return $this;
    }

    /**
     * Set maximum number of pairs.
     */
    public function maxPairs(int|Closure $max): static
    {
        $this->maxPairs = $max;

        return $this;
    }

    /**
     * Get the key label.
     */
    public function getKeyLabel(): string
    {
        return $this->evaluate($this->keyLabel);
    }

    /**
     * Get the value label.
     */
    public function getValueLabel(): string
    {
        return $this->evaluate($this->valueLabel);
    }

    /**
     * Get the key placeholder.
     */
    public function getKeyPlaceholder(): ?string
    {
        return $this->evaluate($this->keyPlaceholder);
    }

    /**
     * Get the value placeholder.
     */
    public function getValuePlaceholder(): ?string
    {
        return $this->evaluate($this->valuePlaceholder);
    }

    /**
     * Check if keys must be unique.
     */
    public function hasUniqueKeys(): bool
    {
        return $this->evaluate($this->uniqueKeys);
    }

    /**
     * Check if reorderable.
     */
    public function isReorderable(): bool
    {
        return $this->evaluate($this->reorderable);
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'keyLabel' => $this->getKeyLabel(),
            'valueLabel' => $this->getValueLabel(),
            'keyPlaceholder' => $this->getKeyPlaceholder(),
            'valuePlaceholder' => $this->getValuePlaceholder(),
            'uniqueKeys' => $this->hasUniqueKeys(),
            'reorderable' => $this->isReorderable(),
            'minPairs' => $this->evaluate($this->minPairs),
            'maxPairs' => $this->evaluate($this->maxPairs),
        ]);
    }
}
