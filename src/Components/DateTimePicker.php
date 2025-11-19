<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * DateTime Picker Component
 *
 * A combined date and time picker with support for:
 * - Date and time selection
 * - Timezone support
 * - Min/max datetime validation
 * - 12/24-hour format
 */
class DateTimePicker extends Field
{
    protected string $view = 'laravilt-forms::components.fields.datetime-picker';

    /**
     * Whether to use a 24-hour format.
     */
    protected bool|Closure $format24Hour = false;

    /**
     * Time step in minutes.
     */
    protected int|Closure $step = 1;

    /**
     * Minimum datetime.
     */
    protected string|Closure|null $minDateTime = null;

    /**
     * Maximum datetime.
     */
    protected string|Closure|null $maxDateTime = null;

    /**
     * Timezone for display.
     */
    protected string|Closure|null $timezone = null;

    /**
     * Whether to show seconds.
     */
    protected bool|Closure $withSeconds = false;

    /**
     * Use 24-hour format.
     */
    public function format24Hour(bool|Closure $condition = true): static
    {
        $this->format24Hour = $condition;

        return $this;
    }

    /**
     * Set time step in minutes.
     */
    public function step(int|Closure $minutes): static
    {
        $this->step = $minutes;

        return $this;
    }

    /**
     * Set minimum datetime.
     */
    public function minDateTime(string|Closure $datetime): static
    {
        $this->minDateTime = $datetime;

        return $this;
    }

    /**
     * Set maximum datetime.
     */
    public function maxDateTime(string|Closure $datetime): static
    {
        $this->maxDateTime = $datetime;

        return $this;
    }

    /**
     * Set timezone.
     */
    public function timezone(string|Closure $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Include seconds.
     */
    public function withSeconds(bool|Closure $condition = true): static
    {
        $this->withSeconds = $condition;

        return $this;
    }

    /**
     * Check if using 24-hour format.
     */
    public function uses24HourFormat(): bool
    {
        return $this->evaluate($this->format24Hour);
    }

    /**
     * Get time step.
     */
    public function getStep(): int
    {
        return $this->evaluate($this->step);
    }

    /**
     * Get minimum datetime.
     */
    public function getMinDateTime(): ?string
    {
        return $this->evaluate($this->minDateTime);
    }

    /**
     * Get maximum datetime.
     */
    public function getMaxDateTime(): ?string
    {
        return $this->evaluate($this->maxDateTime);
    }

    /**
     * Get timezone.
     */
    public function getTimezone(): ?string
    {
        return $this->evaluate($this->timezone);
    }

    /**
     * Check if seconds should be shown.
     */
    public function shouldShowSeconds(): bool
    {
        return $this->evaluate($this->withSeconds);
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'format24Hour' => $this->uses24HourFormat(),
            'step' => $this->getStep(),
            'minDateTime' => $this->getMinDateTime(),
            'maxDateTime' => $this->getMaxDateTime(),
            'timezone' => $this->getTimezone(),
            'withSeconds' => $this->shouldShowSeconds(),
        ]);
    }

}
