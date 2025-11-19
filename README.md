![Screenshot](https://raw.githubusercontent.com/laravilt/forms/master/arts/cover.jpg)

# Laravilt Forms

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravilt/forms.svg?style=flat-square)](https://packagist.org/packages/laravilt/forms)
[![Total Downloads](https://img.shields.io/packagist/dt/laravilt/forms.svg?style=flat-square)](https://packagist.org/packages/laravilt/forms)

Beautiful, accessible, and feature-rich form components for Laravel applications. Built with Vue.js 3, Tailwind CSS 4, and reka-ui components.

## Features

- 🎨 **Beautiful UI** - Modern design with Tailwind CSS 4
- ♿ **Accessible** - WCAG compliant with ARIA attributes
- 🌐 **RTL Support** - Full right-to-left language support
- 📱 **Responsive** - Mobile-first design
- 🔒 **Validation** - Built-in Laravel validation with custom rules
- 🌍 **i18n** - Multi-language support (English, Arabic)
- 🚀 **Performance** - Optimized with lazy loading
- 🎯 **Type Safe** - Full TypeScript support
- 📦 **20+ Components** - Everything you need for forms
- 🔧 **Customizable** - Extensive configuration options

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- Node.js 18.x or higher
- Vue.js 3.x

## Installation

Install via Composer:

```bash
composer require laravilt/forms
```

Install JavaScript dependencies:

```bash
npm install
```

## Quick Start

### Basic Usage

```php
use Laravilt\Forms\Components\TextInput;
use Laravilt\Forms\Components\Select;
use Laravilt\Forms\Components\DatePicker;

$fields = [
    TextInput::make('name')
        ->label('Full Name')
        ->required()
        ->placeholder('Enter your name'),

    TextInput::make('email')
        ->label('Email Address')
        ->email()
        ->required(),

    Select::make('country')
        ->label('Country')
        ->options([
            'us' => 'United States',
            'uk' => 'United Kingdom',
            'ca' => 'Canada',
        ])
        ->searchable(),

    DatePicker::make('birth_date')
        ->label('Date of Birth')
        ->maxDate(now()->subYears(18)),
];
```

### Render in Blade

```blade
<x-laravilt-forms::form
    :schema="$fields"
    action="/submit"
    method="POST"
/>
```

## Available Components

### Input Components
- **TextInput** - Single-line text with prefix/suffix, masking, character count
- **Textarea** - Multi-line text with auto-resize, word/character count
- **Hidden** - Hidden field for storing invisible data

### Choice Components
- **Select** - Dropdown with search, multiple selection, grouping
- **Checkbox** - Single checkbox or checkbox list
- **CheckboxList** - Multiple checkboxes with options
- **Radio** - Radio button group with boolean option
- **Toggle** - Toggle switch with custom labels/colors
- **ToggleButtons** - Button-style toggle group

### Date & Time Components
- **DatePicker** - Date selection with month/year dropdowns, centered styling
- **DateTimePicker** - Date and time selection with 24h format support
- **DateRangePicker** - Date range with connected selection highlighting
- **TimePicker** - Simple time selection

### File Components
- **FileUpload** - File upload with drag-drop, preview, multiple files, validation

### Special Components
- **ColorPicker** - Color selection with presets
- **TagsInput** - Tag input with autocomplete
- **KeyValue** - Key-value pair input
- **Repeater** - Repeatable field groups
- **RichEditor** - Rich text WYSIWYG editor
- **MarkdownEditor** - Markdown editor with preview
- **CodeEditor** - Code editor with syntax highlighting
- **Slider** - Range slider
- **RateInput** - Star rating input
- **Builder** - Dynamic form builder

## Date & Time Components (New Features!)

All date/time components now include:
- **Month/Year Dropdowns** - Quick navigation (50 years back, 10 years forward)
- **Centered Date Text** - Properly aligned date cells
- **Enhanced Styling** - Beautiful select dropdowns with hover states
- **24h Time Format** - Configurable time format in DateTimePicker
- **Connected Range** - Visual connection between selected dates in DateRangePicker

### DatePicker Example

```php
DatePicker::make('appointment_date')
    ->label('Appointment Date')
    ->minDate(now())
    ->maxDate(now()->addDays(30))
    ->placeholder('Select date')
    ->required()
```

### DateTimePicker Example

```php
DateTimePicker::make('meeting_time')
    ->label('Meeting Time')
    ->hourCycle(24)  // 24-hour format
    ->minDate(now()->toDateTimeString())
    ->required()
```

### DateRangePicker Example

```php
DateRangePicker::make('vacation_dates')
    ->label('Vacation Period')
    ->numberOfMonths(2)
    ->closeOnSelect(false)
    ->minDate(now())
    ->required()
```

## Validation

Comprehensive validation support with shortcuts:

```php
TextInput::make('email')
    ->email()
    ->required()
    ->minLength(5)
    ->maxLength(100)
    ->unique('users', 'email')
    ->validationMessages([
        'email.unique' => 'This email is already registered',
    ])
```

### Available Validation Shortcuts

```php
->required()
->email()
->url()
->numeric()
->integer()
->min($value)
->max($value)
->minLength($length)
->maxLength($length)
->alpha()
->alphaNum()
->alphaDash()
->unique($table, $column)
->exists($table, $column)
->confirmed()
->regex($pattern)
```

## Working with Schemas

Use with the `laravilt/schemas` package for advanced layouts:

```php
use Laravilt\Schemas\Components\Section;
use Laravilt\Schemas\Components\Grid;
use Laravilt\Schemas\Components\Tabs;

$schema = Tabs::make('user_form')
    ->tabs([
        Tab::make('Personal Info')
            ->schema([
                Section::make('basic')
                    ->heading('Basic Information')
                    ->schema([
                        Grid::make()->columns(2)->schema([
                            TextInput::make('first_name')->required(),
                            TextInput::make('last_name')->required(),
                        ]),
                    ]),
            ]),
    ]);
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=laravilt-forms-config
```

Customize in `config/laravilt-forms.php`:

```php
return [
    'validation' => [
        'show_errors_inline' => true,
        'show_required_asterisk' => true,
    ],
    'file_upload' => [
        'max_file_size' => 10240,  // 10MB
        'default_disk' => 'public',
    ],
    'date_time' => [
        'default_format' => 'Y-m-d',
        'locale' => 'en',
    ],
];
```

## Customization

### Publishing Views

```bash
php artisan vendor:publish --tag=laravilt-forms-views
```

### Publishing Translations

```bash
php artisan vendor:publish --tag=laravilt-forms-lang
```

### Custom Components

Create custom field components:

```php
namespace App\Forms\Components;

use Laravilt\Forms\Components\Field;

class PhoneInput extends Field
{
    protected string $view = 'forms.components.phone-input';

    public function countryCode(string $code): static
    {
        $this->countryCode = $code;
        return $this;
    }
}
```

## Documentation

Comprehensive documentation is available in the `docs/` directory:

- **[Getting Started](docs/getting-started.md)** - Installation and basic usage
- **[Component Reference](docs/COMPONENTS.md)** - Detailed component documentation
- **[Validation Guide](docs/validation.md)** - Validation rules and custom messages
- **[Customization](docs/customization.md)** - Theming, templates, and custom components

## Testing

Run tests:

```bash
composer test
```

## Code Style

Format code:

```bash
composer format
```

or

```bash
vendor/bin/pint
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security issues, please email security@laravilt.com instead of using the issue tracker.

## Credits

- **Laravilt Team**
- Built with [reka-ui](https://reka-ui.com)
- Powered by [Vue.js 3](https://vuejs.org)
- Styled with [Tailwind CSS 4](https://tailwindcss.com)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
