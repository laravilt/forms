<?php

namespace Laravilt\Forms\Support;

/**
 * Locale metadata helper for translatable fields.
 *
 * Locales are defined in config/laravilt-forms.php under "locales", either as
 * plain codes or as code => ['name' => ..., 'direction' => ..., 'label' => ...].
 * This helper normalises both shapes into the metadata the frontend needs.
 * A code without metadata gets its own code as the name, the uppercased base
 * code as the label and "ltr" as the direction.
 */
class Locales
{
    /**
     * Locale codes from config('laravilt-forms.locales'), falling back to the app locale.
     */
    public static function default(): array
    {
        $codes = static::codes(config('laravilt-forms.locales'));

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
     * Metadata for a locale as configured in laravilt-forms.locales, if any.
     */
    public static function configured(string $locale): array
    {
        $locales = config('laravilt-forms.locales');

        if (! is_array($locales)) {
            return [];
        }

        $meta = $locales[$locale] ?? null;

        return is_array($meta) ? $meta : [];
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
