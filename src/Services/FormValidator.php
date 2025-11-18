<?php

namespace Laravilt\Forms\Services;

use Laravilt\Schemas\Components\Component;
use Laravilt\Schemas\Schema;

/**
 * FormValidator Service
 *
 * Extracts validation rules from form schema components
 * to ensure validation matches the form definition.
 */
class FormValidator
{
    /**
     * Extract validation rules from a schema.
     */
    public static function getRules(Schema $schema): array
    {
        $rules = [];
        $messages = [];

        $components = $schema->getSchema();
        \Log::info('FormValidator: Processing schema', [
            'component_count' => count($components),
            'component_types' => array_map(fn ($c) => get_class($c), $components),
        ]);

        self::extractRulesFromComponents($components, $rules, $messages);

        \Log::info('FormValidator: Generated rules', [
            'rules' => $rules,
            'messages' => $messages,
        ]);

        return [
            'rules' => $rules,
            'messages' => $messages,
        ];
    }

    /**
     * Recursively extract rules from components.
     */
    protected static function extractRulesFromComponents(array $components, array &$rules, array &$messages): void
    {
        foreach ($components as $component) {
            // Skip non-Component objects
            if (! $component instanceof Component) {
                continue;
            }

            // Handle Tabs component (has getTabs() method)
            if (method_exists($component, 'getTabs')) {
                $tabs = $component->getTabs();
                \Log::info('FormValidator: Found Tabs component', [
                    'tab_count' => count($tabs),
                    'tab_types' => array_map(fn ($t) => get_class($t), $tabs),
                ]);
                foreach ($tabs as $tab) {
                    if (method_exists($tab, 'getSchema') && $schema = $tab->getSchema()) {
                        \Log::info('FormValidator: Processing Tab schema', [
                            'schema_count' => count($schema),
                            'schema_types' => array_map(fn ($s) => get_class($s), $schema),
                        ]);
                        self::extractRulesFromComponents($schema, $rules, $messages);
                    }
                }

                continue;
            }

            // Handle components with getSchema() method (Section, Tab, Grid)
            if (method_exists($component, 'getSchema') && $schema = $component->getSchema()) {
                self::extractRulesFromComponents($schema, $rules, $messages);

                continue;
            }

            // Extract validation rules from field components
            if (method_exists($component, 'getName')) {
                $name = $component->getName();
                if ($name) {
                    $fieldRules = self::getFieldRules($component);
                    \Log::info('FormValidator: Found field component', [
                        'name' => $name,
                        'type' => get_class($component),
                        'rules' => $fieldRules,
                    ]);
                    if (! empty($fieldRules)) {
                        $rules[$name] = $fieldRules;
                    }

                    // Get custom validation messages
                    $fieldMessages = self::getFieldMessages($component, $name);
                    if (! empty($fieldMessages)) {
                        $messages = array_merge($messages, $fieldMessages);
                    }
                }
            }
        }
    }

    /**
     * Get validation rules for a specific field component.
     */
    protected static function getFieldRules(Component $component): array|string
    {
        $rules = [];

        // Check if required
        if (method_exists($component, 'isRequired') && $component->isRequired()) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Get component type for specific validations
        $type = self::getComponentType($component);

        switch ($type) {
            case 'text_input':
                $rules[] = 'string';

                // Check for email type
                if (method_exists($component, 'getType') && $component->getType() === 'email') {
                    $rules[] = 'email';
                }

                // Check for URL type
                if (method_exists($component, 'getType') && $component->getType() === 'url') {
                    $rules[] = 'url';
                }

                // Max length
                if (method_exists($component, 'getMaxLength') && $maxLength = $component->getMaxLength()) {
                    $rules[] = "max:$maxLength";
                } else {
                    $rules[] = 'max:255'; // Default max
                }
                break;

            case 'textarea':
                $rules[] = 'string';
                if (method_exists($component, 'getMaxLength') && $maxLength = $component->getMaxLength()) {
                    $rules[] = "max:$maxLength";
                } else {
                    $rules[] = 'max:1000'; // Default max for textarea
                }
                break;

            case 'select':
                $rules[] = 'string';
                // Add 'in' rule if options are available
                if (method_exists($component, 'getOptions') && $options = $component->getOptions()) {
                    $validValues = array_keys($options);
                    $rules[] = 'in:'.implode(',', $validValues);
                }
                break;

            case 'checkbox':
                $rules[] = 'array';
                // If options exist, validate array items
                if (method_exists($component, 'getOptions') && $options = $component->getOptions()) {
                    $validValues = array_keys($options);

                    return [
                        $component->getName() => implode('|', $rules),
                        $component->getName().'.*' => 'string|in:'.implode(',', $validValues),
                    ];
                }
                break;

            case 'radio':
                $rules[] = 'string';
                if (method_exists($component, 'getOptions') && $options = $component->getOptions()) {
                    $validValues = array_keys($options);
                    $rules[] = 'in:'.implode(',', $validValues);
                }
                break;

            case 'toggle':
                $rules[] = 'boolean';
                break;

            case 'date_picker':
                $rules[] = 'date';
                break;

            case 'time_picker':
                $rules[] = 'date_format:H:i';
                break;

            case 'date_time_picker':
                $rules[] = 'date';
                break;

            case 'file_upload':
                $rules[] = 'file';

                // Max size (convert KB to KB for Laravel validation)
                if (method_exists($component, 'getMaxSize') && $maxSize = $component->getMaxSize()) {
                    $rules[] = "max:$maxSize";
                }

                // Accepted file types
                if (method_exists($component, 'getAcceptedFileTypes') && $types = $component->getAcceptedFileTypes()) {
                    $mimes = self::convertFileTypesToMimes($types);
                    if (! empty($mimes)) {
                        $rules[] = 'mimes:'.implode(',', $mimes);
                    }
                }
                break;

            case 'tags_input':
                $rules[] = 'array';
                break;

            default:
                // For unknown types, just use string
                $rules[] = 'string';
                break;
        }

        return implode('|', array_filter($rules));
    }

