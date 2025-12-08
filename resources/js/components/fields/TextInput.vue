<template>
    <!-- Use slot if provided (Blade mode), otherwise use internal template (Direct Vue mode) -->
    <slot
        v-if="$slots.default"
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
        :hint-actions="hintActions"
    >
        <!-- Phone Input for tel type -->
        <PhoneInput
            v-if="type === 'tel'"
            :name="name"
            :model-value="localValue"
            @update:model-value="handleInput"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
        />

        <!-- Regular input for other types -->
        <div v-else class="relative flex items-center gap-2">
            <!-- Prefix Actions -->
            <div v-if="prefixActions && prefixActions.length" class="flex items-center gap-1">
                <ActionButton
                    v-for="action in prefixActions"
                    :key="action.name"
                    v-bind="action"
                />
            </div>

            <div class="relative flex-1">
                <div
                    v-if="prefixText"
                    ref="prefixRef"
                    class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-sm text-muted-foreground z-10"
                >
                    {{ prefixText }}
                </div>

                <Input
                    :id="id || name"
                    :name="name"
                    :type="type || 'text'"
                    v-model="inputValue"
                    :placeholder="placeholder"
                    :required="required"
                    :disabled="disabled"
                    :readonly="readonly"
                    :autofocus="autofocus"
                    :autocomplete="autocomplete"
                    :minlength="minLength"
                    :maxlength="maxLength"
                    :pattern="pattern"
                    v-bind="extraAttributes"
                    :class="[
                        hasError
                            ? 'border-destructive focus-visible:ring-destructive'
                            : '',
                    ]"
                    :style="prefixText ? { paddingInlineStart: `${prefixPadding}px` } : {}"
                    :aria-invalid="hasError ? 'true' : 'false'"
                    :aria-describedby="hasError ? `${name}-error` : undefined"
                />
            </div>

            <!-- Suffix Actions -->
            <div v-if="suffixActions && suffixActions.length" class="flex items-center gap-1">
                <ActionButton
                    v-for="action in suffixActions"
                    :key="action.name"
                    v-bind="action"
                    :getFormData="getFormData"
                />
            </div>
        </div>

        <div
            v-if="showCharacterCount && maxLength && type !== 'tel'"
            class="mt-1.5 text-right text-xs text-muted-foreground"
        >
            {{ characterCount }} / {{ maxLength }}
        </div>
    </FieldWrapper>
</template>

<script setup lang="ts">
import { Input } from '@/components/ui/input';
import type { ComputedRef } from 'vue';
import { computed, inject, nextTick, onMounted, ref, useSlots, watch } from 'vue';
import FieldWrapper from '../FieldWrapper.vue';
import PhoneInput from '../PhoneInput.vue';
import ActionButton from '@laravilt/actions/components/ActionButton.vue';

const slots = useSlots();

// Ref for measuring prefix width
const prefixRef = ref<HTMLElement | null>(null);
const prefixWidth = ref(0);

// Calculate padding based on prefix width (prefix width + extra space for padding)
const prefixPadding = computed(() => {
    // Base padding (12px = pl-3) + prefix width + small gap (8px)
    return prefixWidth.value > 0 ? prefixWidth.value + 8 : 32;
});

// Measure prefix width after mount and when prefix changes
const measurePrefix = () => {
    if (prefixRef.value) {
        prefixWidth.value = prefixRef.value.offsetWidth;
    }
};

onMounted(() => {
    nextTick(measurePrefix);
});

const props = defineProps<{
    id?: string;
    name: string;
    type?: string;
    label?: string;
    placeholder?: string;
    hint?: string;
    required?: boolean;
    disabled?: boolean;
    readonly?: boolean;
    autofocus?: boolean;
    autocomplete?: string;
    minLength?: number;
    maxLength?: number;
    pattern?: string;
    prefixText?: string;
    helperText?: string;
    showCharacterCount?: boolean;
    value?: string;
    extraAttributes?: Record<string, any>;
    hidden?: boolean;
    columnSpan?: number | string;
    hintActions?: any[];
    prefixActions?: any[];
    suffixActions?: any[];
    isLive?: boolean;
    isLazy?: boolean;
    liveDebounce?: number;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()

// Use local state for input to avoid cursor position issues with reactive fields
const localValue = ref(props.value || '')
let debounceTimeout: ReturnType<typeof setTimeout> | null = null
let isUserTyping = false

// Sync local value when prop changes from external source (not from user input)
watch(() => props.value, (newValue) => {
    // Don't update if user is actively typing
    if (isUserTyping) {
        return
    }

    // Only update local value if it's different
    if (newValue !== localValue.value) {
        localValue.value = newValue || ''
    }
})

// Re-measure prefix when it changes
watch(() => props.prefixText, () => {
    nextTick(measurePrefix);
})

const handleInput = (value: string | null) => {
    // Mark that user is typing
    isUserTyping = true

    // Update local value immediately for smooth typing
    localValue.value = value || ''

    // Clear existing debounce timeout
    if (debounceTimeout) {
        clearTimeout(debounceTimeout)
    }

    // For live/lazy fields, debounce the emit
    if (props.isLive || props.isLazy) {
        const delay = props.isLazy ? (props.liveDebounce || 500) : (props.liveDebounce || 300)
        debounceTimeout = setTimeout(() => {
            emit('update:modelValue', value || '')
            // Allow prop updates after a short delay
            setTimeout(() => {
                isUserTyping = false
            }, 100)
        }, delay)
    } else {
        // For non-reactive fields, emit immediately
        emit('update:modelValue', value || '')
        // Allow prop updates after a short delay
        setTimeout(() => {
            isUserTyping = false
        }, 100)
    }
}

const inputValue = computed({
    get: () => localValue.value,
    set: handleInput
})

const characterCount = computed(() => localValue.value.length);

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
