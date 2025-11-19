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
  required?: boolean
  disabled?: boolean
  length?: number
  mask?: boolean
  otp?: boolean
  type?: 'numeric' | 'alpha' | 'alphanumeric'
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  value: null,
  length: 4,
  mask: false,
  otp: false,
  type: 'numeric',
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
      <span v-if="required" class="text-destructive ml-0.5">*</span>
    </label>

    <!-- Hidden input for form submission -->
    <input
      v-if="name"
      type="hidden"
      :name="name"
      :value="pinValue.join('')"
    />

    <!-- Pin Input -->
    <PinInputRoot
      :model-value="pinValue"
      @update:model-value="updateValue"
      :disabled="disabled"
      :otp="otp"
      :type="type"
      :mask="mask"
      class="flex items-center gap-2"
    >
      <PinInputInput
        v-for="(id, index) in length"
        :key="id"
        :index="index"
        class="flex h-12 w-12 items-center justify-center rounded-md border border-input bg-background text-center text-sm ring-offset-background transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
      />
    </PinInputRoot>

    <!-- Helper text -->
    <p
      v-if="helperText"
      class="text-xs text-muted-foreground mt-1"
    >
      {{ helperText }}
    </p>
  </div>
</template>
