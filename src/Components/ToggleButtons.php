<?php

namespace Laravilt\Forms\Components;

use Closure;

class ToggleButtons extends Field
{
    protected string $view = 'laravilt-forms::components.fields.toggle-buttons';

    protected array|Closure $options = [];

    protected bool $multiple = false;

    protected bool $inline = true;

    protected array|Closure $icons = [];

    protected array|Closure $colors = [];

    protected bool $grouped = false;

    /**
     * Set the available options.
     */
    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Allow multiple selections.
     */
    public function multiple(bool $condition = true): static
    {
        $this->multiple = $condition;

        return $this;
    }

    /**
     * Display the buttons inline.
     */
    public function inline(bool $condition = true): static
    {
        $this->inline = $condition;

        return $this;
    }

    /**
     * Set icons for each option (FilamentPHP v4 compatibility).
     *
     * @param  array|Closure  $icons  Map of option value => icon name
     */
    public function icons(array|Closure $icons): static
    {
        $this->icons = $icons;

        return $this;
    }

    /**
     * Set colors for each option (FilamentPHP v4 compatibility).
     *
     * @param  array|Closure  $colors  Map of option value => color
     */
    public function colors(array|Closure $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    /**
     * Group buttons together (FilamentPHP v4 compatibility).
     */
    public function grouped(bool $condition = true): static
    {
        $this->grouped = $condition;

        return $this;
    }

    /**
     * Get the icons array.
     */
    protected function getIcons(): array
    {
        if ($this->icons instanceof Closure) {
            return ($this->icons)();
        }

        return $this->icons;
    }

    /**
     * Get the colors array.
     */
    protected function getColors(): array
    {
        if ($this->colors instanceof Closure) {
            return ($this->colors)();
        }

        return $this->colors;
    }

    /**
     * Get the options array.
     */
    protected function getOptions(): array
    {
        if ($this->options instanceof Closure) {
            return ($this->options)();
        }

        return $this->options;
    }

    /**
     * Transform options array to format expected by Vue component.
     */
    protected function transformOptions(array $options): array
    {
        $transformed = [];
        $icons = $this->getIcons();
        $colors = $this->getColors();

        foreach ($options as $key => $value) {
            // If already in correct format (has 'value' and 'label' keys)
            if (is_array($value) && isset($value['value']) && isset($value['label'])) {
                $option = $value;
            } else {
                // Transform key-value pairs to object format
                $option = [
                    'value' => $key,
                    'label' => $value,
                ];
            }

            // Add icon if specified
            $optionValue = $option['value'] ?? $key;
            if (isset($icons[$optionValue])) {
                $option['icon'] = $icons[$optionValue];
            }

            // Add color if specified
            if (isset($colors[$optionValue])) {
                $option['color'] = $colors[$optionValue];
            }

            $transformed[] = $option;
        }

        return $transformed;
    }

    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'options' => $this->transformOptions($this->getOptions()),
            'multiple' => $this->multiple,
            'inline' => $this->inline,
            'grouped' => $this->grouped,
        ]);
    }
}
