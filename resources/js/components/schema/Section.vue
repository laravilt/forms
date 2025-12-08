<template>
    <div class="rounded-lg border p-6 space-y-6">
        <!-- Section Header -->
        <header v-if="heading">
            <div
                :class="[
                    'flex items-center gap-2',
                    collapsible ? 'cursor-pointer' : ''
                ]"
                @click="collapsible && toggleCollapse()"
            >
                <component
                    v-if="icon && getIconComponent(icon)"
                    :is="getIconComponent(icon)"
                    class="h-4 w-4 text-muted-foreground flex-shrink-0"
                />
                <h3 class="mb-0.5 text-base font-medium">
                    {{ heading }}
                </h3>
                <ChevronDown
                    v-if="collapsible"
                    :class="[
                        'h-4 w-4 text-muted-foreground transition-transform ml-auto',
                        isCollapsed ? '-rotate-90' : ''
                    ]"
                />
            </div>
            <p v-if="description" class="text-sm text-muted-foreground">
                {{ description }}
            </p>
        </header>

        <!-- Section Content -->
        <div v-show="!isCollapsed" class="space-y-6">
            <template v-for="(component, index) in schema" :key="component.name || component.id || index">
                <component
                    :is="getComponent(component)"
                    v-bind="getComponentProps(component)"
                    :value="isSchemaComponent(component) ? undefined : modelValue?.[component.name]"
                    :modelValue="isSchemaComponent(component) ? modelValue : undefined"
                    @update:model-value="(value) => handleComponentUpdate(component, value)"
                />
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, defineAsyncComponent } from 'vue'
import { ChevronDown } from 'lucide-vue-next'
import * as LucideIcons from 'lucide-vue-next'

const props = defineProps<{
    heading?: string
    description?: string
    icon?: string
    collapsible?: boolean
    collapsed?: boolean
    schema?: Array<any>
    modelValue?: Record<string, any>
}>()

const emit = defineEmits<{
    'update:modelValue': [value: Record<string, any>]
}>()

const isCollapsed = ref(props.collapsed || false)

const toggleCollapse = () => {
    if (props.collapsible) {
        isCollapsed.value = !isCollapsed.value
    }
}

// Convert kebab-case or snake_case icon names to PascalCase for lucide-vue-next
const getIconComponent = (iconName: string) => {
    if (!iconName) return null

    // Convert formats like 'user', 'id-card', 'map-pin' to PascalCase
    const pascalCase = iconName
        .split(/[-_]/)
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join('')

    return (LucideIcons as any)[pascalCase] || null
}

// Map component types to their Vue components
const componentMap: Record<string, any> = {
    // Schema layout components
    grid: defineAsyncComponent(() => import('./Grid.vue')),

    // Form field components
    text_input: defineAsyncComponent(() => import('../fields/TextInput.vue')),
    textarea: defineAsyncComponent(() => import('../fields/Textarea.vue')),
    select: defineAsyncComponent(() => import('../fields/Select.vue')),
    checkbox: defineAsyncComponent(() => import('../fields/Checkbox.vue')),
    radio: defineAsyncComponent(() => import('../fields/Radio.vue')),
    toggle: defineAsyncComponent(() => import('../fields/Toggle.vue')),
    toggle_buttons: defineAsyncComponent(() => import('../fields/ToggleButtons.vue')),
    hidden: defineAsyncComponent(() => import('../fields/Hidden.vue')),
    date_picker: defineAsyncComponent(() => import('../fields/DatePicker.vue')),
    time_picker: defineAsyncComponent(() => import('../fields/TimePicker.vue')),
    date_time_picker: defineAsyncComponent(() => import('../fields/DateTimePicker.vue')),
    date_range_picker: defineAsyncComponent(() => import('../fields/DateRangePicker.vue')),
    color_picker: defineAsyncComponent(() => import('../fields/ColorPicker.vue')),
    file_upload: defineAsyncComponent(() => import('../fields/FileUpload.vue')),
    rich_editor: defineAsyncComponent(() => import('../fields/RichEditor.vue')),
    markdown_editor: defineAsyncComponent(() => import('../fields/MarkdownEditor.vue')),
    tags_input: defineAsyncComponent(() => import('../fields/TagsInput.vue')),
    key_value: defineAsyncComponent(() => import('../fields/KeyValue.vue')),
    repeater: defineAsyncComponent(() => import('../fields/Repeater.vue')),
    builder: defineAsyncComponent(() => import('../fields/Builder.vue')),
    icon_picker: defineAsyncComponent(() => import('../fields/IconPicker.vue')),
    number_field: defineAsyncComponent(() => import('../fields/NumberField.vue')),
    pin_input: defineAsyncComponent(() => import('../fields/PinInput.vue')),
    rate_input: defineAsyncComponent(() => import('../fields/RateInput.vue')),
}

const getComponent = (component: any) => {
    const type = component.component || 'div'
    return componentMap[type] || 'div'
}

const getComponentProps = (component: any) => {
    const { value, modelValue, ...props } = component
    return props
}

const isSchemaComponent = (component: any) => {
    const schemaComponents = ['grid']
    return schemaComponents.includes(component.component)
}

const handleComponentUpdate = (component: any, value: any) => {
    if (isSchemaComponent(component)) {
        // For schema components, merge the entire value object
        emit('update:modelValue', { ...props.modelValue, ...value })
    } else {
        // For regular fields, update the specific field
        emit('update:modelValue', { ...props.modelValue, [component.name]: value })
    }
}
</script>
