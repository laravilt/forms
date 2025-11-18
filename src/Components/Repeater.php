<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Repeater Component
 *
 * A repeater field for creating dynamic lists of fields with support for:
 * - Nested field schemas
 * - Add/remove items
 * - Reordering items
 * - Collapsible items
 * - Item labels
 */
class Repeater extends Field
{
    protected string $view = 'laravilt-forms::components.fields.repeater';

    /**
     * The repeater's field schema.
     */
    protected array $schema = [];

    /**
     * Minimum number of items.
     */
    protected int|Closure|null $minItems = null;

    /**
     * Maximum number of items.
     */
    protected int|Closure|null $maxItems = null;

    /**
     * Default number of items.
     */
    protected int|Closure $defaultItems = 0;

    /**
     * Whether items can be reordered.
     */
    protected bool|Closure $orderable = true;

    /**
     * Whether items are collapsible.
     */
    protected bool|Closure $collapsible = false;

    /**
     * Whether items are collapsed by default.
     */
    protected bool|Closure $collapsed = false;

    /**
     * The add button label.
     */
    protected string|Closure|null $addButtonLabel = null;

    /**
     * Closure to generate item labels.
     */
    protected ?Closure $itemLabel = null;

    /**
     * Whether to show item numbers.
     */
    protected bool|Closure $showItemNumbers = true;

    /**
     * Grid columns for the repeater items.
     */
    protected int|Closure $gridColumns = 1;

    /**
     * Set the repeater schema.
     */
    public function schema(array $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * Get the repeater schema.
     */
    public function getSchema(): array
    {
        return $this->schema;
    }

    /**
     * Set minimum items.
     */
    public function minItems(int|Closure $min): static
    {
        $this->minItems = $min;

        return $this;
    }

    /**
     * Set maximum items.
     */
    public function maxItems(int|Closure $max): static
    {
        $this->maxItems = $max;

        return $this;
    }

    /**
     * Set default number of items.
     */
    public function defaultItems(int|Closure $count): static
    {
        $this->defaultItems = $count;

        return $this;
    }

    /**
     * Enable/disable ordering.
     */
    public function orderable(bool|Closure $condition = true): static
    {
        $this->orderable = $condition;

        return $this;
    }

    /**
     * Make items collapsible.
     */
    public function collapsible(bool|Closure $condition = true): static
    {
        $this->collapsible = $condition;

        return $this;
    }

    /**
     * Set items as collapsed by default.
     */
    public function collapsed(bool|Closure $condition = true): static
    {
        $this->collapsed = $condition;
        $this->collapsible = true;

        return $this;
    }

    /**
     * Set the add button label.
     */
    public function addButtonLabel(string|Closure $label): static
    {
        $this->addButtonLabel = $label;

        return $this;
    }

    /**
     * Set the item label generator.
     */
    public function itemLabel(Closure $callback): static
    {
        $this->itemLabel = $callback;

        return $this;
    }

    /**
     * Show/hide item numbers.
     */
    public function showItemNumbers(bool|Closure $condition = true): static
    {
        $this->showItemNumbers = $condition;

        return $this;
    }

    /**
     * Set grid columns.
     */
    public function gridColumns(int|Closure $columns): static
    {
        $this->gridColumns = $columns;

        return $this;
    }

    /**
     * Make it a simple repeater (single field).
     */
    public function simple(): static
    {
        return $this->showItemNumbers(false)
            ->collapsible(false)
            ->gridColumns(1);
    }

    /**
     * Check if orderable.
     */
    public function isOrderable(): bool
    {
        return $this->evaluate($this->orderable);
    }

    /**
     * Check if collapsible.
     */
    public function isCollapsible(): bool
    {
        return $this->evaluate($this->collapsible);
    }

    /**
     * Check if collapsed by default.
     */
    public function isCollapsed(): bool
    {
        return $this->evaluate($this->collapsed);
    }

    /**
     * Get add button label.
     */
    public function getAddButtonLabel(): string
    {
        return $this->evaluate($this->addButtonLabel) ?? 'Add item';
    }

    /**
     * Get item label.
     */
    public function getItemLabel(int $index, array $state): ?string
    {
        if ($this->itemLabel === null) {
            return null;
        }

        return call_user_func($this->itemLabel, $state, $index);
    }

    /**
     * Check if item numbers should be shown.
     */
    public function shouldShowItemNumbers(): bool
    {
        return $this->evaluate($this->showItemNumbers);
    }

    /**
     * Get grid columns.
     */
    public function getGridColumns(): int
    {
        return $this->evaluate($this->gridColumns);
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'schema' => collect($this->schema)
                ->map(fn ($component) => $component->toLaraviltProps())
                ->all(),
            'minItems' => $this->evaluate($this->minItems),
            'maxItems' => $this->evaluate($this->maxItems),
            'defaultItems' => $this->evaluate($this->defaultItems),
            'orderable' => $this->isOrderable(),
            'collapsible' => $this->isCollapsible(),
            'collapsed' => $this->isCollapsed(),
            'addButtonLabel' => $this->getAddButtonLabel(),
            'showItemNumbers' => $this->shouldShowItemNumbers(),
            'gridColumns' => $this->getGridColumns(),
        ]);
    }
}
