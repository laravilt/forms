import ActionButton from '@laravilt/actions/components/ActionButton';
import { ErrorsContext, SchemaContext, useErrors, type SchemaContextValue } from '@laravilt/support/composables/contexts';
import { useLatest } from '@laravilt/support/composables/hooks';
import { resolveComponent } from '@laravilt/support/composables/registry';
import { Fragment, useCallback, useEffect, useImperativeHandle, useMemo, useRef, useState, type Ref } from 'react';

export interface FormHandle {
    getFormData(): Record<string, any>;
    validateForm(): boolean;
    clearValidationErrors(): void;
}

export interface FormProps {
    schema: Array<any>;
    modelValue?: Record<string, any>;
    schemaId?: string;
    disabled?: boolean;
    formController?: string;
    formMethod?: string;
    onUpdateModelValue?: (value: Record<string, any>) => void;
    onUpdateSchema?: (value: any[]) => void;
    ref?: Ref<FormHandle>;
}

const schemaComponentTypes = ['tabs', 'section', 'grid'];

// Recursively extract all field components and their default values from schema
const extractFieldDefaults = (schema: Array<any>): Record<string, any> => {
    const defaults: Record<string, any> = {};

    // Safety check - ensure schema is an array
    if (!schema || !Array.isArray(schema)) {
        console.warn('extractFieldDefaults: schema is not an array', schema);
        return defaults;
    }

    for (const component of schema) {
        // Skip actions
        if (component.hasAction === true || (component.name && !component.component)) {
            continue;
        }

        // If it's a tabs component, extract from all tabs
        if (component.component === 'tabs' && component.tabs && Array.isArray(component.tabs)) {
            for (const tab of component.tabs) {
                if (tab.schema && Array.isArray(tab.schema)) {
                    Object.assign(defaults, extractFieldDefaults(tab.schema));
                }
            }
            continue; // Don't add the tabs component itself as a field
        }

        // If it's a schema component (section, grid), recurse into its schema
        if (schemaComponentTypes.includes(component.component) && component.schema && Array.isArray(component.schema)) {
            Object.assign(defaults, extractFieldDefaults(component.schema));
            continue; // Don't add the schema component itself as a field
        }

        // If it has a name and component type (it's an actual field), add its default value
        if (component.name && component.component && !schemaComponentTypes.includes(component.component)) {
            // Use defaultValue or default, but NOT value (value could be an object from backend)
            // Use ?? (nullish coalescing) to properly handle false/0 values
            defaults[component.name] = component.defaultValue ?? component.default ?? component.value ?? null;
        }
    }

    return defaults;
};

// Extract required fields from schema recursively
const extractRequiredFields = (schema: Array<any>): Array<{ name: string; label: string }> => {
    const requiredFields: Array<{ name: string; label: string }> = [];

    if (!schema || !Array.isArray(schema)) {
        return requiredFields;
    }

    for (const component of schema) {
        // Skip actions
        if (component.hasAction === true || (component.name && !component.component)) {
            continue;
        }

        // If it's a tabs component, extract from all tabs
        if (component.component === 'tabs' && component.tabs && Array.isArray(component.tabs)) {
            for (const tab of component.tabs) {
                if (tab.schema && Array.isArray(tab.schema)) {
                    requiredFields.push(...extractRequiredFields(tab.schema));
                }
            }
            continue;
        }

        // If it's a schema component (section, grid), recurse into its schema
        if (schemaComponentTypes.includes(component.component) && component.schema && Array.isArray(component.schema)) {
            requiredFields.push(...extractRequiredFields(component.schema));
            continue;
        }

        // If it has a name and is required, add to the list
        if (component.name && component.required) {
            requiredFields.push({
                name: component.name,
                label: component.label || component.name,
            });
        }
    }

    return requiredFields;
};

// Find a field by name in the schema (recursively)
const findFieldInSchema = (schema: any[], fieldName: string): any => {
    for (const component of schema) {
        if (component.name === fieldName) {
            return component;
        }

        // Check nested schemas
        if (component.component === 'tabs' && component.tabs) {
            for (const tab of component.tabs) {
                if (tab.schema) {
                    const found = findFieldInSchema(tab.schema, fieldName);
                    if (found) return found;
                }
            }
        }

        if (component.schema) {
            const found = findFieldInSchema(component.schema, fieldName);
            if (found) return found;
        }
    }
    return null;
};

