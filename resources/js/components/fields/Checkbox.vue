<template>
    <!-- Use slot if provided (Blade mode), otherwise use internal template (Direct Vue mode) -->
    <slot
        v-if="$slots.default"
        :name="name"
        :label="label"
        :options="options"
        :inline="inline"
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
        :hidden-label="!options || Object.keys(options).length === 0"
        :hint-actions="hintActions"
    >
        <!-- Single Checkbox (when no options provided) -->
        <div
            v-if="!options || Object.keys(options).length === 0"
            class="flex items-center space-x-2"
        >
            <!-- Hidden input for unchecked value (false) -->
            <input
                type="hidden"
                :name="name"
                :value="uncheckedValue ?? false"
            />

            <Checkbox
                :id="name"
                :name="name"
                v-model="checkboxValue"
                :value="checkedValue ?? true"
                :disabled="disabled"
                :aria-invalid="hasError ? 'true' : 'false'"
            />
            <Label
                v-if="label"
                :for="name"
                class="text-sm leading-none font-normal peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
            >
                {{ label }}
            </Label>
        </div>

        <!-- Checkbox List (when options provided) -->
        <div v-else>
            <Label v-if="label" class="mb-3 block text-sm font-medium">{{
                label
            }}</Label>
            <div :class="inline ? 'flex flex-wrap gap-4' : 'space-y-3'">
                <div
                    v-for="(optLabel, optValue) in options"
                    :key="optValue"
                    class="flex items-center space-x-2"
                >
                    <Checkbox
                        :id="`${name}_${optValue}`"
                        :name="`${name}[]`"
                        :value="optValue"
                        :disabled="disabled"
                        :aria-invalid="hasError ? 'true' : 'false'"
                    />
                    <Label
                        :for="`${name}_${optValue}`"
                        class="text-sm leading-none font-normal peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                    >
                        {{ optLabel }}
                    </Label>
                </div>
            </div>
        </div>
    </FieldWrapper>
</template>

<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import type { ComputedRef } from 'vue';
import { computed, inject, ref, watch } from 'vue';
import FieldWrapper from '../FieldWrapper.vue';

const props = defineProps<{
    name: string;
    label?: string;
    hint?: string;
    options?: Record<string, string>;
    inline?: boolean;
    required?: boolean;
    disabled?: boolean;
    helperText?: string;
    hidden?: boolean;
    columnSpan?: number | string;
    defaultValue?: any;
    value?: any;
    checkedValue?: any;
    uncheckedValue?: any;
    hintActions?: any[];
    isLive?: boolean;
    isLazy?: boolean;
    liveDebounce?: number;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: any]
}>();

const checkboxValue = computed({
    get: () => props.value,
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
