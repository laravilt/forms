<?php

namespace Laravilt\Forms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravilt\Forms\Components\Select;
use Laravilt\Schemas\Components\Tabs;
use Laravilt\Support\Utilities\Get;
use Laravilt\Support\Utilities\Set;

/**
 * Reactive Field Controller
 *
 * Handles real-time field updates for reactive fields (live/lazy).
 * Re-evaluates field schemas based on current form data and returns updated field configurations.
 */
class ReactiveFieldController extends Controller
{
    /**
     * Update reactive fields based on current form data.
     *
     * This endpoint receives:
     * - controller: The controller class name
     * - method: The method that builds the schema
     * - data: Current form data
     * - changed_field: The field that triggered the update
     *
     * Returns updated field configurations (options, etc.)
     *
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        $controllerClass = $request->input('controller');
        $method = $request->input('method', 'getSchema');
        $formData = $request->input('data', []);
        $changedField = $request->input('changed_field');

        // Repeater context (for reactive fields inside Repeater)
        $repeaterName = $request->input('repeater_name');
        $repeaterIndex = $request->input('repeater_index');
        $fieldName = $request->input('field_name');

        try {
            // Instantiate the controller
            if (! class_exists($controllerClass)) {
                return response()->json([
                    'error' => 'Controller not found',
                ], 404);
            }

            $controller = app($controllerClass);

            // Temporarily add formData to the request so Select components can access it
            $request->merge(['formData' => $formData]);

            // Create Get and Set utilities for afterStateUpdated callbacks
            $get = new Get($formData);
            $set = new Set($formData);

            // Prefer getFormSchema for modal forms (ManageRecords), fall back to getSchema
            $schemaMethod = method_exists($controller, 'getFormSchema') ? 'getFormSchema' : $method;

            if (! method_exists($controller, $schemaMethod)) {
                return response()->json([
                    'error' => 'Method not found',
                ], 404);
            }

            // Call the method with formData parameter and pass changedField for afterStateUpdated
            // The controller should return the raw schema before serialization
            $schema = $controller->$schemaMethod($formData);

            // Handle different return types from getSchema()
            // 1. Array containing Schema object(s) - e.g., [$form] from CreateRecord/EditRecord
            // 2. Schema object directly
            // 3. Already serialized array (backward compatibility)

            // Build repeater context if this is a reactive field inside a Repeater
            $repeaterContext = null;
            if ($repeaterName !== null && $repeaterIndex !== null && $fieldName !== null) {
                $repeaterContext = [
                    'repeaterName' => $repeaterName,
                    'repeaterIndex' => (int) $repeaterIndex,
                    'fieldName' => $fieldName,
                ];
            }

            if (is_array($schema) && count($schema) > 0) {
                $firstItem = $schema[0] ?? null;

                // If first item is a Schema object, process it
                if (is_object($firstItem) && method_exists($firstItem, 'toLaraviltProps')) {
                    // formData is passed by reference, so afterStateUpdated callbacks will modify it
                    $schemaData = $firstItem->toLaraviltProps($formData, null, $changedField, $repeaterContext)['schema'];
                } else {
                    // Already serialized array
                    $schemaData = $schema;
                }
            } elseif (is_object($schema) && method_exists($schema, 'toLaraviltProps')) {
                // Direct Schema object
                // formData is passed by reference, so afterStateUpdated callbacks will modify it
                $schemaData = $schema->toLaraviltProps($formData, null, $changedField, $repeaterContext)['schema'];
            } else {
                // Already serialized (backward compatibility)
                $schemaData = $schema;
            }

            // Return the full re-evaluated schema with updated data
            // formData was modified by reference in toLaraviltProps if afterStateUpdated callbacks ran
            return response()->json([
                'schema' => $schemaData,
                'data' => $formData,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Failed to update fields: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recursively evaluate schema array (serialized components) and extract updated field options.
     *
     * @param  array  $schema  Array of serialized component data
     * @param  Get  $get  Get utility for reading form data
     * @param  Set  $set  Set utility for writing form data
     * @return array Map of field names to their updated configurations
     */
    protected function evaluateSchemaArray(array $schema, Get $get, Set $set): array
    {
        $updatedFields = [];

        foreach ($schema as $componentData) {
            // Skip if not an array
            if (! is_array($componentData)) {
                continue;
            }

            $componentType = $componentData['component'] ?? null;
            $componentName = $componentData['name'] ?? null;

            // Handle nested schemas (tabs, sections, grids)
            if ($componentType === 'tabs' && isset($componentData['tabs'])) {
                foreach ($componentData['tabs'] as $tab) {
                    if (isset($tab['schema'])) {
                        $tabFields = $this->evaluateSchemaArray($tab['schema'], $get, $set);
                        $updatedFields = array_merge($updatedFields, $tabFields);
                    }
                }

                continue;
            }

            if (in_array($componentType, ['section', 'grid']) && isset($componentData['schema'])) {
                $nestedFields = $this->evaluateSchemaArray($componentData['schema'], $get, $set);
                $updatedFields = array_merge($updatedFields, $nestedFields);

                continue;
            }

            // Check if this is a select field with hasDynamicOptions
            if ($componentType === 'select' && $componentName && isset($componentData['hasDynamicOptions']) && $componentData['hasDynamicOptions']) {
                // This field needs its options re-evaluated
                // by re-calling getSchema() which re-evaluates the closures
                $updatedFields[$componentName] = [
                    'name' => $componentName,
                    'needsEvaluation' => true,
                ];
            }
        }

        return $updatedFields;
    }

