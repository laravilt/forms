# Validation Guide

## Overview

Laravilt Forms provides comprehensive validation support through Laravel's built-in validation system, with convenient helper methods and custom error messages.

## Basic Validation

### Using Validation Shortcuts

All form components include convenient validation shortcuts:

```php
TextInput::make('email')
    ->required()
    ->email()
    ->maxLength(255)
```

### Available Shortcuts

```php
// Required
->required()

// String validation
->minLength(5)
->maxLength(100)
->regex('/^[A-Z]/')

// Numeric validation
->numeric()
->integer()
->min(1)
->max(100)

// String type validation
->alpha()           // Only alphabetic characters
->alphaNum()        // Only alphanumeric characters
->alphaDash()       // Alphanumeric with dashes and underscores

// Format validation
->email()
->url()
->date()
->time()

// Database validation
->unique('users', 'email')
->exists('users', 'id')

// Confirmation
->confirmed()       // Requires field_confirmation field
```

## Custom Validation Rules

### Using Laravel Rules

```php
use Illuminate\Validation\Rules\Password;

TextInput::make('password')
    ->rules([
        'required',
        'string',
        Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised(),
    ])
```

### Custom Rule Classes

```php
use App\Rules\UsernameRule;

TextInput::make('username')
    ->rules([
        'required',
        'min:3',
        'max:20',
        new UsernameRule(),
    ])
```

### Conditional Validation

```php
TextInput::make('company')
    ->rules(function ($get) {
        $userType = $get('user_type');

        return $userType === 'business'
            ? ['required', 'string', 'max:255']
            : ['nullable', 'string', 'max:255'];
    })
```

## Custom Error Messages

### Per-Field Messages

```php
TextInput::make('username')
    ->required()
    ->minLength(3)
    ->validationMessages([
        'username.required' => 'Please enter a username',
        'username.min' => 'Username must be at least 3 characters long',
    ])
```

### Custom Attribute Names

```php
TextInput::make('user_email')
    ->email()
    ->required()
    ->validationAttribute('Email Address')
    // Error: "The Email Address field is required"
```

## Real-time Validation

### Client-Side Validation

Enable real-time validation on the frontend:

```php
TextInput::make('email')
    ->email()
    ->required()
    ->reactive()  // Validates on input change
```

### Debounced Validation

Add debounce for better UX:

```php
TextInput::make('username')
    ->required()
    ->unique('users', 'username')
    ->reactive()
    ->debounce(500)  // Wait 500ms after typing stops
```

## Complex Validation Examples

### Password with Confirmation

```php
use Illuminate\Validation\Rules\Password;

TextInput::make('password')
    ->type('password')
    ->rules([
        'required',
        Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols(),
    ])
    ->confirmed(),

TextInput::make('password_confirmation')
    ->type('password')
    ->label('Confirm Password')
    ->required()
```

### Unique Email with Ignore

```php
TextInput::make('email')
    ->email()
    ->rules([
        'required',
        'email',
        Rule::unique('users')->ignore($userId),
    ])
```

### Date Range Validation

```php
DatePicker::make('start_date')
    ->required()
    ->rules(['required', 'date', 'before:end_date']),

DatePicker::make('end_date')
    ->required()
    ->rules(['required', 'date', 'after:start_date'])
```

### File Upload Validation

```php
FileUpload::make('document')
    ->required()
    ->rules([
        'required',
        'file',
        'mimes:pdf,doc,docx',
        'max:5120', // 5MB
    ])
```

### Multiple File Validation

```php
FileUpload::make('images')
    ->multiple()
    ->rules([
        'required',
        'array',
        'min:1',
        'max:5',
    ])
    ->each([
        'image',
        'mimes:jpeg,png,jpg,gif',
        'max:2048', // 2MB per image
    ])
```

## Form-Level Validation

### Server-Side Form Validation

Create a Form Request class:

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your full name',
            'email.unique' => 'This email is already registered',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'email address',
            'password' => 'password',
        ];
    }
}
```

### Using in Controller

```php
public function store(StoreUserRequest $request)
{
    $validated = $request->validated();

    User::create($validated);

    return redirect()->route('users.index');
}
```

## Validation Error Display

### Inline Errors

Errors are automatically displayed below each field:

```php
TextInput::make('email')
    ->email()
    ->required()
    // Errors appear inline below the field
```

### Disabling Inline Errors

```php
TextInput::make('email')
    ->email()
    ->required()
    ->hideErrorMessage()
```

### Custom Error Styling

Customize error appearance in your CSS:

```css
.error-message {
    @apply text-sm text-red-600 mt-1;
}

.field-with-error input {
    @apply border-red-500 focus:ring-red-500;
}
```

## Validation Tips

### 1. Use Shortcuts for Common Rules

```php
// Good
TextInput::make('email')->email()->required()

// Avoid
TextInput::make('email')->rules(['required', 'email'])
```

### 2. Group Related Validation

```php
// Good
TextInput::make('username')
    ->required()
    ->minLength(3)
    ->maxLength(20)
    ->alphaDash()
```

### 3. Use Custom Messages Wisely

Only add custom messages when the default message isn't clear:

```php
// Good - clarifies business rule
TextInput::make('age')
    ->numeric()
    ->min(18)
    ->validationMessages([
        'age.min' => 'You must be 18 or older to register',
    ])

// Unnecessary - default message is fine
TextInput::make('name')
    ->required()
    ->validationMessages([
        'name.required' => 'The name field is required', // Same as default
    ])
```

### 4. Leverage Conditional Rules

```php
Select::make('role')
    ->options(['user' => 'User', 'admin' => 'Admin'])
    ->required(),

TextInput::make('admin_code')
    ->rules(fn ($get) =>
        $get('role') === 'admin'
            ? ['required', 'string']
            : ['nullable']
    )
```

## Testing Validation

### Unit Tests

```php
use Tests\TestCase;

class UserValidationTest extends TestCase
{
    public function test_email_is_required()
    {
        $response = $this->post('/users', [
            'name' => 'John Doe',
            // email missing
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_email_must_be_valid()
    {
        $response = $this->post('/users', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
```
