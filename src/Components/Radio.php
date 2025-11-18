<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Radio Field
 *
 * A radio button group with support for:
 * - Radio button options
 * - Inline or stacked layout
 * - Custom option descriptions
 * - Boolean radio (Yes/No)
 */
class Radio extends Field
{
    protected string $view = 'laravilt-forms::components.fields.radio';

    protected array|Closure $options = [];

    protected bool $inline = false;

    protected bool $boolean = false;

    protected array $descriptions = [];

    /**
     * Set radio options.
     *
     * @param  array|Closure  $options  Array of options ['value' => 'label']
     */
    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get radio options.
     */
    public function getOptions(): array
    {
        if ($this->boolean) {
            return [
                1 => __('forms::fields.radio.yes'),
                0 => __('forms::fields.radio.no'),
            ];
        }

        return $this->evaluate($this->options);
    }

    /**
     * Display radio buttons inline.
     */
    public function inline(bool $condition = true): static
    {
        $this->inline = $condition;

        return $this;
    }

    /**
     * Check if radio buttons should be inline.
     */
    public function isInline(): bool
    {
        return $this->inline;
    }

    /**
     * Make this a boolean radio (Yes/No).
     */
    public function boolean(bool $condition = true): static
    {
        $this->boolean = $condition;

        return $this;
    }

    /**
     * Check if this is a boolean radio.
     */
    public function isBoolean(): bool
    {
        return $this->boolean;
    }

    /**
     * Set descriptions for specific options.
     *
     * @param  array  $descriptions  ['option_value' => 'description']
     */
    public function descriptions(array $descriptions): static
    {
        $this->descriptions = $descriptions;

        return $this;
    }

    /**
     * Get description for an option.
     */
    public function getOptionDescription(mixed $value): ?string
    {
        return $this->descriptions[$value] ?? null;
    }

    /**
     * Get all descriptions.
     */
    public function getDescriptions(): array
    {
        return $this->descriptions;
    }

    /**
     * Check if a specific option is selected.
     */
    public function isSelected(mixed $value): bool
    {
        return $this->getValue() == $value;
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'options' => $this->getOptions(),
            'inline' => $this->isInline(),
            'boolean' => $this->isBoolean(),
            'descriptions' => $this->getDescriptions(),
        ]);
    }
}
