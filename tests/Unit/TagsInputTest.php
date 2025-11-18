<?php

use Laravilt\Forms\Components\TagsInput;

test('it can create tags input', function () {
    $field = TagsInput::make('tags');

    expect($field->getName())->toBe('tags');
});

test('it can set suggestions', function () {
    $field = TagsInput::make('tags')->suggestions(['Laravel', 'Vue', 'React']);

    expect($field->getSuggestions())->toHaveCount(3);
});

test('it can set min tags', function () {
    $field = TagsInput::make('tags')->minTags(2);

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('minTags', 2);
});

test('it can set max tags', function () {
    $field = TagsInput::make('tags')->maxTags(5);

    $props = $field->toLaraviltProps();
    expect($props)->toHaveKey('maxTags', 5);
});

test('it can set separators', function () {
    $field = TagsInput::make('tags')->separators([',', ';']);

    expect($field->getSeparators())->toContain(',');
    expect($field->getSeparators())->toContain(';');
});

test('it can be case sensitive', function () {
    $field = TagsInput::make('tags')->caseSensitive();

    expect($field->isCaseSensitive())->toBeTrue();
});

test('it can allow duplicates', function () {
    $field = TagsInput::make('tags')->allowDuplicates();

    expect($field->allowsDuplicates())->toBeTrue();
});

test('it can set tag pattern', function () {
    $field = TagsInput::make('tags')->tagPattern('[a-zA-Z]+');

    expect($field->getTagPattern())->toBe('[a-zA-Z]+');
});

test('it serializes to laravilt props', function () {
    $field = TagsInput::make('tags')
        ->label('Tags')
        ->suggestions(['Laravel', 'Vue'])
        ->minTags(1)
        ->maxTags(10);

    $props = $field->toLaraviltProps();

    expect($props)->toHaveKey('suggestions');
    expect($props)->toHaveKey('minTags', 1);
    expect($props)->toHaveKey('maxTags', 10);
});
