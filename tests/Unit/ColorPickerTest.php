<?php

use Laravilt\Forms\Components\ColorPicker;

test('it can create color picker', function () {
    $field = ColorPicker::make('color');

    expect($field->getName())->toBe('color');
});

test('it can enable alpha channel', function () {
    $field = ColorPicker::make('color')->alpha();

    expect($field->hasAlpha())->toBeTrue();
});

test('it can set format', function () {
    $field = ColorPicker::make('color')->format('rgb');

    expect($field->getFormat())->toBe('rgb');
});

test('it defaults to hex format', function () {
    $field = ColorPicker::make('color');

    expect($field->getFormat())->toBe('hex');
});

test('it can set swatches', function () {
    $field = ColorPicker::make('color')->swatches([
        '#FF0000',
        '#00FF00',
        '#0000FF',
    ]);

    expect($field->getSwatches())->toHaveCount(3);
    expect($field->shouldShowSwatches())->toBeTrue();
});

test('it serializes to laravilt props', function () {
    $field = ColorPicker::make('color')
        ->label('Theme Color')
        ->alpha()
        ->format('rgb')
        ->swatches(['#FF0000', '#00FF00']);

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('alpha', true);
    expect($props)->toHaveKey('format', 'rgb');
    expect($props)->toHaveKey('swatches');
    expect($props['swatches'])->toHaveCount(2);
});