    /**
     * Recursively evaluate schema components (object instances) and extract updated field options.
     *
     * @param  array  $components  Array of component instances
     * @param  Get  $get  Get utility for reading form data
     * @param  Set  $set  Set utility for writing form data
     * @return array Map of field names to their updated configurations
     */
    protected function evaluateSchemaComponents(array $components, Get $get, Set $set): array
    {
        $updatedFields = [];

        foreach ($components as $component) {
            // Skip if not a component instance
            if (! is_object($component)) {
                continue;
            }

            // Check if it's a Select component with dynamic options
            if ($component instanceof Select) {
                $fieldName = $component->getName();

                // Evaluate the options closure if it exists
                $options = $component->evaluateOptions($get, $set);

                // Transform options to frontend format
                $transformedOptions = [];
                foreach ($options as $key => $value) {
                    $transformedOptions[] = [
                        'value' => (string) $key,
                        'label' => $value,
                    ];
                }

                $updatedFields[$fieldName] = [
                    'name' => $fieldName,
                    'options' => $transformedOptions,
                ];

            }

            // Handle nested schema components (Section, Grid, Tabs)
            if (method_exists($component, 'getSchema')) {
                $nestedComponents = $component->getSchema();
                if (is_array($nestedComponents)) {
                    $nestedFields = $this->evaluateSchemaComponents($nestedComponents, $get, $set);
                    $updatedFields = array_merge($updatedFields, $nestedFields);
                }
            }

            // Handle Tabs specifically
            if ($component instanceof Tabs) {
                $tabs = $component->getTabs();
                foreach ($tabs as $tab) {
                    if (method_exists($tab, 'getSchema')) {
                        $tabSchema = $tab->getSchema();
                        if (is_array($tabSchema)) {
                            $this->evaluateSchemaComponents($tabSchema, $get, $set);
                            $updatedFields = array_merge($updatedFields, $nestedFields);
                        }
                    }
                }
            }
        }

        return $updatedFields;
    }

    /**
     * Recursively find and execute afterStateUpdated callback for a specific field.
     *
     * @param  array  $schema  The schema array
     * @param  string  $fieldName  The field that changed
     * @param  mixed  $value  The new value
     * @param  Get  $get  Get utility
     * @param  Set  $set  Set utility
     */
    protected function executeAfterStateUpdated(array $schema, string $fieldName, mixed $value, Get $get, Set $set): void
    {
        foreach ($schema as $component) {
            // Skip if not an array
            if (! is_array($component)) {
                continue;
            }

            // Handle nested schemas (tabs, sections, grids)
            if (in_array($component['component'] ?? null, ['tabs', 'section', 'grid'])) {
                if (isset($component['schema']) && is_array($component['schema'])) {
                    $this->executeAfterStateUpdated($component['schema'], $fieldName, $value, $get, $set);
                }

                if (isset($component['tabs']) && is_array($component['tabs'])) {
                    foreach ($component['tabs'] as $tab) {
                        if (isset($tab['schema']) && is_array($tab['schema'])) {
                            $this->executeAfterStateUpdated($tab['schema'], $fieldName, $value, $get, $set);
                        }
                    }
                }
            }
        }
    }
}
