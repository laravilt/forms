<?php

namespace Laravilt\Forms\Components;

use Closure;
use Laravilt\Forms\Concerns\CanBeReactive;
use Laravilt\Forms\Concerns\HasDefaultValue;
use Laravilt\Forms\Concerns\HasValidation;
use Laravilt\Support\Component;

/**
 * Base Field Component
 *
 * Foundation for all form field components. Provides:
 * - Input state management
 * - Validation rules
 * - Required/disabled states
 * - Placeholder text
 * - Default values
 * - Error handling
 * - Labels and helper text
 * - Visibility control
 * - Reactive field support (live/lazy)
 */
abstract class Field extends Component
{
    use CanBeReactive;
    use HasDefaultValue;
    use HasValidation;

    /**
     * Whether the field should autofocus.
     */
    protected bool $autofocus = false;

    /**
     * The field's autocomplete attribute.
     */
    protected ?string $autocomplete = null;

    /**
     * The field's tabindex.
     */
    protected ?int $tabindex = null;

    /**
     * Custom attributes for the field.
     */
    protected array $extraAttributes = [];

    /**
     * Whether the field is reactive.
     */
    protected bool|Closure $reactive = false;

    /**
     * Callback to run after state is updated.
     */
    protected ?Closure $afterStateUpdated = null;

    /**
     * Hint actions (displayed next to the label).
     */
    protected array $hintActions = [];

    /**
     * Prefix actions (displayed before the input).
     */
    protected array $prefixActions = [];

    /**
     * Suffix actions (displayed after the input).
     */
    protected array $suffixActions = [];

    /**
     * Set the field to autofocus.
     */
    public function autofocus(bool $condition = true): static
    {
        $this->autofocus = $condition;

        return $this;
    }

    /**
     * Check if the field should autofocus.
     */
    public function shouldAutofocus(): bool
    {
        return $this->evaluate($this->autofocus);
    }

    /**
     * Set the autocomplete attribute.
     */
    public function autocomplete(?string $autocomplete): static
    {
        $this->autocomplete = $autocomplete;

        return $this;
    }

    /**
     * Get the autocomplete attribute.
     */
    public function getAutocomplete(): ?string
    {
        return $this->evaluate($this->autocomplete);
    }

    /**
     * Set the tabindex.
     */
    public function tabindex(?int $tabindex): static
    {
        $this->tabindex = $tabindex;

        return $this;
    }

    /**
     * Get the tabindex.
     */
    public function getTabindex(): ?int
    {
        return $this->evaluate($this->tabindex);
    }

    /**
     * Set extra HTML attributes.
     */
    public function extraAttributes(array $attributes, bool $merge = false): static
    {
        if ($merge) {
            $this->extraAttributes = array_merge($this->extraAttributes, $attributes);
        } else {
            $this->extraAttributes = $attributes;
        }

        return $this;
    }

    /**
     * Get extra HTML attributes.
     */
    public function getExtraAttributes(): array
    {
        return $this->extraAttributes;
    }

    /**
     * Get the field's current value.
     */
    public function getValue(): mixed
    {
        return $this->getState() ?? $this->getDefaultValue();
    }

    /**
     * Make the field reactive.
     */
    public function reactive(bool|Closure $condition = true): static
    {
        $this->reactive = $condition;

        return $this;
    }

    /**
     * Check if field is reactive.
     */
    public function isReactive(): bool
    {
        return $this->evaluate($this->reactive);
    }

    /**
     * Set callback for after state updated.
     */
    public function afterStateUpdated(?Closure $callback): static
    {
        $this->afterStateUpdated = $callback;

        return $this;
    }

    /**
     * Get the after state updated callback.
     */
    public function getAfterStateUpdated(): ?Closure
    {
        return $this->afterStateUpdated;
    }

    /**
     * Get the field type (based on class name).
     */
    public function getType(): string
    {
        $className = class_basename($this);

        return \Illuminate\Support\Str::kebab($className);
    }

    /**
     * Add a hint action.
     */
    public function hintAction(\Laravilt\Actions\Action $action): static
    {
        $this->hintActions[] = $action;

        return $this;
    }

    /**
     * Add multiple hint actions.
     */
    public function hintActions(array $actions): static
    {
        $this->hintActions = array_merge($this->hintActions, $actions);

        return $this;
    }

    /**
     * Get hint actions.
     */
    public function getHintActions(): array
    {
        return $this->hintActions;
    }

    /**
     * Add a prefix action.
     */
    public function prefixAction(\Laravilt\Actions\Action $action): static
    {
        $this->prefixActions[] = $action;

        return $this;
    }

    /**
     * Add multiple prefix actions.
     */
    public function prefixActions(array $actions): static
    {
        $this->prefixActions = array_merge($this->prefixActions, $actions);

        return $this;
    }

    /**
     * Get prefix actions.
     */
    public function getPrefixActions(): array
    {
        return $this->prefixActions;
    }

    /**
     * Add a suffix action.
     */
    public function suffixAction(\Laravilt\Actions\Action $action): static
    {
        $this->suffixActions[] = $action;

        return $this;
    }

    /**
     * Add multiple suffix actions.
     */
    public function suffixActions(array $actions): static
    {
        $this->suffixActions = array_merge($this->suffixActions, $actions);

        return $this;
    }

    /**
     * Get suffix actions.
     */
    public function getSuffixActions(): array
    {
        return $this->suffixActions;
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'label' => $this->getLabel(),
            'helperText' => $this->getHelperText(),
            'placeholder' => $this->getPlaceholder(),
            'required' => $this->isRequired(),
            'disabled' => $this->isDisabled(),
            'hidden' => $this->isHidden(),
            'readonly' => $this->isReadonly(),
            'autofocus' => $this->shouldAutofocus(),
            'autocomplete' => $this->getAutocomplete(),
            'tabindex' => $this->getTabindex(),
            'reactive' => $this->isReactive(),
            'columnSpan' => $this->getColumnSpan(),
            'validation' => $this->getValidationRules(),
            'validationMessages' => $this->getValidationMessages(),
            'defaultValue' => $this->getDefaultValue(),
            'value' => $this->getValue(),
            'extraAttributes' => $this->getExtraAttributes(),
            'hintActions' => collect($this->getHintActions())->map->toArray()->filter()->values()->toArray(),
            'prefixActions' => collect($this->getPrefixActions())->map->toArray()->filter()->values()->toArray(),
            'suffixActions' => collect($this->getSuffixActions())->map->toArray()->filter()->values()->toArray(),
        ]);
    }
}
