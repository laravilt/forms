<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Date Picker Field
 *
 * A date picker with support for:
 * - Date selection
 * - Date range selection
 * - Min/max dates
 * - Disabled dates
 * - Custom date format
 */
class DatePicker extends Field
{
    protected string $view = 'laravilt-forms::components.fields.date-picker';

    protected string $format = 'Y-m-d';

    protected string $displayFormat = 'M d, Y';

    protected ?string $minDate = null;

    protected ?string $maxDate = null;

    protected array|Closure $disabledDates = [];

    protected bool $range = false;

    protected bool $timePicker = false;

    protected ?string $firstDayOfWeek = null;

    /**
     * Set the date format (for storage).
     */
    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get the date format.
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Set the display format (for UI).
     */
    public function displayFormat(string $format): static
    {
        $this->displayFormat = $format;

        return $this;
    }

    /**
     * Get the display format.
     */
    public function getDisplayFormat(): string
    {
        return $this->displayFormat;
    }

    /**
     * Set minimum selectable date.
     */
    public function minDate(string|Closure $date): static
    {
        $this->minDate = $date;

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
    public function maxDate(string|Closure $date): static
    {
        $this->maxDate = $date;

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
     * Set disabled dates.
     */
    public function disabledDates(array|Closure $dates): static
    {
        $this->disabledDates = $dates;

        return $this;
    }

    /**
     * Get disabled dates.
     */
    public function getDisabledDates(): array
    {
        return $this->evaluate($this->disabledDates);
    }

    /**
     * Enable date range selection.
     */
    public function range(bool $condition = true): static
    {
        $this->range = $condition;

        return $this;
    }

    /**
     * Check if range selection is enabled.
     */
    public function isRange(): bool
    {
        return $this->range;
    }

    /**
     * Enable time picker.
     */
    public function timePicker(bool $condition = true): static
    {
        $this->timePicker = $condition;

        return $this;
    }

    /**
     * Check if time picker is enabled.
     */
    public function hasTimePicker(): bool
    {
        return $this->timePicker;
    }

    /**
     * Set first day of week (0 = Sunday, 1 = Monday).
     */
    public function firstDayOfWeek(int $day): static
    {
        $this->firstDayOfWeek = $day;

        return $this;
    }

    /**
     * Get first day of week.
     */
    public function getFirstDayOfWeek(): ?string
    {
        return $this->firstDayOfWeek;
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'format' => $this->getFormat(),
            'displayFormat' => $this->getDisplayFormat(),
            'minDate' => $this->getMinDate(),
            'maxDate' => $this->getMaxDate(),
            'disabledDates' => $this->getDisabledDates(),
            'range' => $this->isRange(),
            'timePicker' => $this->hasTimePicker(),
            'firstDayOfWeek' => $this->getFirstDayOfWeek(),
        ]);
    }
}
