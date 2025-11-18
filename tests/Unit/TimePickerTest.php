<?php

use Laravilt\Forms\Components\TimePicker;

test('it can create time picker', function () {
    $field = TimePicker::make('meeting_time');

    expect($field->getName())->toBe('meeting_time');
});

test('it can set 24 hour format', function () {
    $field = TimePicker::make('time')->format24Hour();

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('format24Hour', true);
});

test('it can set minute step', function () {
    $field = TimePicker::make('time')->step(15);

    expect($field->getStep())->toBe(15);
});

test('it can include seconds', function () {
    $field = TimePicker::make('time')->withSeconds();

    expect($field->shouldShowSeconds())->toBeTrue();
});

test('it serializes to laravilt props', function () {
    $field = TimePicker::make('alarm')
        ->label('Alarm Time')
        ->format24Hour();

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('name', 'alarm');
    expect($props)->toHaveKey('label', 'Alarm Time');
});
