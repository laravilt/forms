<?php

use Laravilt\Forms\Components\Toggle;

test('it can create toggle', function () {
    $field = Toggle::make('active');

    expect($field->getName())->toBe('active');
});

test('it can set on and off labels', function () {
    $field = Toggle::make('enabled')
        ->onLabel('Enabled')
        ->offLabel('Disabled');

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('onLabel', 'Enabled');
    expect($props)->toHaveKey('offLabel', 'Disabled');
});

test('it can set on and off colors', function () {
    $field = Toggle::make('dark_mode')
        ->onColor('primary')
        ->offColor('gray');

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('onColor', 'primary');
    expect($props)->toHaveKey('offColor', 'gray');
});

test('it can set on and off icons', function () {
    $field = Toggle::make('notifications')
        ->onIcon('bell')
        ->offIcon('bell-slash');

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('onIcon', 'bell');
    expect($props)->toHaveKey('offIcon', 'bell-slash');
});

test('it serializes to laravilt props', function () {
    $field = Toggle::make('public')
        ->label('Public Profile')
        ->onLabel('Public')
        ->offLabel('Private');

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('name', 'public');
    expect($props)->toHaveKey('label', 'Public Profile');
});
