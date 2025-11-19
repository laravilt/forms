<?php

namespace Laravilt\Forms\Components;

use Closure;

class Builder extends Field
{
    protected string $view = 'laravilt-forms::components.fields.builder';

    protected array|Closure $blocks = [];

    protected string|Closure $addActionLabel = 'Add Block';

    protected string|Closure $addActionAlignment = 'center';

    protected bool|Closure $addable = true;

    protected bool|Closure $deletable = true;

    protected bool|Closure $reorderable = true;

    protected bool|Closure $reorderableWithButtons = false;

    protected bool|Closure $reorderableWithDragAndDrop = true;

    protected bool|Closure $collapsible = false;

    protected bool|Closure $collapsed = false;

    protected bool|Closure $cloneable = false;

    protected bool|Closure $blockNumbers = true;

    protected bool|Closure $blockIcons = false;

    protected bool|Closure $blockPreviews = false;

    protected int|Closure|null $minItems = null;

    protected int|Closure|null $maxItems = null;

    protected int|Closure $blockPickerColumns = 1;

    protected string|Closure|null $blockPickerWidth = null;

    /**
     * Set the available blocks.
     */
    public function blocks(array|Closure $blocks): static
    {
        $this->blocks = $blocks;

        return $this;
    }

    /**
     * Set the add action label.
     */
    public function addActionLabel(string|Closure $label): static
    {
        $this->addActionLabel = $label;

        return $this;
    }

    /**
     * Set the add action alignment.
     */
    public function addActionAlignment(string|Closure $alignment): static
    {
        $this->addActionAlignment = $alignment;

        return $this;
    }

    /**
     * Enable/disable adding items.
     */
    public function addable(bool|Closure $condition = true): static
    {
        $this->addable = $condition;

        return $this;
    }

    /**
     * Enable/disable deleting items.
     */
    public function deletable(bool|Closure $condition = true): static
    {
        $this->deletable = $condition;

        return $this;
    }

    /**
     * Enable/disable reordering.
     */
    public function reorderable(bool|Closure $condition = true): static
    {
        $this->reorderable = $condition;

        return $this;
    }

    /**
     * Enable/disable reordering with buttons.
     */
    public function reorderableWithButtons(bool|Closure $condition = true): static
    {
        $this->reorderableWithButtons = $condition;

        return $this;
    }

    /**
     * Enable/disable reordering with drag and drop.
     */
    public function reorderableWithDragAndDrop(bool|Closure $condition = true): static
    {
        $this->reorderableWithDragAndDrop = $condition;

        return $this;
    }

    /**
     * Enable/disable collapsible blocks.
     */
    public function collapsible(bool|Closure $condition = true): static
    {
        $this->collapsible = $condition;

        return $this;
    }

    /**
     * Set blocks to be collapsed by default.
     */
    public function collapsed(bool|Closure $condition = true): static
    {
        $this->collapsed = $condition;

        return $this;
    }

    /**
     * Enable/disable cloning items.
     */
    public function cloneable(bool|Closure $condition = true): static
    {
        $this->cloneable = $condition;

        return $this;
    }

    /**
     * Enable/disable block numbers.
     */
    public function blockNumbers(bool|Closure $condition = true): static
    {
        $this->blockNumbers = $condition;

        return $this;
    }

    /**
     * Enable/disable block icons in headers.
     */
    public function blockIcons(bool|Closure $condition = true): static
    {
        $this->blockIcons = $condition;

        return $this;
    }

    /**
     * Enable/disable block previews.
     */
    public function blockPreviews(bool|Closure $condition = true): static
    {
        $this->blockPreviews = $condition;

        return $this;
    }

    /**
     * Set minimum number of items.
     */
    public function minItems(int|Closure $min): static
    {
        $this->minItems = $min;

        return $this;
    }

    /**
     * Set maximum number of items.
     */
    public function maxItems(int|Closure $max): static
    {
        $this->maxItems = $max;

        return $this;
    }

    /**
     * Set block picker columns.
     */
    public function blockPickerColumns(int|array|Closure $columns): static
    {
        $this->blockPickerColumns = $columns;

        return $this;
    }

    /**
     * Set block picker width.
     */
    public function blockPickerWidth(string|Closure $width): static
    {
        $this->blockPickerWidth = $width;

        return $this;
    }

