<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Number Field Component
 *
 * A number input field with stepper buttons for increment/decrement.
 * Supports:
 * - Min/Max boundaries
 * - Step increments
 * - Number formatting (decimal, percentage, currency)
 * - Locale-aware formatting
 */
class NumberField extends Field
{
    protected string $view = 'laravilt-forms::components.fields.number-field';

    protected int|float|Closure|null $min = null;

    protected int|float|Closure|null $max = null;

    protected int|float|Closure $step = 1;

    protected array|Closure $formatOptions = [];

    protected string|Closure|null $locale = null;

    protected string|Closure|null $prefix = null;

    protected string|Closure|null $suffix = null;

    /**
     * Set minimum value.
     */
    public function min(int|float|Closure $min): static
    {
        $this->min = $min;

        return $this;
    }

    /**
     * Get minimum value.
     */
    public function getMin(): int|float|null
    {
        return $this->evaluate($this->min);
    }

    /**
     * Set maximum value.
     */
    public function max(int|float|Closure $max): static
    {
        $this->max = $max;

        return $this;
    }

    /**
     * Get maximum value.
     */
    public function getMax(): int|float|null
    {
        return $this->evaluate($this->max);
    }

    /**
     * Set step increment.
     */
    public function step(int|float|Closure $step): static
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step increment.
     */
    public function getStep(): int|float
    {
        return $this->evaluate($this->step);
    }

    /**
     * Set format options for number formatting.
     */
    public function formatOptions(array|Closure $options): static
    {
        $this->formatOptions = $options;

        return $this;
    }

    /**
     * Get format options.
     */
    public function getFormatOptions(): array
    {
        return $this->evaluate($this->formatOptions);
    }

    /**
     * Format as currency.
     */
    public function currency(string $currency = 'USD'): static
    {
        $this->formatOptions = [
            'style' => 'currency',
            'currency' => $currency,
        ];

        return $this;
    }

    /**
     * Format as percentage.
     */
    public function percentage(): static
    {
        $this->formatOptions = [
            'style' => 'percent',
        ];

        if ($this->step === 1) {
            $this->step = 0.01;
        }

        return $this;
    }

    /**
     * Set locale for formatting.
     */
    public function locale(string|Closure $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get number format locale.
     */
    public function getNumberLocale(): ?string
    {
        return $this->evaluate($this->locale);
    }

    /**
     * Set prefix text.
     */
    public function prefix(string|Closure $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get prefix text.
     */
    public function getPrefix(): ?string
    {
        return $this->evaluate($this->prefix);
    }

    /**
     * Set suffix text.
     */
    public function suffix(string|Closure $suffix): static
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Get suffix text.
     */
    public function getSuffix(): ?string
    {
        return $this->evaluate($this->suffix);
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'min' => $this->getMin(),
            'max' => $this->getMax(),
            'step' => $this->getStep(),
            'formatOptions' => $this->getFormatOptions(),
            'locale' => $this->getNumberLocale(),
            'prefix' => $this->getPrefix(),
            'suffix' => $this->getSuffix(),
        ]);
    }
}
