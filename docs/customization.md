# Customization Guide

## Overview

Laravilt Forms provides multiple ways to customize the appearance and behavior of form components to match your application's design system.

## Styling Components

### Using Tailwind CSS Classes

Add custom classes to any component:

```php
TextInput::make('name')
    ->extraAttributes([
        'class' => 'custom-input-class',
    ])
```

### Component-Specific Styling

```php
Select::make('country')
    ->extraAttributes([
        'class' => 'w-full md:w-1/2',
    ])
```

## Theme Customization

### Colors

Customize colors through Tailwind configuration:

```js
// tailwind.config.js
module.exports = {
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#f0f9ff',
                    500: '#3b82f6',
                    600: '#2563eb',
                    // ...
                },
            },
        },
    },
}
```

### Dark Mode

All components support dark mode automatically:

```html
<html class="dark">
    <!-- Components adapt to dark mode -->
</html>
```

Customize dark mode colors:

```css
.dark .form-input {
    @apply bg-gray-800 text-white border-gray-700;
}
```

## Custom Component Templates

### Publishing Views

Publish the package views for customization:

```bash
php artisan vendor:publish --tag=laravilt-forms-views
```

Views will be copied to `resources/views/vendor/laravilt-forms/`.

### Customizing Field Wrapper

Edit `resources/views/vendor/laravilt-forms/components/field-wrapper.blade.php`:

```blade
<div class="custom-field-wrapper">
    @if($label)
        <label class="custom-label">
            {{ $label }}
            @if($required)
                <span class="custom-required">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if($helperText)
        <p class="custom-helper-text">{{ $helperText }}</p>
    @endif

    @error($name)
        <span class="custom-error">{{ $message }}</span>
    @enderror
</div>
```

### Customizing Individual Components

Each component has its own Blade template that can be customized:

- `fields/text-input.blade.php`
- `fields/select.blade.php`
- `fields/date-picker.blade.php`
- etc.

## Custom Translations

### Adding New Languages

1. Publish language files:

```bash
php artisan vendor:publish --tag=laravilt-forms-lang
```

2. Create new language file:

```bash
resources/lang/vendor/laravilt-forms/fr/fields.php
```

3. Add translations:

```php
return [
    'placeholder' => [
        'search' => 'Rechercher...',
        'select' => 'Sélectionner...',
    ],
    'actions' => [
        'upload' => 'Télécharger',
        'remove' => 'Supprimer',
    ],
    // ...
];
```

### Overriding Existing Translations

Edit published translation files in `resources/lang/vendor/laravilt-forms/`.

## Custom Field Components

### Creating a Custom Field

1. Create a new component class:

```php
namespace App\Forms\Components;

use Laravilt\Forms\Components\Field;

class PhoneInput extends Field
{
    protected string $view = 'forms.components.phone-input';

    protected ?string $countryCode = null;

    public function countryCode(string $code): static
    {
        $this->countryCode = $code;
        return $this;
    }

    public function toLaraviltProps(): array
    {
        return array_merge(parent::toLaraviltProps(), [
            'countryCode' => $this->countryCode,
        ]);
    }
}
```

2. Create the Blade view:

```blade
{{-- resources/views/forms/components/phone-input.blade.php --}}
<x-laravilt-forms::field-wrapper
    :name="$name"
    :label="$label"
    :required="$required"
    :helper-text="$helperText"
>
    <div class="flex gap-2">
        <select class="w-24" wire:model="countryCode">
            <option value="+1">+1</option>
            <option value="+44">+44</option>
            <!-- ... -->
        </select>
        <input
            type="tel"
            name="{{ $name }}"
            class="flex-1"
            {{ $attributes }}
        />
    </div>
</x-laravilt-forms::field-wrapper>
```

3. Use your custom component:

```php
use App\Forms\Components\PhoneInput;

PhoneInput::make('phone')
    ->countryCode('+1')
    ->required()
```

## Extending Existing Components

### Using Inheritance

