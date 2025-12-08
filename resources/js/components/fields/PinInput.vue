<script setup lang="ts">
import { ref } from 'vue'
import {
  PinInputRoot,
  PinInputInput,
} from 'radix-vue'

interface Props {
  name?: string
  value?: string | null
  modelValue?: string | null
  label?: string
  helperText?: string
  error?: string
  required?: boolean
  disabled?: boolean
  length?: number
  mask?: boolean
  otp?: boolean
  type?: 'numeric' | 'alpha' | 'alphanumeric'
  align?: 'left' | 'center' | 'right'
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  value: null,
  length: 4,
  mask: false,
  otp: false,
  type: 'numeric',
  align: 'left',
})

const emit = defineEmits<{
  'update:modelValue': [value: string | null]
  'update:value': [value: string | null]
}>()

const pinValue = ref<string[]>(
  props.modelValue?.split('') || props.value?.split('') || []
)

const updateValue = (value: string[]) => {
  pinValue.value = value
  const stringValue = value.join('')
  emit('update:modelValue', stringValue || null)
  emit('update:value', stringValue || null)
}

const alignmentClasses = {
  left: {
    container: 'justify-start',
    text: 'text-start',
  },
  center: {
    container: 'justify-center',
    text: 'text-center',
  },
  right: {
    container: 'justify-end',
    text: 'text-end',
  },
}
</script>

<template>
  <div class="w-full space-y-2">
    <!-- Label -->
    <label
      v-if="label"
      :for="name"
      :class="['text-sm font-medium block text-foreground', alignmentClasses[align].text]"
    >
      {{ label }}
      <span v-if="required" class="text-destructive ms-0.5">*</span>
    </label>

    <!-- Hidden input for form submission -->
    <input
      v-if="name"
      type="hidden"
      :name="name"
      :value="pinValue.join('')"
    />

    <!-- Pin Input -->
    <div :class="['flex py-2', alignmentClasses[align].container]">
      <PinInputRoot
        :model-value="pinValue"
        @update:model-value="updateValue"
        :disabled="disabled"
        :otp="otp"
        :type="type"
        :mask="mask"
        class="flex items-center gap-3"
      >
        <PinInputInput
          v-for="(id, index) in length"
          :key="id"
          :index="index"
          :class="[
            'flex h-14 w-14 items-center justify-center rounded-lg border-2 bg-background text-center text-lg font-semibold ring-offset-background transition-all duration-200 file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50',
            error
              ? 'border-destructive hover:border-destructive focus-visible:outline-none focus-visible:border-destructive focus-visible:ring-4 focus-visible:ring-destructive/10'
              : 'border-input hover:border-primary/50 focus-visible:outline-none focus-visible:border-primary focus-visible:ring-4 focus-visible:ring-primary/10 data-[complete]:border-primary data-[complete]:bg-primary/5'
          ]"
        />
      </PinInputRoot>
    </div>

    <!-- Error message -->
    <p
      v-if="error"
      :class="['text-xs text-destructive', alignmentClasses[align].text]"
    >
      {{ error }}
    </p>

    <!-- Helper text -->
    <p
      v-else-if="helperText"
      :class="['text-xs text-muted-foreground', alignmentClasses[align].text]"
    >
      {{ helperText }}
    </p>
  </div>
</template>
