# MCP Server Integration

The Laravilt Forms package can be integrated with MCP (Model Context Protocol) server for AI agent interaction.

## Available Generator Commands

### make:form
Generate a new form class.

**Usage:**
```bash
php artisan make:form UserForm
php artisan make:form Admin/UserForm
php artisan make:form UserForm --resource
php artisan make:form UserForm --force
```

**Arguments:**
- `name` (string, required): Form class name (StudlyCase)

**Options:**
- `--resource`: Generate a resource form with CRUD operations
- `--force`: Overwrite existing file

**Generated Structure (Basic):**
```php
<?php

namespace App\Forms;

use Laravilt\Forms\Form;
use Laravilt\Forms\Concerns\HasSchema;
use Laravilt\Forms\Components\TextInput;

class UserForm extends Form
{
    use HasSchema;

    protected function setUp(): void
    {
        parent::setUp();
        // Configure your form here
    }

    public function schema(array $components): static
    {
        $this->schema = $components;
        return $this;
    }
}
```

**Generated Structure (Resource):**
```php
<?php

namespace App\Forms;

use Laravilt\Forms\Form;
use Laravilt\Forms\Concerns\HasSchema;
use Laravilt\Forms\Components\TextInput;

class UserForm extends Form
{
    use HasSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schema([
            TextInput::make('name')
                ->label('Name')
                ->required(),
            // Add more fields here
        ]);
    }

    public function fillFromModel($model): static
    {
        // Fill form from model
        $this->fill($model->toArray());
        return $this;
    }

    public function saveToModel($model, array $data): void
    {
        // Save form data to model
        $model->fill($data)->save();
    }
}
```

### make:component
Generate a custom form field component.

**Usage:**
```bash
php artisan make:component CustomField
```

**Arguments:**
- `name` (string, required): Component class name

## Integration Example

MCP server tools should provide:

1. **list-forms** - List all form classes in the application
2. **form-info** - Get details about a specific form class
3. **generate-form** - Generate a new form class with specified fields
4. **list-field-types** - List all available field types
5. **generate-component** - Generate a custom form field component

## Field Types Reference

For MCP tools to provide field type information:

### Basic Fields
- TextInput: Single-line text input
- Textarea: Multi-line text input
- NumberField: Numeric input
- Select: Dropdown select
- Checkbox: Single checkbox
- CheckboxList: Multiple checkboxes
- Radio: Radio button group
- Toggle: Switch/toggle button
- ToggleButtons: Button group toggle
- Hidden: Hidden field

### Date & Time Fields
- DatePicker: Single date selection
- DateTimePicker: Date and time selection
- TimePicker: Time selection
- DateRangePicker: Date range selection

### Advanced Fields
- ColorPicker: Color selection
- IconPicker: Icon picker
- FileUpload: File/image upload
- RichEditor: WYSIWYG editor
- MarkdownEditor: Markdown editor
- CodeEditor: Code editor with syntax highlighting
- TagsInput: Multiple tags input
- KeyValue: Key-value pair editor
- Slider: Range slider
- RateInput: Star rating input
- PinInput: PIN/OTP input

### Dynamic Fields
- Repeater: Repeatable field groups
- Builder: Block-based content builder

## Security

The MCP server runs with the same permissions as your Laravel application. Ensure:
- Proper file permissions on the app/Forms directory
- Secure configuration of the MCP server
- Limited access to the MCP configuration file
