<?php

namespace Laravilt\Forms\Components;

use Closure;

class ToggleButtons extends Field
{
    protected string $view = 'laravilt-forms::components.fields.toggle-buttons';

    protected array|Closure $options = [];

    protected bool $multiple = false;

    protected bool $inline = true;

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

        foreach ($options as $key => $value) {
            // If already in correct format (has 'value' and 'label' keys)
            if (is_array($value) && isset($value['value']) && isset($value['label'])) {
                $transformed[] = $value;
            } else {
                // Transform key-value pairs to object format
                $transformed[] = [
                    'value' => $key,
                    'label' => $value,
                ];
            }
        }

        return $transformed;
    }

    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'options' => $this->transformOptions($this->getOptions()),
            'multiple' => $this->multiple,
            'inline' => $this->inline,
        ]);
    }
}
