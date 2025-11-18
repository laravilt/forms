<?php

namespace Laravilt\Forms\Components;

use Closure;

/**
 * Tags Input Component
 *
 * A tags input field with support for:
 * - Adding/removing tags
 * - Suggestions from predefined list
 * - Validation per tag
 * - Minimum/maximum number of tags
 * - Custom separators
 */
class TagsInput extends Field
{
    protected string $view = 'laravilt-forms::components.fields.tags-input';

    /**
     * Predefined tag suggestions.
     */
    protected array|Closure $suggestions = [];

    /**
     * Minimum number of tags.
     */
    protected int|Closure|null $minTags = null;

    /**
     * Maximum number of tags.
     */
    protected int|Closure|null $maxTags = null;

    /**
     * Tag separators (e.g., comma, space).
     */
    protected array|Closure $separators = [',', 'Enter'];

    /**
     * Whether tags are case sensitive.
     */
    protected bool|Closure $caseSensitive = false;

    /**
     * Whether to allow duplicate tags.
     */
    protected bool|Closure $allowDuplicates = false;

    /**
     * Tag validation pattern.
     */
    protected string|Closure|null $tagPattern = null;

    /**
     * Set tag suggestions.
     */
    public function suggestions(array|Closure $suggestions): static
    {
        $this->suggestions = $suggestions;

        return $this;
    }

    /**
     * Set minimum number of tags.
     */
    public function minTags(int|Closure $min): static
    {
        $this->minTags = $min;

        return $this;
    }

    /**
     * Set maximum number of tags.
     */
    public function maxTags(int|Closure $max): static
    {
        $this->maxTags = $max;

        return $this;
    }

    /**
     * Set tag separators.
     */
    public function separators(array|Closure $separators): static
    {
        $this->separators = $separators;

        return $this;
    }

    /**
     * Make tags case sensitive.
     */
    public function caseSensitive(bool|Closure $condition = true): static
    {
        $this->caseSensitive = $condition;

        return $this;
    }

    /**
     * Allow duplicate tags.
     */
    public function allowDuplicates(bool|Closure $condition = true): static
    {
        $this->allowDuplicates = $condition;

        return $this;
    }

    /**
     * Set tag validation pattern (regex).
     */
    public function tagPattern(string|Closure $pattern): static
    {
        $this->tagPattern = $pattern;

        return $this;
    }

    /**
     * Get tag suggestions.
     */
    public function getSuggestions(): array
    {
        return $this->evaluate($this->suggestions);
    }

    /**
     * Get tag separators.
     */
    public function getSeparators(): array
    {
        return $this->evaluate($this->separators);
    }

    /**
     * Check if tags are case sensitive.
     */
    public function isCaseSensitive(): bool
    {
        return $this->evaluate($this->caseSensitive);
    }

    /**
     * Check if duplicate tags are allowed.
     */
    public function allowsDuplicates(): bool
    {
        return $this->evaluate($this->allowDuplicates);
    }

    /**
     * Get tag validation pattern.
     */
    public function getTagPattern(): ?string
    {
        return $this->evaluate($this->tagPattern);
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'suggestions' => $this->getSuggestions(),
            'minTags' => $this->evaluate($this->minTags),
            'maxTags' => $this->evaluate($this->maxTags),
            'separators' => $this->getSeparators(),
            'caseSensitive' => $this->isCaseSensitive(),
            'allowDuplicates' => $this->allowsDuplicates(),
            'tagPattern' => $this->getTagPattern(),
        ]);
    }
}
