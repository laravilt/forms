<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Plugin Settings
    |--------------------------------------------------------------------------
    |
    | Configure your plugin settings here.
    |
    */

    'enabled' => env('LARAVILT_FORMS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | The endpoints behind FileUpload, MarkdownEditor uploads and live/reactive
    | fields: /uploads and /reactive-fields/update. They accept a storage disk
    | from the request, so keep them behind authentication. A panel that uses its
    | own guard should use e.g. 'auth:admin' here.
    |
    */

    'routes' => [
        'enabled' => env('LARAVILT_FORMS_ROUTES', true),
        'middleware' => ['web', 'auth', 'throttle:120,1'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | The locales offered by TranslatableInput when a field does not call
    | ->locales() itself. Each entry may be a plain code or a code => metadata
    | pair with a native "name" and a "direction" (ltr or rtl); an optional
    | "label" overrides the short badge shown on the globe button.
    |
    | 'locales' => ['en', 'ar', 'ckb'],
    | 'locales' => [
    |     'en' => ['name' => 'English', 'direction' => 'ltr'],
    |     'ar' => ['name' => 'العربية', 'direction' => 'rtl'],
    |     'ckb' => ['name' => 'کوردی', 'direction' => 'rtl'],
    | ],
    |
    | A plain code gets the code as its name and "ltr" as its direction.
    | Leave empty to fall back to the application locale.
    |
    */

    'locales' => [
        'en' => ['name' => 'English', 'direction' => 'ltr'],
    ],

    // Add your configuration options here
];
