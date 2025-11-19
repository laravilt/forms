<template>
    <div
        :class="[
            'laravilt-field-wrapper',
            columnSpanClass,
            { 'hidden': hidden }
        ]"
        :data-field-name="name"
        :data-field-type="fieldType"
    >
        <!-- Above Label Slot -->
        <div v-if="$slots.aboveLabel" class="field-above-label mb-1">
            <slot name="aboveLabel" />
        </div>

        <!-- Label Section -->
        <div v-if="label && !hiddenLabel" class="field-label-section mb-2">
            <!-- Before Label Slot -->
            <div v-if="$slots.beforeLabel" class="field-before-label inline-block mr-2">
                <slot name="beforeLabel" />
            </div>

            <label
                :for="fieldId"
                :class="[
                    'block text-sm font-medium text-gray-900 dark:text-white',
                    { 'sr-only': labelSrOnly }
                ]"
            >
                {{ label }}

                <span v-if="required" class="text-red-500 ml-0.5" aria-label="required">*</span>

                <span v-if="hint" class="text-xs text-gray-500 ml-2 font-normal">{{ hint }}</span>
            </label>

            <!-- After Label Slot -->
            <div v-if="$slots.afterLabel" class="field-after-label inline-block ml-2">
                <slot name="afterLabel" />
            </div>
        </div>

        <!-- Below Label Slot -->
        <div v-if="$slots.belowLabel" class="field-below-label mb-2">
            <slot name="belowLabel" />
        </div>

        <!-- Above Content Slot -->
        <div v-if="$slots.aboveContent" class="field-above-content mb-2">
            <slot name="aboveContent" />
        </div>

        <!-- Content Section -->
        <div
            :class="[
                'field-content',
                { 'opacity-60 cursor-not-allowed': disabled }
            ]"
        >
            <!-- Before Content Slot -->
            <div v-if="$slots.beforeContent" class="field-before-content mb-2">
                <slot name="beforeContent" />
            </div>

            <!-- Main Content (Input Field) -->
            <slot />

            <!-- After Content Slot -->
            <div v-if="$slots.afterContent" class="field-after-content mt-2">
                <slot name="afterContent" />
            </div>
        </div>

        <!-- Below Content Slot -->
        <div v-if="$slots.belowContent" class="field-below-content mt-2">
            <slot name="belowContent" />
        </div>

        <!-- Helper Text -->
        <p v-if="helperText && !hasError" class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
            {{ helperText }}
        </p>

        <!-- Error Message -->
        <p v-if="hasError && errorMessage" class="mt-1.5 text-xs text-red-600 dark:text-red-400" role="alert">
            {{ errorMessage }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import type { ComputedRef } from 'vue'

const props = defineProps<{
    id?: string
    name: string
    label?: string
    helperText?: string
    hint?: string
    required?: boolean
    disabled?: boolean
    hidden?: boolean
    columnSpan?: number | string
    fieldType?: string
    hiddenLabel?: boolean
    labelSrOnly?: boolean
}>()

// Generate field ID from name if not provided
const fieldId = computed(() => {
    if (props.id) return props.id
    if (props.name) return props.name.toLowerCase().replace(/[^a-z0-9-_]/g, '-')
    return undefined
})

// Inject errors from parent
const errors = inject<ComputedRef<Record<string, string | string[]>>>('errors', computed(() => ({})))

const hasError = computed(() => {
    return errors.value && errors.value[props.name] !== undefined
})

const errorMessage = computed(() => {
    if (!hasError.value) return null
    const error = errors.value[props.name]
    return Array.isArray(error) ? error[0] : error
})

// Column span classes
const columnSpanClass = computed(() => {
    switch (props.columnSpan) {
        case 1: return 'col-span-1'
        case 2: return 'col-span-2'
        case 3: return 'col-span-3'
        case 4: return 'col-span-4'
        case 6: return 'col-span-6'
        case 12:
        case 'full': return 'col-span-12'
        default: return 'col-span-1'
    }
})
</script>
