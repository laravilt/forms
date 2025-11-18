<?php

use Illuminate\Support\Facades\Route;
use Laravilt\Forms\Http\Controllers\FormsDemoController;

/*
|--------------------------------------------------------------------------
| Laravilt Forms Demo Routes
|--------------------------------------------------------------------------
|
| These routes are for demonstrating and testing all form components.
| They should only be loaded in local development environments.
|
*/

if (app()->environment('local')) {
    Route::prefix('forms/demo')
        ->name('forms.demo.')
        ->group(function () {
            Route::get('/', [FormsDemoController::class, 'index'])->name('index');
            Route::post('/submit', [FormsDemoController::class, 'submit'])->name('submit');
        });
}
