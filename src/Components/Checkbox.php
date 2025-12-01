<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Checkbox Field
 *
 * A checkbox input with support for:
 * - Single checkbox
 * - Checkbox list (multiple options)
 * - Inline or stacked layout
 * - Custom checked/unchecked values
 */
class Checkbox extends Field
{
    protected string $view = 'laravilt-forms::components.fields.checkbox';

    protected array|Closure $options = [];

    protected bool $inline = false;

    protected mixed $checkedValue = true;

    protected mixed $uncheckedValue = false;

    protected string|Closure|null $description = null;

    /**
     * Set checkbox options for checkbox list.
     *
     * @param  array|Closure  $options  Array of options ['value' => 'label']
     */
    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get checkbox options.
     */
    public function getOptions(): array
    {
        return $this->evaluate($this->options);
    }

    /**
     * Check if this is a checkbox list.
     */
    public function isCheckboxList(): bool
    {
        return ! empty($this->getOptions());
    }

    /**
     * Display checkboxes inline.
     */
    public function inline(bool $condition = true): static
    {
        $this->inline = $condition;

        return $this;
    }

    /**
     * Check if checkboxes should be inline.
     */
    public function isInline(): bool
    {
        return $this->inline;
    }

    /**
     * Set the value when checkbox is checked.
     */
    public function checkedValue(mixed $value): static
    {
        $this->checkedValue = $value;

        return $this;
    }

    /**
     * Get the checked value.
     */
    public function getCheckedValue(): mixed
    {
        return $this->evaluate($this->checkedValue);
    }

    /**
     * Set the value when checkbox is unchecked.
     */
    public function uncheckedValue(mixed $value): static
    {
        $this->uncheckedValue = $value;

        return $this;
    }

    /**
     * Get the unchecked value.
     */
    public function getUncheckedValue(): mixed
    {
        return $this->evaluate($this->uncheckedValue);
    }

    /**
     * Set description text.
     */
    public function description(string|Closure $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     */
    public function getDescription(): ?string
    {
        return $this->evaluate($this->description);
    }

    /**
     * Check if checkbox is checked.
     */
    public function isChecked(): bool
    {
        $value = $this->getValue();

        if ($this->isCheckboxList()) {
            return is_array($value);
        }

        return $value === $this->getCheckedValue();
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'options' => $this->getOptions(),
            'inline' => $this->isInline(),
            'checkedValue' => $this->getCheckedValue(),
            'uncheckedValue' => $this->getUncheckedValue(),
            'description' => $this->getDescription(),
            'isChecked' => $this->isChecked(),
            'isCheckboxList' => $this->isCheckboxList(),
            'isLive' => $this->isLive(),
            'isLazy' => $this->isLazy(),
            'liveDebounce' => $this->getLiveDebounce(),
        ]);
    }
}
