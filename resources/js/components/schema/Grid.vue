<template>
    <div v-if="visibleSchema.length > 0" :class="gridClasses">
        <div
            v-for="(child, index) in visibleSchema"
            :key="child.name || child.id || index"
            :class="getColumnSpanClass(child)"
        >
            <component
                :is="getComponent(child)"
                v-bind="getComponentProps(child)"
                :value="isSchemaComponent(child) ? undefined : modelValue?.[child.name]"
                :model-value="isSchemaComponent(child) ? modelValue : modelValue?.[child.name]"
                :disabled="props.disabled || child.disabled"
                @update:model-value="(value) => isSchemaComponent(child) ? updateSchemaValue(value) : updateValue(child.name, value)"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue'
import { colSpanClass, gridColsClass } from '../../lib/gridClasses'

// Import commonly used components directly for faster modal load
import TextInput from '../fields/TextInput.vue'
import Textarea from '../fields/Textarea.vue'
import Toggle from '../fields/Toggle.vue'
import Checkbox from '../fields/Checkbox.vue'
import CheckboxList from '../fields/CheckboxList.vue'
import Select from '../fields/Select.vue'

const props = defineProps<{
    columns: number | Record<string, number>
    schema: Array<any>
    modelValue?: Record<string, any>
    disabled?: boolean
}>()

const emit = defineEmits<{
    'update:modelValue': [value: Record<string, any>]
}>()

// Filter out hidden fields from schema
const visibleSchema = computed(() => {
    return props.schema.filter(child => child && child.hidden !== true)
})

const updateValue = (name: string, value: any) => {
    const newValue = {
        ...(props.modelValue || {}),
        [name]: value
    }
    emit('update:modelValue', newValue)
}

// Schema components (Tabs, Section, Grid) take the whole model, not a single field value
const schemaComponentTypes = ['tabs', 'section', 'grid']
const isSchemaComponent = (component: any) => schemaComponentTypes.includes(component.component)

// Nested schema components emit the whole (merged) model
const updateSchemaValue = (value: Record<string, any>) => {
    emit('update:modelValue', { ...(props.modelValue || {}), ...(value || {}) })
}

// Get component props, excluding value, modelValue, and disabled since we set them explicitly
const getComponentProps = (component: any) => {
    const { value, modelValue, disabled, ...rest } = component
    return rest
}

const toLaraviltName = (name: any) => {
  if (!name) return
  return 'laravilt-' + name.replaceAll('_', '-')
}

const getComponent = (component: any) => {
  return toLaraviltName(component.component) || 'div'
}

const gridClasses = computed(() => {
    const classes = ['grid', 'gap-6']

    if (typeof props.columns === 'number') {
        classes.push('grid-cols-1')
        if (props.columns > 1) {
            classes.push(gridColsClass(props.columns, 'md'))
        }
    } else if (props.columns && typeof props.columns === 'object') {
        for (const breakpoint of ['default', 'sm', 'md', 'lg', 'xl']) {
            if (props.columns[breakpoint]) classes.push(gridColsClass(props.columns[breakpoint], breakpoint))
        }
    }

    return classes.filter(Boolean).join(' ')
})

const getColumnSpanClass = (child: any): string => {
    if (!child.columnSpan) return ''

    // Handle 'full' string value from columnSpanFull()
    if (child.columnSpan === 'full') {
        return 'col-span-full'
    }

    if (typeof child.columnSpan === 'number') {
        return colSpanClass(child.columnSpan)
    }

    if (typeof child.columnSpan === 'object') {
        const classes: string[] = []
        for (const breakpoint of ['default', 'sm', 'md', 'lg']) {
            if (child.columnSpan[breakpoint]) classes.push(colSpanClass(child.columnSpan[breakpoint], breakpoint))
        }
        return classes.filter(Boolean).join(' ')
    }

    return ''
}
</script>
