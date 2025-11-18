<?php

use Laravilt\Forms\Components\TextInput;

test('it can hide field', function () {
    $field = TextInput::make('secret')->hidden();

    expect($field->isHidden())->toBeTrue();
    expect($field->isVisible())->toBeFalse();
});

test('it can show field conditionally', function () {
    $field = TextInput::make('other')->visible(fn () => true);

    expect($field->isVisible())->toBeTrue();
    expect($field->isHidden())->toBeFalse();
});

test('it can hide field conditionally', function () {
    $field = TextInput::make('other')->hidden(fn () => true);

    expect($field->isHidden())->toBeTrue();
});

test('field is visible by default', function () {
    $field = TextInput::make('name');

    expect($field->isVisible())->toBeTrue();
    expect($field->isHidden())->toBeFalse();
});
