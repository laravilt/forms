<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Time Picker Component
 *
 * A time picker field with support for:
 * - 12/24 hour format
 * - Step intervals
 * - Min/max time validation
 */
class TimePicker extends Field
{
    protected string $view = 'laravilt-forms::components.fields.time-picker';

    /**
     * Whether to use 24-hour format.
     */
    protected bool|Closure $format24Hour = false;

    /**
     * Time step in minutes.
     */
    protected int|Closure $step = 1;

    /**
     * Minimum time.
     */
    protected string|Closure|null $minTime = null;

    /**
     * Maximum time.
     */
    protected string|Closure|null $maxTime = null;

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
     * Set minimum time.
     */
    public function minTime(string|Closure $time): static
    {
        $this->minTime = $time;

        return $this;
    }

    /**
     * Set maximum time.
     */
    public function maxTime(string|Closure $time): static
    {
        $this->maxTime = $time;

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
     * Get minimum time.
     */
    public function getMinTime(): ?string
    {
        return $this->evaluate($this->minTime);
    }

    /**
     * Get maximum time.
     */
    public function getMaxTime(): ?string
    {
        return $this->evaluate($this->maxTime);
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
            'minTime' => $this->getMinTime(),
            'maxTime' => $this->getMaxTime(),
            'withSeconds' => $this->shouldShowSeconds(),
        ]);
    }
}
