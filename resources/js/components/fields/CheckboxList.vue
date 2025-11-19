<script setup lang="ts">
import { Checkbox as BaseCheckbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import * as LucideIcons from 'lucide-vue-next'

interface Option {
  value: string | number
  label: string
  disabled?: boolean
}

interface Props {
  modelValue?: (string | number)[]
  options: Option[]
  disabled?: boolean
  inline?: boolean
  columns?: number
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: () => [],
  disabled: false,
  inline: false,
  columns: 1,
})

const emit = defineEmits<{
  'update:modelValue': [value: (string | number)[]]
}>()

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

const isChecked = (value: string | number) => {
  return props.modelValue?.includes(value) ?? false
}

const handleChange = (optionValue: string | number, checked: boolean | 'indeterminate') => {
  const currentValues = props.modelValue || []
  let newValues: (string | number)[]

  if (checked === true) {
    // Add value if not already present
    if (!currentValues.includes(optionValue)) {
      newValues = [...currentValues, optionValue]
    } else {
      newValues = currentValues
    }
  } else {
    // Remove value
    newValues = currentValues.filter(v => v !== optionValue)
  }

  emit('update:modelValue', newValues)
}

const gridClass = () => {
  if (props.inline) {
    return 'flex flex-wrap gap-4'
  }
  if (props.columns > 1) {
    return `grid gap-4 grid-cols-${props.columns}`
  }
  return 'flex flex-col gap-4'
}
</script>

<template>
  <div :class="gridClass()">
    <div
      v-for="option in options"
      :key="option.value"
      class="flex items-start gap-2"
    >
      <!-- Prefix icon -->
      <component
        v-if="getIconComponent(props.prefixIcon)"
        :is="getIconComponent(props.prefixIcon)"
        class="h-4 w-4 mt-0.5"
        :class="getIconColorClass(props.prefixIconColor)"
      />

      <!-- Checkbox -->
      <BaseCheckbox
        :checked="isChecked(option.value)"
        :disabled="disabled || option.disabled"
        @update:checked="(checked) => handleChange(option.value, checked)"
      />

      <!-- Label -->
      <Label class="text-sm font-medium leading-none cursor-pointer">
        {{ option.label }}
      </Label>

      <!-- Suffix icon -->
      <component
        v-if="getIconComponent(props.suffixIcon)"
        :is="getIconComponent(props.suffixIcon)"
        class="h-4 w-4 mt-0.5"
        :class="getIconColorClass(props.suffixIconColor)"
      />
    </div>
  </div>
</template>
