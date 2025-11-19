# Getting Started with Laravilt Forms

## Introduction

Laravilt Forms is a comprehensive form component package for Laravel applications that provides beautiful, accessible, and feature-rich form fields. Built on top of `laravilt/support`, it offers seamless integration with Vue.js and supports multiple platforms including web, API, and Flutter.

## Features

- 🎨 **Beautiful UI** - Modern, customizable components with Tailwind CSS
- ♿ **Accessible** - WCAG compliant with ARIA attributes
- 🌐 **RTL Support** - Full right-to-left language support
- 📱 **Responsive** - Mobile-first design
- 🔒 **Validation** - Built-in Laravel validation with custom rules
- 🌍 **i18n** - Multi-language support (English, Arabic)
- 🚀 **Performance** - Optimized for speed with lazy loading
- 🎯 **Type Safe** - Full TypeScript support on frontend

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- Node.js 18.x or higher
- Vue.js 3.x

## Installation

### 1. Install the Package

```bash
composer require laravilt/forms
```

### 2. Install JavaScript Dependencies

```bash
npm install
```

### 3. Publish Assets (Optional)

Publish the package configuration:

```bash
php artisan vendor:publish --tag=laravilt-forms-config
```

Publish the language files:

```bash
php artisan vendor:publish --tag=laravilt-forms-lang
```

Publish the views (for customization):

```bash
php artisan vendor:publish --tag=laravilt-forms-views
```

## Basic Usage

### Using Components in PHP

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

### Rendering in Blade

```blade
<x-laravilt-forms::form
    :schema="$fields"
    action="/submit"
    method="POST"
/>
```

### Using with Schemas

```php
use Laravilt\Schemas\Components\Section;
use Laravilt\Schemas\Components\Grid;

$schema = Section::make('personal_info')
    ->heading('Personal Information')
    ->description('Enter your personal details')
    ->schema([
        Grid::make()
            ->columns(2)
            ->schema([
                TextInput::make('first_name')->required(),
                TextInput::make('last_name')->required(),
                TextInput::make('email')->email()->required()->columnSpan(2),
                DatePicker::make('birth_date'),
                Select::make('gender')->options([
                    'male' => 'Male',
                    'female' => 'Female',
                ]),
            ]),
    ]);
```

## Configuration

The configuration file `config/laravilt-forms.php` allows you to customize:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Validation
    |--------------------------------------------------------------------------
    |
    | Configure default validation behavior for all form fields.
    |
    */
    'validation' => [
        'show_errors_inline' => true,
        'show_required_asterisk' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload
    |--------------------------------------------------------------------------
    |
    | Configure file upload defaults.
    |
    */
    'file_upload' => [
        'default_disk' => 'public',
        'default_directory' => 'uploads',
        'max_file_size' => 10240, // 10MB in KB
        'allowed_mime_types' => [
            'image/*',
            'application/pdf',
            'application/msword',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Date & Time
    |--------------------------------------------------------------------------
    |
    | Configure date and time picker defaults.
    |
    */
    'date_time' => [
        'default_format' => 'Y-m-d',
        'display_format' => 'M d, Y',
        'time_format' => 'H:i',
        'first_day_of_week' => 0, // 0 = Sunday, 1 = Monday
        'locale' => 'en',
    ],
];
```

## Available Components

### Input Components
- **TextInput** - Single-line text input with prefix/suffix, masking, character count
- **Textarea** - Multi-line text with auto-resize, word/character count
- **Hidden** - Hidden field for storing invisible data

### Choice Components
- **Select** - Dropdown with search, multiple selection, grouping
- **Checkbox** - Single checkbox or checkbox list
- **Radio** - Radio button group with boolean option
- **Toggle** - Toggle switch with custom labels and colors

### Date & Time Components
- **DatePicker** - Date selection with month/year dropdowns
- **DateTimePicker** - Date and time selection (24h format)
- **DateRangePicker** - Date range with connected selection
- **TimePicker** - Time selection

### Special Components
- **FileUpload** - File upload with drag-drop, preview, multiple files
- **ColorPicker** - Color selection with presets
- **TagsInput** - Tag input with autocomplete
- **KeyValue** - Key-value pair input
- **Repeater** - Repeatable field groups
- **RichEditor** - Rich text editor (WYSIWYG)
- **MarkdownEditor** - Markdown editor with preview
- **CodeEditor** - Code editor with syntax highlighting
- **Slider** - Range slider
- **RateInput** - Star rating input

## Next Steps

- [Component Reference](COMPONENTS.md) - Detailed documentation for each component
- [Validation Guide](validation.md) - Learn about validation rules and custom messages
- [Customization](customization.md) - Customize appearance and behavior
- [Advanced Usage](advanced.md) - Advanced features and techniques
