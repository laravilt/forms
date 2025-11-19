<?php

namespace Laravilt\Forms\Components;

use Carbon\Carbon;
use Closure;
use DateTime;

class DatePicker extends Field
{
    protected string $view = 'laravilt-forms::components.fields.date-picker';

    protected ?string $format = 'Y-m-d';

    protected ?string $displayFormat = null;

    protected Carbon|string|Closure|null $minDate = null;

    protected Carbon|string|Closure|null $maxDate = null;

    protected bool $native = false;

    protected bool $timePicker = false;

    protected array $disabledDates = [];

    protected bool $seconds = true;

    protected ?string $timezone = null;

    protected ?string $locale = null;

    protected ?int $hoursStep = null;

    protected ?int $minutesStep = null;

    protected ?int $secondsStep = null;

    protected ?int $firstDayOfWeek = null;

    protected bool $closeOnDateSelection = false;

    protected Carbon|string|Closure|null $defaultFocusedDate = null;

    protected bool $is12hr = false;

    /**
     * Use native date input.
     */
    public function native(bool|Closure $condition = true): static
    {
        if ($condition instanceof Closure) {
            $this->native = $condition();
        } else {
            $this->native = $condition;
        }

        return $this;
    }

    /**
     * Enable time-only selection.
     */
    public function time(bool|Closure $condition = true): static
    {
        $shouldApply = $condition instanceof Closure ? $condition() : $condition;

        if ($shouldApply) {
            $this->timePicker = true;
            // Set time-only format, respecting 12hr if already set
            $hourToken = $this->is12hr ? 'h' : 'H';
            $ampmToken = $this->is12hr ? ' A' : '';
            $this->format = $this->seconds ? "{$hourToken}:i:s{$ampmToken}" : "{$hourToken}:i{$ampmToken}";
        }

        return $this;
    }

    /**
     * Enable date-only selection (default).
     */
    public function date(bool|Closure $condition = true): static
    {
        $shouldApply = $condition instanceof Closure ? $condition() : $condition;

        if ($shouldApply) {
            $this->timePicker = false;
            $this->format = 'Y-m-d';
        }

        return $this;
    }

    /**
     * Enable date and time selection.
     */
    public function datetime(bool|Closure $condition = true): static
    {
        $shouldApply = $condition instanceof Closure ? $condition() : $condition;

        if ($shouldApply) {
            $this->timePicker = true;
            // Set datetime format, respecting 12hr if already set
            $hourToken = $this->is12hr ? 'h' : 'H';
            $ampmToken = $this->is12hr ? ' A' : '';
            $this->format = $this->seconds ? "Y-m-d {$hourToken}:i:s{$ampmToken}" : "Y-m-d {$hourToken}:i{$ampmToken}";
        }

        return $this;
    }

    /**
     * Set the storage format.
     */
    public function format(string|Closure $format): static
    {
        if ($format instanceof Closure) {
            $this->format = $format();
        } else {
            $this->format = $format;
        }

        return $this;
    }

    /**
     * Set the display format (for non-native pickers).
     */
    public function displayFormat(string|Closure $format): static
    {
        if ($format instanceof Closure) {
            $this->displayFormat = $format();
        } else {
            $this->displayFormat = $format;
        }

        return $this;
    }

    /**
     * Set the locale for date display.
     */
    public function locale(string|Closure $locale): static
    {
        if ($locale instanceof Closure) {
            $this->locale = $locale();
        } else {
            $this->locale = $locale;
        }

        return $this;
    }

    /**
     * Enable/disable seconds input.
     */
    public function seconds(bool|Closure $condition = true): static
    {
        $shouldApply = $condition instanceof Closure ? $condition() : $condition;
        $this->seconds = $shouldApply;

        // Update format based on seconds setting if timePicker is enabled
        if ($this->timePicker) {
            // Determine if using 12-hour or 24-hour format
            $hourToken = $this->is12hr ? 'h' : 'H';
            $ampmToken = $this->is12hr ? ' A' : '';

            // Check if it's time-only (no date components)
            if (!str_contains($this->format, 'Y') && !str_contains($this->format, 'm') && !str_contains($this->format, 'd')) {
                // Time-only format
                $this->format = $shouldApply ? "{$hourToken}:i:s{$ampmToken}" : "{$hourToken}:i{$ampmToken}";
            } else {
                // DateTime format
                $this->format = $shouldApply ? "Y-m-d {$hourToken}:i:s{$ampmToken}" : "Y-m-d {$hourToken}:i{$ampmToken}";
            }
        }

        return $this;
    }

    /**
     * Set the timezone.
     */
    public function timezone(string|Closure $timezone): static
    {
        if ($timezone instanceof Closure) {
            $this->timezone = $timezone();
        } else {
            $this->timezone = $timezone;
        }

        return $this;
    }

