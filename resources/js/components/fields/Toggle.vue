<template>
    <!-- Use slot if provided (Blade mode), otherwise use internal template (Direct Vue mode) -->
    <slot v-if="$slots.default"
        :name="name"
        :label="label"
        :helperText="helperText"
        :toggleValue="toggleValue"
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
        <div class="flex items-center justify-between space-x-2">
            <Label :for="name" class="flex flex-col space-y-1">
                <span>{{ label }}</span>
                <span v-if="!hasError && helperText" class="font-normal text-sm text-muted-foreground">
                    {{ helperText }}
                </span>
            </Label>
            <Switch
                :id="name"
                :name="name"
                v-model:checked="toggleValue"
                :disabled="disabled"
                :aria-invalid="hasError ? 'true' : 'false'"
                :aria-describedby="hasError ? `${name}-error` : undefined"
            />
        </div>
    </FieldWrapper>
</template>

<script setup lang="ts">
import { ref, computed, inject } from 'vue'
import { Switch } from '@/components/ui/switch'
import { Label } from '@/components/ui/label'
import FieldWrapper from '../FieldWrapper.vue'
import type { ComputedRef } from 'vue'

const props = defineProps<{
    name: string
    label?: string
    hint?: string
    helperText?: string
    required?: boolean
    disabled?: boolean
    value?: boolean
    hidden?: boolean
    columnSpan?: number | string
}>()

const toggleValue = ref(props.value || false)

// Inject errors from parent
const errors = inject<ComputedRef<Record<string, string | string[]>>>('errors', computed(() => ({})))

const errorMessage = computed(() => {
    const error = errors.value[props.name]
    if (!error) return null
    return Array.isArray(error) ? error[0] : error
})

const hasError = computed(() => !!errorMessage.value)
</script>
