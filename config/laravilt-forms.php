<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Plugin Enabled
    |--------------------------------------------------------------------------
    */

    'enabled' => env('LARAVILT_FORMS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Form Theme
    |--------------------------------------------------------------------------
    |
    | This option controls the default theme for form components.
    | Available themes: 'light', 'dark', 'auto'
    |
    */

    'theme' => env('LARAVILT_FORMS_THEME', 'light'),

    /*
    |--------------------------------------------------------------------------
    | RTL Support
    |--------------------------------------------------------------------------
    |
    | Enable or disable right-to-left language support.
    |
    */

    'rtl' => env('LARAVILT_FORMS_RTL', false),

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    |
    | Configuration for form validation behavior.
    |
    */

    'validation' => [
        'validate_on_blur' => true,
        'validate_on_change' => false,
        'show_errors_on_touch' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Defaults
    |--------------------------------------------------------------------------
    |
    | Default configuration for form fields.
    |
    */

    'defaults' => [
        'required_marker' => '*',
        'show_required_marker' => true,
        'textarea_rows' => 3,
        'select_searchable_threshold' => 10,
    ],

];
