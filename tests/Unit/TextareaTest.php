<?php

use Laravilt\Forms\Components\Textarea;

test('it can create textarea', function () {
    $field = Textarea::make('bio');

    expect($field->getName())->toBe('bio');
});

test('it can set rows', function () {
    $field = Textarea::make('bio')->rows(5);

    expect($field->getRows())->toBe(5);
});

test('it can set min length', function () {
    $field = Textarea::make('bio')->minLength(10);

    expect($field->getValidationRules())->toContain('min:10');
});

test('it can set max length', function () {
    $field = Textarea::make('bio')->maxLength(500);

    expect($field->getValidationRules())->toContain('max:500');
});

test('it can auto resize', function () {
    $field = Textarea::make('bio')->autosize();

    expect($field->shouldAutosize())->toBeTrue();
});

test('it can show character count', function () {
    $field = Textarea::make('bio')->characterCount();

    expect($field->shouldShowCharacterCount())->toBeTrue();
});

test('it can show word count', function () {
    $field = Textarea::make('bio')->wordCount();

    expect($field->shouldShowWordCount())->toBeTrue();
});

test('it serializes to laravilt props', function () {
    $field = Textarea::make('bio')
        ->label('Biography')
        ->rows(4)
        ->maxLength(500)
        ->characterCount();

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('rows', 4);
    expect($props)->toHaveKey('maxLength', 500);
    expect($props)->toHaveKey('showCharacterCount', true);
});
