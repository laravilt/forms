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

Modern date picker with month/year dropdown selectors for easy navigation.

**Features**:
- Single date selection
- Month/year dropdown selectors (50 years back, 10 years forward)
- Min/max date constraints
- Locale support
- Centered date text
- Keyboard navigation
- ARIA accessible

**Example**:
```php
DatePicker::make('birth_date')
    ->label('Date of Birth')
    ->maxDate(now()->subYears(18))
    ->minDate(now()->subYears(100))
    ->required()
    ->helperText('You must be 18 or older')
```

**With Min/Max Constraints**:
```php
DatePicker::make('appointment_date')
    ->minDate(now())
    ->maxDate(now()->addDays(30))
    ->placeholder('Select appointment date')
```

**Props**:
- `minDate(?string $date)` - Minimum selectable date (Y-m-d format)
- `maxDate(?string $date)` - Maximum selectable date (Y-m-d format)
- `locale(string $locale)` - Calendar locale (default: 'en')
- `placeholder(string $text)` - Placeholder text

---

### DateTimePicker

**Location**: `src/Components/DateTimePicker.php`

Date and time picker with 24-hour format support and month/year selectors.

**Features**:
- Date and time selection
- 24-hour time format (configurable)
- Month/year dropdown selectors
- Minute granularity
- Min/max datetime constraints
- Locale support

**Example**:
```php
DateTimePicker::make('meeting_time')
    ->label('Meeting Date & Time')
    ->hourCycle(24)  // 24-hour format
    ->required()
    ->minDate(now()->toDateTimeString())
```

**With 12-hour Format**:
```php
DateTimePicker::make('appointment')
    ->hourCycle(12)  // 12-hour format with AM/PM
    ->placeholder('Select date and time')
```

**Props**:
- `hourCycle(int $cycle)` - Time format: 12 or 24 (default: 24)
- `minDate(?string $date)` - Minimum datetime (Y-m-d H:i format)
- `maxDate(?string $date)` - Maximum datetime (Y-m-d H:i format)
- `locale(string $locale)` - Calendar locale

---

### DateRangePicker

**Location**: `src/Components/DateRangePicker.php`

Date range picker with connected selection highlighting.

**Features**:
- Start and end date selection
- Connected visual range (highlighted dates between)
- Month/year dropdown selectors
- Multiple month display (configurable)
- Close on select option
- Min/max constraints

**Example**:
```php
DateRangePicker::make('vacation_dates')
    ->label('Vacation Period')
    ->numberOfMonths(2)  // Show 2 months
    ->closeOnSelect(false)  // Keep open after selecting
    ->minDate(now())
    ->required()
```

**With Auto-Close**:
```php
DateRangePicker::make('project_timeline')
    ->numberOfMonths(1)
    ->closeOnSelect(true)  // Close after end date selected
```

**Props**:
- `numberOfMonths(int $count)` - Number of months to display (default: 2)
- `closeOnSelect(bool $close)` - Auto-close after selection (default: false)
- `minDate(?string $date)` - Minimum selectable date
- `maxDate(?string $date)` - Maximum selectable date
- `locale(string $locale)` - Calendar locale

**Value Format**:
Returns an array with `start` and `end` keys:
```php
[
    'start' => '2024-01-15',
    'end' => '2024-01-20',
]
```

---

### TimePicker

**Location**: `src/Components/TimePicker.php`

Simple time input using native HTML5 time picker.

**Features**:
- Hour and minute selection
- Granularity control (hour, minute, second)
- Native browser UI
- 12/24 hour format based on locale

**Example**:
```php
TimePicker::make('preferred_time')
    ->label('Preferred Contact Time')
    ->granularity('minute')  // hour, minute, or second
    ->placeholder('Select time')
```

**With Second Precision**:
```php
TimePicker::make('exact_time')
    ->granularity('second')
    ->required()
```

**Props**:
- `granularity(string $level)` - Time precision: 'hour', 'minute', or 'second' (default: 'minute')

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
