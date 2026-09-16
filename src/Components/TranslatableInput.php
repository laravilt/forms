<?php

namespace Laravilt\Forms\Components;

use Closure;
use Laravilt\Forms\Support\Locales;

/**
 * Translatable Input Field
 *
 * A multi-language text field whose value is an array keyed by locale code,
 * e.g. ['en' => 'Title', 'ar' => 'العنوان']. Supports:
 * - Single-line or multiline (textarea) editing
 * - Explicit, config-driven or app-locale based locale lists
 * - Per-locale validation rules (name.en, name.ar, ...)
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
     * Resolution order: explicit locales(), config('laravilt-forms.locales'), app locale.
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
     * Get validation rules keyed by locale: name.en, name.ar, ...
     */
    public function getValidationRules(): array
    {
        $name = $this->getName();
        $required = $this->getRequiredLocales();
        $extra = $this->getExtraValidationRules();

        $rules = [
            $name => [$required === [] ? 'nullable' : 'required', 'array'],
        ];

        foreach ($this->getLocales() as $locale) {
            $localeRules = [in_array($locale, $required, true) ? 'required' : 'nullable', 'string'];

            if ($this->maxLength !== null) {
                $localeRules[] = "max:{$this->maxLength}";
            }

            $rules["{$name}.{$locale}"] = array_values(array_unique(array_merge($localeRules, $extra), SORT_REGULAR));
        }

        return $rules;
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
