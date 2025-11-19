<template>
    <div :class="gridClasses">
        <div
            v-for="(child, index) in schema"
            :key="child.id || index"
            :class="getColumnSpanClass(child)"
        >
            <component
                :is="getComponent(child)"
                v-bind="child"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue'

const props = defineProps<{
    columns: number | Record<string, number>
    schema: Array<any>
}>()

const componentMap: Record<string, any> = {
    text_input: defineAsyncComponent(() => import('../fields/TextInput.vue')),
    textarea: defineAsyncComponent(() => import('../fields/Textarea.vue')),
    select: defineAsyncComponent(() => import('../fields/Select.vue')),
    checkbox: defineAsyncComponent(() => import('../fields/Checkbox.vue')),
    radio: defineAsyncComponent(() => import('../fields/Radio.vue')),
    toggle: defineAsyncComponent(() => import('../fields/Toggle.vue')),
    toggle_buttons: defineAsyncComponent(() => import('../fields/ToggleButtons.vue')),
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
    console.log('Grid rendering:', {
        component: type,
        exists: !!componentMap[type],
        hasType: !!component.type,
        actualType: component.type,
        name: component.name
    })
    const result = componentMap[type] || 'div'
    if (result === 'div') {
        console.warn('Falling back to div for:', component)
    }
    return result
}

const gridClasses = computed(() => {
    const classes = ['grid', 'gap-6']

    if (typeof props.columns === 'number') {
        classes.push(`grid-cols-1`)
        if (props.columns > 1) {
            classes.push(`md:grid-cols-${props.columns}`)
        }
    } else if (typeof props.columns === 'object') {
        if (props.columns.default) classes.push(`grid-cols-${props.columns.default}`)
        if (props.columns.sm) classes.push(`sm:grid-cols-${props.columns.sm}`)
        if (props.columns.md) classes.push(`md:grid-cols-${props.columns.md}`)
        if (props.columns.lg) classes.push(`lg:grid-cols-${props.columns.lg}`)
        if (props.columns.xl) classes.push(`xl:grid-cols-${props.columns.xl}`)
    }

    return classes.join(' ')
})

const getColumnSpanClass = (child: any): string => {
    if (!child.columnSpan) return ''

    if (typeof child.columnSpan === 'number') {
        return `col-span-${child.columnSpan}`
    }

    if (typeof child.columnSpan === 'object') {
        const classes: string[] = []
        if (child.columnSpan.default) classes.push(`col-span-${child.columnSpan.default}`)
        if (child.columnSpan.sm) classes.push(`sm:col-span-${child.columnSpan.sm}`)
        if (child.columnSpan.md) classes.push(`md:col-span-${child.columnSpan.md}`)
        if (child.columnSpan.lg) classes.push(`lg:col-span-${child.columnSpan.lg}`)
        return classes.join(' ')
    }

    return ''
}
</script>
