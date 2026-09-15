<template>
    <component :is="as" ref="formRef" :class="containerClass" @submit.prevent>
        <template v-for="(component, index) in internalSchema" :key="component.name || component.id || index">
            <!-- Group consecutive actions together (only render on first action in group) -->
            <div v-if="isAction(component) && !isPreviousItemAction(index) && isNextItemAction(index)" class="flex flex-wrap gap-2 items-start">
                <FormActionButton
                    v-bind="component"
                    :getFormData="getFormData"
                />
                <template v-for="(nextComponent, nextIndex) in getConsecutiveActions(index)" :key="nextComponent.name || nextComponent.id || (index + nextIndex + 1)">
                    <FormActionButton
                        v-bind="nextComponent"
                        :getFormData="getFormData"
                    />
                </template>
            </div>

            <!-- Render single action (not part of a group) -->
            <FormActionButton
                v-else-if="isAction(component) && !isPreviousItemAction(index) && !isNextItemAction(index)"
                v-bind="component"
                :getFormData="getFormData"
            />

            <!-- Render regular form components -->
            <component
                v-else-if="!isAction(component)"
                :is="getComponent(component)"
                v-bind="getComponentProps(component)"
                :value="isSchemaComponent(component) ? undefined : internalFormData[component.name]"
                :model-value="isSchemaComponent(component) ? internalFormData : internalFormData[component.name]"
                :disabled="disabled || component.disabled"
                :error="getError(component)"
                @update:model-value="(value) => handleComponentUpdate(component, value)"
            />
        </template>
    </component>
</template>

<script lang="ts">
// Module-level (shared by all instances): gives every Form a unique 'laravilt:form-scope' id
let formScopeCounter = 0
</script>

<script setup lang="ts">
import { defineAsyncComponent, onMounted, onUnmounted, computed, h, ref, watch, provide, inject, nextTick } from 'vue'
import ActionButton from '@laravilt/actions/components/ActionButton.vue'

// Import commonly used components directly for faster modal/form load
import Grid from './schema/Grid.vue'
import Section from './schema/Section.vue'
import TextInput from './fields/TextInput.vue'
import Textarea from './fields/Textarea.vue'
import Toggle from './fields/Toggle.vue'
import Checkbox from './fields/Checkbox.vue'
import CheckboxList from './fields/CheckboxList.vue'
import Select from './fields/Select.vue'
import Hidden from './fields/Hidden.vue'

// Unique id of this form root, provided as 'laravilt:form-scope' (see ActionButton's action-updated-data event)
const formScope = `laravilt-form-${++formScopeCounter}`

// A <form>, or a <div> when nested inside another form (e.g. tab content) since nested forms are invalid HTML
const formRef = ref<HTMLElement | null>(null)
const internalFormData = ref<Record<string, any>>({})

// Local validation errors for client-side validation
const localErrors = ref<Record<string, string>>({})

// Inject errors from ErrorProvider (it's a computed ref)
const injectedErrors = inject<any>('errors', {})

// Merged errors: local client-side errors + server-side errors
const errors = computed(() => {
    const serverErrors = injectedErrors?.value || injectedErrors || {}
    return { ...localErrors.value, ...serverErrors }
})

// Create a wrapper component for form actions that can collect form data
const FormActionButton = {
    props: {
        getFormData: Function,
    },
    setup(props: any, { attrs }: any) {
        return () => h(ActionButton, {
            ...attrs,
            getFormData: props.getFormData,
        })
    },
}

const props = withDefaults(defineProps<{
    schema: Array<any>
    modelValue?: Record<string, any>
    schemaId?: string
    disabled?: boolean
    formController?: string
    formMethod?: string
    as?: 'form' | 'div'
}>(), {
    formController: undefined,
    formMethod: 'getSchema',
    as: 'form',
})

const emit = defineEmits<{
    'update:modelValue': [value: Record<string, any>]
    'update:schema': [value: any[]]
}>()

// Make schema internally reactive so it can be updated by reactive fields
const internalSchema = ref(props.schema)

// Watch for prop schema changes (from page navigation, etc.)
watch(() => props.schema, (newSchema) => {
    internalSchema.value = newSchema
})

