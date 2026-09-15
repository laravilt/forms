<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // The service provider only registers the package routes in the local environment
    Route::group([], __DIR__.'/../../routes/web.php');

    Storage::fake('public');
});

it('returns the stored path as plain text for FilePond', function () {
    $response = $this->post('/uploads', [
        'file' => UploadedFile::fake()->image('photo.png'),
        'disk' => 'public',
        'directory' => 'attachments',
    ]);

    $response->assertOk();

    $path = $response->getContent();

    expect($path)->toStartWith('attachments/')
        ->and($response->headers->get('Content-Type'))->toContain('text/plain');

    Storage::disk('public')->assertExists($path);
});

it('returns the stored path and public url as json when asked for the url', function () {
    $response = $this->post('/uploads', [
        'file' => UploadedFile::fake()->image('photo.png'),
        'disk' => 'public',
        'directory' => 'attachments',
        'acceptedFileTypes' => ['image/png', 'image/jpeg'],
        'maxSize' => 1024,
        'withUrl' => '1',
    ]);

    $response->assertOk()->assertJsonStructure(['path', 'url']);

    $path = $response->json('path');

    expect($path)->toStartWith('attachments/')
        ->and($response->json('url'))->toBe(Storage::disk('public')->url($path));

    Storage::disk('public')->assertExists($path);
});
