<?php

use Laravilt\Forms\Components\Hidden;

test('it can create hidden field', function () {
    $field = Hidden::make('token');

    expect($field->getName())->toBe('token');
});

test('it is always hidden', function () {
    $field = Hidden::make('secret');

    expect($field->isHidden())->toBeTrue();
});

test('it serializes to laravilt props', function () {
    $field = Hidden::make('csrf_token');

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('name', 'csrf_token');
    expect($props)->toHaveKey('hidden', true);
    expect($props)->toHaveKey('type', 'hidden');
});
