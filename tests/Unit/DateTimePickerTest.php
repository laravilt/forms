<?php

use Laravilt\Forms\Components\DateTimePicker;

test('it can create date time picker', function () {
    $field = DateTimePicker::make('appointment');

    expect($field->getName())->toBe('appointment');
});

test('it can use 24 hour format', function () {
    $field = DateTimePicker::make('datetime')->format24Hour();

    expect($field->uses24HourFormat())->toBeTrue();
});

test('it can set timezone', function () {
    $field = DateTimePicker::make('event')->timezone('America/New_York');

    expect($field->getTimezone())->toBe('America/New_York');
});

test('it can set min and max datetime', function () {
    $field = DateTimePicker::make('booking')
        ->minDateTime('2024-01-01 00:00:00')
        ->maxDateTime('2024-12-31 23:59:59');

    expect($field->getMinDateTime())->toBe('2024-01-01 00:00:00');
    expect($field->getMaxDateTime())->toBe('2024-12-31 23:59:59');
});

test('it serializes to laravilt props', function () {
    $field = DateTimePicker::make('start')
        ->label('Start Time')
        ->timezone('UTC');

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('name', 'start');
    expect($props)->toHaveKey('label', 'Start Time');
});
