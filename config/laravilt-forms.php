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

    // Add your configuration options here
];
