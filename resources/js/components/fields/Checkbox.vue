<template>
    <!-- Use slot if provided (Blade mode), otherwise use internal template (Direct Vue mode) -->
    <slot v-if="$slots.default"
        :name="name"
        :label="label"
        :options="options"
        :inline="inline"
        :hasError="hasError"
    />

    <!-- Internal template for direct Vue usage -->
    <FieldWrapper v-else
        :name="name"
        :label="label"
        :helper-text="helperText"
        :hint="hint"
        :required="required"
        :disabled="disabled"
        :hidden="hidden"
        :column-span="columnSpan"
        :hidden-label="true"
    >
        <div>
            <Label v-if="label" class="text-sm font-medium block mb-3">{{ label }}</Label>
            <div :class="inline ? 'flex flex-wrap gap-4' : 'space-y-3'">
                <div v-for="(optLabel, optValue) in options" :key="optValue" class="flex items-center space-x-2">
                    <Checkbox
                        :id="`${name}_${optValue}`"
                        :name="`${name}[]`"
                        :value="optValue"
                        :disabled="disabled"
                        :aria-invalid="hasError ? 'true' : 'false'"
                    />
                    <Label
                        :for="`${name}_${optValue}`"
                        class="text-sm font-normal leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                    >
                        {{ optLabel }}
                    </Label>
                </div>
            </div>
        </div>
    </FieldWrapper>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import FieldWrapper from '../FieldWrapper.vue'
import type { ComputedRef } from 'vue'

const props = defineProps<{
    name: string
    label?: string
    hint?: string
    options?: Record<string, string>
    inline?: boolean
    required?: boolean
    disabled?: boolean
    helperText?: string
    hidden?: boolean
    columnSpan?: number | string
}>()

// Inject errors from parent
const errors = inject<ComputedRef<Record<string, string | string[]>>>('errors', computed(() => ({})))

const errorMessage = computed(() => {
    const error = errors.value[props.name]
    if (!error) return null
    return Array.isArray(error) ? error[0] : error
})

const hasError = computed(() => !!errorMessage.value)
</script>
