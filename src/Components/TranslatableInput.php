<?php

namespace Laravilt\Forms\Components;

use Closure;
use Laravilt\Forms\Rules\TranslationsRule;
use Laravilt\Forms\Support\Locales;

/**
 * Translatable Input Field
 *
 * A multi-language text field whose value is an array keyed by locale code,
 * e.g. ['en' => 'Title', 'ar' => 'العنوان']. Supports:
 * - Single-line or multiline (textarea) editing
 * - Explicit, config-driven or app-locale based locale lists
 * - Per-locale validation (errors reported as name.en, name.ar, ...)
 * - Hydration from JSON strings, arrays or null
 */
class TranslatableInput extends Field
{
    protected string $view = 'laravilt-forms::components.fields.translatable-input';

    protected bool|Closure $multiline = false;

    protected ?int $rows = 3;

    protected array|Closure|null $locales = null;

    protected string|Closure|null $activeLocale = null;

    protected ?array $requiredLocales = null;

    protected ?int $maxLength = null;

    /**
     * Render a textarea per locale instead of a single-line input.
     */
    public function multiline(bool|Closure $condition = true): static
    {
        $this->multiline = $condition;

        return $this;
    }

    /**
     * Check if the field is multiline.
     */
    public function isMultiline(): bool
    {
        return (bool) $this->evaluate($this->multiline);
    }

