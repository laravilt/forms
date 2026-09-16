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
    | ->locales() itself. Leave this empty to reuse the languages the panel
    | already knows about from config('app.available_locales'), the same list
    | the Locale & Timezone settings page shows, so a project defines its
    | languages once in config/app.php:
    |
    | 'available_locales' => [
    |     ['value' => 'en', 'label' => 'English', 'dir' => 'ltr'],
    |     ['value' => 'ar', 'label' => 'العربية', 'dir' => 'rtl'],
    | ],
    |
    | Set it only when the languages content is written in differ from the
    | languages the UI is shown in. Each entry may be a plain code or a
    | code => metadata pair with a native "name" and a "direction" (ltr or
    | rtl); an optional "label" overrides the short badge on the globe button.
    |
    | 'locales' => ['en', 'ar', 'ckb'],
    | 'locales' => [
    |     'en' => ['name' => 'English', 'direction' => 'ltr'],
    |     'ar' => ['name' => 'العربية', 'direction' => 'rtl'],
    |     'ckb' => ['name' => 'کوردی', 'direction' => 'rtl'],
    | ],
    |
    | A plain code gets the code as its name and "ltr" as its direction. When
    | both lists are empty the field falls back to the application locale.
    |
    */

    'locales' => [],

    // Add your configuration options here
];
