<template>
    <!-- Use slot if provided (Blade mode), otherwise use internal template (Direct Vue mode) -->
    <slot v-if="$slots.default"
        :id="id || name"
        :name="name"
        :type="type || 'text'"
        :inputValue="inputValue"
        :placeholder="placeholder"
        :readonly="readonly"
        :autofocus="autofocus"
        :autocomplete="autocomplete"
        :minLength="minLength"
        :maxLength="maxLength"
        :pattern="pattern"
        :prefixText="prefixText"
        :showCharacterCount="showCharacterCount"
        :characterCount="characterCount"
        :hasError="hasError"
        :extraAttributes="extraAttributes"
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
        <!-- Phone Input for tel type -->
        <PhoneInput
            v-if="type === 'tel'"
            :name="name"
            v-model="inputValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
        />

        <!-- Regular input for other types -->
        <div v-else class="relative">
            <div
                v-if="prefixText"
                class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-muted-foreground text-sm"
            >
                {{ prefixText }}
            </div>

            <Input
                :id="id || name"
                :name="name"
                :type="type || 'text'"
                v-model="inputValue"
                :placeholder="placeholder"
                :readonly="readonly"
                :autofocus="autofocus"
                :autocomplete="autocomplete"
                :minlength="minLength"
                :maxlength="maxLength"
                :pattern="pattern"
                v-bind="extraAttributes"
                :class="[
                    prefixText ? 'pl-8' : '',
                    hasError ? 'border-destructive focus-visible:ring-destructive' : ''
                ]"
                :aria-invalid="hasError ? 'true' : 'false'"
                :aria-describedby="hasError ? `${name}-error` : undefined"
            />
        </div>

        <div v-if="showCharacterCount && maxLength && type !== 'tel'" class="text-xs text-muted-foreground text-right mt-1.5">
            {{ characterCount }} / {{ maxLength }}
        </div>
    </FieldWrapper>
</template>

<script setup lang="ts">
import { ref, computed, inject, useSlots, onMounted } from 'vue'
import { Input } from '@/components/ui/input'
import PhoneInput from '../PhoneInput.vue'
import FieldWrapper from '../FieldWrapper.vue'
import type { ComputedRef } from 'vue'

const slots = useSlots()

onMounted(() => {
    console.log('TextInput mounted for:', props.name)
    console.log('Has default slot:', !!slots.default)
    console.log('Props:', { name: props.name, label: props.label, type: props.type, placeholder: props.placeholder })
    console.log('inputValue:', inputValue.value)
    console.log('Element:', document.querySelector(`[data-field-name="${props.name}"]`))
})

const props = defineProps<{
    id?: string
    name: string
    type?: string
    label?: string
    placeholder?: string
    hint?: string
    required?: boolean
    disabled?: boolean
    readonly?: boolean
    autofocus?: boolean
    autocomplete?: string
    minLength?: number
    maxLength?: number
    pattern?: string
    prefixText?: string
    helperText?: string
    showCharacterCount?: boolean
    value?: string
    extraAttributes?: Record<string, any>
    hidden?: boolean
    columnSpan?: number | string
}>()

const inputValue = ref(props.value || '')

const characterCount = computed(() => inputValue.value.length)

// Inject errors from parent
const errors = inject<ComputedRef<Record<string, string | string[]>>>('errors', computed(() => ({})))

const errorMessage = computed(() => {
    const error = errors.value[props.name]
    if (!error) return null
    return Array.isArray(error) ? error[0] : error
})

const hasError = computed(() => !!errorMessage.value)
</script>
