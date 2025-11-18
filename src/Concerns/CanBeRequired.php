<?php

namespace Laravilt\Forms\Concerns;

use Closure;

trait CanBeRequired
{
    protected bool|Closure $required = false;

    /**
     * Mark the field as required.
     */
    public function required(bool|Closure $condition = true): static
    {
        $this->required = $condition;

        // Auto-add 'required' validation rule
        if ($condition === true && method_exists($this, 'addRules')) {
            $this->addRules('required');
        }

        return $this;
    }

    /**
     * Check if the field is required.
     */
    public function isRequired(): bool
    {
        return $this->evaluate($this->required);
    }

    /**
     * Check if validation rules contain a specific rule.
     */
    protected function hasValidationRule(string $rule): bool
    {
        if (! method_exists($this, 'getValidationRules')) {
            return false;
        }

        $rules = $this->getValidationRules();

        if (is_string($rules)) {
            return str_contains($rules, $rule);
        }

        if (is_array($rules)) {
            foreach ($rules as $r) {
                if (is_string($r) && str_contains($r, $rule)) {
                    return true;
                }
                if (is_object($r) && method_exists($r, '__toString') && str_contains((string) $r, $rule)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Add a validation rule.
     */
    protected function addValidationRule(string $rule): void
    {
        if (! method_exists($this, 'rules')) {
            return;
        }

        $currentRules = $this->validationRules ?? [];

        if (is_string($currentRules)) {
            $this->validationRules = $currentRules.'|'.$rule;
        } elseif (is_array($currentRules)) {
            $this->validationRules[] = $rule;
        } else {
            $this->validationRules = [$rule];
        }
    }
}