```php
namespace App\Forms\Components;

use Laravilt\Forms\Components\TextInput as BaseTextInput;

class CustomTextInput extends BaseTextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extraAttributes([
            'class' => 'custom-default-class',
        ]);
    }

    public function myCustomMethod(): static
    {
        // Add custom functionality
        return $this;
    }
}
```

## JavaScript Customization

### Customizing Vue Components

1. Publish the Vue components:

```bash
php artisan vendor:publish --tag=laravilt-forms-components
```

2. Customize in `resources/js/vendor/laravilt-forms/`.

3. Update your import paths:

```js
// resources/js/app.js
import DatePicker from './vendor/laravilt-forms/components/DatePicker.vue'
```

### Adding Custom Behavior

Extend component functionality using Vue composables:

```vue
<script setup>
import { ref, watch } from 'vue'
import { useDatePicker } from '@/composables/useDatePicker'

const props = defineProps({
    // ...
})

const { selectedDate, formatDate } = useDatePicker()

// Custom behavior
watch(selectedDate, (newDate) => {
    console.log('Date changed:', formatDate(newDate))
})
</script>
```

## Configuration Customization

### Environment-Based Configuration

Use environment variables in your config:

```php
// config/laravilt-forms.php
return [
    'file_upload' => [
        'max_file_size' => env('FORMS_MAX_FILE_SIZE', 10240),
        'allowed_mime_types' => explode(',', env('FORMS_ALLOWED_MIMES', 'image/*,application/pdf')),
    ],
];
```

### Per-Environment Configs

```env
# .env.production
FORMS_MAX_FILE_SIZE=5120
FORMS_ALLOWED_MIMES=image/jpeg,image/png,application/pdf

# .env.development
FORMS_MAX_FILE_SIZE=51200
FORMS_ALLOWED_MIMES=*
```

## Icon Customization

### Using Custom Icon Sets

Configure your preferred icon library:

```php
TextInput::make('search')
    ->prefixIcon('fa-search')  // FontAwesome
    ->suffixIcon('bi-x')       // Bootstrap Icons
```

### Custom Icon Component

```php
// In AppServiceProvider
use Laravilt\Forms\Components\TextInput;

TextInput::configureUsing(function (TextInput $input) {
    // Default icon class
    $input->iconClass('w-5 h-5');
});
```

## Responsive Design

### Mobile-First Customization

```php
Grid::make()
    ->columns(1)  // Mobile: 1 column
    ->columns('sm', 2)  // Small screens: 2 columns
    ->columns('md', 3)  // Medium screens: 3 columns
    ->columns('lg', 4)  // Large screens: 4 columns
```

### Conditional Rendering

```php
TextInput::make('desktop_only')
    ->hiddenOn('mobile')
    ->visibleOn(['tablet', 'desktop'])
```

## Performance Optimization

### Lazy Loading Components

```php
FileUpload::make('images')
    ->multiple()
    ->lazyLoad()  // Load only when visible
```

### Debouncing Validation

```php
TextInput::make('username')
    ->unique('users')
    ->reactive()
    ->debounce(500)  // Wait 500ms
```

## Best Practices

### 1. Use Component Macros for Reusability

```php
// In AppServiceProvider
use Laravilt\Forms\Components\TextInput;

TextInput::macro('phone', function () {
    return $this
        ->type('tel')
        ->mask('(999) 999-9999')
        ->placeholder('(123) 456-7890');
});

// Usage
TextInput::make('phone')->phone()
```

### 2. Create Form Presets

```php
class FormPresets
{
    public static function addressFields(): array
    {
        return [
            TextInput::make('street')->required(),
            TextInput::make('city')->required(),
            Select::make('state')->options(/* ... */)->required(),
            TextInput::make('zip')->required(),
        ];
    }
}

// Usage
$schema = [
    ...FormPresets::addressFields(),
];
```

### 3. Consistent Styling

Define global styling in a service provider:

```php
use Laravilt\Forms\Components\TextInput;
use Laravilt\Forms\Components\Select;

TextInput::configureUsing(function (TextInput $input) {
    $input->extraAttributes(['class' => 'my-input-class']);
});

Select::configureUsing(function (Select $select) {
    $select->searchable();
});
```
