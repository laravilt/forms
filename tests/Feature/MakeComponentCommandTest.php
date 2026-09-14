<?php

use Illuminate\Support\Facades\File;

afterEach(function () {
    File::delete(app_path('Forms/Components/RatingStars.php'));
    File::delete(resource_path('js/components/forms/rating-stars.tsx'));
    File::delete(resource_path('js/components/forms/rating-stars.vue'));
});

it('generates a React component with --react', function () {
    $this->artisan('make:form-component', ['name' => 'RatingStars', '--react' => true])
        ->assertSuccessful();

    $path = resource_path('js/components/forms/rating-stars.tsx');

    expect(File::exists(app_path('Forms/Components/RatingStars.php')))->toBeTrue()
        ->and(File::exists($path))->toBeTrue()
        ->and(File::get($path))
        ->toContain('export default function RatingStars(')
        ->toContain('onUpdateModelValue?.(next);')
        ->not->toContain('{{ componentName }}');
});

it('generates a Vue component with --vue', function () {
    $this->artisan('make:form-component', ['name' => 'RatingStars', '--vue' => true])
        ->assertSuccessful();

    expect(File::exists(resource_path('js/components/forms/rating-stars.vue')))->toBeTrue()
        ->and(File::exists(resource_path('js/components/forms/rating-stars.tsx')))->toBeFalse();
});
