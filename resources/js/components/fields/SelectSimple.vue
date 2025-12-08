<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import * as LucideIcons from 'lucide-vue-next'

interface Option {
  value: string | number
  label: string
  disabled?: boolean
}

interface Props {
  name?: string
  value?: string | number | (string | number)[] | null
  modelValue?: string | number | (string | number)[] | null
  label?: string
  options: Option[]
  placeholder?: string
  multiple?: boolean
  disabled?: boolean
  required?: boolean
  helperText?: string
  native?: boolean
  searchable?: boolean
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Select an option',
  multiple: false,
  disabled: false,
  native: true,
  searchable: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number | (string | number)[] | null]
  'update:value': [value: string | number | (string | number)[] | null]
}>()

// Internal state for the current value
const internalValue = ref<string | number | (string | number)[] | null>(
  props.value ?? props.modelValue ?? (props.multiple ? [] : null)
)

// Watch for prop changes and update internal state
watch(() => props.value ?? props.modelValue, (newValue) => {
  internalValue.value = newValue ?? (props.multiple ? [] : null)
}, { immediate: true })

// Use internal value for display and form submission
const currentValue = computed(() => internalValue.value)

// Helper to get Lucide icon component by name
const getIconComponent = (iconName?: string) => {
  if (!iconName) return null
  const pascalCase = iconName
    .split(/[-_\s]/)
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join('')
  return (LucideIcons as any)[pascalCase] || null
}

// Helper to get Tailwind color classes for icons
const getIconColorClass = (color?: string) => {
  if (!color) return 'text-muted-foreground'

  const colorMap: Record<string, string> = {
    primary: 'text-primary',
    secondary: 'text-secondary',
    success: 'text-green-600',
    danger: 'text-red-600',
    warning: 'text-yellow-600',
    info: 'text-blue-600',
    muted: 'text-muted-foreground',
    destructive: 'text-destructive',
  }

  return colorMap[color] || `text-${color}`
}

const handleChange = (event: Event) => {
  const target = event.target as HTMLSelectElement

  if (props.multiple) {
    const selectedOptions = Array.from(target.selectedOptions).map(opt => opt.value)
    internalValue.value = selectedOptions
    emit('update:modelValue', selectedOptions.length > 0 ? selectedOptions : null)
    emit('update:value', selectedOptions.length > 0 ? selectedOptions : null)
  } else {
    const value = target.value === '' ? null : target.value
    internalValue.value = value
    emit('update:modelValue', value)
    emit('update:value', value)
  }
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
      :value="multiple ? (Array.isArray(currentValue) ? currentValue.join(',') : '') : (currentValue || '')"
    />

    <div class="flex items-center gap-2 w-full">
      <!-- Prefix icon -->
      <component
        v-if="getIconComponent(prefixIcon)"
        :is="getIconComponent(prefixIcon)"
        class="h-4 w-4 shrink-0"
        :class="getIconColorClass(prefixIconColor)"
      />

      <!-- Native select -->
      <select
        v-if="multiple"
        :multiple="true"
        :disabled="disabled"
        :value="Array.isArray(currentValue) ? currentValue : []"
        class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
        @change="handleChange"
      >
        <option
          v-for="option in options"
          :key="option.value"
          :value="option.value"
          :disabled="option.disabled"
        >
          {{ option.label }}
        </option>
      </select>
      <select
        v-else
        :disabled="disabled"
        :value="currentValue || ''"
        class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
        @change="handleChange"
      >
        <option value="">{{ placeholder }}</option>
        <option
          v-for="option in options"
          :key="option.value"
          :value="option.value"
          :disabled="option.disabled"
        >
          {{ option.label }}
        </option>
      </select>

      <!-- Suffix icon -->
      <component
        v-if="getIconComponent(suffixIcon)"
        :is="getIconComponent(suffixIcon)"
        class="h-4 w-4 shrink-0"
        :class="getIconColorClass(suffixIconColor)"
      />
    </div>

    <!-- Helper text -->
    <p
      v-if="helperText"
      class="text-xs text-muted-foreground mt-1"
    >
      {{ helperText }}
    </p>
  </div>
</template>
