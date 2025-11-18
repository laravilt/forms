<?php

namespace Laravilt\Forms\Concerns;

use Closure;
use Illuminate\Validation\Rule;

trait HasValidation
{
    protected string|array|Closure|null $validationRules = null;

    protected array $validationMessages = [];

    protected array $validationAttributes = [];

    /**
     * Set validation rules.
     *
     * @param  string|array|Closure  $rules  Validation rules (string, array, or closure)
     */
    public function rules(string|array|Closure $rules): static
    {
        $this->validationRules = $rules;

        return $this;
    }

    /**
     * Add validation rules to existing ones.
     */
    public function addRules(string|array $rules): static
    {
        $currentRules = $this->validationRules ?? [];

        if (is_string($currentRules) && is_string($rules)) {
            $this->validationRules = $currentRules.'|'.$rules;
        } elseif (is_array($currentRules) && is_array($rules)) {
            $this->validationRules = array_merge($currentRules, $rules);
        } else {
            // Convert both to array and merge
            $currentRules = is_string($currentRules) ? explode('|', $currentRules) : (array) $currentRules;
            $rules = is_string($rules) ? explode('|', $rules) : (array) $rules;
            $this->validationRules = array_merge($currentRules, $rules);
        }

        return $this;
    }

    /**
     * Get validation rules.
     */
    public function getValidationRules(): string|array|null
    {
        return $this->evaluate($this->validationRules);
    }

    /**
     * Set custom validation messages.
     */
    public function validationMessages(array $messages): static
    {
        $this->validationMessages = $messages;

        return $this;
    }

    /**
     * Get validation messages.
     */
    public function getValidationMessages(): array
    {
        // Prefix messages with field name for Laravel validation
        $messages = [];
        foreach ($this->validationMessages as $rule => $message) {
            $messages["{$this->getName()}.{$rule}"] = $message;
        }

        return $messages;
    }

    /**
     * Set custom validation attributes.
     */
    public function validationAttribute(string $attribute): static
    {
        $this->validationAttributes = [$this->getName() => $attribute];

        return $this;
    }

    /**
     * Get validation attributes.
     */
    public function getValidationAttributes(): array
    {
        return $this->validationAttributes;
    }

    /**
     * Common validation rule shortcuts.
     */
    public function email(): static
    {
        return $this->addRules('email');
    }

    public function url(): static
    {
        return $this->addRules('url');
    }

    public function numeric(): static
    {
        return $this->addRules('numeric');
    }

    public function integer(): static
    {
        return $this->addRules('integer');
    }

    public function min(int|float $min): static
    {
        return $this->addRules("min:{$min}");
    }

    public function max(int|float $max): static
    {
        return $this->addRules("max:{$max}");
    }

    public function minLength(int $length): static
    {
        return $this->addRules("min:{$length}");
    }

    public function maxLength(int $length): static
    {
        return $this->addRules("max:{$length}");
    }

    public function unique(string $table, string $column = 'NULL', ?string $ignoreId = null): static
    {
        if ($ignoreId) {
            return $this->addRules(Rule::unique($table, $column)->ignore($ignoreId));
        }

        return $this->addRules("unique:{$table},{$column}");
    }

    public function exists(string $table, string $column = 'NULL'): static
    {
        return $this->addRules("exists:{$table},{$column}");
    }

    public function confirmed(): static
    {
        return $this->addRules('confirmed');
    }

    public function regex(string $pattern): static
    {
        return $this->addRules("regex:{$pattern}");
    }

    public function alpha(): static
    {
        return $this->addRules('alpha');
    }

    public function alphaDash(): static
    {
        return $this->addRules('alpha_dash');
    }

    public function alphaNum(): static
    {
        return $this->addRules('alpha_num');
    }
}
