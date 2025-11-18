<?php

use Laravilt\Forms\Components\TextInput;

test('it can create text input', function () {
    $field = TextInput::make('name');

    expect($field->getName())->toBe('name');
});

test('it can set label', function () {
    $field = TextInput::make('name')->label('Full Name');

    expect($field->getLabel())->toBe('Full Name');
});

test('it generates label from name', function () {
    $field = TextInput::make('first_name');

    expect($field->getLabel())->toBe('First name');
});

test('it can set placeholder', function () {
    $field = TextInput::make('email')->placeholder('your@email.com');

    expect($field->getPlaceholder())->toBe('your@email.com');
});

test('it can set as required', function () {
    $field = TextInput::make('name')->required();

    expect($field->isRequired())->toBeTrue();
    expect($field->getValidationRules())->toContain('required');
});

test('it can set as disabled', function () {
    $field = TextInput::make('name')->disabled();

    expect($field->isDisabled())->toBeTrue();
});

test('it can set as hidden', function () {
    $field = TextInput::make('name')->hidden();

    expect($field->isHidden())->toBeTrue();
});

test('it can set default value', function () {
    $field = TextInput::make('name')->default('John Doe');

    expect($field->getDefaultValue())->toBe('John Doe');
    expect($field->getValue())->toBe('John Doe');
});

test('it can set email type', function () {
    $field = TextInput::make('email')->email();

    expect($field->getType())->toBe('email');
    expect($field->getValidationRules())->toContain('email');
});

test('it can set password type', function () {
    $field = TextInput::make('password')->password();

    expect($field->getType())->toBe('password');
});

test('it can set url type', function () {
    $field = TextInput::make('website')->url();

    expect($field->getType())->toBe('url');
    expect($field->getValidationRules())->toContain('url');
});

test('it can set tel type', function () {
    $field = TextInput::make('phone')->tel();

    expect($field->getType())->toBe('tel');
});

test('it can set min length', function () {
    $field = TextInput::make('username')->minLength(3);

    expect($field->getValidationRules())->toContain('min:3');
});

test('it can set max length', function () {
    $field = TextInput::make('username')->maxLength(20);

    expect($field->getValidationRules())->toContain('max:20');
});

test('it can set prefix', function () {
    $field = TextInput::make('price')->prefix('$');

    expect($field->getPrefix())->toBe('$');
});

test('it can set suffix', function () {
    $field = TextInput::make('weight')->suffix('kg');

    expect($field->getSuffix())->toBe('kg');
});

test('it can be reactive', function () {
    $field = TextInput::make('quantity')->reactive();

    expect($field->isReactive())->toBeTrue();
});

test('it can set column span', function () {
    $field = TextInput::make('name')->columnSpan(2);

    expect($field->getColumnSpan())->toBe(2);
});

test('it can set helper text', function () {
    $field = TextInput::make('email')->helperText('We will never share your email');

    expect($field->getHelperText())->toBe('We will never share your email');
});

test('it serializes to laravilt props', function () {
    $field = TextInput::make('email')
        ->label('Email Address')
        ->email()
        ->required()
        ->placeholder('your@email.com')
        ->helperText('Enter your email');

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('label', 'Email Address');
    expect($props)->toHaveKey('placeholder', 'your@email.com');
    expect($props)->toHaveKey('required', true);
    expect($props)->toHaveKey('helperText', 'Enter your email');
    expect($props['validation'])->toContain('required');
    expect($props['validation'])->toContain('email');
});
