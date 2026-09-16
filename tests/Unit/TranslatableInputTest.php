<?php

use Laravilt\Forms\Components\TranslatableInput;
use Laravilt\Forms\Rules\TranslationsRule;
use Laravilt\Forms\Support\Locales;

beforeEach(function () {
    config()->set('laravilt-forms.locales', [
        'en' => ['name' => 'English', 'direction' => 'ltr'],
        'ar' => ['name' => 'العربية', 'direction' => 'rtl'],
        'ckb' => ['name' => 'کوردی', 'direction' => 'rtl'],
    ]);

    $this->input = TranslatableInput::make('name');
});

it('can be instantiated with make method', function () {
    $input = TranslatableInput::make('title');

    expect($input)->toBeInstanceOf(TranslatableInput::class)
        ->and($input->getName())->toBe('title');
});

it('uses the locales from config by default', function () {
    expect($this->input->getLocales())->toBe(['en', 'ar', 'ckb']);
});

it('falls back to the app locale when neither config has locales', function () {
    config()->set('laravilt-forms.locales', []);
    config()->set('app.available_locales', []);
    app()->setLocale('fr');

    expect($this->input->getLocales())->toBe(['fr']);
});

it('falls back to app.available_locales when the forms config is empty', function () {
    config()->set('laravilt-forms.locales', []);
    config()->set('app.available_locales', [
        ['value' => 'en', 'label' => 'English', 'dir' => 'ltr'],
        ['value' => 'ar', 'label' => 'العربية', 'dir' => 'rtl'],
    ]);

    expect($this->input->getLocales())->toBe(['en', 'ar'])
        ->and($this->input->toArray()['locales'])->toBe([
            ['code' => 'en', 'name' => 'English', 'label' => 'EN', 'direction' => 'ltr'],
            ['code' => 'ar', 'name' => 'العربية', 'label' => 'AR', 'direction' => 'rtl'],
        ]);
});

it('prefers laravilt-forms.locales over app.available_locales', function () {
    config()->set('laravilt-forms.locales', ['de']);
    config()->set('app.available_locales', [
        ['value' => 'en', 'label' => 'English', 'dir' => 'ltr'],
    ]);

    expect($this->input->getLocales())->toBe(['de']);
});

it('reads metadata from app.available_locales for plain codes in the forms config', function () {
    config()->set('laravilt-forms.locales', ['ar', 'en' => ['name' => 'Custom']]);
    config()->set('app.available_locales', [
        ['value' => 'en', 'label' => 'English', 'dir' => 'ltr'],
        ['value' => 'ar', 'label' => 'العربية', 'dir' => 'rtl'],
    ]);

    expect(Locales::name('ar'))->toBe('العربية')
        ->and(Locales::direction('ar'))->toBe('rtl')
        ->and(Locales::name('en'))->toBe('Custom');
});

it('accepts plain codes and keyed metadata in app.available_locales', function () {
    config()->set('laravilt-forms.locales', []);
    config()->set('app.available_locales', ['en', 'he' => ['name' => 'עברית', 'direction' => 'rtl'], ['code' => 'fa', 'dir' => 'RTL'], 42, ['label' => 'no code']]);

    expect(Locales::default())->toBe(['en', 'he', 'fa'])
        ->and(Locales::name('en'))->toBe('en')
        ->and(Locales::name('he'))->toBe('עברית')
        ->and(Locales::isRtl('he'))->toBeTrue()
        ->and(Locales::isRtl('fa'))->toBeTrue();
});

it('can set explicit locales', function () {
    $this->input->locales(['en', 'fr']);

    expect($this->input->getLocales())->toBe(['en', 'fr']);
});

it('can set locales from a closure', function () {
    $this->input->locales(fn () => ['de']);

    expect($this->input->getLocales())->toBe(['de']);
});

it('resolves the active locale', function () {
    app()->setLocale('ar');

    expect($this->input->getActiveLocale())->toBe('ar');

    $this->input->activeLocale('ckb');

    expect($this->input->getActiveLocale())->toBe('ckb');

    $this->input->activeLocale('fr');

    expect($this->input->getActiveLocale())->toBe('ar');

    app()->setLocale('fr');

    expect($this->input->getActiveLocale())->toBe('en');
});

it('is single-line by default', function () {
    expect($this->input->isMultiline())->toBeFalse()
        ->and($this->input->toArray()['multiline'])->toBeFalse();
});

