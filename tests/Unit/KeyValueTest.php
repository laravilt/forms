<?php

use Laravilt\Forms\Components\KeyValue;

test('it can create key value field', function () {
    $field = KeyValue::make('metadata');

    expect($field->getName())->toBe('metadata');
});

test('it can set key and value labels', function () {
    $field = KeyValue::make('settings')
        ->keyLabel('Property')
        ->valueLabel('Value');

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('keyLabel', 'Property');
    expect($props)->toHaveKey('valueLabel', 'Value');
});

test('it can enforce unique keys', function () {
    $field = KeyValue::make('config')->allowDuplicateKeys(false);

    expect($field->hasUniqueKeys())->toBeTrue();
});

test('it can be reorderable', function () {
    $field = KeyValue::make('env')->reorderable();

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('reorderable', true);
});

test('it can set key and value placeholders', function () {
    $field = KeyValue::make('tags')
        ->keyPlaceholder('Enter key')
        ->valuePlaceholder('Enter value');

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('keyPlaceholder', 'Enter key');
    expect($props)->toHaveKey('valuePlaceholder', 'Enter value');
});

test('it serializes to laravilt props', function () {
    $field = KeyValue::make('metadata')
        ->label('Metadata')
        ->allowDuplicateKeys(false);

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('name', 'metadata');
    expect($props)->toHaveKey('label', 'Metadata');
});
