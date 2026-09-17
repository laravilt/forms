<template>
    <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
        <!-- Section Header -->
        <header v-if="heading" :class="['px-6 py-4 transition-all duration-200', isCollapsed ? '' : 'border-b']">
            <div
                :class="[
                    'flex items-center gap-3',
                    collapsible ? 'cursor-pointer select-none' : ''
                ]"
                @click="collapsible && toggleCollapse()"
            >
                <div
                    v-if="icon && getIconComponent(icon)"
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary flex-shrink-0"
                >
                    <component
                        :is="getIconComponent(icon)"
                        class="h-5 w-5"
                    />
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="leading-none font-semibold">
                        {{ heading }}
                    </h3>
                    <p v-if="description" class="mt-1 text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>
                <ChevronDown
                    v-if="collapsible"
                    :class="[
                        'h-4 w-4 text-muted-foreground transition-transform duration-200 ease-out flex-shrink-0',
                        isCollapsed ? '-rotate-90 rtl:rotate-90' : ''
                    ]"
                />
            </div>
        </header>

        <!-- Section Content with smooth collapse -->
        <div
            class="grid transition-all duration-200 ease-out"
            :style="{ gridTemplateRows: isCollapsed ? '0fr' : '1fr' }"
        >
            <div class="overflow-hidden">
                <div class="p-6 space-y-6">
                    <template v-for="(component, index) in schema" :key="component.name || component.id || index">
                        <component
                            :is="getComponent(component)"
                            v-bind="getComponentProps(component)"
                            :value="isSchemaComponent(component) ? undefined : modelValue?.[component.name]"
                            :model-value="isSchemaComponent(component) ? modelValue : modelValue?.[component.name]"
                            :disabled="props.disabled || component.disabled"
                            @update:model-value="(value) => handleComponentUpdate(component, value)"
                        />
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, defineAsyncComponent } from 'vue'
import { ChevronDown } from 'lucide-vue-next'
import * as LucideIcons from 'lucide-vue-next'
import CheckboxList from '../fields/CheckboxList.vue'

const props = defineProps<{
    heading?: string
    description?: string
    icon?: string
    collapsible?: boolean
    collapsed?: boolean
    schema?: Array<any>
    modelValue?: Record<string, any>
    disabled?: boolean
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
    translatable_input: defineAsyncComponent(() => import('../fields/TranslatableInput.vue')),
    select: defineAsyncComponent(() => import('../fields/Select.vue')),
    checkbox: defineAsyncComponent(() => import('../fields/Checkbox.vue')),
    checkbox_list: CheckboxList,
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
    code_editor: defineAsyncComponent(() => import('../fields/CodeEditor.vue')),
    slider: defineAsyncComponent(() => import('../fields/Slider.vue')),
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

// Get component props, excluding value, modelValue, and disabled since we set them explicitly
const getComponentProps = (component: any) => {
    const { value, modelValue, disabled, ...rest } = component
    return rest
}

const isSchemaComponent = (component: any) => {
    const schemaComponents = ['grid']
    return schemaComponents.includes(component.component)
}

const handleComponentUpdate = (component: any, value: any) => {
    console.log('[Section] handleComponentUpdate', {
        componentName: component.name,
        componentType: component.component,
        isSchemaComponent: isSchemaComponent(component),
        value,
        currentModelValue: props.modelValue
    })

    if (isSchemaComponent(component)) {
        // For schema components, merge the entire value object
        const newValue = { ...props.modelValue, ...value }
        console.log('[Section] Emitting schema component update:', newValue)
        emit('update:modelValue', newValue)
    } else {
        // For regular fields, update the specific field
        const newValue = { ...props.modelValue, [component.name]: value }
        console.log('[Section] Emitting field update:', newValue)
        emit('update:modelValue', newValue)
    }
}
</script>
