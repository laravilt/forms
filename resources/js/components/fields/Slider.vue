<script setup lang="ts">
import * as LucideIcons from 'lucide-vue-next'
import { SliderRange, SliderRoot, SliderThumb, SliderTrack } from 'reka-ui'
import { computed, ref, watch } from 'vue'

interface Props {
  name?: string
  value?: number
  modelValue?: number
  min?: number
  max?: number
  step?: number
  marks?: number[]
  showValue?: boolean
  disabled?: boolean
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: 0,
  min: 0,
  max: 100,
  step: 1,
  showValue: false,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: number]
  'update:value': [value: number]
}>()

// Internal state for slider value
const internalValue = ref<number>(props.value ?? props.modelValue ?? 0)

// Watch for prop changes and update internal state
watch(() => props.value ?? props.modelValue, (newValue) => {
  internalValue.value = newValue ?? 0
}, { immediate: true })

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

const handleChange = (value: number[]) => {
  internalValue.value = value[0]
  emit('update:modelValue', value[0])
  emit('update:value', value[0])
}

const sliderValue = computed(() => [internalValue.value])
</script>

<template>
  <div class="w-full space-y-2">
    <!-- Hidden input for form submission -->
    <input
      v-if="name"
      type="hidden"
      :name="name"
      :value="internalValue"
    />

    <div class="flex items-center gap-4">
      <!-- Prefix icon -->
      <component
        v-if="getIconComponent(props.prefixIcon)"
        :is="getIconComponent(props.prefixIcon)"
        class="h-4 w-4 shrink-0"
        :class="getIconColorClass(props.prefixIconColor)"
      />

      <!-- Slider -->
      <SliderRoot
        :model-value="sliderValue"
        :min="min"
        :max="max"
        :step="step"
        :disabled="disabled"
        class="relative flex items-center w-full touch-none select-none"
        @update:model-value="handleChange"
      >
        <SliderTrack class="relative h-2 w-full grow overflow-hidden rounded-full bg-secondary">
          <SliderRange class="absolute h-full bg-primary" />
        </SliderTrack>
        <SliderThumb
          class="block h-5 w-5 rounded-full border-2 border-primary bg-background ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
        />
      </SliderRoot>

      <!-- Value display -->
      <span v-if="showValue" class="text-sm font-medium text-muted-foreground min-w-[3ch] text-right shrink-0">
        {{ internalValue }}
      </span>

      <!-- Suffix icon -->
      <component
        v-if="getIconComponent(props.suffixIcon)"
        :is="getIconComponent(props.suffixIcon)"
        class="h-4 w-4 shrink-0"
        :class="getIconColorClass(props.suffixIconColor)"
      />
    </div>

    <!-- Marks -->
    <div v-if="marks && marks.length > 0" class="flex justify-between px-2">
      <span
        v-for="mark in marks"
        :key="mark"
        class="text-xs text-muted-foreground"
      >
        {{ mark }}
      </span>
    </div>
  </div>
</template>
