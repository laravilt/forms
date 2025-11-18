<?php

use Laravilt\Forms\Components\TextInput;

test('it can mark field as required', function () {
    $field = TextInput::make('name')->required();

    expect($field->isRequired())->toBeTrue();
});

test('it adds required validation rule automatically', function () {
    $field = TextInput::make('name')->required();

    expect($field->getValidationRules())->toContain('required');
});

test('it can mark field as required conditionally', function () {
    $condition = true;
    $field = TextInput::make('name')->required($condition);

    expect($field->isRequired())->toBeTrue();

    $condition = false;
    $field2 = TextInput::make('email')->required($condition);

    expect($field2->isRequired())->toBeFalse();
});

test('it can use closure for conditional required', function () {
    $field = TextInput::make('other')->required(fn () => true);

    expect($field->isRequired())->toBeTrue();
});