// Check if a value is empty (null, undefined, empty string, empty array)
const isValueEmpty = (value: any): boolean => {
    if (value === null || value === undefined) return true;
    if (typeof value === 'string' && value.trim() === '') return true;
    if (Array.isArray(value) && value.length === 0) return true;
    return false;
};

// Check if an item is an action
const isAction = (item: any) => {
    // Actions have hasAction property or don't have a component property
    return item.hasAction === true || (item.name && !item.component);
};

// Check if a component is a schema component (needs entire modelValue, not just a field value)
const isSchemaComponent = (component: any) => {
    return schemaComponentTypes.includes(component.component);
};

const toLaraviltName = (name: any) => {
    if (!name) return;
    return 'laravilt-' + String(name).replaceAll('_', '-');
};

// Get component props, excluding value, modelValue, and disabled since we set them explicitly
const getComponentProps = (component: any) => {
    const { value, modelValue, disabled, ...rest } = component;
    return rest;
};

export default function Form({
    schema,
    modelValue,
    schemaId,
    disabled,
    formController = undefined,
    formMethod = 'getSchema',
    onUpdateModelValue,
    ref,
}: FormProps) {
    const formRef = useRef<HTMLFormElement | null>(null);

    // Make schema internally reactive so it can be updated by reactive fields
    const [internalSchema, setInternalSchemaState] = useState<any[]>(schema);
    const internalSchemaRef = useRef<any[]>(schema);
    const setInternalSchema = (next: any[]) => {
        internalSchemaRef.current = next;
        setInternalSchemaState(next);
    };

    // Form data (state for rendering + ref for synchronous reads, like Vue's `.value`)
    // Initial value mirrors the immediate modelValue watcher
    const [internalFormData, setInternalFormDataState] = useState<Record<string, any>>(() =>
        modelValue && Object.keys(modelValue).length > 0 ? { ...extractFieldDefaults(schema), ...modelValue } : {},
    );
    const internalFormDataRef = useRef<Record<string, any>>(internalFormData);
    const setInternalFormData = (next: Record<string, any>) => {
        internalFormDataRef.current = next;
        setInternalFormDataState(next);
    };

    const latest = useLatest({ onUpdateModelValue, formController, formMethod });
    const emitModelValue = (value: Record<string, any>) => latest.current.onUpdateModelValue?.(value);

    // Local validation errors for client-side validation
    const [localErrors, setLocalErrors] = useState<Record<string, string>>({});

    // Errors from ErrorProvider
    const injectedErrors = useErrors();

    // Merged errors: local client-side errors + server-side errors
    const errors = useMemo(() => ({ ...localErrors, ...(injectedErrors || {}) }), [localErrors, injectedErrors]);

    // Watch for prop schema changes (from page navigation, etc.)
    const previousSchema = useRef(schema);
    useEffect(() => {
        if (previousSchema.current === schema) return;
        previousSchema.current = schema;
        setInternalSchema(schema);
    }, [schema]);

    // Watch for external modelValue changes and merge them
    const previousModelValue = useRef(modelValue);
    useEffect(() => {
        if (previousModelValue.current === modelValue) return;
        previousModelValue.current = modelValue;

        if (modelValue && Object.keys(modelValue).length > 0) {
            // Get defaults from schema
            const defaults = extractFieldDefaults(internalSchemaRef.current);
            // Merge defaults with new value - new value takes precedence
            setInternalFormData({
                ...defaults,
                ...modelValue,
            });
        }
    }, [modelValue]);

    // Initialize on mount + listen for action-updated-data events from ActionButton
    const latestModelValue = useLatest(modelValue);
    useEffect(() => {
        // Only initialize with defaults if modelValue is empty
        const currentModelValue = latestModelValue.current;
        if (!currentModelValue || Object.keys(currentModelValue).length === 0) {
            const defaults = extractFieldDefaults(internalSchemaRef.current);
            setInternalFormData({ ...defaults });
            emitModelValue(internalFormDataRef.current);
        }

        // Handle action-updated data events
        const handleActionUpdatedData = (event: Event) => {
            const updatedData = (event as CustomEvent).detail;

            if (updatedData && typeof updatedData === 'object') {
                // Merge the updated data into internal form data
                setInternalFormData({
                    ...internalFormDataRef.current,
                    ...updatedData,
                });
                emitModelValue(internalFormDataRef.current);
            }
        };

        window.addEventListener('action-updated-data', handleActionUpdatedData);

        return () => {
            window.removeEventListener('action-updated-data', handleActionUpdatedData);
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Function to update schema (for reactive fields)
    const pendingRestore = useRef<{ scrollX: number; scrollY: number; activeElement: HTMLElement | null } | null>(null);
    const updateSchema = useCallback((newSchema: any[]) => {
        // Save current scroll position and focused element
        pendingRestore.current = {
            scrollX: window.scrollX,
            scrollY: window.scrollY,
            activeElement: document.activeElement as HTMLElement,
        };

        setInternalSchema(newSchema);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Restore scroll position and focus after the schema re-render (Vue: nextTick)
    useEffect(() => {
        const restore = pendingRestore.current;
        if (!restore) return;
        pendingRestore.current = null;

        window.scrollTo({
            top: restore.scrollY,
            left: restore.scrollX,
            behavior: 'instant' as ScrollBehavior,
        });

        // Restore focus if element still exists
        if (restore.activeElement && document.contains(restore.activeElement)) {
            restore.activeElement.focus({ preventScroll: true });
        }
    }, [internalSchema]);

    // Trigger reactive field update
    const triggerReactiveFieldUpdate = async (fieldName: string, _field: any) => {
        const { formController: controller, formMethod: method } = latest.current;

        // Skip if no form controller is configured
        if (!controller) {
            console.warn('[Form] No formController configured, skipping reactive field update');
            return;
        }

        // TODO (from Vue): Implement debouncing if needed
        try {
            const payload = {
                controller,
                method: method || 'getSchema',
                data: internalFormDataRef.current,
                changed_field: fieldName,
            };

            const response = await fetch('/reactive-fields/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                throw new Error('Failed to update reactive fields');
            }

            const result = await response.json();

            if (result.schema) {
                updateSchema(result.schema);
            }

            // Update form data if backend modified it (from afterStateUpdated)
            if (result.data) {
                setInternalFormData({ ...internalFormDataRef.current, ...result.data });
                emitModelValue(internalFormDataRef.current);
            }
        } catch (error) {
            console.error('[FormRenderer] Error updating reactive fields:', error);
        }
    };

    const updateValue = async (name: string, value: any) => {
        // Update internal form data
        setInternalFormData({
            ...internalFormDataRef.current,
            [name]: value,
        });

        emitModelValue(internalFormDataRef.current);

        // Check if this field is reactive (live/lazy)
        const field = findFieldInSchema(internalSchemaRef.current, name);

        if (field && (field.isLive || field.isLazy)) {
            await triggerReactiveFieldUpdate(name, field);
        }
    };

    // Handle component update events
    const handleComponentUpdate = async (component: any, value: any) => {
        if (isSchemaComponent(component)) {
            // For schema components (Section, Grid, Tabs), value is an object with field updates
            const oldData = { ...internalFormDataRef.current };

            // Check if any values actually changed before proceeding
            let hasChanges = false;
            for (const [fieldName, fieldValue] of Object.entries(value || {})) {
                if (oldData[fieldName] !== fieldValue) {
                    hasChanges = true;
                    break;
                }
            }

            // Skip if nothing changed (performance optimization)
            if (!hasChanges) {
                return;
            }

            setInternalFormData({ ...internalFormDataRef.current, ...value });
            emitModelValue(internalFormDataRef.current);

            // Check each field that changed for reactivity
            for (const [fieldName, fieldValue] of Object.entries(value)) {
                // Only trigger if value actually changed
                if (oldData[fieldName] !== fieldValue) {
                    // Find the field in schema and check if it's reactive
                    const field = findFieldInSchema(internalSchemaRef.current, fieldName);
                    if (field && (field.isLive || field.isLazy)) {
                        await triggerReactiveFieldUpdate(fieldName, field);
                    }
                }
            }
        } else {
            await updateValue(component.name, value);
        }
    };

    // Collect all form data - just return the internal tracked data
    const getFormData = useCallback(() => {
        // Return a copy to avoid mutations
        return { ...internalFormDataRef.current };
    }, []);

    // Validate the form - combines HTML5 validation with custom required field checks
    const validateForm = useCallback(() => {
        // Clear previous local validation errors
        setLocalErrors({});

        // Get all required fields from schema
        const requiredFields = extractRequiredFields(internalSchemaRef.current);

        // Check each required field
        const newErrors: Record<string, string> = {};
        for (const field of requiredFields) {
            const value = internalFormDataRef.current[field.name];
            if (isValueEmpty(value)) {
                newErrors[field.name] = `${field.label} is required.`;
            }
        }

        // If there are validation errors, set them and return false
        if (Object.keys(newErrors).length > 0) {
            setLocalErrors(newErrors);
            return false;
        }

        // Also run HTML5 validation for native form elements
        if (formRef.current) {
            const isHtml5Valid = formRef.current.checkValidity();
            if (!isHtml5Valid) {
                formRef.current.reportValidity();
                return false;
            }
        }

        return true;
    }, []);

    // Clear local validation errors (useful when modal closes or form resets)
    const clearValidationErrors = useCallback(() => {
        setLocalErrors({});
    }, []);

    // Provide getFormData, validateForm, updateSchema, schemaId, formController, and formMethod to all child components
    const schemaContext = useMemo<SchemaContextValue>(
        () => ({
            getFormData,
            validateForm,
            updateSchema,
            schemaId: schemaId || null,
            formController,
            formMethod,
        }),
        [getFormData, validateForm, updateSchema, schemaId, formController, formMethod],
    );

    // Expose getFormData, validateForm, and clearValidationErrors to parent components via ref
    useImperativeHandle(ref, () => ({ getFormData, validateForm, clearValidationErrors }), [
        getFormData,
        validateForm,
        clearValidationErrors,
    ]);

    // Check if the next item is an action
    const isNextItemAction = (index: number) => {
        const nextItem = internalSchema[index + 1];
        return nextItem && isAction(nextItem);
    };

    // Check if the previous item is an action
    const isPreviousItemAction = (index: number) => {
        const prevItem = internalSchema[index - 1];
        return prevItem && isAction(prevItem);
    };

    // Get all consecutive actions starting from the next index
    const getConsecutiveActions = (startIndex: number) => {
        const actions = [];
        let currentIndex = startIndex + 1;

        while (currentIndex < internalSchema.length && isAction(internalSchema[currentIndex])) {
            actions.push(internalSchema[currentIndex]);
            currentIndex++;
        }

        return actions;
    };

    // Determine container spacing based on what we're rendering
    const containerClass = (() => {
        if (!internalSchema || !Array.isArray(internalSchema) || internalSchema.length === 0) return '';

        // Check if we're rendering sections - if so, use space-y-12
        const hasSections = internalSchema.some((c) => c.component === 'section');
        if (hasSections) return 'space-y-12';

        // Otherwise use space-y-6 for general spacing
        return 'space-y-6';
    })();

    // Get error message for a component
    const getError = (component: any) => {
        if (!component.name || !errors) return undefined;
        const error = (errors as Record<string, string | string[]>)[component.name];
        return Array.isArray(error) ? error[0] : error;
    };

    // Wrapper for form actions that can collect form data
    const renderAction = (action: any, key: string | number) => (
        <ActionButton key={key} {...({ ...action, getFormData } as any)} />
    );

    return (
        <SchemaContext.Provider value={schemaContext}>
            {/* Override the errors provided by ErrorProvider with our merged errors (local + server) */}
            <ErrorsContext.Provider value={errors}>
                <form ref={formRef} className={containerClass} onSubmit={(e) => e.preventDefault()}>
                    {(Array.isArray(internalSchema) ? internalSchema : []).map((component: any, index: number) => {
                        const key = component.name || component.id || index;

                        // Group consecutive actions together (only render on first action in group)
                        if (isAction(component) && !isPreviousItemAction(index) && isNextItemAction(index)) {
                            return (
                                <div key={key} className="flex flex-wrap gap-2 items-start">
                                    {renderAction(component, 'first')}
                                    {getConsecutiveActions(index).map((nextComponent: any, nextIndex: number) =>
                                        renderAction(nextComponent, nextComponent.name || nextComponent.id || index + nextIndex + 1),
                                    )}
                                </div>
                            );
                        }

                        // Render single action (not part of a group)
                        if (isAction(component) && !isPreviousItemAction(index) && !isNextItemAction(index)) {
                            return renderAction(component, key);
                        }

                        // Render regular form components
                        if (!isAction(component)) {
                            const Component = resolveComponent(toLaraviltName(component.component));

                            if (!Component) {
                                return <div key={key} />;
                            }

                            return (
                                <Component
                                    key={key}
                                    {...getComponentProps(component)}
                                    value={isSchemaComponent(component) ? undefined : internalFormData[component.name]}
                                    modelValue={isSchemaComponent(component) ? internalFormData : internalFormData[component.name]}
                                    disabled={disabled || component.disabled}
                                    error={getError(component)}
                                    onUpdateModelValue={(value: any) => handleComponentUpdate(component, value)}
                                />
                            );
                        }

                        return <Fragment key={key} />;
                    })}
                </form>
            </ErrorsContext.Provider>
        </SchemaContext.Provider>
    );
}
