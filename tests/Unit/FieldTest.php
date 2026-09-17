<?php

use Laravilt\Forms\Components\Field;

beforeEach(function () {
    $this->field = createTestField('test-field');
});

it('can be instantiated with make method', function () {
    $field = createTestField('test');

    expect($field)->toBeInstanceOf(Field::class)
        ->and($field->getName())->toBe('test');
});

it('can set and get label', function () {
    $this->field->label('Test Label');

    expect($this->field->getLabel())->toBe('Test Label');
});

it('can set and get placeholder', function () {
    $this->field->placeholder('Enter value...');

    expect($this->field->getPlaceholder())->toBe('Enter value...');
});

it('can set and get helper text', function () {
    $this->field->helperText('This is a helper');

    expect($this->field->getHelperText())->toBe('This is a helper');
});

it('can set field as required', function () {
    $this->field->required();

    expect($this->field->isRequired())->toBeTrue();
});

it('can set field as disabled', function () {
    $this->field->disabled();

    expect($this->field->isDisabled())->toBeTrue();
});

it('can set field as readonly', function () {
    $this->field->readonly();

    expect($this->field->isReadonly())->toBeTrue();
});

it('can set field as hidden', function () {
    $this->field->hidden();

    expect($this->field->isHidden())->toBeTrue();
});

it('can set autofocus', function () {
    $this->field->autofocus();

    expect($this->field->shouldAutofocus())->toBeTrue();
});

it('can set autocomplete attribute', function () {
    $this->field->autocomplete('email');

    expect($this->field->getAutocomplete())->toBe('email');
});

it('can set tabindex', function () {
    $this->field->tabindex(1);

    expect($this->field->getTabindex())->toBe(1);
});

it('can set extra attributes', function () {
    $this->field->extraAttributes(['data-test' => 'value']);

    expect($this->field->getExtraAttributes())->toBe(['data-test' => 'value']);
});

it('can merge extra attributes', function () {
    $this->field->extraAttributes(['attr1' => 'value1']);
    $this->field->extraAttributes(['attr2' => 'value2'], true);

    expect($this->field->getExtraAttributes())->toBe([
        'attr1' => 'value1',
        'attr2' => 'value2',
    ]);
});

it('can set default value', function () {
    $this->field->default('default-value');

    expect($this->field->getDefaultValue())->toBe('default-value');
});

it('can set validation rules', function () {
    $this->field->rules(['required', 'email']);

    expect($this->field->getValidationRules())->toBe(['required', 'email']);
});

it('mirrors required() as a validation rule alongside other rules', function () {
    $this->field->required()->rules(['max:255']);

    expect($this->field->getValidationRules())->toBe(['required', 'max:255']);
});

it('mirrors required() as a validation rule when there are no other rules', function () {
    $this->field->required();

    expect($this->field->getValidationRules())->toBe(['required']);
});

it('keeps string rules as a string when adding required', function () {
    $this->field->required()->rules('email|max:255');

    expect($this->field->getValidationRules())->toBe('required|email|max:255');
});

it('does not duplicate or override an explicit presence rule', function () {
    $this->field->required()->rules(['nullable', 'email']);

    expect($this->field->getValidationRules())->toBe(['nullable', 'email']);

    $this->field->rules(['required', 'email']);

    expect($this->field->getValidationRules())->toBe(['required', 'email']);
});

it('can add validation rule', function () {
    $this->field->rules(['required']);

    expect($this->field->getValidationRules())->toBe(['required']);
});

it('can set as reactive', function () {
    $this->field->reactive();

    expect($this->field->isReactive())->toBeTrue();
});

it('can set after state updated callback', function () {
    $callback = fn () => 'called';
    $this->field->afterStateUpdated($callback);

    expect($this->field->getAfterStateUpdated())->toBe($callback);
});

it('supports closure for label', function () {
    $this->field->label(fn () => 'Dynamic Label');

    expect($this->field->getLabel())->toBe('Dynamic Label');
});

it('supports closure for placeholder', function () {
    $this->field->placeholder(fn () => 'Dynamic Placeholder');

    expect($this->field->getPlaceholder())->toBe('Dynamic Placeholder');
});

it('supports closure for required state', function () {
    $this->field->required(fn () => true);

    expect($this->field->isRequired())->toBeTrue();
});

it('supports closure for disabled state', function () {
    $this->field->disabled(fn () => true);

    expect($this->field->isDisabled())->toBeTrue();
});

it('can add hint action', function () {
    expect($this->field->getHintActions())->toBeArray()
        ->and($this->field->getHintActions())->toBeEmpty();
});

it('can add multiple hint actions', function () {
    expect($this->field->getHintActions())->toBeArray()
        ->and($this->field->getHintActions())->toBeEmpty();
});

it('can add prefix action', function () {
    expect($this->field->getPrefixActions())->toBeArray()
        ->and($this->field->getPrefixActions())->toBeEmpty();
});

it('can add suffix action', function () {
    expect($this->field->getSuffixActions())->toBeArray()
        ->and($this->field->getSuffixActions())->toBeEmpty();
});

it('serializes all properties to laravilt props', function () {
    $this->field
        ->label('Test Label')
        ->placeholder('Test Placeholder')
        ->helperText('Test Helper')
        ->required()
        ->disabled()
        ->autofocus()
        ->default('default-value');

    $props = $this->field->toLaraviltProps();

    expect($props)->toHaveKey('label')
        ->and($props['label'])->toBe('Test Label')
        ->and($props)->toHaveKey('placeholder')
        ->and($props['placeholder'])->toBe('Test Placeholder')
        ->and($props)->toHaveKey('helperText')
        ->and($props['helperText'])->toBe('Test Helper')
        ->and($props)->toHaveKey('required')
        ->and($props['required'])->toBeTrue()
        ->and($props)->toHaveKey('disabled')
        ->and($props['disabled'])->toBeTrue()
        ->and($props)->toHaveKey('autofocus')
        ->and($props['autofocus'])->toBeTrue()
        ->and($props)->toHaveKey('defaultValue')
        ->and($props['defaultValue'])->toBe('default-value');
});

it('supports method chaining', function () {
    $result = $this->field
        ->label('Test')
        ->placeholder('Test')
        ->required()
        ->disabled();

    expect($result)->toBe($this->field);
});