    /**
     * Get custom validation messages for a field.
     */
    protected static function getFieldMessages(Component $component, string $name): array
    {
        $messages = [];
        $label = method_exists($component, 'getLabel') ? $component->getLabel() : ucfirst(str_replace('_', ' ', $name));

        // Required message
        if (method_exists($component, 'isRequired') && $component->isRequired()) {
            $messages["$name.required"] = "$label is required.";
        }

        $type = self::getComponentType($component);

        // Type-specific messages
        switch ($type) {
            case 'text_input':
                if (method_exists($component, 'getType')) {
                    if ($component->getType() === 'email') {
                        $messages["$name.email"] = 'Please enter a valid email address.';
                    }
                    if ($component->getType() === 'url') {
                        $messages["$name.url"] = 'Please enter a valid URL.';
                    }
                }
                if (method_exists($component, 'getMaxLength') && $maxLength = $component->getMaxLength()) {
                    $messages["$name.max"] = "$label cannot exceed $maxLength characters.";
                }
                break;

            case 'file_upload':
                if (method_exists($component, 'getMaxSize') && $maxSize = $component->getMaxSize()) {
                    $maxMB = round($maxSize / 1024, 1);
                    $messages["$name.max"] = "$label size cannot exceed {$maxMB}MB.";
                }
                if (method_exists($component, 'getAcceptedFileTypes') && $types = $component->getAcceptedFileTypes()) {
                    $messages["$name.mimes"] = "$label must be a valid file type.";
                }
                break;

            case 'select':
            case 'radio':
                $messages["$name.in"] = "Please select a valid $label.";
                break;
        }

        return $messages;
    }

    /**
     * Get the component type name.
     */
    protected static function getComponentType(Component $component): string
    {
        $className = class_basename($component);

        // Convert class name to snake_case type
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }

    /**
     * Convert file types (MIME types or extensions) to Laravel mimes format.
     */
    protected static function convertFileTypesToMimes(array $types): array
    {
        $mimes = [];

        foreach ($types as $type) {
            // Handle wildcard types like 'image/*'
            if ($type === 'image/*') {
                $mimes = array_merge($mimes, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
            } elseif ($type === 'video/*') {
                $mimes = array_merge($mimes, ['mp4', 'avi', 'mov', 'wmv']);
            } elseif ($type === 'audio/*') {
                $mimes = array_merge($mimes, ['mp3', 'wav', 'ogg']);
            } elseif (str_starts_with($type, '.')) {
                // Extension like '.pdf' -> 'pdf'
                $mimes[] = ltrim($type, '.');
            } elseif (str_contains($type, '/')) {
                // MIME type like 'application/pdf' -> 'pdf'
                $extension = match ($type) {
                    'application/pdf' => 'pdf',
                    'application/msword' => 'doc',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                    'application/vnd.ms-excel' => 'xls',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                    'text/plain' => 'txt',
                    default => null,
                };
                if ($extension) {
                    $mimes[] = $extension;
                }
            }
        }

        return array_unique($mimes);
    }
}
