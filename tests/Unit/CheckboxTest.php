<?php

use Laravilt\Forms\Components\Checkbox;

test('it can create checkbox', function () {
    $field = Checkbox::make('terms');

    expect($field->getName())->toBe('terms');
});

test('it can set options', function () {
    $field = Checkbox::make('preferences')
        ->options([
            'email' => 'Email notifications',
            'sms' => 'SMS notifications',
        ]);

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('options');
    expect($props['options'])->toHaveKey('email');
    expect($props['options'])->toHaveKey('sms');
});

test('it can be inline', function () {
    $field = Checkbox::make('preferences')->inline();

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('inline', true);
});

test('it can set checked value', function () {
    $field = Checkbox::make('accept')
        ->checkedValue('yes')
        ->uncheckedValue('no');

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('checkedValue', 'yes');
    expect($props)->toHaveKey('uncheckedValue', 'no');
});

test('it serializes to laravilt props', function () {
    $field = Checkbox::make('newsletter')
        ->label('Subscribe to newsletter')
        ->description('Get weekly updates');

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('name', 'newsletter');
    expect($props)->toHaveKey('label', 'Subscribe to newsletter');
});