it('can be multiline with rows', function () {
    $this->input->multiline()->rows(5);

    $props = $this->input->toArray();

    expect($this->input->isMultiline())->toBeTrue()
        ->and($props['multiline'])->toBeTrue()
        ->and($props['rows'])->toBe(5);
});

it('exposes locale metadata to the frontend', function () {
    $props = $this->input->toArray();

    expect($props['locales'])->toBe([
        ['code' => 'en', 'name' => 'English', 'label' => 'EN', 'direction' => 'ltr'],
        ['code' => 'ar', 'name' => 'العربية', 'label' => 'AR', 'direction' => 'rtl'],
        ['code' => 'ckb', 'name' => 'کوردی', 'label' => 'CKB', 'direction' => 'rtl'],
    ])->and($props['activeLocale'])->toBe('en');
});

it('supports a custom label and defaults for missing metadata', function () {
    config()->set('laravilt-forms.locales', [
        'ckb' => ['name' => 'سۆرانی', 'direction' => 'rtl', 'label' => 'KU'],
        'xx' => ['direction' => 'rtl'],
        'fr',
    ]);

    expect($this->input->getLocales())->toBe(['ckb', 'xx', 'fr'])
        ->and($this->input->toArray()['locales'])->toBe([
            ['code' => 'ckb', 'name' => 'سۆرانی', 'label' => 'KU', 'direction' => 'rtl'],
            ['code' => 'xx', 'name' => 'xx', 'label' => 'XX', 'direction' => 'rtl'],
            ['code' => 'fr', 'name' => 'fr', 'label' => 'FR', 'direction' => 'ltr'],
        ]);
});

it('takes direction only from config', function () {
    config()->set('laravilt-forms.locales', ['ar' => ['direction' => 'ltr'], 'he']);

    expect(Locales::direction('ar'))->toBe('ltr')
        ->and(Locales::isRtl('ar'))->toBeFalse()
        ->and(Locales::direction('he'))->toBe('ltr');
});

it('accepts metadata shaped locales on the field', function () {
    $this->input->locales(['fr' => ['name' => 'Français'], 'de']);

    expect($this->input->getLocales())->toBe(['fr', 'de']);
});

it('derives labels from regional codes', function () {
    config()->set('laravilt-forms.locales', ['ar_EG' => ['name' => 'العربية', 'direction' => 'rtl'], 'en-US']);

    expect(Locales::direction('ar_EG'))->toBe('rtl')
        ->and(Locales::direction('en-US'))->toBe('ltr')
        ->and(Locales::label('en-US'))->toBe('EN')
        ->and(Locales::label('ar_EG'))->toBe('AR')
        ->and(Locales::name('ar_EG'))->toBe('العربية');
});

it('builds nullable validation rules per locale by default', function () {
    expect($this->input->getLocaleValidationRules())->toBe([
        'name' => ['nullable', 'array'],
        'name.en' => ['nullable', 'string'],
        'name.ar' => ['nullable', 'string'],
        'name.ckb' => ['nullable', 'string'],
    ]);
});

it('requires every locale when the field is required', function () {
    $this->input->required()->maxLength(50);

    expect($this->input->getLocaleValidationRules())->toBe([
        'name' => ['required', 'array'],
        'name.en' => ['required', 'string', 'max:50'],
        'name.ar' => ['required', 'string', 'max:50'],
        'name.ckb' => ['required', 'string', 'max:50'],
    ]);
});

it('can restrict the required locales', function () {
    $this->input->required()->requiredLocales(['en', 'fr']);

    expect($this->input->getRequiredLocales())->toBe(['en'])
        ->and($this->input->getLocaleValidationRules())->toBe([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string'],
            'name.ar' => ['nullable', 'string'],
            'name.ckb' => ['nullable', 'string'],
        ]);
});

it('appends custom rules to every locale', function () {
    $this->input->rules(['min:2']);

    expect($this->input->getLocaleRules()['ar'])->toBe(['nullable', 'string', 'min:2']);
});

