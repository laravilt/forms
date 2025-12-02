# Laravilt Forms Documentation

Complete form builder system with 30+ field types, validation, and Blade/Inertia.js integration for Laravilt.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Architecture](#architecture)
3. [Form Generation](#form-generation)
4. [Field Types](#field-types)
5. [API Reference](#api-reference)
6. [MCP Server Integration](mcp-server.md)

## Overview

Laravilt Forms provides a comprehensive form building system with:

- **30+ Field Types**: Text, select, date, file upload, rich editor, and more
- **Fluent Builder**: Chainable methods for clean, readable form definitions
- **Validation**: Built-in Laravel validation integration
- **Blade Components**: Pre-built UI components with Reka UI styling
- **Inertia Integration**: Seamless Vue 3 form handling
- **Real-time Reactivity**: Dynamic field visibility, options, and validation
- **File Management**: Advanced file upload with preview and validation
- **Rich Content**: WYSIWYG editor, Markdown editor, and code editor support

## Quick Start

```bash
# Generate a new form class
php artisan make:form UserForm

# Generate a resource form (CRUD)
php artisan make:form UserForm --resource

# Use in Blade (server-side)
<x-laravilt::form :schema="$form" />

# Use in Inertia (client-side)
<Form :schema="form.schema" />
```

## Key Features

### 📝 Basic Fields
- **TextInput**: Single-line text input with validation
- **Textarea**: Multi-line text input
- **NumberField**: Numeric input with min/max
- **Select**: Dropdown select with search
- **Checkbox**: Single checkbox
- **CheckboxList**: Multiple checkboxes
- **Radio**: Radio button group
- **Toggle**: Switch/toggle button
- **ToggleButtons**: Button group toggle
- **Hidden**: Hidden field

### 📅 Date & Time
- **DatePicker**: Single date selection
- **DateTimePicker**: Date and time selection
- **TimePicker**: Time selection
- **DateRangePicker**: Date range selection

### 🎨 Advanced Inputs
- **ColorPicker**: Color selection with swatches
- **IconPicker**: Lucide icon picker
- **FileUpload**: File/image upload with preview
- **RichEditor**: WYSIWYG editor (TipTap)
- **MarkdownEditor**: Markdown editor with preview
- **CodeEditor**: Syntax-highlighted code editor (CodeMirror)
- **TagsInput**: Multiple tags input
- **KeyValue**: Key-value pair editor
- **Slider**: Range slider
- **RateInput**: Star rating input
- **PinInput**: PIN/OTP input

### 🔄 Dynamic Fields
- **Repeater**: Repeatable field groups
- **Builder**: Block-based content builder

### 🎯 Field Features
- Label and placeholder
- Help text and hints
- Required indicator
- Validation rules
- Disabled and readonly states
- Conditional visibility
- Default values
- Dependent fields
- Custom prefixes/suffixes
- Inline layout

## System Requirements

- PHP 8.3+
- Laravel 12+
- Blade or Inertia.js v2+
- Vue 3 (for Inertia)

## Installation

```bash
composer require laravilt/forms
```

The service provider is auto-discovered and will register automatically.

## Configuration

Publish the configuration:

```bash
php artisan vendor:publish --tag=laravilt-forms-config
```

Publish the views:

```bash
php artisan vendor:publish --tag=laravilt-forms-views
```

Publish the assets:

```bash
php artisan vendor:publish --tag=laravilt-forms-assets
```

## Basic Usage

### Creating a Form Class

```php
<?php

namespace App\Forms;

use Laravilt\Forms\Form;
use Laravilt\Forms\Components\TextInput;
use Laravilt\Forms\Components\Textarea;
use Laravilt\Forms\Components\Select;
use Laravilt\Forms\Concerns\HasSchema;

class UserForm extends Form
{
    use HasSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schema([
            TextInput::make('name')
                ->label('Full Name')
                ->required()
                ->placeholder('Enter your name'),

            TextInput::make('email')
                ->label('Email Address')
                ->email()
                ->required()
                ->unique('users', 'email'),

            Textarea::make('bio')
                ->label('Biography')
                ->rows(5)
                ->placeholder('Tell us about yourself'),

            Select::make('role')
                ->label('Role')
                ->options([
                    'admin' => 'Administrator',
                    'editor' => 'Editor',
                    'viewer' => 'Viewer',
                ])
                ->required(),
        ]);
    }
}
```

### Using in a Controller

```php
use App\Forms\UserForm;
use Inertia\Inertia;

public function create()
{
    $form = UserForm::make();

    return Inertia::render('Users/Create', [
        'form' => $form->toLaraviltProps(),
    ]);
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'bio' => 'nullable|string',
        'role' => 'required|in:admin,editor,viewer',
    ]);

    User::create($validated);

    return redirect()->route('users.index');
}
```

### Using in Blade

```blade
<x-laravilt::form
    :schema="$form->getSchema()"
    action="/users"
    method="POST"
/>
```

### Using in Vue (Inertia)

```vue
<template>
  <div>
    <Form
      :schema="form.schema"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import Form from '@/components/forms/Form.vue'

const props = defineProps({
  form: Object
})

const form = useForm({})

const handleSubmit = () => {
  form.post('/users')
}
</script>
```

## Field Types Reference

### TextInput

```php
TextInput::make('name')
    ->label('Name')
    ->placeholder('Enter name')
    ->required()
    ->minLength(3)
    ->maxLength(255)
    ->prefix('Mr.')
    ->suffix('@example.com')
    ->helperText('Enter your full name');
```

### Select

```php
Select::make('country')
    ->label('Country')
    ->options([
        'us' => 'United States',
        'uk' => 'United Kingdom',
        'ca' => 'Canada',
    ])
    ->searchable()
    ->required()
    ->placeholder('Select a country');
```

### DatePicker

```php
DatePicker::make('birth_date')
    ->label('Birth Date')
    ->required()
    ->minDate(now()->subYears(100))
    ->maxDate(now()->subYears(18))
    ->displayFormat('F j, Y');
```

### FileUpload

```php
FileUpload::make('avatar')
    ->label('Profile Picture')
    ->image()
    ->maxSize(2048) // 2MB
    ->acceptedFileTypes(['image/jpeg', 'image/png'])
    ->imagePreview()
    ->imageCropAspectRatio('1:1')
    ->imageResizeTargetWidth('500')
    ->imageResizeTargetHeight('500');
```

### RichEditor

```php
RichEditor::make('content')
    ->label('Content')
    ->required()
    ->toolbarButtons([
        'bold',
        'italic',
        'underline',
        'link',
        'bulletList',
        'orderedList',
        'heading',
    ])
    ->minLength(100);
```

### Repeater

```php
use Laravilt\Forms\Components\Repeater;

Repeater::make('contacts')
    ->label('Contacts')
    ->schema([
        TextInput::make('name')
            ->label('Name')
            ->required(),
        TextInput::make('email')
            ->label('Email')
            ->email()
            ->required(),
        TextInput::make('phone')
            ->label('Phone'),
    ])
    ->minItems(1)
    ->maxItems(5)
    ->addActionLabel('Add Contact')
    ->reorderable()
    ->collapsible();
```

### KeyValue

```php
KeyValue::make('metadata')
    ->label('Metadata')
    ->keyLabel('Key')
    ->valueLabel('Value')
    ->addActionLabel('Add Field')
    ->reorderable();
```

### ColorPicker

```php
ColorPicker::make('brand_color')
    ->label('Brand Color')
    ->required()
    ->swatches([
        '#FF0000',
        '#00FF00',
        '#0000FF',
    ]);
```

## Validation

### Built-in Rules

```php
TextInput::make('email')
    ->required()
    ->email()
    ->unique('users', 'email')
    ->confirmed();

NumberField::make('age')
    ->required()
    ->min(18)
    ->max(100);

TextInput::make('password')
    ->required()
    ->minLength(8)
    ->maxLength(255)
    ->password()
    ->confirmed();
```

### Custom Validation Rules

```php
TextInput::make('username')
    ->rules(['required', 'string', 'alpha_dash', 'unique:users']);
```

## Conditional Fields

### Visibility Based on Another Field

```php
use Laravilt\Support\Utilities\Get;

Select::make('country')
    ->label('Country')
    ->options([
        'us' => 'United States',
        'ca' => 'Canada',
    ])
    ->live(), // Make field reactive

Select::make('state')
    ->label('State')
    ->options(fn (Get $get) =>
        $get('country') === 'us'
            ? ['ca' => 'California', 'ny' => 'New York']
            : ['on' => 'Ontario', 'bc' => 'British Columbia']
    )
    ->visible(fn (Get $get) => in_array($get('country'), ['us', 'ca']))
    ->required();
```

### After State Updated

```php
use Laravilt\Support\Utilities\Set;

Select::make('product_type')
    ->label('Product Type')
    ->options([
        'physical' => 'Physical Product',
        'digital' => 'Digital Product',
    ])
    ->afterStateUpdated(function ($value, Set $set) {
        if ($value === 'digital') {
            $set('requires_shipping', false);
        }
    })
    ->live();
```

## Generator Commands

```bash
# Generate a basic form
php artisan make:form UserForm

# Generate a resource form with CRUD operations
php artisan make:form UserForm --resource

# Force overwrite existing file
php artisan make:form UserForm --force

# Generate a form component (custom field)
php artisan make:component CustomField
```

## Best Practices

1. **Use Form Classes**: Create dedicated form classes for reusability
2. **Validate on Server**: Always validate on server-side even with client-side validation
3. **Use Appropriate Field Types**: Choose the right field type for better UX
4. **Add Helper Text**: Provide clear instructions with helper text
5. **Group Related Fields**: Use sections to organize related fields
6. **Mark Required Fields**: Always indicate required fields
7. **Provide Feedback**: Show validation errors clearly
8. **Test Accessibility**: Ensure forms are keyboard-navigable

## Examples

### Login Form

```php
use Laravilt\Forms\Form;
use Laravilt\Forms\Components\TextInput;

class LoginForm extends Form
{
    use HasSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schema([
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->autofocus(),

            TextInput::make('password')
                ->label('Password')
                ->password()
                ->required(),

            Checkbox::make('remember')
                ->label('Remember me'),
        ]);
    }
}
```

### Product Form

```php
use Laravilt\Forms\Components\*;
use Laravilt\Schemas\Components\Section;

class ProductForm extends Form
{
    use HasSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schema([
            Section::make('Basic Information')
                ->schema([
                    TextInput::make('name')
                        ->label('Product Name')
                        ->required(),

                    Textarea::make('description')
                        ->label('Description')
                        ->required()
                        ->rows(5),

                    Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->required(),
                ]),

            Section::make('Pricing')
                ->schema([
                    NumberField::make('price')
                        ->label('Price')
                        ->prefix('$')
                        ->required()
                        ->min(0)
                        ->step(0.01),

                    NumberField::make('compare_at_price')
                        ->label('Compare at Price')
                        ->prefix('$')
                        ->min(0)
                        ->step(0.01),

                    Toggle::make('is_taxable')
                        ->label('Taxable')
                        ->default(true),
                ]),

            Section::make('Inventory')
                ->schema([
                    NumberField::make('quantity')
                        ->label('Quantity')
                        ->required()
                        ->min(0)
                        ->default(0),

                    Toggle::make('track_inventory')
                        ->label('Track Inventory')
                        ->default(true),
                ]),

            Section::make('Media')
                ->schema([
                    FileUpload::make('images')
                        ->label('Product Images')
                        ->image()
                        ->multiple()
                        ->maxFiles(5)
                        ->maxSize(5120)
                        ->imagePreview()
                        ->reorderable(),
                ]),
        ]);
    }
}
```

## Available Components

### Basic
- TextInput
- Textarea
- NumberField
- Select
- Checkbox
- CheckboxList
- Radio
- Toggle
- ToggleButtons
- Hidden

### Date & Time
- DatePicker
- DateTimePicker
- TimePicker
- DateRangePicker

### Advanced
- ColorPicker
- IconPicker
- FileUpload
- RichEditor
- MarkdownEditor
- CodeEditor
- TagsInput
- KeyValue
- Slider
- RateInput
- PinInput

### Dynamic
- Repeater
- Builder

## Support

- GitHub Issues: github.com/laravilt/forms
- Documentation: docs.laravilt.com
- Discord: discord.laravilt.com
