<?php

namespace Laravilt\Forms\Components;

use Closure;

class Repeater extends Field
{
    protected string $view = 'laravilt-forms::components.fields.repeater';

    protected array|Closure $schema = [];

    protected string $addButtonLabel = 'Add';

    protected string $deleteButtonLabel = 'Delete';

    protected bool $reorderable = false;

    protected bool $collapsible = false;

    protected bool $cloneable = false;

    protected bool $deletable = true;

    protected ?int $minItems = null;

    protected ?int $maxItems = null;

    /**
     * Set the field schema.
     */
    public function schema(array|Closure $schema): static
    {
        $this->schema = $schema;

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
     * Set the delete button label.
     */
    public function deleteButtonLabel(string $label): static
    {
        $this->deleteButtonLabel = $label;

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
     * Enable/disable collapsible items.
     */
    public function collapsible(bool $condition = true): static
    {
        $this->collapsible = $condition;

        return $this;
    }

    /**
     * Enable/disable cloneable items.
     */
    public function cloneable(bool $condition = true): static
    {
        $this->cloneable = $condition;

        return $this;
    }

    /**
     * Enable/disable deletable items.
     */
    public function deletable(bool $condition = true): static
    {
        $this->deletable = $condition;

        return $this;
    }

    /**
     * Set minimum number of items.
     */
    public function minItems(int $min): static
    {
        $this->minItems = $min;

        return $this;
    }

    /**
     * Set maximum number of items.
     */
    public function maxItems(int $max): static
    {
        $this->maxItems = $max;

        return $this;
    }

    /**
     * Get the schema array.
     */
    public function getSchema(): array
    {
        if ($this->schema instanceof Closure) {
            return ($this->schema)();
        }

        return $this->schema;
    }

    protected function getVueComponent(): string
    {
        return 'Repeater';
    }

    protected function getVueProps(): array
    {
        // Serialize schema components to their Inertia props
        $serializedSchema = array_map(function ($component) {
            if (method_exists($component, 'toInertiaProps')) {
                return $component->toInertiaProps();
            }

            return $component;
        }, $this->getSchema());

        return array_merge([
            'schema' => $serializedSchema,
            'addButtonLabel' => $this->addButtonLabel,
            'deleteButtonLabel' => $this->deleteButtonLabel,
            'reorderable' => $this->reorderable,
            'collapsible' => $this->collapsible,
            'cloneable' => $this->cloneable,
            'deletable' => $this->deletable,
            'minItems' => $this->minItems,
            'maxItems' => $this->maxItems,
            'required' => $this->isRequired(),
            'rules' => $this->getValidationRules(),
            'defaultValue' => $this->getState() ?? [],
        ], $this->getIconProps());
    }

    protected function getFlutterWidget(): string
    {
        return 'LaraviltRepeater';
    }

    protected function getFlutterWidgetProps(): array
    {
        // Serialize schema components to their Flutter props
        $serializedSchema = array_map(function ($component) {
            if (method_exists($component, 'toFlutterProps')) {
                return $component->toFlutterProps();
            }

            return $component;
        }, $this->getSchema());

        return [
            'schema' => $serializedSchema,
            'addButtonLabel' => $this->addButtonLabel,
            'deleteButtonLabel' => $this->deleteButtonLabel,
            'reorderable' => $this->reorderable,
            'collapsible' => $this->collapsible,
            'cloneable' => $this->cloneable,
            'deletable' => $this->deletable,
            'minItems' => $this->minItems,
            'maxItems' => $this->maxItems,
            'required' => $this->isRequired(),
            'validators' => $this->getValidationRules(),
            'initialValue' => $this->getState() ?? [],
        ];
    }

    public function toLaraviltProps(): array
    {
        // Serialize schema components to their Laravilt props for Blade context
        $serializedSchema = array_map(function ($component) {
            if (method_exists($component, 'toLaraviltProps')) {
                return $component->toLaraviltProps();
            }

            return $component;
        }, $this->getSchema());

        return array_merge(parent::toLaraviltProps(), [
            'name' => $this->name,
            'schema' => $serializedSchema,
            'addButtonLabel' => $this->addButtonLabel,
            'deleteButtonLabel' => $this->deleteButtonLabel,
            'reorderable' => $this->reorderable,
            'collapsible' => $this->collapsible,
            'cloneable' => $this->cloneable,
            'deletable' => $this->deletable,
            'minItems' => $this->minItems,
            'maxItems' => $this->maxItems,
        ]);
    }
}
