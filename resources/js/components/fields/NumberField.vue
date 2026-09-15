<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import {
  NumberFieldRoot,
  NumberFieldInput,
  NumberFieldIncrement,
  NumberFieldDecrement,
} from 'radix-vue'
import { Minus, Plus } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

interface Props {
  name?: string
  value?: number | null
  modelValue?: number | null
  label?: string
  helperText?: string
  required?: boolean
  disabled?: boolean
  readonly?: boolean
  min?: number | null
  max?: number | null
  step?: number
  formatOptions?: Record<string, any>
  locale?: string | null
  prefix?: string | null
  suffix?: string | null
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  value: null,
  min: null,
  max: null,
  step: 1,
  formatOptions: () => ({}),
  locale: null,
  prefix: null,
  suffix: null,
})

const emit = defineEmits<{
  'update:modelValue': [value: number | null]
  'update:value': [value: number | null]
}>()

const numberValue = ref<number | undefined>(
  props.modelValue ?? props.value ?? undefined
)

// Watch for external value changes (e.g., when editing)
watch(() => props.modelValue ?? props.value, (newValue) => {
  if (newValue !== null && newValue !== undefined) {
    numberValue.value = newValue
  }
}, { immediate: true })

const updateValue = (value: number | undefined) => {
  // radix-vue's NumberField has no readonly mode, so ignore its keyboard/wheel steps here
  if (props.readonly) return
  numberValue.value = value
  emit('update:modelValue', value ?? null)
  emit('update:value', value ?? null)
}
</script>

<template>
  <div class="w-full space-y-2">
    <!-- Label -->
    <label
      v-if="label"
      :for="name"
      class="text-sm font-medium block text-foreground"
    >
      {{ label }}
      <span v-if="required" class="text-destructive ms-0.5">*</span>
    </label>

    <!-- Hidden input for form submission -->
    <input
      v-if="name"
      type="hidden"
      :name="name"
      :value="numberValue ?? ''"
    />

    <!-- Number Field -->
    <NumberFieldRoot
      :model-value="numberValue"
      @update:model-value="updateValue"
      :min="min ?? undefined"
      :max="max ?? undefined"
      :step="step"
      :format-options="formatOptions"
      :locale="locale ?? undefined"
      :disabled="disabled"
      class="relative flex items-center"
    >
      <!-- Decrement Button -->
      <NumberFieldDecrement as-child>
        <Button
          type="button"
          variant="outline"
          size="icon"
          class="h-10 w-10 rounded-e-none border-e-0"
          :disabled="disabled || readonly"
        >
          <Minus class="h-4 w-4" />
        </Button>
      </NumberFieldDecrement>

      <!-- Input with prefix/suffix -->
      <div class="relative flex-1">
        <span
          v-if="prefix"
          class="absolute start-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground pointer-events-none"
        >
          {{ prefix }}
        </span>

        <NumberFieldInput
          :id="name"
          :readonly="readonly"
          class="flex h-10 w-full rounded-none border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 text-center"
          :class="{
            'ps-8': prefix,
            'pe-8': suffix,
          }"
        />

        <span
          v-if="suffix"
          class="absolute end-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground pointer-events-none"
        >
          {{ suffix }}
        </span>
      </div>

      <!-- Increment Button -->
      <NumberFieldIncrement as-child>
        <Button
          type="button"
          variant="outline"
          size="icon"
          class="h-10 w-10 rounded-s-none border-s-0"
          :disabled="disabled || readonly"
        >
          <Plus class="h-4 w-4" />
        </Button>
      </NumberFieldIncrement>
    </NumberFieldRoot>

    <!-- Helper text -->
    <p
      v-if="helperText"
      class="text-xs text-muted-foreground mt-1"
    >
      {{ helperText }}
    </p>
  </div>
</template>
