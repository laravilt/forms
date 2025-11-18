<?php

use Laravilt\Forms\Components\Radio;

test('it can create radio', function () {
    $field = Radio::make('gender');

    expect($field->getName())->toBe('gender');
});

test('it can set options', function () {
    $field = Radio::make('gender')
        ->options([
            'male' => 'Male',
            'female' => 'Female',
        ]);

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('options');
    expect($props['options'])->toHaveKey('male');
    expect($props['options'])->toHaveKey('female');
});

test('it can be inline', function () {
    $field = Radio::make('gender')->inline();

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('inline', true);
});

test('it can be boolean mode', function () {
    $field = Radio::make('active')->boolean();

    expect($field->isBoolean())->toBeTrue();
});

test('it can set descriptions for options', function () {
    $field = Radio::make('experience')
        ->options([
            'beginner' => 'Beginner',
            'expert' => 'Expert',
        ])
        ->descriptions([
            'beginner' => '0-1 years',
            'expert' => '5+ years',
        ]);

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('descriptions');
    expect($props['descriptions'])->toHaveKey('beginner');
});

test('it serializes to laravilt props', function () {
    $field = Radio::make('level')
        ->label('Experience Level')
        ->options(['junior' => 'Junior', 'senior' => 'Senior']);

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('name', 'level');
    expect($props)->toHaveKey('label', 'Experience Level');
});
