<template>
    <!-- Use slot if provided (Blade mode), otherwise use internal template (Direct Vue mode) -->
    <slot
        v-if="$slots.default"
        :name="name"
        :label="label"
        :helperText="helperText"
        :toggleValue="toggleValue"
        :hasError="hasError"
    />

    <!-- Internal template for direct Vue usage -->
    <FieldWrapper
        v-else
        :name="name"
        :label="label"
        :helper-text="helperText"
        :hint="hint"
        :required="required"
        :disabled="disabled"
        :hidden="hidden"
        :column-span="columnSpan"
        :hidden-label="true"
        :hint-actions="hintActions"
    >
        <div class="flex items-center justify-between space-x-2">
            <Label :for="name" class="flex flex-col space-y-1">
                <span>{{ label }}</span>
                <span
                    v-if="!hasError && helperText"
                    class="text-sm font-normal text-muted-foreground"
                >
                    {{ helperText }}
                </span>
            </Label>
            <Switch
                :id="name"
                :name="name"
                v-model="toggleValue"
                :disabled="disabled"
                :aria-invalid="hasError ? 'true' : 'false'"
                :aria-describedby="hasError ? `${name}-error` : undefined"
            />
        </div>
    </FieldWrapper>
</template>

<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import type { ComputedRef, Ref } from 'vue';
import { computed, inject, ref, watch } from 'vue';
import FieldWrapper from '../FieldWrapper.vue';

const props = defineProps<{
    name: string;
    label?: string;
    hint?: string;
    helperText?: string;
    required?: boolean;
    disabled?: boolean;
    value?: boolean;
    hidden?: boolean;
    columnSpan?: number | string;
    hintActions?: any[];
    isLive?: boolean;
    isLazy?: boolean;
    liveDebounce?: number;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: boolean]
}>();

const toggleValue = computed({
    get: () => props.value ?? false,
    set: (value) => emit('update:modelValue', value)
});

// Inject errors from parent
const errors = inject<ComputedRef<Record<string, string | string[]>>>(
    'errors',
    computed(() => ({})),
);

// Inject dependencies for reactive fields
const getFormData = inject<(() => Record<string, any>) | undefined>('getFormData', undefined);
const updateSchema = inject<((schema: any[]) => void) | undefined>('updateSchema', undefined);

const errorMessage = computed(() => {
    const error = errors.value[props.name];
    if (!error) return null;
    return Array.isArray(error) ? error[0] : error;
});

const hasError = computed(() => !!errorMessage.value);
</script>
