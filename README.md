![Screenshot](https://raw.githubusercontent.com/laravilt/forms/master/arts/cover.jpg)

# Laravilt Forms

Beautiful form components for Laravilt with validation, RTL support, and multi-platform serialization.

## Features

- ✅ **Rich Form Fields** - TextInput, Textarea, Select, Checkbox, Radio, Toggle, DatePicker, FileUpload, and more
- ✅ **Validation** - Built-in Laravel validation with field-level rules
- ✅ **RTL Support** - Full right-to-left language support (Arabic, Hebrew, etc.)
- ✅ **Multi-platform** - Serializes to Laravilt (Blade+Vue), API, and Flutter
- ✅ **Fluent API** - Chainable methods for building forms
- ✅ **Type Safe** - Full PHP 8.3+ type hints
- ✅ **Customizable** - Extensive configuration options

## Installation

You can install the plugin via composer:

```bash
composer require laravilt/forms
```

## Basic Usage

### Using Form Fields with Schema

Form fields are used with the `Schema` class from `laravilt/schemas`:

```php
use Laravilt\Schemas\Schema;
use Laravilt\Forms\Components\TextInput;
use Laravilt\Forms\Components\Textarea;
use Laravilt\Forms\Components\Select;

$form = Schema::make()
    ->schema([
        TextInput::make('name')
            ->label('Full Name')
            ->placeholder('Enter your name')
            ->required()
            ->maxLength(100),

        TextInput::make('email')
            ->email()
            ->required()
            ->placeholder('your@email.com'),

        Textarea::make('bio')
            ->label('Biography')
            ->rows(4)
            ->maxLength(500)
            ->characterCount(),

        Select::make('country')
            ->label('Country')
            ->options([
                'us' => 'United States',
                'uk' => 'United Kingdom',
                'ca' => 'Canada',
            ])
            ->searchable()
            ->required(),
    ]);
```

### Using in Resources

```php
use Filament\Resources\Resource;
use Laravilt\Forms\Components\TextInput;

class UserResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
            ]);
    }
}
```

### Available Components

#### Text Inputs
- `TextInput` - Single-line text input with types (email, password, tel, url, search)
- `Textarea` - Multi-line text input with auto-resize
- `Hidden` - Hidden field

#### Choice Fields
- `Select` - Dropdown select (single/multiple, searchable, native)
- `Checkbox` - Checkbox (single or list)
- `Radio` - Radio buttons
- `Toggle` - Toggle switch (on/off)

#### Date & Time
- `DatePicker` - Date picker with range support
- `TimePicker` - Time picker
- `DateTimePicker` - Combined date and time

#### File Upload
- `FileUpload` - File upload with drag-drop, image preview, validation

### Validation

```php
TextInput::make('email')
    ->email() // Add email validation
    ->required() // Mark as required
    ->minLength(5)
    ->maxLength(100)
    ->unique('users', 'email') // Unique in database
    ->rules('custom_rule'); // Custom validation rules
```

### Field State and Data

```php
// Set field state
TextInput::make('name')
    ->default('John Doe')
    ->state('Jane Doe');

// Get field value
$value = $field->getValue();
```

### Service Provider

The service provider is automatically registered via package discovery. If you need to manually register it:

```php
// config/app.php
'providers' => [
    // ...
    Laravilt\Forms\FormsServiceProvider::class,
],
```

### Filament Plugin (Optional)

If you're using Filament, register the plugin in your Panel provider:

```php
use Laravilt\Forms\FormsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FormsPlugin::make(),
        ]);
}
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="laravilt-forms-config"
```

## Assets

Publish the plugin assets:

```bash
php artisan vendor:publish --tag="laravilt-forms-assets"
```

## Testing

```bash
composer test
```

## Code Style

```bash
composer format
```

## Static Analysis

```bash
composer analyse
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