    /**
     * Resolve a property that may be a Closure.
     */
    protected function evaluate(mixed $value, array $parameters = []): mixed
    {
        return $value instanceof Closure ? $value(...array_values($parameters)) : $value;
    }

    /**
     * Get the blocks array.
     */
    protected function getBlocks(): array
    {
        $blocks = $this->evaluate($this->blocks);

        return collect($blocks)->map(function ($block) {
            if (is_object($block) && method_exists($block, 'toArray')) {
                return $block->toArray();
            }

            return $block;
        })->toArray();
    }

    protected function getVueComponent(): string
    {
        return 'Builder';
    }

    protected function getVueProps(): array
    {
        return array_merge([
            'blocks' => $this->getBlocks(),
            'addActionLabel' => $this->evaluate($this->addActionLabel),
            'addActionAlignment' => $this->evaluate($this->addActionAlignment),
            'addable' => $this->evaluate($this->addable),
            'deletable' => $this->evaluate($this->deletable),
            'reorderable' => $this->evaluate($this->reorderable),
            'reorderableWithButtons' => $this->evaluate($this->reorderableWithButtons),
            'reorderableWithDragAndDrop' => $this->evaluate($this->reorderableWithDragAndDrop),
            'collapsible' => $this->evaluate($this->collapsible),
            'collapsed' => $this->evaluate($this->collapsed),
            'cloneable' => $this->evaluate($this->cloneable),
            'blockNumbers' => $this->evaluate($this->blockNumbers),
            'blockIcons' => $this->evaluate($this->blockIcons),
            'blockPreviews' => $this->evaluate($this->blockPreviews),
            'minItems' => $this->evaluate($this->minItems),
            'maxItems' => $this->evaluate($this->maxItems),
            'blockPickerColumns' => $this->evaluate($this->blockPickerColumns),
            'blockPickerWidth' => $this->evaluate($this->blockPickerWidth),
            'required' => $this->isRequired(),
            'rules' => $this->getValidationRules(),
            'defaultValue' => $this->getState() ?? [],
        ], $this->getIconProps());
    }

    protected function getFlutterWidget(): string
    {
        return 'LaraviltBuilder';
    }

    protected function getFlutterWidgetProps(): array
    {
        return [
            'blocks' => $this->getBlocks(),
            'addActionLabel' => $this->evaluate($this->addActionLabel),
            'addable' => $this->evaluate($this->addable),
            'deletable' => $this->evaluate($this->deletable),
            'reorderable' => $this->evaluate($this->reorderable),
            'collapsible' => $this->evaluate($this->collapsible),
            'collapsed' => $this->evaluate($this->collapsed),
            'cloneable' => $this->evaluate($this->cloneable),
            'minItems' => $this->evaluate($this->minItems),
            'maxItems' => $this->evaluate($this->maxItems),
            'required' => $this->isRequired(),
            'validators' => $this->getValidationRules(),
            'initialValue' => $this->getState() ?? [],
        ];
    }

    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'name' => $this->name,
            'blocks' => $this->getBlocks(),
            'addActionLabel' => $this->evaluate($this->addActionLabel),
            'addActionAlignment' => $this->evaluate($this->addActionAlignment),
            'addable' => $this->evaluate($this->addable),
            'deletable' => $this->evaluate($this->deletable),
            'reorderable' => $this->evaluate($this->reorderable),
            'reorderableWithButtons' => $this->evaluate($this->reorderableWithButtons),
            'reorderableWithDragAndDrop' => $this->evaluate($this->reorderableWithDragAndDrop),
            'collapsible' => $this->evaluate($this->collapsible),
            'collapsed' => $this->evaluate($this->collapsed),
            'cloneable' => $this->evaluate($this->cloneable),
            'blockNumbers' => $this->evaluate($this->blockNumbers),
            'blockIcons' => $this->evaluate($this->blockIcons),
            'blockPreviews' => $this->evaluate($this->blockPreviews),
            'minItems' => $this->evaluate($this->minItems),
            'maxItems' => $this->evaluate($this->maxItems),
            'blockPickerColumns' => $this->evaluate($this->blockPickerColumns),
            'blockPickerWidth' => $this->evaluate($this->blockPickerWidth),
        ]);
    }
}