it('exposes one rule list per field for schema consumers', function () {
    $this->input->required()->requiredLocales(['en'])->maxLength(50);

    $rules = $this->input->getValidationRules();

    expect($rules[0])->toBe('required')
        ->and($rules[1])->toBe('array')
        ->and($rules[2])->toBeInstanceOf(TranslationsRule::class)
        ->and($rules[2]->getRules())->toBe([
            'en' => ['required', 'string', 'max:50'],
            'ar' => ['nullable', 'string', 'max:50'],
            'ckb' => ['nullable', 'string', 'max:50'],
        ])
        ->and(json_decode(json_encode($rules), true)[2])->toBe($rules[2]->getRules());
});

it('reports per-locale errors when validated as a schema field', function () {
    $this->input->label('Name')->required()->requiredLocales(['en'])->maxLength(5);

    $validator = validator(
        ['name' => ['en' => '', 'ar' => 'طويل جدا', 'ckb' => '']],
        ['name' => $this->input->getValidationRules()],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toBe(['name.en', 'name.ar'])
        ->and($validator->errors()->first('name.en'))->toBe('The Name (EN) field is required.')
        ->and($validator->errors()->first('name.ar'))->toBe('The Name (AR) field must not be greater than 5 characters.');
});

it('passes validation when every required locale is filled', function () {
    $this->input->required()->requiredLocales(['en']);

    $validator = validator(
        ['name' => ['en' => 'Hello', 'ar' => '', 'ckb' => '']],
        ['name' => $this->input->getValidationRules()],
    );

    expect($validator->passes())->toBeTrue();
});

it('uses custom messages per locale rule', function () {
    $this->input->required()->requiredLocales(['en'])->validationMessages(['required' => 'Fill me in']);

    $validator = validator(['name' => ['en' => '']], ['name' => $this->input->getValidationRules()]);

    expect($validator->errors()->first('name.en'))->toBe('Fill me in');
});

it('names nested locale attributes', function () {
    $this->input->label('Name');

    expect($this->input->getValidationAttributes())->toBe([
        'name.en' => 'Name (EN)',
        'name.ar' => 'Name (AR)',
        'name.ckb' => 'Name (CKB)',
    ]);
});

it('hydrates from a json string', function () {
    $state = $this->input->hydrateState('{"en":"Hello","ar":"مرحبا"}');

    expect($state)->toBe(['en' => 'Hello', 'ar' => 'مرحبا', 'ckb' => '']);
});

it('hydrates from an array and fills missing locales', function () {
    expect($this->input->hydrateState(['ckb' => 'سڵاو']))
        ->toBe(['en' => '', 'ar' => '', 'ckb' => 'سڵاو']);
});

it('hydrates null to empty strings for every locale', function () {
    expect($this->input->hydrateState(null))->toBe(['en' => '', 'ar' => '', 'ckb' => '']);
});

it('hydrates a plain string into the active locale', function () {
    app()->setLocale('ar');

    expect($this->input->hydrateState('مرحبا'))->toBe(['en' => '', 'ar' => 'مرحبا', 'ckb' => '']);
});

it('dehydrates to an array with every locale present', function () {
    expect($this->input->dehydrateState('{"en":"Hello"}'))->toBe(['en' => 'Hello', 'ar' => '', 'ckb' => ''])
        ->and($this->input->dehydrateState(['en' => 'Hi', 'fr' => 'Salut']))->toBe(['en' => 'Hi', 'ar' => '', 'ckb' => '']);
});

it('runs custom hydrate and dehydrate callbacks', function () {
    $this->input
        ->hydrateStateUsing(fn ($state) => ['en' => strtoupper((string) $state)])
        ->dehydrateStateUsing(fn ($state) => json_encode($state));

    expect($this->input->hydrateState('hello'))->toBe(['en' => 'HELLO', 'ar' => '', 'ckb' => ''])
        ->and($this->input->dehydrateState(['en' => 'Hi']))->toBe('{"en":"Hi","ar":"","ckb":""}');
});

it('serializes the value as a locale keyed array', function () {
    $this->input->state('{"en":"Hello"}');

    $props = $this->input->toArray();

    expect($props['value'])->toBe(['en' => 'Hello', 'ar' => '', 'ckb' => ''])
        ->and($props['requiredLocales'])->toBe([])
        ->and($props['maxLength'])->toBeNull();
});

it('supports method chaining', function () {
    $result = $this->input
        ->multiline()
        ->rows(4)
        ->locales(['en'])
        ->activeLocale('en')
        ->requiredLocales(['en'])
        ->maxLength(10);

    expect($result)->toBe($this->input);
});