    /**
     * Set the number of rows (multiline only).
     */
    public function rows(int $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * Get the number of rows.
     */
    public function getRows(): ?int
    {
        return $this->rows;
    }

    /**
     * Restrict the editable locales: plain codes or code => metadata, like the config key.
     */
    public function locales(array|Closure $locales): static
    {
        $this->locales = $locales;

        return $this;
    }

    /**
     * Get the allowed locales.
     *
     * Resolution order: explicit locales(), config('laravilt-forms.locales'),
     * config('app.available_locales'), app locale.
     */
    public function getLocales(): array
    {
        $codes = Locales::codes($this->evaluate($this->locales));

        return $codes === [] ? Locales::default() : $codes;
    }

    /**
     * Set the locale edited by the main (collapsed) input.
     */
    public function activeLocale(string|Closure $locale): static
    {
        $this->activeLocale = $locale;

        return $this;
    }

    /**
     * Get the active locale: the requested one if allowed, else the app locale
     * if allowed, else the first allowed locale.
     */
    public function getActiveLocale(): string
    {
        $locales = $this->getLocales();
        $requested = $this->evaluate($this->activeLocale);

        if (is_string($requested) && in_array($requested, $locales, true)) {
            return $requested;
        }

        $appLocale = app()->getLocale();

        if (in_array($appLocale, $locales, true)) {
            return $appLocale;
        }

        return $locales[0];
    }

    /**
     * Set the locales that must be filled in. Defaults to every allowed locale
     * when the field is required().
     */
    public function requiredLocales(array $locales): static
    {
        $this->requiredLocales = array_values(array_map('strval', $locales));

        return $this;
    }

    /**
     * Get the locales that must be filled in.
     */
    public function getRequiredLocales(): array
    {
        $locales = $this->getLocales();

        if ($this->requiredLocales !== null) {
            return array_values(array_intersect($this->requiredLocales, $locales));
        }

        return $this->isRequired() ? $locales : [];
    }

    /**
     * Set maximum length per locale.
     *
     * Applied per locale key, so the base 'max' rule is intentionally not added
     * (on an array value it would validate the item count instead).
     */
    public function maxLength(int $length): static
    {
        $this->maxLength = $length;

        return $this;
    }

    /**
     * Get maximum length.
     */
    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    /**
     * Validation rules for the field: presence, array, and a TranslationsRule
     * that validates every locale and reports errors as name.en, name.ar, ...
     *
     * Schema::getValidationRules() assigns one rule list per field name, so the
     * per-locale rules travel inside the rule object instead of as extra keys.
     * Use getLocaleValidationRules() when validating by hand with flat keys.
     */
    public function getValidationRules(): array
    {
        return [
            $this->getRequiredLocales() === [] ? 'nullable' : 'required',
            'array',
            new TranslationsRule(
                $this->getLocaleRules(),
                $this->getLocaleAttributes(),
                $this->validationMessages,
            ),
        ];
    }

    /**
     * Rules per locale code: ['en' => ['required', 'string', 'max:120'], ...].
     *
     * @return array<string, array<int, mixed>>
     */
    public function getLocaleRules(): array
    {
        $required = $this->getRequiredLocales();
        $extra = $this->getExtraValidationRules();
        $rules = [];

        foreach ($this->getLocales() as $locale) {
            $localeRules = [in_array($locale, $required, true) ? 'required' : 'nullable', 'string'];

            if ($this->maxLength !== null) {
                $localeRules[] = "max:{$this->maxLength}";
            }

            $rules[$locale] = array_values(array_unique(array_merge($localeRules, $extra), SORT_REGULAR));
        }

        return $rules;
    }

    /**
     * Flat rules for manual validation: ['name' => [...], 'name.en' => [...], ...].
     *
     * @return array<string, array<int, mixed>>
     */
    public function getLocaleValidationRules(): array
    {
        $name = $this->getName();
        $rules = [$name => [$this->getRequiredLocales() === [] ? 'nullable' : 'required', 'array']];

        foreach ($this->getLocaleRules() as $locale => $localeRules) {
            $rules["{$name}.{$locale}"] = $localeRules;
        }

        return $rules;
    }

    /**
     * Display names per locale code, e.g. ['en' => 'Title (EN)'], so messages
     * read "The Title (EN) field is required." instead of "The title.en field...".
     *
     * @return array<string, string>
     */
    public function getLocaleAttributes(): array
    {
        $label = $this->getLabel() ?: $this->getName();
        $attributes = [];

        foreach ($this->getLocaleMetadata() as $locale) {
            $attributes[$locale['code']] = "{$label} ({$locale['label']})";
        }

        return $attributes;
    }

    /**
     * Validation attributes including the nested locale keys (name.en => Title (EN)).
     */
    public function getValidationAttributes(): array
    {
        $name = $this->getName();
        $attributes = parent::getValidationAttributes();

        foreach ($this->getLocaleAttributes() as $locale => $attribute) {
            $attributes["{$name}.{$locale}"] = $attribute;
        }

        return $attributes;
    }

    /**
     * Rules added via rules() / addRules(), minus the presence rules handled per locale.
     */
    protected function getExtraValidationRules(): array
    {
        $rules = parent::getValidationRules();

        if ($rules === null || $rules === '' || $rules === []) {
            return [];
        }

        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        return array_values(array_filter($rules, fn ($rule) => ! in_array($rule, ['required', 'nullable', 'string'], true)));
    }

    /**
     * Get the current value, always as an array with every allowed locale present.
     */
    public function getValue(): mixed
    {
        return $this->normalizeTranslations(parent::getValue());
    }

    /**
     * Hydrate from a JSON string, an array or null.
     */
    public function hydrateState(mixed $state): mixed
    {
        return $this->normalizeTranslations(parent::hydrateState($state));
    }

    /**
     * Dehydrate to an array keyed by locale.
     */
    public function dehydrateState(mixed $state): mixed
    {
        return parent::dehydrateState($this->normalizeTranslations($state));
    }

    /**
     * Normalise any input into [locale => string] with every allowed locale present.
     */
    public function normalizeTranslations(mixed $state): array
    {
        $translations = [];

        if (is_string($state)) {
            $decoded = json_decode($state, true);

            if (is_array($decoded)) {
                $translations = $decoded;
            } elseif ($state !== '') {
                $translations = [$this->getActiveLocale() => $state];
            }
        } elseif (is_array($state)) {
            $translations = $state;
        } elseif (is_object($state) && method_exists($state, 'toArray')) {
            $translations = $state->toArray();
        }

        $normalized = [];

        foreach ($this->getLocales() as $locale) {
            $value = $translations[$locale] ?? '';

            $normalized[$locale] = is_scalar($value) ? (string) $value : '';
        }

        return $normalized;
    }

    /**
     * Get the locale metadata for the frontend.
     *
     * @return array<int, array{code: string, name: string, label: string, direction: string}>
     */
    public function getLocaleMetadata(): array
    {
        return Locales::metadata($this->getLocales());
    }

    /**
     * Serialize component for Laravilt (Blade + Vue.js).
     */
    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'locales' => $this->getLocaleMetadata(),
            'activeLocale' => $this->getActiveLocale(),
            'requiredLocales' => $this->getRequiredLocales(),
            'multiline' => $this->isMultiline(),
            'rows' => $this->getRows(),
            'maxLength' => $this->getMaxLength(),
        ]);
    }
}
