<?php

use Laravilt\Forms\Components\RichEditor;

test('it can create rich editor', function () {
    $field = RichEditor::make('content');

    expect($field->getName())->toBe('content');
});

test('it can set toolbar buttons', function () {
    $field = RichEditor::make('body')
        ->toolbarButtons(['bold', 'italic', 'link']);

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('toolbarButtons');
    expect($props['toolbarButtons'])->toContain('bold');
    expect($props['toolbarButtons'])->toContain('italic');
});

test('it can disable images', function () {
    $field = RichEditor::make('notes')->disableImages();

    expect($field->getDisabledFeatures())->toContain('image');
});

test('it can show character count', function () {
    $field = RichEditor::make('article')->showCharacterCount();

    expect($field->shouldShowCharacterCount())->toBeTrue();
});

test('it serializes to laravilt props', function () {
    $field = RichEditor::make('content')
        ->label('Content')
        ->toolbarButtons(['bold', 'italic']);

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('name', 'content');
    expect($props)->toHaveKey('label', 'Content');
});
