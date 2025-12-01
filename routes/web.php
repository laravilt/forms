<?php

use Illuminate\Support\Facades\Route;
use Laravilt\Forms\Http\Controllers\FileUploadController;
use Laravilt\Forms\Http\Controllers\ReactiveFieldController;

/*
|--------------------------------------------------------------------------
| Forms Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your plugin. These
| routes are loaded by the ServiceProvider within a group which
| contains the "web" middleware group.
|
*/

Route::prefix('uploads')
    ->name('uploads.')
    ->group(function () {
        Route::post('/', [FileUploadController::class, 'upload'])->name('upload');
        Route::delete('/', [FileUploadController::class, 'delete'])->name('delete');
        Route::post('/temporary-url', [FileUploadController::class, 'temporaryUrl'])->name('temporary-url');
        Route::get('/private', [FileUploadController::class, 'servePrivate'])->name('private');
    });

// Reactive fields route (for live/lazy field updates)
Route::post('/reactive-fields/update', [ReactiveFieldController::class, 'update'])
    ->name('reactive-fields.update');