// Recursively extract all field components and their default values from schema
const extractFieldDefaults = (schema: Array<any>): Record<string, any> => {
    const defaults: Record<string, any> = {}
    const schemaComponentTypes = ['tabs', 'section', 'grid']

    // Safety check - ensure schema is an array
    if (!schema || !Array.isArray(schema)) {
        console.warn('extractFieldDefaults: schema is not an array', schema)
        return defaults
    }

    for (const component of schema) {
        // Skip actions
        if (component.hasAction === true || (component.name && !component.component)) {
            continue
        }

        // If it's a tabs component, extract from all tabs
        if (component.component === 'tabs' && component.tabs && Array.isArray(component.tabs)) {
            for (const tab of component.tabs) {
                if (tab.schema && Array.isArray(tab.schema)) {
                    Object.assign(defaults, extractFieldDefaults(tab.schema))
                }
            }
            continue // Don't add the tabs component itself as a field
        }

        // If it's a schema component (section, grid), recurse into its schema
        if (schemaComponentTypes.includes(component.component) && component.schema && Array.isArray(component.schema)) {
            Object.assign(defaults, extractFieldDefaults(component.schema))
            continue // Don't add the schema component itself as a field
        }

        // If it has a name and component type (it's an actual field), add its default value
        if (component.name && component.component && !schemaComponentTypes.includes(component.component)) {
            // Use defaultValue or default, but NOT value (value could be an object from backend)
            // For hidden fields, also check the 'default' property
            // Use ?? (nullish coalescing) to properly handle false/0 values
            defaults[component.name] = component.defaultValue ?? component.default ?? component.value ?? null
        }
    }

    return defaults
}

// Initialize form data with defaults from schema
// Note: The watcher with immediate: true handles initial modelValue merging
const initializeFormData = () => {
    // Only initialize with defaults if modelValue is empty
    // The watcher handles cases where modelValue is provided
    if (!props.modelValue || Object.keys(props.modelValue).length === 0) {
        const defaults = extractFieldDefaults(internalSchema.value)
        internalFormData.value = { ...defaults }
        emit('update:modelValue', internalFormData.value)
    }
}

// Handle action-updated data events
const handleActionUpdatedData = (event: CustomEvent) => {
    // Ignore data from an action that belongs to another form (unscoped events still apply)
    const eventScope = (event as any).laraviltFormScope;
    if (eventScope && eventScope !== formScope) {
        return;
    }

    const updatedData = event.detail;

    if (updatedData && typeof updatedData === 'object') {
        // Merge the updated data into internal form data
        internalFormData.value = {
            ...internalFormData.value,
            ...updatedData
        };
        emit('update:modelValue', internalFormData.value);
    }
};

// Initialize on mount
onMounted(() => {
    initializeFormData();

    // Listen for action-updated-data events from ActionButton
    window.addEventListener('action-updated-data', handleActionUpdatedData as EventListener);
});

// Cleanup on unmount
onUnmounted(() => {
    window.removeEventListener('action-updated-data', handleActionUpdatedData as EventListener);
});

// Watch for internal schema changes (from reactive fields)
// Don't re-initialize form data as it will reset user input
// The schema update only changes field options, not the data itself
watch(internalSchema, () => {
    // Intentionally empty - we don't need to do anything when schema changes
    // The reactive update already handles option changes
}, { deep: true })

// Whether the last modelValue had data. A parent that passes a fresh `{}` on every render must not
// wipe user input, so only a genuine change from data to empty resets the form.
let modelHadData = false

// Watch for external modelValue changes and merge them
// Use immediate: true to handle initial value properly (important for edit/view modals)
watch(() => props.modelValue, (newValue) => {
    const hasData = !!newValue && Object.keys(newValue).length > 0

    if (hasData) {
        // Get defaults from schema
        const defaults = extractFieldDefaults(internalSchema.value)
        // Merge defaults with new value - new value takes precedence
        internalFormData.value = {
            ...defaults,
            ...newValue
        }
    } else if (modelHadData) {
        // The parent cleared the model (e.g. after submit): reset to the schema defaults
        internalFormData.value = { ...extractFieldDefaults(internalSchema.value) }
        localErrors.value = {}
        emit('update:modelValue', internalFormData.value)
    }

    modelHadData = hasData
}, { deep: true, immediate: true })

// Handle component update events
const handleComponentUpdate = async (component: any, value: any) => {
    if (isSchemaComponent(component)) {
        // For schema components (Section, Grid, Tabs), value is an object with field updates
        // Update internal form data first
        const oldData = { ...internalFormData.value }

        // Check if any values actually changed before proceeding
        let hasChanges = false
        for (const [fieldName, fieldValue] of Object.entries(value)) {
            if (oldData[fieldName] !== fieldValue) {
                hasChanges = true
                break
            }
        }

        // Skip if nothing changed (performance optimization)
        if (!hasChanges) {
            return
        }

        internalFormData.value = { ...internalFormData.value, ...value }
        emit('update:modelValue', internalFormData.value)

        // Check each field that changed for reactivity
        for (const [fieldName, fieldValue] of Object.entries(value)) {
            // Only trigger if value actually changed
            if (oldData[fieldName] !== fieldValue) {
                // Find the field in schema and check if it's reactive
                const field = findFieldInSchema(internalSchema.value, fieldName)
                if (field && (field.isLive || field.isLazy)) {
                    await triggerReactiveFieldUpdate(fieldName, field)
                }
            }
        }
    } else {
        await updateValue(component.name, value)
    }
}

