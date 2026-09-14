import { useLatest } from '@laravilt/support/composables/hooks';
import { useSchemaContext } from '@laravilt/support/composables/contexts';
import { useEffect, useRef } from 'react';

/**
 * Hook for handling reactive field updates (live/lazy fields).
 *
 * This handles the automatic update of dependent fields when a field value changes.
 * It watches the field's value and POSTs to the backend to re-evaluate the schema,
 * updating dependent field options dynamically.
 *
 * @param fieldName - The name of the field
 * @param fieldValue - The field's current value (the Vue version takes a Ref)
 * @param options - Configuration options
 */
export function useReactiveField(
    fieldName: string | undefined,
    fieldValue: any,
    options: {
        isLive?: boolean;
        isLazy?: boolean;
        liveDebounce?: number;
        dependsOn?: string[];
        dependentOptions?: Record<string, any>;
    } = {},
) {
    const { isLive, isLazy, liveDebounce = 500, dependsOn = [], dependentOptions } = options;

    // Dependencies from the Form (Vue: inject('getFormData') / inject('updateSchema'))
    const { getFormData, updateSchema } = useSchemaContext();
    const latest = useLatest({ getFormData, updateSchema });

    const reloadTimeout = useRef<ReturnType<typeof setTimeout> | null>(null);
    const isUpdating = useRef(false); // Flag to prevent duplicate updates
    const previousValue = useRef<any>(fieldValue);

    // If this field is live/lazy, watch for changes and POST to backend
    useEffect(() => {
        if (Object.is(previousValue.current, fieldValue)) {
            return;
        }

        previousValue.current = fieldValue;

        if (!((isLive || isLazy) && fieldName)) {
            return;
        }

        const debounceMs = isLazy ? liveDebounce : isLive && liveDebounce > 0 ? liveDebounce : 0;

        // Skip if already updating
        if (isUpdating.current) {
            return;
        }

        // Clear existing timeout
        if (reloadTimeout.current) {
            clearTimeout(reloadTimeout.current);
        }

        // Function to trigger reactive field update
        const triggerUpdate = async () => {
            isUpdating.current = true;
            const formData = latest.current.getFormData ? latest.current.getFormData() : {};

            try {
                // POST to reactive field endpoint
                const response = await fetch('/reactive-fields/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        controller: 'App\\Http\\Controllers\\DemoController',
                        method: 'getSchema',
                        data: formData,
                        changed_field: fieldName,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Failed to update reactive fields');
                }

                const result = await response.json();

                // Update the schema with the new data
                if (latest.current.updateSchema && result.schema) {
                    latest.current.updateSchema(result.schema);
                }
            } catch (error) {
                console.error('Error updating reactive fields:', error);
            } finally {
                // Reset the flag after a short delay to allow schema updates to complete
                setTimeout(() => {
                    isUpdating.current = false;
                }, 100);
            }
        };

        // Apply debounce if needed
        if (debounceMs > 0) {
            reloadTimeout.current = setTimeout(triggerUpdate, debounceMs);
        } else {
            void triggerUpdate();
        }
    }, [fieldValue, fieldName, isLive, isLazy, liveDebounce, latest]);

    // If this field depends on other fields, filter options based on dependencies
    const getFilteredOptions = (allOptions: any[] | Record<string, any>): any[] => {
        // If no dependencies or no dependent options, return all options as-is
        if (!dependsOn || dependsOn.length === 0 || !dependentOptions) {
            // If allOptions is already an array, return it
            if (Array.isArray(allOptions)) {
                return allOptions;
            }
            // If it's an object, convert to array format
            return Object.entries(allOptions).map(([value, label]) => ({
                value: String(value),
                label: String(label),
            }));
        }

        // Get current form data to check dependency values
        const formData = latest.current.getFormData ? latest.current.getFormData() : {};

        // Get the value of the field we depend on (first dependency)
        const parentFieldName = dependsOn[0];
        const parentValue = formData[parentFieldName];

        // If parent value is not set, return empty options
        if (!parentValue) {
            return [];
        }

        // Get options for the selected parent value
        const optionsForParent = dependentOptions[parentValue];

        if (!optionsForParent) {
            return [];
        }

        // Convert to array format
        return Object.entries(optionsForParent).map(([value, label]) => ({
            value: String(value),
            label: String(label as string),
        }));
    };

    return {
        getFilteredOptions,
    };
}
