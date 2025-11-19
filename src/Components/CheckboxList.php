<?php

namespace Laravilt\Forms\Components;

use Closure;

class CheckboxList extends Field
{
    protected string $view = 'laravilt-forms::components.fields.checkbox-list';

    protected array|Closure $options = [];

    protected bool $inline = false;

    protected ?int $columns = null;

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

    protected function getVueComponent(): string
    {
        return 'CheckboxList';
    }

    protected function getVueProps(): array
    {
        return array_merge([
            'options' => $this->getOptions(),
            'disabled' => $this->disabled,
            'inline' => $this->inline,
            'columns' => $this->columns,
            'required' => $this->isRequired(),
            'rules' => $this->getValidationRules(),
            'defaultValue' => $this->getState() ?? [],
        ], $this->getIconProps());
    }

    protected function getFlutterWidget(): string
    {
        return 'LaraviltCheckboxList';
    }

    protected function getFlutterWidgetProps(): array
    {
        return [
            'options' => $this->getOptions(),
            'enabled' => ! $this->disabled,
            'inline' => $this->inline,
            'columns' => $this->columns,
            'required' => $this->isRequired(),
            'validators' => $this->getValidationRules(),
            'initialValue' => $this->getState() ?? [],
        ];
    }
}
