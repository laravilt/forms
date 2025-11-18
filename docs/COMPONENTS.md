# Form Components Reference

## Overview

The Laravilt Forms package provides a comprehensive set of form field components built on top of the `laravilt/support` package.

## Base Classes

### Field

**Location**: `src/Components/Field.php`

The base class for all form fields. Provides:
- State management
- Validation
- Required/disabled states
- Placeholder support
- Default values
- Readonly mode
- Autofocus
- Autocomplete
- Extra attributes

**Traits Used**:
- `CanBeDisabled` - Disable field
- `CanBeRequired` - Mark field as required
- `HasDefaultValue` - Set default value
- `HasPlaceholder` - Add placeholder text
- `HasValidation` - Validation rules with shortcuts

---

## Text Input Components

### TextInput

**Location**: `src/Components/TextInput.php`

Single-line text input with extensive features.

**Features**:
- Multiple input types: text, email, password, tel, url, search
- Prefix/suffix icons and text
- Input masking
- Character count
- Min/max length validation
- Pattern validation

**Example**:
```php
TextInput::make('email')
    ->email()
    ->required()
    ->placeholder('your@email.com')
    ->prefixIcon('heroicon-o-envelope')
    ->maxLength(100)
    ->characterCount()
```

---

### Textarea

**Location**: `src/Components/Textarea.php`

Multi-line text input.

**Features**:
- Auto-resize based on content
- Row configuration (min/max)
- Character count
- Word count
- Max length enforcement

**Example**:
```php
Textarea::make('description')
    ->rows(4)
    ->autosize()
    ->minRows(3)
    ->maxRows(10)
    ->maxLength(500)
    ->characterCount()
    ->wordCount()
```

---

### Hidden

**Location**: `src/Components/Hidden.php`

Hidden input field for storing data invisibly.

**Example**:
```php
Hidden::make('user_id')
    ->default(auth()->id())
```

---

## Choice Components

### Select

**Location**: `src/Components/Select.php`

Dropdown select field.

**Features**:
- Single/multiple selection
- Searchable options
- Native or custom dropdown
- Grouped options
- Custom option rendering
- Max items for multiple
- Empty state messages

**Example**:
```php
Select::make('country')
    ->options([
        'us' => 'United States',
        'uk' => 'United Kingdom',
        'ca' => 'Canada',
    ])
    ->searchable()
    ->placeholder('Select a country')
    ->native(false)
```

**Multiple Select**:
```php
Select::make('tags')
    ->options($tags)
    ->multiple()
    ->maxItems(5)
```

---

### Checkbox

**Location**: `src/Components/Checkbox.php`

Checkbox input (single or list).

**Features**:
- Single checkbox
- Checkbox list (multiple options)
- Inline or stacked layout
- Custom checked/unchecked values
- Description text

**Example (Single)**:
```php
Checkbox::make('agree_to_terms')
    ->description('I agree to the terms and conditions')
    ->required()
```

**Example (List)**:
```php
Checkbox::make('permissions')
    ->options([
        'read' => 'Read',
        'write' => 'Write',
        'delete' => 'Delete',
    ])
    ->inline()
```

---

### Radio

**Location**: `src/Components/Radio.php`

Radio button group.

**Features**:
- Radio options
- Inline or stacked layout
- Boolean radio (Yes/No)
- Custom option descriptions

**Example**:
```php
Radio::make('gender')
    ->options([
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
    ])
    ->inline()
```

**Boolean Example**:
```php
Radio::make('is_active')
    ->boolean()
    ->inline()
```

---

### Toggle

**Location**: `src/Components/Toggle.php`

Toggle switch (on/off).

**Features**:
- Custom on/off values
- On/off labels
- On/off icons
- On/off colors

**Example**:
```php
Toggle::make('notifications')
    ->onLabel('Enabled')
    ->offLabel('Disabled')
    ->onIcon('heroicon-o-bell')
    ->offIcon('heroicon-o-bell-slash')
    ->onColor('success')
    ->offColor('gray')
```

---

## Date & Time Components

### DatePicker

**Location**: `src/Components/DatePicker.php`

Date picker with advanced features.

**Features**:
- Single date or date range
- Min/max dates
- Disabled dates
- Custom date format
- Time picker option
- First day of week configuration

**Example**:
```php
DatePicker::make('birth_date')
    ->format('Y-m-d')
    ->displayFormat('M d, Y')
    ->maxDate(now())
    ->required()
```

**Range Example**:
```php
DatePicker::make('event_dates')
    ->range()
    ->minDate(now())
```

---

## File Components

### FileUpload

**Location**: `src/Components/FileUpload.php`

File upload with drag-drop support.

**Features**:
- Single/multiple file upload
- Drag and drop
- File type restrictions
- File size limits
- Image preview
- Avatar mode (circular)
- Custom upload/delete handlers
- Storage configuration

**Example**:
```php
FileUpload::make('avatar')
    ->image()
    ->avatar()
    ->maxSize(1024) // 1MB
    ->directory('avatars')
    ->disk('public')
```

**Multiple Files**:
```php
FileUpload::make('attachments')
    ->multiple()
    ->maxFiles(5)
    ->acceptedFileTypes(['application/pdf', 'image/*'])
    ->maxSize(5120) // 5MB
```

---

## Using Form Components

Form components are designed to work with the `Schema` class from `laravilt/schemas`.

### Basic Usage

```php
use Laravilt\Schemas\Schema;
use Laravilt\Forms\Components\TextInput;
use Laravilt\Forms\Components\Select;

$schema = Schema::make()
    ->schema([
        TextInput::make('name')->required(),
        TextInput::make('email')->email()->required(),
        Select::make('role')->options([
            'admin' => 'Administrator',
            'user' => 'User',
        ]),
    ]);
```

### In Filament Resources

```php
use Filament\Resources\Resource;
use Laravilt\Forms\Components\TextInput;

class UserResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required(),
            TextInput::make('email')->email()->required(),
        ]);
    }
}
```

---

## Validation

All field components support Laravel validation rules through the `HasValidation` concern.

### Validation Shortcuts

```php
TextInput::make('email')
    ->email()              // email rule
    ->required()           // required rule
    ->min(5)              // min:5
    ->max(100)            // max:100
    ->minLength(5)        // min:5
    ->maxLength(100)      // max:100
    ->numeric()           // numeric rule
    ->integer()           // integer rule
    ->alpha()             // alpha rule
    ->alphaNum()          // alpha_num rule
    ->alphaDash()         // alpha_dash rule
    ->url()               // url rule
    ->confirmed()         // confirmed rule
    ->unique('users', 'email')  // unique
    ->exists('users', 'id')     // exists
    ->regex('/^[A-Z]/')         // regex
```

### Custom Rules

```php
TextInput::make('username')
    ->rules(['required', 'min:3', 'max:20', new CustomRule()])
    ->validationMessages([
        'username.required' => 'Username is required',
        'username.min' => 'Username must be at least 3 characters',
    ])
    ->validationAttribute('Username')
```

---

## RTL Support

All components automatically support RTL languages based on the application locale.

The package includes translations for:
- English (en)
- Arabic (ar)

---

## Multi-Platform Serialization

All components can serialize to:
1. **Laravilt Props** (Blade + Vue.js) - `toLaraviltProps()`
2. **API Props** (REST API) - `toApiProps()`
3. **Flutter Props** (Mobile) - `toFlutterProps()`

This is handled by the base `Component` class from `laravilt/support`.
