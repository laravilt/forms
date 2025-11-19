<?php

namespace Laravilt\Forms\Components;

class KeyValue extends Field
{
    protected string $view = 'laravilt-forms::components.fields.key-value';

    protected string $keyLabel = 'Key';

    protected string $valueLabel = 'Value';

    protected string $addButtonLabel = 'Add';

    protected bool $reorderable = false;

    protected bool $deletable = true;

    /**
     * Set the key label.
     */
    public function keyLabel(string $label): static
    {
        $this->keyLabel = $label;

        return $this;
    }

    /**
     * Set the value label.
     */
    public function valueLabel(string $label): static
    {
        $this->valueLabel = $label;

        return $this;
    }

    /**
     * Set the add button label.
     */
    public function addButtonLabel(string $label): static
    {
        $this->addButtonLabel = $label;

        return $this;
    }

    /**
     * Enable/disable reordering.
     */
    public function reorderable(bool $condition = true): static
    {
        $this->reorderable = $condition;

        return $this;
    }

    /**
     * Enable/disable deletion.
     */
    public function deletable(bool $condition = true): static
    {
        $this->deletable = $condition;

        return $this;
    }

    protected function getVueComponent(): string
    {
        return 'KeyValue';
    }

    protected function getVueProps(): array
    {
        return array_merge([
            'keyLabel' => $this->keyLabel,
            'valueLabel' => $this->valueLabel,
            'addButtonLabel' => $this->addButtonLabel,
            'reorderable' => $this->reorderable,
            'deletable' => $this->deletable,
            'required' => $this->isRequired(),
            'rules' => $this->getValidationRules(),
            'defaultValue' => $this->getState() ?? [],
        ], $this->getIconProps());
    }

    protected function getFlutterWidget(): string
    {
        return 'LaraviltKeyValue';
    }

    protected function getFlutterWidgetProps(): array
    {
        return [
            'keyLabel' => $this->keyLabel,
            'valueLabel' => $this->valueLabel,
            'addButtonLabel' => $this->addButtonLabel,
            'reorderable' => $this->reorderable,
            'deletable' => $this->deletable,
            'required' => $this->isRequired(),
            'validators' => $this->getValidationRules(),
            'initialValue' => $this->getState() ?? [],
        ];
    }
}
