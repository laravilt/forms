<?php

use Illuminate\Support\Facades\Route;
use Laravilt\Forms\Http\Controllers\FileUploadController;
use Laravilt\Forms\Http\Controllers\FormsDemoController;

/*
|--------------------------------------------------------------------------
| Laravilt Forms Routes
|--------------------------------------------------------------------------
|
| File upload routes are primary routes available in all environments.
| Demo routes are only loaded in local development environments.
|
*/

// File upload routes - available in all environments
Route::prefix('uploads')
    ->name('uploads.')
    ->group(function () {
        Route::post('/', [FileUploadController::class, 'upload'])->name('upload');
        Route::delete('/', [FileUploadController::class, 'delete'])->name('delete');
        Route::post('/temporary-url', [FileUploadController::class, 'temporaryUrl'])->name('temporary-url');
        Route::get('/private', [FileUploadController::class, 'servePrivate'])->name('private');
    });

// Demo routes - REMOVED
// The demo functionality has been removed in favor of comprehensive documentation.
// Please refer to the /docs directory for complete usage examples:
// - docs/getting-started.md - Installation and basic usage
// - docs/COMPONENTS.md - Detailed component reference
// - docs/validation.md - Validation guide
// - docs/customization.md - Customization guide
//
// if (app()->environment('local')) {
//     Route::prefix('forms/demo')
//         ->name('forms.demo.')
//         ->group(function () {
//             Route::get('/', [FormsDemoController::class, 'index'])->name('index');
//             Route::post('/submit', [FormsDemoController::class, 'submit'])->name('submit');
//
//             // Blade-only demo (uses Laravilt middleware for AJAX support)
//             Route::middleware(\Laravilt\Support\LaraviltCore\Http\Middleware\LaraviltMiddleware::class)
//                 ->group(function () {
//                     Route::get('/blade', [FormsDemoController::class, 'blade'])->name('blade');
//                     Route::post('/blade/submit', [FormsDemoController::class, 'bladeSubmit'])->name('blade.submit');
//                 });
//         });
// }
