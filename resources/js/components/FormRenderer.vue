<template>
    <div :class="containerClass">
        <component
            v-for="(component, index) in schema"
            :key="component.id || index"
            :is="getComponent(component)"
            v-bind="component"
        />
    </div>
</template>

<script setup lang="ts">
import { defineAsyncComponent, onMounted, computed } from 'vue'

const props = defineProps<{
    schema: Array<any>
}>()

// Determine container spacing based on what we're rendering
const containerClass = computed(() => {
    if (!props.schema || props.schema.length === 0) return ''

    // Check if we're rendering sections - if so, use space-y-12
    const hasSections = props.schema.some(c => c.component === 'section')
    if (hasSections) return 'space-y-12'

    // Otherwise use space-y-6 for general spacing
    return 'space-y-6'
})

onMounted(() => {
    console.log('FormRenderer schema:', props.schema)
    if (props.schema && props.schema.length > 0) {
        console.log('First component:', props.schema[0])
        console.log('Component type:', props.schema[0]?.component || props.schema[0]?.type)
    }
})

// Map component types to their Vue components
const componentMap: Record<string, any> = {
    // Schema layout components
    tabs: defineAsyncComponent(() => import('./schema/Tabs.vue')),
    section: defineAsyncComponent(() => import('./schema/Section.vue')),
    grid: defineAsyncComponent(() => import('./schema/Grid.vue')),

    // Form field components
    text_input: defineAsyncComponent(() => import('./fields/TextInput.vue')),
    textarea: defineAsyncComponent(() => import('./fields/Textarea.vue')),
    select: defineAsyncComponent(() => import('./fields/Select.vue')),
    checkbox: defineAsyncComponent(() => import('./fields/Checkbox.vue')),
    radio: defineAsyncComponent(() => import('./fields/Radio.vue')),
    toggle: defineAsyncComponent(() => import('./fields/Toggle.vue')),
    toggle_buttons: defineAsyncComponent(() => import('./fields/ToggleButtons.vue')),
    date_picker: defineAsyncComponent(() => import('./fields/DatePicker.vue')),
    time_picker: defineAsyncComponent(() => import('./fields/TimePicker.vue')),
    date_time_picker: defineAsyncComponent(() => import('./fields/DateTimePicker.vue')),
    date_range_picker: defineAsyncComponent(() => import('./fields/DateRangePicker.vue')),
    color_picker: defineAsyncComponent(() => import('./fields/ColorPicker.vue')),
    file_upload: defineAsyncComponent(() => import('./fields/FileUpload.vue')),
    rich_editor: defineAsyncComponent(() => import('./fields/RichEditor.vue')),
    markdown_editor: defineAsyncComponent(() => import('./fields/MarkdownEditor.vue')),
    tags_input: defineAsyncComponent(() => import('./fields/TagsInput.vue')),
    key_value: defineAsyncComponent(() => import('./fields/KeyValue.vue')),
    repeater: defineAsyncComponent(() => import('./fields/Repeater.vue')),
    builder: defineAsyncComponent(() => import('./fields/Builder.vue')),
    icon_picker: defineAsyncComponent(() => import('./fields/IconPicker.vue')),
    number_field: defineAsyncComponent(() => import('./fields/NumberField.vue')),
    pin_input: defineAsyncComponent(() => import('./fields/PinInput.vue')),
    rate_input: defineAsyncComponent(() => import('./fields/RateInput.vue')),
}

const getComponent = (component: any) => {
    // Get component type from the component object
    const type = component.component || 'div'

    console.log('FormRenderer rendering:', {
        component: type,
        exists: !!componentMap[type],
        name: component.name
    })

    const result = componentMap[type] || 'div'
    if (result === 'div') {
        console.warn('FormRenderer: Falling back to div for:', component)
    }

    // Return the mapped component or a div fallback
    return result
}
</script>