    /**
     * Use 12-hour format with AM/PM.
     */
    public function format12hr(bool|Closure $condition = true): static
    {
        $shouldApply = $condition instanceof Closure ? $condition() : $condition;
        $this->is12hr = $shouldApply;

        // Always update format if applying 12hr, even if timePicker is not set yet
        // This allows format12hr() to be called before time() or datetime()
        if ($shouldApply && $this->format) {
            // Replace H with h for 12-hour, and add A for AM/PM
            $this->format = str_replace('H', 'h', $this->format);

            // Add AM/PM marker if not already present
            if (!str_contains($this->format, 'A') && !str_contains($this->format, 'a')) {
                $this->format .= ' A';
            }
        }

        return $this;
    }

    /**
     * Set minimum selectable date.
     */
    public function minDate(DateTime|string|Closure|null $date): static
    {
        if ($date instanceof Closure) {
            $this->minDate = $date();
        } else {
            $this->minDate = $date;
        }

        return $this;
    }

    /**
     * Set maximum selectable date.
     */
    public function maxDate(DateTime|string|Closure|null $date): static
    {
        if ($date instanceof Closure) {
            $this->maxDate = $date();
        } else {
            $this->maxDate = $date;
        }

        return $this;
    }

    /**
     * Set specific dates to disable.
     */
    public function disabledDates(array|Closure $dates): static
    {
        if ($dates instanceof Closure) {
            $this->disabledDates = $dates();
        } else {
            $this->disabledDates = $dates;
        }

        return $this;
    }

    /**
     * Set hours step interval.
     */
    public function hoursStep(int|Closure $step): static
    {
        if ($step instanceof Closure) {
            $this->hoursStep = $step();
        } else {
            $this->hoursStep = $step;
        }

        return $this;
    }

    /**
     * Set minutes step interval.
     */
    public function minutesStep(int|Closure $step): static
    {
        if ($step instanceof Closure) {
            $this->minutesStep = $step();
        } else {
            $this->minutesStep = $step;
        }

        return $this;
    }

    /**
     * Set seconds step interval.
     */
    public function secondsStep(int|Closure $step): static
    {
        if ($step instanceof Closure) {
            $this->secondsStep = $step();
        } else {
            $this->secondsStep = $step;
        }

        return $this;
    }

    /**
     * Set first day of week (0-7, with Monday as 1 and Sunday as 7 or 0).
     */
    public function firstDayOfWeek(int|Closure $day): static
    {
        if ($day instanceof Closure) {
            $this->firstDayOfWeek = $day();
        } else {
            $this->firstDayOfWeek = $day;
        }

        return $this;
    }

    /**
     * Week starts on Monday.
     */
    public function weekStartsOnMonday(): static
    {
        return $this->firstDayOfWeek(1);
    }

    /**
     * Week starts on Sunday.
     */
    public function weekStartsOnSunday(): static
    {
        return $this->firstDayOfWeek(0);
    }

    /**
     * Close picker when date is selected.
     */
    public function closeOnDateSelection(bool|Closure $condition = true): static
    {
        if ($condition instanceof Closure) {
            $this->closeOnDateSelection = $condition();
        } else {
            $this->closeOnDateSelection = $condition;
        }

        return $this;
    }

    /**
     * Set default focused date when calendar opens.
     */
    public function defaultFocusedDate(DateTime|string|Closure|null $date): static
    {
        if ($date instanceof Closure) {
            $this->defaultFocusedDate = $date();
        } else {
            $this->defaultFocusedDate = $date;
        }

        return $this;
    }

    /**
     * Resolve date value to string.
     */
    protected function resolveDate(Carbon|DateTime|string|Closure|null $date): ?string
    {
        if ($date === null) {
            return null;
        }

        if ($date instanceof Closure) {
            $date = $date();
        }

        if ($date instanceof Carbon || $date instanceof DateTime) {
            return $date->format($this->format ?? 'Y-m-d');
        }

        return $date;
    }

    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'format' => $this->format,
            'displayFormat' => $this->displayFormat ?? $this->format,
            'minDate' => $this->resolveDate($this->minDate),
            'maxDate' => $this->resolveDate($this->maxDate),
            'native' => $this->native,
            'timePicker' => $this->timePicker,
            'disabledDates' => $this->disabledDates,
            'seconds' => $this->seconds,
            'timezone' => $this->timezone,
            'locale' => $this->locale ?? 'en',
            'hoursStep' => $this->hoursStep,
            'minutesStep' => $this->minutesStep,
            'secondsStep' => $this->secondsStep,
            'firstDayOfWeek' => $this->firstDayOfWeek,
            'closeOnDateSelection' => $this->closeOnDateSelection,
            'defaultFocusedDate' => $this->resolveDate($this->defaultFocusedDate),
            'is12hr' => $this->is12hr,
        ]);
    }
}
