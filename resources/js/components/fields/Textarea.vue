<template>
    <!-- Use slot if provided (Blade mode), otherwise use internal template (Direct Vue mode) -->
    <slot v-if="$slots.default"
        :id="id || name"
        :name="name"
        :textValue="textValue"
        :placeholder="placeholder"
        :rows="rows"
        :maxLength="maxLength"
        :showCharacterCount="showCharacterCount"
        :showWordCount="showWordCount"
        :wordCount="wordCount"
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
    >
        <Textarea
            :id="id || name"
            :name="name"
            v-model="textValue"
            :placeholder="placeholder"
            :rows="rows"
            :class="[
                hasError ? 'border-destructive focus-visible:ring-destructive' : ''
            ]"
            :aria-invalid="hasError ? 'true' : 'false'"
            :aria-describedby="hasError ? `${name}-error` : undefined"
        />

        <div v-if="showCharacterCount || showWordCount" class="flex gap-4 text-xs text-muted-foreground mt-1.5">
            <span v-if="showCharacterCount && maxLength">
                {{ textValue.length }} / {{ maxLength }} characters
            </span>
            <span v-if="showWordCount">
                {{ wordCount }} words
            </span>
        </div>
    </FieldWrapper>
</template>

<script setup lang="ts">
import { ref, computed, inject } from 'vue'
import Textarea from '@/components/ui/textarea.vue'
import FieldWrapper from '../FieldWrapper.vue'
import type { ComputedRef } from 'vue'

const props = defineProps<{
    id?: string
    name: string
    label?: string
    placeholder?: string
    hint?: string
    required?: boolean
    disabled?: boolean
    rows?: number
    helperText?: string
    value?: string
    maxLength?: number
    showCharacterCount?: boolean
    showWordCount?: boolean
    hidden?: boolean
    columnSpan?: number | string
}>()

const textValue = ref(props.value || '')

const wordCount = computed(() => {
    return textValue.value.trim().split(/\s+/).filter(word => word.length > 0).length
})

// Inject errors from parent
const errors = inject<ComputedRef<Record<string, string | string[]>>>('errors', computed(() => ({})))

const errorMessage = computed(() => {
    const error = errors.value[props.name]
    if (!error) return null
    return Array.isArray(error) ? error[0] : error
})

const hasError = computed(() => !!errorMessage.value)
</script>
