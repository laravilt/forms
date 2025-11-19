<script setup lang="ts">
import { Button } from '@/components/ui/button'
import * as LucideIcons from 'lucide-vue-next'
import { computed, ref, watch } from 'vue'

interface Option {
  value: string | number
  label: string
  disabled?: boolean
  icon?: string
}

interface Props {
  name?: string
  value?: string | number | (string | number)[]
  modelValue?: string | number | (string | number)[]
  options: Option[]
  multiple?: boolean
  inline?: boolean
  disabled?: boolean
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
}

const props = withDefaults(defineProps<Props>(), {
  multiple: false,
  inline: true,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number | (string | number)[]]
  'update:value': [value: string | number | (string | number)[]]
}>()

// Internal state for the current value
const internalValue = ref<string | number | (string | number)[]>(
  props.value ?? props.modelValue ?? (props.multiple ? [] : '')
)

// Watch for prop changes and update internal state
watch(() => props.value ?? props.modelValue, (newValue) => {
  internalValue.value = newValue ?? (props.multiple ? [] : '')
}, { immediate: true })

// Use internal value for display and form submission
const currentValue = computed(() => internalValue.value)

// Helper to get Lucide icon component by name
const getIconComponent = (iconName?: string) => {
  if (!iconName) return null
  // Convert kebab-case or lowercase to PascalCase for Lucide icons
  const pascalCase = iconName
    .split(/[-_\s]/)
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join('')
  return (LucideIcons as any)[pascalCase] || null
}

// Helper to get Tailwind color classes for icons
const getIconColorClass = (color?: string) => {
  if (!color) return 'text-muted-foreground'

  // Map common color names to Tailwind classes
  const colorMap: Record<string, string> = {
    'primary': 'text-primary',
    'secondary': 'text-secondary',
    'success': 'text-green-600',
    'danger': 'text-red-600',
    'warning': 'text-yellow-600',
    'info': 'text-blue-600',
    'muted': 'text-muted-foreground',
    'destructive': 'text-destructive',
  }

  return colorMap[color] || `text-${color}`
}

const isSelected = (value: string | number) => {
  if (props.multiple && Array.isArray(internalValue.value)) {
    return internalValue.value.includes(value)
  }
  return internalValue.value === value
}

const handleToggle = (value: string | number) => {
  if (props.multiple) {
    const currentValues = Array.isArray(internalValue.value) ? internalValue.value : []
    const newValue = currentValues.includes(value)
      ? currentValues.filter(v => v !== value)
      : [...currentValues, value]

    internalValue.value = newValue
    emit('update:modelValue', newValue)
    emit('update:value', newValue)
  } else {
    internalValue.value = value
    emit('update:modelValue', value)
    emit('update:value', value)
  }
}
</script>

<template>
  <div class="w-full space-y-2">
    <!-- Hidden input for form submission -->
    <input
      v-if="name"
      type="hidden"
      :name="name"
      :value="multiple ? (Array.isArray(currentValue) ? currentValue.join(',') : '') : currentValue"
    />

    <!-- Header icons -->
    <div v-if="getIconComponent(props.prefixIcon) || getIconComponent(props.suffixIcon)" class="flex items-center justify-between">
      <component
        v-if="getIconComponent(props.prefixIcon)"
        :is="getIconComponent(props.prefixIcon)"
        class="h-4 w-4"
        :class="getIconColorClass(props.prefixIconColor)"
      />
      <component
        v-if="getIconComponent(props.suffixIcon)"
        :is="getIconComponent(props.suffixIcon)"
        class="h-4 w-4"
        :class="getIconColorClass(props.suffixIconColor)"
      />
    </div>

    <!-- Button group -->
    <div
      class="inline-flex rounded-md shadow-sm"
      :class="inline ? 'flex-wrap gap-1' : 'flex-col gap-1'"
      role="group"
    >
      <Button
        v-for="option in options"
        :key="option.value"
        type="button"
        :variant="isSelected(option.value) ? 'default' : 'outline'"
        :disabled="disabled || option.disabled"
        class="gap-2"
        @click="handleToggle(option.value)"
      >
        <component
          v-if="getIconComponent(option.icon)"
          :is="getIconComponent(option.icon)"
          class="h-4 w-4"
        />
        {{ option.label }}
      </Button>
    </div>
  </div>
</template>
