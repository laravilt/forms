<?php

use Laravilt\Forms\Components\Select;

test('it can create select', function () {
    $field = Select::make('country');

    expect($field->getName())->toBe('country');
});

test('it can set options', function () {
    $field = Select::make('country')->options([
        'us' => 'United States',
        'uk' => 'United Kingdom',
    ]);

    expect($field->getOptions())->toBe([
        'us' => 'United States',
        'uk' => 'United Kingdom',
    ]);
});

test('it can be searchable', function () {
    $field = Select::make('country')->searchable();

    expect($field->isSearchable())->toBeTrue();
});

test('it can be multiple', function () {
    $field = Select::make('tags')->multiple();

    expect($field->isMultiple())->toBeTrue();
});

test('it can be native', function () {
    $field = Select::make('country')->native();

    expect($field->isNative())->toBeTrue();
});

test('it serializes to laravilt props', function () {
    $field = Select::make('country')
        ->label('Country')
        ->options([
            'us' => 'United States',
            'uk' => 'United Kingdom',
        ])
        ->searchable()
        ->required();

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('options');
    expect($props['options'])->toHaveCount(2);
    expect($props)->toHaveKey('searchable', true);
    expect($props)->toHaveKey('required', true);
});
