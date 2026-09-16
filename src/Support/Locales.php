<?php

namespace Laravilt\Forms\Support;

/**
 * Locale metadata helper for translatable fields.
 *
 * Locales come from config/laravilt-forms.php under "locales" when that key is
 * set, either as plain codes or as code => ['name' => ..., 'direction' => ...,
 * 'label' => ...]. When it is empty they come from config('app.available_locales'),
 * the list the panel's Locale & Timezone page uses, shaped as
 * ['value' => 'en', 'label' => 'English', 'dir' => 'ltr'] entries.
 * This helper normalises every shape into the metadata the frontend needs.
 * A code without metadata gets its own code as the name, the uppercased base
 * code as the label and "ltr" as the direction.
 */
class Locales
{
    /**
     * Locale codes from config('laravilt-forms.locales'), else
     * config('app.available_locales'), else the app locale.
     */
    public static function default(): array
    {
        $codes = static::codes(config('laravilt-forms.locales'));

        if ($codes === []) {
            $codes = static::codes(static::fromApp());
        }

        return $codes === [] ? [app()->getLocale()] : $codes;
    }

    /**
     * Extract locale codes from either config shape: ['en', 'ar'] or ['en' => [...]].
     */
    public static function codes(mixed $locales): array
    {
        if (! is_array($locales)) {
            return [];
        }

        $codes = [];

        foreach ($locales as $key => $value) {
            $code = is_string($key) ? $key : $value;

            if (is_string($code) && $code !== '') {
                $codes[] = $code;
            }
        }

        return array_values(array_unique($codes));
    }

    /**
     * config('app.available_locales') normalised to code => ['name', 'direction'].
     *
     * Accepts the panel shape (['value' => 'en', 'label' => 'English', 'dir' => 'ltr'])
     * as well as the plain-code and code => metadata shapes used by this package.
     *
     * @return array<string, array{name?: string, direction?: string}>
     */
    public static function fromApp(): array
    {
        $locales = config('app.available_locales');

        if (! is_array($locales)) {
            return [];
        }

        $normalised = [];

        foreach ($locales as $key => $value) {
            if (is_string($key)) {
                $normalised[$key] = is_array($value) ? $value : [];

                continue;
            }

            if (is_string($value) && $value !== '') {
                $normalised[$value] = [];

                continue;
            }

            if (! is_array($value)) {
                continue;
            }

            $code = $value['value'] ?? $value['code'] ?? null;

            if (! is_string($code) || $code === '') {
                continue;
            }

            $meta = [];

            $name = $value['name'] ?? $value['label'] ?? null;
            $direction = $value['direction'] ?? $value['dir'] ?? null;

            if (is_string($name) && $name !== '') {
                $meta['name'] = $name;
            }

            if (is_string($direction) && $direction !== '') {
                $meta['direction'] = $direction;
            }

            $normalised[$code] = $meta;
        }

        return $normalised;
    }

    /**
     * Metadata for a locale from laravilt-forms.locales, else app.available_locales, if any.
     */
    public static function configured(string $locale): array
    {
        $locales = config('laravilt-forms.locales');

        if (is_array($locales) && is_array($locales[$locale] ?? null)) {
            return $locales[$locale];
        }

        return static::fromApp()[$locale] ?? [];
    }

    /**
     * Native name from config, else the code itself.
     */
    public static function name(string $locale): string
    {
        $name = static::configured($locale)['name'] ?? null;

        return is_string($name) && $name !== '' ? $name : $locale;
    }

    /**
     * Short label from config, else the base code uppercased ("en-US" => "EN").
     */
    public static function label(string $locale): string
    {
        $label = static::configured($locale)['label'] ?? null;

        return is_string($label) && $label !== '' ? $label : strtoupper(static::base($locale));
    }

    /**
     * Text direction from config ('rtl' or 'ltr'), else 'ltr'.
     */
    public static function direction(string $locale): string
    {
        return static::isRtl($locale) ? 'rtl' : 'ltr';
    }

    /**
     * Check if a locale is configured as right-to-left.
     */
    public static function isRtl(string $locale): bool
    {
        $direction = static::configured($locale)['direction'] ?? null;

        return is_string($direction) && strtolower($direction) === 'rtl';
    }

    /**
     * Frontend metadata for a list of locale codes.
     *
     * @return array<int, array{code: string, name: string, label: string, direction: string}>
     */
    public static function metadata(array $locales): array
    {
        return array_values(array_map(fn (string $locale) => [
            'code' => $locale,
            'name' => static::name($locale),
            'label' => static::label($locale),
            'direction' => static::direction($locale),
        ], static::codes($locales)));
    }

    /**
     * Base language code: "ar_EG" / "ar-EG" => "ar".
     */
    public static function base(string $locale): string
    {
        return strtolower(explode('-', explode('_', $locale)[0])[0]);
    }
}
