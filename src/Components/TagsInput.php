<?php

namespace Laravilt\Forms\Components;

use Closure;

class TagsInput extends Field
{
    protected string $view = 'laravilt-forms::components.fields.tags-input';

    protected string $separator = ',';

    protected array|Closure $suggestions = [];

    protected ?int $maxTags = null;

    /**
     * Set the tag separator.
     */
    public function separator(string $separator): static
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Set tag suggestions.
     */
    public function suggestions(array|Closure $suggestions): static
    {
        $this->suggestions = $suggestions;

        return $this;
    }

    /**
     * Set maximum number of tags.
     */
    public function maxTags(?int $maxTags): static
    {
        $this->maxTags = $maxTags;

        return $this;
    }

    /**
     * Get the suggestions array.
     */
    protected function getSuggestions(): array
    {
        if ($this->suggestions instanceof Closure) {
            return ($this->suggestions)();
        }

        return $this->suggestions;
    }

    protected function getVueComponent(): string
    {
        return 'TagsInput';
    }

    protected function getVueProps(): array
    {
        return array_merge([
            'placeholder' => $this->placeholder,
            'disabled' => $this->disabled,
            'separator' => $this->separator,
            'suggestions' => $this->getSuggestions(),
            'required' => $this->isRequired(),
            'rules' => $this->getValidationRules(),
            'defaultValue' => $this->getState() ?? [],
        ], $this->getIconProps());
    }

    protected function getFlutterWidget(): string
    {
        return 'LaraviltTagsInput';
    }

    protected function getFlutterWidgetProps(): array
    {
        return [
            'placeholder' => $this->placeholder,
            'enabled' => ! $this->disabled,
            'separator' => $this->separator,
            'suggestions' => $this->getSuggestions(),
            'required' => $this->isRequired(),
            'validators' => $this->getValidationRules(),
            'initialValue' => $this->getState() ?? [],
        ];
    }

    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'name' => $this->name,
            'separator' => $this->separator,
            'suggestions' => $this->getSuggestions(),
        ]);
    }
}
