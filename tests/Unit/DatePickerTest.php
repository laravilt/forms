<?php

use Laravilt\Forms\Components\DatePicker;

test('it can create date picker', function () {
    $field = DatePicker::make('birthdate');

    expect($field->getName())->toBe('birthdate');
});

test('it can set format', function () {
    $field = DatePicker::make('date')->format('Y-m-d');

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('format', 'Y-m-d');
});

test('it can set display format', function () {
    $field = DatePicker::make('date')->displayFormat('F j, Y');

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('displayFormat', 'F j, Y');
});

test('it can set min and max dates', function () {
    $field = DatePicker::make('event_date')
        ->minDate('2024-01-01')
        ->maxDate('2024-12-31');

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('minDate', '2024-01-01');
    expect($props)->toHaveKey('maxDate', '2024-12-31');
});

test('it can be range mode', function () {
    $field = DatePicker::make('period')->range();

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('range', true);
});

test('it can disable specific dates', function () {
    $field = DatePicker::make('appointment')->disabledDates(['2024-12-25']);

    expect($field->getDisabledDates())->toContain('2024-12-25');
});

test('it serializes to laravilt props', function () {
    $field = DatePicker::make('date')
        ->label('Event Date')
        ->format('Y-m-d');

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('name', 'date');
    expect($props)->toHaveKey('label', 'Event Date');
});