const updateValue = async (name: string, value: any) => {
    // Update internal form data
    internalFormData.value = {
        ...internalFormData.value,
        [name]: value
    }

    emit('update:modelValue', internalFormData.value)

    // Check if this field is reactive (live/lazy)
    const field = findFieldInSchema(internalSchema.value, name)

    if (field && (field.isLive || field.isLazy)) {
        await triggerReactiveFieldUpdate(name, field)
    }
}

// Find a field by name in the schema (recursively)
const findFieldInSchema = (schema: any[], fieldName: string): any => {
    for (const component of schema) {
        if (component.name === fieldName) {
            return component
        }

        // Check nested schemas
        if (component.component === 'tabs' && component.tabs) {
            for (const tab of component.tabs) {
                if (tab.schema) {
                    const found = findFieldInSchema(tab.schema, fieldName)
                    if (found) return found
                }
            }
        }

        if (component.schema) {
            const found = findFieldInSchema(component.schema, fieldName)
            if (found) return found
        }
    }
    return null
}

// Sequence number of the latest reactive request; older responses that finish later are ignored
let reactiveRequestSeq = 0

// Trigger reactive field update
const triggerReactiveFieldUpdate = async (fieldName: string, field: any) => {
    // Skip if no form controller is configured
    if (!props.formController) {
        console.warn('[Form] No formController configured, skipping reactive field update')
        return
    }

    const requestId = ++reactiveRequestSeq

    const debounceMs = field.isLazy
        ? (field.liveDebounce || 500)
        : (field.isLive && field.liveDebounce ? field.liveDebounce : 0)

    // TODO: Implement debouncing if needed
    try {
        const payload = {
            controller: props.formController,
            method: props.formMethod || 'getSchema',
            data: internalFormData.value,
            changed_field: fieldName,
        }

        const response = await fetch('/reactive-fields/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(payload),
        })

        if (!response.ok) {
            throw new Error('Failed to update reactive fields')
        }

        const result = await response.json()

        // A newer reactive request started while this one was in flight; its response wins
        if (requestId !== reactiveRequestSeq) {
            return
        }

        if (result.schema) {
            updateSchema(result.schema)
        }

        // Update form data if backend modified it (from afterStateUpdated)
        if (result.data) {
            internalFormData.value = { ...internalFormData.value, ...result.data }
            emit('update:modelValue', internalFormData.value)
        }
    } catch (error) {
        console.error('[FormRenderer] Error updating reactive fields:', error)
    }
}

// Collect all form data - just return the internal tracked data
const getFormData = () => {
    // Return a copy to avoid mutations
    return { ...internalFormData.value }
}

// Extract required fields from schema recursively
const extractRequiredFields = (schema: Array<any>): Array<{ name: string; label: string }> => {
    const requiredFields: Array<{ name: string; label: string }> = []
    const schemaComponentTypes = ['tabs', 'section', 'grid']

    if (!schema || !Array.isArray(schema)) {
        return requiredFields
    }

    for (const component of schema) {
        // Skip actions
        if (component.hasAction === true || (component.name && !component.component)) {
            continue
        }

        // If it's a tabs component, extract from all tabs
        if (component.component === 'tabs' && component.tabs && Array.isArray(component.tabs)) {
            for (const tab of component.tabs) {
                if (tab.schema && Array.isArray(tab.schema)) {
                    requiredFields.push(...extractRequiredFields(tab.schema))
                }
            }
            continue
        }

        // If it's a schema component (section, grid), recurse into its schema
        if (schemaComponentTypes.includes(component.component) && component.schema && Array.isArray(component.schema)) {
            requiredFields.push(...extractRequiredFields(component.schema))
            continue
        }

        // If it has a name and is required, add to the list
        if (component.name && component.required) {
            requiredFields.push({
                name: component.name,
                label: component.label || component.name
            })
        }
    }

    return requiredFields
}

// Check if a value is empty (null, undefined, empty string, empty array)
const isValueEmpty = (value: any): boolean => {
    if (value === null || value === undefined) return true
    if (typeof value === 'string' && value.trim() === '') return true
    if (Array.isArray(value) && value.length === 0) return true
    return false
}

