<?php

namespace Laravilt\Forms\Components\Builder;

use Closure;

class Block
{
    protected string $name;

    protected string|Closure|null $label = null;

    protected string|Closure|null $icon = null;

    protected array|Closure $schema = [];

    protected int|Closure $columns = 1;

    protected bool|Closure $isCollapsible = false;

    /**
     * Create a new block instance.
     */
    public static function make(string $name): static
    {
        $block = new static();
        $block->name = $name;

        return $block;
    }

    /**
     * Set the block label.
     */
    public function label(string|Closure $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the block icon.
     */
    public function icon(string|Closure $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set the block schema.
     */
    public function schema(array|Closure $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * Set the number of columns.
     */
    public function columns(int|Closure $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Make the block collapsible.
     */
    public function collapsible(bool|Closure $condition = true): static
    {
        $this->isCollapsible = $condition;

        return $this;
    }

    /**
     * Get the block name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the block label.
     */
    public function getLabel(): string
    {
        $label = $this->evaluate($this->label);

        return $label ?? str($this->name)->title()->value();
    }

    /**
     * Get the block icon.
     */
    public function getIcon(): ?string
    {
        return $this->evaluate($this->icon);
    }

    /**
     * Get the block schema.
     */
    public function getSchema(): array
    {
        $schema = $this->evaluate($this->schema);

        return is_array($schema) ? $schema : [];
    }

    /**
     * Get the number of columns.
     */
    public function getColumns(): int
    {
        return $this->evaluate($this->columns);
    }

    /**
     * Check if the block is collapsible.
     */
    public function isCollapsible(): bool
    {
        return $this->evaluate($this->isCollapsible);
    }

    /**
     * Convert the block to an array.
     */
    public function toArray(): array
    {
        // Serialize schema components
        $serializedSchema = array_map(function ($component) {
            if (method_exists($component, 'toLaraviltProps')) {
                return $component->toLaraviltProps();
            }

            if (method_exists($component, 'toArray')) {
                return $component->toArray();
            }

            return $component;
        }, $this->getSchema());

        return [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'icon' => $this->getIcon(),
            'schema' => $serializedSchema,
            'columns' => $this->getColumns(),
            'collapsible' => $this->isCollapsible(),
        ];
    }

    /**
     * Resolve a property that may be a Closure.
     */
    protected function evaluate(mixed $value, array $parameters = []): mixed
    {
        return $value instanceof Closure ? $value(...array_values($parameters)) : $value;
    }
}
