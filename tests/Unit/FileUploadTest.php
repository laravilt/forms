<?php

use Laravilt\Forms\Components\FileUpload;

test('it can create file upload', function () {
    $field = FileUpload::make('avatar');

    expect($field->getName())->toBe('avatar');
});

test('it can set as image', function () {
    $field = FileUpload::make('avatar')->image();

    expect($field->isImage())->toBeTrue();
});

test('it can set as multiple', function () {
    $field = FileUpload::make('documents')->multiple();

    expect($field->isMultiple())->toBeTrue();
});

test('it can set max files', function () {
    $field = FileUpload::make('images')->maxFiles(5);

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('maxFiles', 5);
});

test('it can set max size', function () {
    $field = FileUpload::make('avatar')->maxSize(2048);

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('maxSize', 2048);
});

test('it can set accepted file types', function () {
    $field = FileUpload::make('document')->acceptedFileTypes(['application/pdf']);

    $props = $field->toLaraviltProps();
    expect($props['acceptedFileTypes'])->toContain('application/pdf');
});

test('it can set disk', function () {
    $field = FileUpload::make('avatar')->disk('public');

    expect($field->getDisk())->toBe('public');
});

test('it can set directory', function () {
    $field = FileUpload::make('avatar')->directory('avatars');

    expect($field->getDirectory())->toBe('avatars');
});

test('it can set collection', function () {
    $field = FileUpload::make('avatar')->collection('avatars');

    expect($field->getCollection())->toBe('avatars');
});

test('it can be avatar mode', function () {
    $field = FileUpload::make('avatar')->avatar();

    expect($field->isAvatar())->toBeTrue();
    expect($field->isImage())->toBeTrue();
});

test('it serializes to laravilt props', function () {
    $field = FileUpload::make('images')
        ->label('Images')
        ->image()
        ->multiple()
        ->maxFiles(10)
        ->maxSize(5120);

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('image', true);
    expect($props)->toHaveKey('multiple', true);
    expect($props)->toHaveKey('maxFiles', 10);
    expect($props)->toHaveKey('maxSize', 5120);
});
