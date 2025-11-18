<?php

use Laravilt\Forms\Components\TextInput;

test('it can add single validation rule', function () {
    $field = TextInput::make('email')->rules('email');

    expect($field->getValidationRules())->toContain('email');
});

test('it can add multiple validation rules', function () {
    $field = TextInput::make('email')->rules(['email', 'max:255']);

    expect($field->getValidationRules())->toContain('email');
    expect($field->getValidationRules())->toContain('max:255');
});

test('it can use email shortcut', function () {
    $field = TextInput::make('email')->email();

    expect($field->getValidationRules())->toContain('email');
});

test('it can use url shortcut', function () {
    $field = TextInput::make('website')->url();

    expect($field->getValidationRules())->toContain('url');
});

test('it can use min shortcut', function () {
    $field = TextInput::make('username')->min(3);

    expect($field->getValidationRules())->toContain('min:3');
});

test('it can use max shortcut', function () {
    $field = TextInput::make('username')->max(20);

    expect($field->getValidationRules())->toContain('max:20');
});

test('it can use unique shortcut', function () {
    $field = TextInput::make('email')->unique('users', 'email');

    $rules = $field->getValidationRules();
    expect($rules)->toContain('unique:users,email');
});

test('it can use exists shortcut', function () {
    $field = TextInput::make('user_id')->exists('users', 'id');

    expect($field->getValidationRules())->toContain('exists:users,id');
});

test('it can set custom validation messages', function () {
    $field = TextInput::make('email')
        ->email()
        ->validationMessages([
            'email' => 'Please enter a valid email address',
        ]);

    $messages = $field->getValidationMessages();
    expect($messages)->toHaveKey('email.email');
    expect($messages['email.email'])->toBe('Please enter a valid email address');
});
