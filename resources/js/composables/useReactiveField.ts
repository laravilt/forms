import { watch, inject, ref, type Ref } from 'vue'

/**
 * Composable for handling reactive field updates (live/lazy fields).
 *
 * This handles the automatic update of dependent fields when a field value changes.
 * It watches the field's value and POSTs to the backend to re-evaluate the schema,
 * updating dependent field options dynamically.
 *
 * @param fieldName - The name of the field
 * @param fieldValue - A ref to the field's current value
 * @param options - Configuration options
 */
export function useReactiveField(
    fieldName: string | undefined,
    fieldValue: Ref<any>,
    options: {
        isLive?: boolean
        isLazy?: boolean
        liveDebounce?: number
        dependsOn?: string[]
        dependentOptions?: Record<string, any>
    } = {}
) {
    const { isLive, isLazy, liveDebounce = 500, dependsOn = [], dependentOptions } = options

    // Inject dependencies from FormRenderer
    const getFormData = inject<(() => Record<string, any>) | undefined>('getFormData', undefined)
    const updateSchema = inject<((schema: any[]) => void) | undefined>('updateSchema', undefined)

    // If this field is live/lazy, watch for changes and POST to backend
    if ((isLive || isLazy) && fieldName) {
        let reloadTimeout: ReturnType<typeof setTimeout> | null = null
        let isUpdating = ref(false) // Flag to prevent duplicate updates

        const debounceMs = isLazy ? liveDebounce : (isLive && liveDebounce > 0 ? liveDebounce : 0)

        watch(fieldValue, async (newValue, oldValue) => {
            // Skip if already updating
            if (isUpdating.value) {
                return
            }

            // Clear existing timeout
            if (reloadTimeout) {
                clearTimeout(reloadTimeout)
            }

            // Function to trigger reactive field update
            const triggerUpdate = async () => {
                isUpdating.value = true
                const formData = getFormData ? getFormData() : {}

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
                    })

                    if (!response.ok) {
                        throw new Error('Failed to update reactive fields')
                    }

                    const result = await response.json()

                    // Update the schema with the new data
                    if (updateSchema && result.schema) {
                        updateSchema(result.schema)
                    }
                } catch (error) {
                    console.error('Error updating reactive fields:', error)
                } finally {
                    // Reset the flag after a short delay to allow schema updates to complete
                    setTimeout(() => {
                        isUpdating.value = false
                    }, 100)
                }
            }

            // Apply debounce if needed
            if (debounceMs > 0) {
                reloadTimeout = setTimeout(triggerUpdate, debounceMs)
            } else {
                await triggerUpdate()
            }
        })
    }

    // If this field depends on other fields, filter options based on dependencies
    const getFilteredOptions = (allOptions: any[] | Record<string, any>): any[] => {
        // If no dependencies or no dependent options, return all options as-is
        if (!dependsOn || dependsOn.length === 0 || !dependentOptions) {
            // If allOptions is already an array, return it
            if (Array.isArray(allOptions)) {
                return allOptions
            }
            // If it's an object, convert to array format
            return Object.entries(allOptions).map(([value, label]) => ({
                value: String(value),
                label: String(label)
            }))
        }

        // Get current form data to check dependency values
        const formData = getFormData ? getFormData() : {}

        // Get the value of the field we depend on (first dependency)
        const parentFieldName = dependsOn[0]
        const parentValue = formData[parentFieldName]

        // If parent value is not set, return empty options
        if (!parentValue) {
            return []
        }

        // Get options for the selected parent value
        const optionsForParent = dependentOptions[parentValue]

        if (!optionsForParent) {
            return []
        }

        // Convert to array format
        const filteredOptions = Object.entries(optionsForParent).map(([value, label]) => ({
            value: String(value),
            label: String(label as string)
        }))

        return filteredOptions
    }

    return {
        getFilteredOptions
    }
}
