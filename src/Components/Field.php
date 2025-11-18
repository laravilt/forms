<?php

namespace Laravilt\Forms\Components;

use Laravilt\Forms\Concerns\CanBeDisabled;
use Laravilt\Forms\Concerns\CanBeHidden;
use Laravilt\Forms\Concerns\CanBeRequired;
use Laravilt\Forms\Concerns\HasDefaultValue;
use Laravilt\Forms\Concerns\HasHelperText;
use Laravilt\Forms\Concerns\HasLabel;
use Laravilt\Forms\Concerns\HasPlaceholder;
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
 */
abstract class Field extends Component
{
    use CanBeDisabled;
    use CanBeHidden;
    use CanBeRequired;
    use HasDefaultValue;
    use HasHelperText;
    use HasLabel;
    use HasPlaceholder;
    use HasValidation;

    /**
     * Whether the field is readonly.
     */
    protected bool $readonly = false;

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
     * Mark the field as readonly.
     */
    public function readonly(bool $condition = true): static
    {
        $this->readonly = $condition;

        return $this;
    }

    /**
     * Check if the field is readonly.
     */
    public function isReadonly(): bool
    {
        return $this->evaluate($this->readonly);
    }

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
        ]);
    }
}
