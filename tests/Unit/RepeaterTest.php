<?php

use Laravilt\Forms\Components\Repeater;
use Laravilt\Forms\Components\TextInput;

test('it can create repeater', function () {
    $field = Repeater::make('items');

    expect($field->getName())->toBe('items');
});

test('it can set schema', function () {
    $field = Repeater::make('items')->schema([
        TextInput::make('name'),
        TextInput::make('email'),
    ]);

    expect($field->getSchema())->toHaveCount(2);
});

test('it can set min items', function () {
    $field = Repeater::make('items')->minItems(1);

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('minItems', 1);
});

test('it can set max items', function () {
    $field = Repeater::make('items')->maxItems(10);

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('maxItems', 10);
});

test('it can set default items', function () {
    $field = Repeater::make('items')->defaultItems(2);

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('defaultItems', 2);
});

test('it can be orderable', function () {
    $field = Repeater::make('items')->orderable();

    expect($field->isOrderable())->toBeTrue();
});

test('it can disable ordering', function () {
    $field = Repeater::make('items')->orderable(false);

    expect($field->isOrderable())->toBeFalse();
});

test('it can be collapsible', function () {
    $field = Repeater::make('items')->collapsible();

    expect($field->isCollapsible())->toBeTrue();
});

test('it can be collapsed by default', function () {
    $field = Repeater::make('items')->collapsed();

    expect($field->isCollapsible())->toBeTrue();
    expect($field->isCollapsed())->toBeTrue();
});

test('it can set add button label', function () {
    $field = Repeater::make('items')->addButtonLabel('Add Item');

    expect($field->getAddButtonLabel())->toBe('Add Item');
});

test('it can set grid columns', function () {
    $field = Repeater::make('items')->gridColumns(2);

    expect($field->getGridColumns())->toBe(2);
});

test('it can be simple mode', function () {
    $field = Repeater::make('items')->simple();

    expect($field->shouldShowItemNumbers())->toBeFalse();
    expect($field->isCollapsible())->toBeFalse();
    expect($field->getGridColumns())->toBe(1);
});

test('it serializes schema to laravilt props', function () {
    $field = Repeater::make('items')
        ->schema([
            TextInput::make('name')->label('Name'),
            TextInput::make('email')->email(),
        ])
        ->minItems(1)
        ->maxItems(5);

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('schema');
    expect($props['schema'])->toHaveCount(2);
    expect($props)->toHaveKey('minItems', 1);
    expect($props)->toHaveKey('maxItems', 5);
});
