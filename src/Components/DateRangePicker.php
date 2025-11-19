<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Date Range Picker Component
 *
 * A date range picker that allows selecting start and end dates.
 * Supports:
 * - Min/Max date constraints
 * - Locale-aware formatting
 * - Multiple calendar months
 * - Auto-close on selection
 */
class DateRangePicker extends Field
{
    protected string $view = 'laravilt-forms::components.fields.date-range-picker';

    protected string|Closure|null $minDate = null;

    protected string|Closure|null $maxDate = null;

    protected string|Closure $locale = 'en';

    protected int|Closure $numberOfMonths = 2;

    protected bool|Closure $closeOnSelect = false;

    /**
     * Set minimum selectable date.
     */
    public function minDate(string|Closure $minDate): static
    {
        $this->minDate = $minDate;

        return $this;
    }

    /**
     * Get minimum date.
     */
    public function getMinDate(): ?string
    {
        return $this->evaluate($this->minDate);
    }

    /**
     * Set maximum selectable date.
     */
    public function maxDate(string|Closure $maxDate): static
    {
        $this->maxDate = $maxDate;

        return $this;
    }

    /**
     * Get maximum date.
     */
    public function getMaxDate(): ?string
    {
        return $this->evaluate($this->maxDate);
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
     * Get locale.
     */
    public function getRangeLocale(): string
    {
        return $this->evaluate($this->locale);
    }

    /**
     * Set number of months to display.
     */
    public function numberOfMonths(int|Closure $numberOfMonths): static
    {
        $this->numberOfMonths = $numberOfMonths;

        return $this;
    }

    /**
     * Get number of months.
     */
    public function getNumberOfMonths(): int
    {
        return $this->evaluate($this->numberOfMonths);
    }

    /**
     * Close picker on selection.
     */
    public function closeOnSelect(bool|Closure $closeOnSelect = true): static
    {
        $this->closeOnSelect = $closeOnSelect;

        return $this;
    }

    /**
     * Get close on select state.
     */
    public function getCloseOnSelect(): bool
    {
        return $this->evaluate($this->closeOnSelect);
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'minDate' => $this->getMinDate(),
            'maxDate' => $this->getMaxDate(),
            'locale' => $this->getRangeLocale(),
            'numberOfMonths' => $this->getNumberOfMonths(),
            'closeOnSelect' => $this->getCloseOnSelect(),
        ]);
    }
}
