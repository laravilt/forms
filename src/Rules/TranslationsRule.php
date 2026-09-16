<?php

namespace Laravilt\Forms\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidatorInstance;
use JsonSerializable;

/**
 * Validates a locale-keyed translations array (['en' => ..., 'ar' => ...]).
 *
 * Each locale has its own rule list. Failures are reported under the nested
 * key ("title.en"), exactly as if "title.en" => [...] had been registered on
 * the parent validator. This lets schema consumers that only read one rule
 * list per field (Schema::getValidationRules() assigns $rules[$name]) still
 * validate every locale and surface per-locale errors.
 */
class TranslationsRule implements JsonSerializable, ValidationRule, ValidatorAwareRule
{
    protected ?ValidatorInstance $validator = null;

    /**
     * @param  array<string, array<int, mixed>>  $rules  Rules per locale code, e.g. ['en' => ['required', 'string']]
     * @param  array<string, string>  $attributes  Display names per locale code, e.g. ['en' => 'Title (EN)']
     * @param  array<string, string>  $messages  Custom messages per rule ('required' => ...) or per locale rule ('en.required' => ...)
     */
    public function __construct(
        protected array $rules,
        protected array $attributes = [],
        protected array $messages = [],
    ) {}

    /**
     * @param  ValidatorInstance  $validator
     */
    public function setValidator($validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Non-array values are reported by the field's 'array' rule
        if (! is_array($value)) {
            return;
        }

        $nested = $this->makeValidator($attribute, $value);

        if ($nested->passes()) {
            return;
        }

        // Standalone usage (no parent validator): fail the attribute itself
        if ($this->validator === null) {
            $fail($nested->errors()->first());

            return;
        }

        // Copy the nested errors (title.en, title.ar, ...) onto the parent validator
        foreach ($nested->errors()->messages() as $key => $messages) {
            foreach ($messages as $message) {
                $this->validator->errors()->add($key, $message);
            }
        }
    }

    /**
     * Rules per locale code.
     *
     * @return array<string, array<int, mixed>>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Rules keyed by nested attribute: ['title.en' => [...], 'title.ar' => [...]].
     *
     * @return array<string, array<int, mixed>>
     */
    public function getNestedRules(string $attribute): array
    {
        $rules = [];

        foreach ($this->rules as $locale => $localeRules) {
            $rules["{$attribute}.{$locale}"] = $localeRules;
        }

        return $rules;
    }

    protected function makeValidator(string $attribute, array $value): ValidatorInstance
    {
        $data = [];
        data_set($data, $attribute, $value);

        $attributes = [];
        foreach ($this->attributes as $locale => $name) {
            $attributes["{$attribute}.{$locale}"] = $name;
        }

        $messages = [];
        foreach ($this->messages as $key => $message) {
            // 'en.required' targets one locale, 'required' applies to every locale
            if (str_contains($key, '.')) {
                $messages["{$attribute}.{$key}"] = $message;

                continue;
            }

            foreach (array_keys($this->rules) as $locale) {
                $messages["{$attribute}.{$locale}.{$key}"] = $message;
            }
        }

        return Validator::make($data, $this->getNestedRules($attribute), $messages, $attributes);
    }

    /**
     * Serialized as the per-locale rules so field props stay readable JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->rules;
    }
}
