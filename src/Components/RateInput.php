<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Rate Input Component
 *
 * A rating input field with customizable stars/icons.
 * Supports:
 * - Custom number of stars (max rating)
 * - Half-star/decimal ratings
 * - Custom icons
 * - Custom colors
 * - Read-only mode
 */
class RateInput extends Field
{
    protected string $view = 'laravilt-forms::components.fields.rate-input';

    protected int|Closure $maxRating = 5;

    protected bool|Closure $allowHalf = false;

    protected string|Closure $icon = 'star';

    protected string|Closure|null $color = null;

    protected bool|Closure $showValue = false;

    /**
     * Set the maximum rating value.
     */
    public function maxRating(int|Closure $max): static
    {
        $this->maxRating = $max;

        return $this;
    }

    /**
     * Get the maximum rating value.
     */
    public function getMaxRating(): int
    {
        return $this->evaluate($this->maxRating);
    }

    /**
     * Allow half-star ratings.
     */
    public function allowHalf(bool|Closure $allowHalf = true): static
    {
        $this->allowHalf = $allowHalf;

        return $this;
    }

    /**
     * Get allow half state.
     */
    public function getAllowHalf(): bool
    {
        return $this->evaluate($this->allowHalf);
    }

    /**
     * Set custom icon for rating.
     */
    public function icon(string|Closure $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon name.
     */
    public function getIcon(): string
    {
        return $this->evaluate($this->icon);
    }

    /**
     * Set custom color for filled icons.
     */
    public function color(string|Closure $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color.
     */
    public function getColor(): ?string
    {
        return $this->evaluate($this->color);
    }

    /**
     * Show numeric value next to stars.
     */
    public function showValue(bool|Closure $showValue = true): static
    {
        $this->showValue = $showValue;

        return $this;
    }

    /**
     * Get show value state.
     */
    public function getShowValue(): bool
    {
        return $this->evaluate($this->showValue);
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'max' => $this->getMaxRating(),
            'allowHalf' => $this->getAllowHalf(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'showValue' => $this->getShowValue(),
        ]);
    }
}
