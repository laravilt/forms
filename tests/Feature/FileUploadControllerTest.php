<?php

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

function signedInUser(): User
{
    return (new User)->forceFill(['id' => 1, 'name' => 'Tester', 'email' => 'tester@example.com']);
}

it('registers the upload and reactive-field routes outside the local environment', function () {
    // Testbench runs as "testing": these routes used to exist only when APP_ENV=local,
    // which broke FileUpload, MarkdownEditor uploads and live fields in production.
    expect(app()->environment('local'))->toBeFalse()
        ->and(Route::has('uploads.upload'))->toBeTrue()
        ->and(Route::has('uploads.delete'))->toBeTrue()
        ->and(Route::has('uploads.temporary-url'))->toBeTrue()
        ->and(Route::has('uploads.private'))->toBeTrue()
        ->and(Route::has('reactive-fields.update'))->toBeTrue();
});

it('keeps the upload and reactive-field routes behind the configured middleware', function () {
    $middleware = Route::getRoutes()->getByName('uploads.upload')->gatherMiddleware();

    expect($middleware)->toContain('web', 'auth')
        ->and(Route::getRoutes()->getByName('reactive-fields.update')->gatherMiddleware())->toContain('auth');
});

it('rejects uploads from guests', function () {
    $this->postJson('/uploads', [
        'file' => UploadedFile::fake()->image('photo.png'),
        'disk' => 'public',
    ])->assertUnauthorized();
});

it('returns the stored path as plain text for FilePond', function () {
    $response = $this->actingAs(signedInUser())->post('/uploads', [
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
    $response = $this->actingAs(signedInUser())->post('/uploads', [
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
