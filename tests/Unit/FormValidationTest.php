<?php

use Laravilt\Forms\Components\Select;
use Laravilt\Forms\Components\Textarea;
use Laravilt\Forms\Components\TextInput;
use Laravilt\Schemas\Schema;

test('it validates required fields', function () {
    $form = Schema::make('validation_form')->schema([
        TextInput::make('name')->required(),
        TextInput::make('email')->email()->required(),
    ]);

    $nameRules = $form->getSchema()[0]->getValidationRules();
    $emailRules = $form->getSchema()[1]->getValidationRules();

    expect($nameRules)->toContain('required');
    expect($emailRules)->toContain('required');
    expect($emailRules)->toContain('email');
});

test('it validates email format', function () {
    $field = TextInput::make('email')->email()->required();

    $rules = $field->getValidationRules();

    expect($rules)->toContain('email');
    expect($rules)->toContain('required');
});

test('it validates min and max length', function () {
    $field = TextInput::make('username')->minLength(3)->maxLength(20);

    $rules = $field->getValidationRules();

    expect($rules)->toContain('min:3');
    expect($rules)->toContain('max:20');
});

test('it validates select options', function () {
    $field = Select::make('role')
        ->options([
            'admin' => 'Admin',
            'user' => 'User',
        ])
        ->in(['admin', 'user']);

    $rules = $field->getValidationRules();

    expect($rules)->toContain('in:admin,user');
});

test('it passes validation with valid data', function () {
    $form = Schema::make('valid_form')->schema([
        TextInput::make('name')->required()->minLength(3),
        TextInput::make('email')->email()->required(),
        Textarea::make('bio')->maxLength(500),
    ]);

    $rules = [];
    foreach ($form->getSchema() as $component) {
        if (method_exists($component, 'getName') && method_exists($component, 'getValidationRules')) {
            $rules[$component->getName()] = $component->getValidationRules();
        }
    }

    expect($rules)->toHaveKey('name');
    expect($rules)->toHaveKey('email');
    expect($rules)->toHaveKey('bio');
    expect($rules['name'])->toContain('required');
    expect($rules['name'])->toContain('min:3');
    expect($rules['email'])->toContain('email');
    expect($rules['bio'])->toContain('max:500');
});

test('it collects validation rules from all fields', function () {
    $form = Schema::make('collection_form')->schema([
        TextInput::make('name')->required(),
        TextInput::make('email')->email()->required(),
        TextInput::make('username')->minLength(3)->maxLength(20),
    ]);

    $rules = [];
    foreach ($form->getSchema() as $component) {
        if (method_exists($component, 'getName') && method_exists($component, 'getValidationRules')) {
            $rules[$component->getName()] = $component->getValidationRules();
        }
    }

    expect($rules)->toHaveKey('name');
    expect($rules)->toHaveKey('email');
    expect($rules)->toHaveKey('username');
    expect($rules['name'])->toContain('required');
    expect($rules['email'])->toContain('email');
    expect($rules['email'])->toContain('required');
});