// Validate the form - combines HTML5 validation with custom required field checks
const validateForm = () => {
    // Clear previous local validation errors
    localErrors.value = {}

    // Get all required fields from schema
    const requiredFields = extractRequiredFields(internalSchema.value)

    // Check each required field
    const newErrors: Record<string, string> = {}
    for (const field of requiredFields) {
        const value = internalFormData.value[field.name]
        if (isValueEmpty(value)) {
            newErrors[field.name] = `${field.label} is required.`
        }
    }

    // If there are validation errors, set them and return false
    if (Object.keys(newErrors).length > 0) {
        localErrors.value = newErrors
        return false
    }

    // Also run HTML5 validation for native form elements
    if (formRef.value instanceof HTMLFormElement) {
        const isHtml5Valid = formRef.value.checkValidity()
        if (!isHtml5Valid) {
            formRef.value.reportValidity()
            return false
        }
    }

    return true
}

// Clear local validation errors (useful when modal closes or form resets)
const clearValidationErrors = () => {
    localErrors.value = {}
}

// Function to update schema (for reactive fields)
const updateSchema = (newSchema: any[]) => {
    // Save current scroll position and focused element
    const scrollX = window.scrollX
    const scrollY = window.scrollY
    const activeElement = document.activeElement as HTMLElement

    // Simply update the schema - Vue's reactivity will handle the updates
    // Since we're using ref(), Vue will detect the change
    internalSchema.value = newSchema

    // Restore scroll position and focus immediately in next tick
    nextTick(() => {
        // Restore scroll position
        window.scrollTo({
            top: scrollY,
            left: scrollX,
            behavior: 'instant' as ScrollBehavior
        })

        // Restore focus if element still exists
        if (activeElement && document.contains(activeElement)) {
            activeElement.focus({ preventScroll: true })
        }
    })
}

// Provide getFormData, validateForm, updateSchema, schemaId, formController, and errors to all child components
provide('getFormData', getFormData)
provide('validateForm', validateForm)
provide('updateSchema', updateSchema)
provide('schemaId', props.schemaId || null)
provide('formController', props.formController)
provide('formMethod', props.formMethod)
// Override the errors provided by ErrorProvider with our merged errors (local + server)
provide('errors', errors)
// Scope for action-updated-data events: actions inside this form only update this form
provide('laravilt:form-scope', formScope)

// Expose getFormData, validateForm, and clearValidationErrors to parent components via template ref
defineExpose({
    getFormData,
    validateForm,
    clearValidationErrors
})

// Check if an item is an action
const isAction = (item: any) => {
    // Actions have hasAction property or don't have a component property
    return item.hasAction === true || (item.name && !item.component)
}

// Check if the next item is an action
const isNextItemAction = (index: number) => {
    const nextItem = internalSchema.value[index + 1]
    return nextItem && isAction(nextItem)
}

// Check if the previous item is an action
const isPreviousItemAction = (index: number) => {
    const prevItem = internalSchema.value[index - 1]
    return prevItem && isAction(prevItem)
}

// Get all consecutive actions starting from the next index
const getConsecutiveActions = (startIndex: number) => {
    const actions = []
    let currentIndex = startIndex + 1

    while (currentIndex < internalSchema.value.length && isAction(internalSchema.value[currentIndex])) {
        actions.push(internalSchema.value[currentIndex])
        currentIndex++
    }

    return actions
}

// Check if a component is a schema component (needs entire modelValue, not just a field value)
const isSchemaComponent = (component: any) => {
    const schemaComponents = ['tabs', 'section', 'grid']
    return schemaComponents.includes(component.component)
}

// Determine container spacing based on what we're rendering
const containerClass = computed(() => {
    if (!internalSchema.value || !Array.isArray(internalSchema.value) || internalSchema.value.length === 0) return ''

    // Check if we're rendering sections - if so, use space-y-12
    const hasSections = internalSchema.value.some(c => c.component === 'section')
    if (hasSections) return 'space-y-12'

    // Otherwise use space-y-6 for general spacing
    return 'space-y-6'
})

const toLaraviltName = (name: any) => {
  if (!name) return
  return 'laravilt-' + name.replaceAll('_', '-')
}

const getComponent = (component: any) => {
  return toLaraviltName(component.component) || 'div'
}

// Get component props, excluding value, modelValue, and disabled since we set them explicitly
const getComponentProps = (component: any) => {
    const { value, modelValue, disabled, ...rest } = component
    return rest
}

// Get error message for a component
const getError = (component: any) => {
    // errors is a computed ref, access its value
    const errorsObj = errors.value
    if (!component.name || !errorsObj) return undefined
    const error = errorsObj[component.name]
    return Array.isArray(error) ? error[0] : error
}
</script>
