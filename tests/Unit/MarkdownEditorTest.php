<?php

use Laravilt\Forms\Components\MarkdownEditor;

test('it can create markdown editor', function () {
    $field = MarkdownEditor::make('readme');

    expect($field->getName())->toBe('readme');
});

test('it can set toolbar buttons', function () {
    $field = MarkdownEditor::make('docs')
        ->toolbarButtons(['bold', 'italic', 'heading']);

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('toolbarButtons');
    expect($props['toolbarButtons'])->toContain('bold');
});

test('it can enable live preview', function () {
    $field = MarkdownEditor::make('notes')->livePreview();

    expect($field->hasLivePreview())->toBeTrue();
});

test('it can show character count', function () {
    $field = MarkdownEditor::make('post')->showCharacterCount();

    expect($field->shouldShowCharacterCount())->toBeTrue();
});

test('it can show word count', function () {
    $field = MarkdownEditor::make('article')->showWordCount();

    expect($field->shouldShowWordCount())->toBeTrue();
});

test('it can enable split view', function () {
    $field = MarkdownEditor::make('content')->splitView();

    expect($field->hasSplitView())->toBeTrue();
});

test('it serializes to laravilt props', function () {
    $field = MarkdownEditor::make('readme')
        ->label('README')
        ->showCharacterCount();

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('name', 'readme');
    expect($props)->toHaveKey('label', 'README');
});
