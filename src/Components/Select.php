<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Select Field
 *
 * A dropdown select field with support for:
 * - Single and multiple selection
 * - Searchable options
 * - Custom option rendering
 * - Grouped options
 * - Loading state
 * - Native or custom dropdown
 */
class Select extends Field
{
    protected string $view = 'laravilt-forms::components.fields.select';

    protected array|Closure $options = [];

    protected bool $searchable = false;

    protected bool $multiple = false;

    protected bool $native = false;

    protected ?string $searchPrompt = null;

    protected ?string $noSearchResultsMessage = null;

    protected ?string $emptyStateMessage = null;

    protected bool $allowHtml = false;

    protected ?int $maxItems = null;

    protected ?Closure $getSearchResultsUsing = null;

    protected ?Closure $getOptionLabelUsing = null;

    protected ?Closure $getOptionValueUsing = null;

    /**
     * Set the select options.
     *
     * @param  array|Closure  $options  Array of options ['value' => 'label'] or closure
     */
    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get the select options.
     */
    public function getOptions(): array
    {
        return $this->evaluate($this->options);
    }

    /**
     * Enable searchable select.
     */
    public function searchable(bool $condition = true): static
    {
        $this->searchable = $condition;

        return $this;
    }

    /**
     * Check if select is searchable.
     */
    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    /**
     * Enable multiple selection.
     */
    public function multiple(bool $condition = true): static
    {
        $this->multiple = $condition;

        return $this;
    }

    /**
     * Check if multiple selection is enabled.
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * Use native select dropdown.
     */
    public function native(bool $condition = true): static
    {
        $this->native = $condition;

        return $this;
    }

    /**
     * Check if native select should be used.
     */
    public function isNative(): bool
    {
        return $this->native;
    }

    /**
     * Add "in" validation rule for select options.
     */
    public function in(array $values): static
    {
        $this->addRules('in:'.implode(',', $values));

        return $this;
    }

    /**
     * Set search prompt text.
     */
    public function searchPrompt(string $prompt): static
    {
        $this->searchPrompt = $prompt;

        return $this;
    }

    /**
     * Get search prompt.
     */
    public function getSearchPrompt(): ?string
    {
        return $this->evaluate($this->searchPrompt);
    }

    /**
     * Set no search results message.
     */
    public function noSearchResultsMessage(string $message): static
    {
        $this->noSearchResultsMessage = $message;

        return $this;
    }

    /**
     * Get no search results message.
     */
    public function getNoSearchResultsMessage(): ?string
    {
        return $this->evaluate($this->noSearchResultsMessage);
    }

    /**
     * Set empty state message.
     */
    public function emptyStateMessage(string $message): static
    {
        $this->emptyStateMessage = $message;

        return $this;
    }

    /**
     * Get empty state message.
     */
    public function getEmptyStateMessage(): ?string
    {
        return $this->evaluate($this->emptyStateMessage);
    }

    /**
     * Allow HTML in option labels.
     */
    public function allowHtml(bool $condition = true): static
    {
        $this->allowHtml = $condition;

        return $this;
    }

    /**
     * Check if HTML is allowed in labels.
     */
    public function isHtmlAllowed(): bool
    {
        return $this->allowHtml;
    }

    /**
     * Set maximum number of items for multiple select.
     */
    public function maxItems(int $max): static
    {
        $this->maxItems = $max;

        return $this;
    }

    /**
     * Get maximum items.
     */
    public function getMaxItems(): ?int
    {
        return $this->maxItems;
    }

    /**
     * Set custom search results callback.
     */
    public function getSearchResultsUsing(Closure $callback): static
    {
        $this->getSearchResultsUsing = $callback;

        return $this;
    }

    /**
     * Get search results using custom callback.
     */
    public function getSearchResults(string $query): array
    {
        if ($this->getSearchResultsUsing) {
            return $this->evaluate($this->getSearchResultsUsing, ['query' => $query]);
        }

        // Default: filter options by query
        $options = $this->getOptions();

        return array_filter($options, function ($label) use ($query) {
            return str_contains(strtolower($label), strtolower($query));
        });
    }

    /**
     * Set custom option label callback.
     */
    public function getOptionLabelUsing(Closure $callback): static
    {
        $this->getOptionLabelUsing = $callback;

        return $this;
    }

    /**
     * Get option label.
     */
    public function getOptionLabel(mixed $value): string
    {
        if ($this->getOptionLabelUsing) {
            return $this->evaluate($this->getOptionLabelUsing, ['value' => $value]);
        }

        $options = $this->getOptions();

        return $options[$value] ?? (string) $value;
    }

    /**
     * Set custom option value callback.
     */
    public function getOptionValueUsing(Closure $callback): static
    {
        $this->getOptionValueUsing = $callback;

        return $this;
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'options' => $this->getOptions(),
            'searchable' => $this->isSearchable(),
            'multiple' => $this->isMultiple(),
            'native' => $this->isNative(),
            'searchPrompt' => $this->getSearchPrompt(),
            'noSearchResultsMessage' => $this->getNoSearchResultsMessage(),
            'emptyStateMessage' => $this->getEmptyStateMessage(),
            'allowHtml' => $this->isHtmlAllowed(),
            'maxItems' => $this->getMaxItems(),
        ]);
    }
}
