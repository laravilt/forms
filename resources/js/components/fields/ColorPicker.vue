<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import { X, Plus } from 'lucide-vue-next'

interface Props {
  name?: string
  value?: string | string[] | null
  modelValue?: string | string[] | null
  label?: string
  helperText?: string
  required?: boolean
  disabled?: boolean
  swatches?: string[]
  showSwatches?: boolean
  alpha?: boolean
  format?: string
  multiple?: boolean
  maxItems?: number | null
  minItems?: number | null
  translations?: {
    placeholder?: string
    swatches?: string
    commonColors?: string
  }
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  value: null,
  swatches: () => [],
  showSwatches: false,
  alpha: false,
  format: 'hex',
  multiple: false,
  maxItems: null,
  minItems: null,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | string[] | null]
  'update:value': [value: string | string[] | null]
}>()

// Initialize selected colors based on mode
const initializeColors = () => {
  const initialValue = props.modelValue || props.value
  if (props.multiple) {
    if (Array.isArray(initialValue)) {
      return [...initialValue]
    }
    return initialValue ? [initialValue] : []
  }
  return Array.isArray(initialValue) ? initialValue[0] || null : initialValue || null
}

const selectedColors = ref<string[]>(
  props.multiple
    ? (Array.isArray(props.modelValue || props.value)
        ? [...(props.modelValue || props.value || [])]
        : (props.modelValue || props.value ? [props.modelValue || props.value] : []))
    : []
)
const selectedColor = ref<string | null>(
  !props.multiple
    ? (Array.isArray(props.modelValue || props.value)
        ? (props.modelValue || props.value)?.[0] || null
        : (props.modelValue || props.value) || null)
    : null
)
const isOpen = ref(false)
const customColorInput = ref('')

// Check if color is selected (for multiple mode)
const isColorSelected = (color: string) => {
  if (props.multiple) {
    return selectedColors.value.includes(color)
  }
  return selectedColor.value === color
}

// Check if can add more colors
const canAddMore = computed(() => {
  if (!props.multiple) return true
  if (props.maxItems === null) return true
  return selectedColors.value.length < props.maxItems
})

// Select/toggle color
const selectColor = (color: string) => {
  if (props.multiple) {
    toggleColor(color)
  } else {
    selectedColor.value = color
    emit('update:modelValue', color)
    emit('update:value', color)
    if (props.showSwatches) {
      isOpen.value = false
    }
  }
}

// Toggle color in multiple mode
const toggleColor = (color: string) => {
  const index = selectedColors.value.indexOf(color)
  if (index > -1) {
    // Remove color
    selectedColors.value.splice(index, 1)
  } else if (canAddMore.value) {
    // Add color
    selectedColors.value.push(color)
  }
  emitMultipleValue()
}

// Add custom color
const addCustomColor = () => {
  if (!customColorInput.value) return
  const color = customColorInput.value.startsWith('#')
    ? customColorInput.value
    : `#${customColorInput.value}`

  if (props.multiple) {
    if (!selectedColors.value.includes(color) && canAddMore.value) {
      selectedColors.value.push(color)
      emitMultipleValue()
    }
  } else {
    selectColor(color)
  }
  customColorInput.value = ''
}

// Remove color from selection (multiple mode)
const removeColor = (color: string) => {
  const index = selectedColors.value.indexOf(color)
  if (index > -1) {
    selectedColors.value.splice(index, 1)
    emitMultipleValue()
  }
}

// Emit value for multiple mode
const emitMultipleValue = () => {
  const value = selectedColors.value.length > 0 ? [...selectedColors.value] : null
  emit('update:modelValue', value)
  emit('update:value', value)
}

// Clear selection
const clearSelection = () => {
  if (props.multiple) {
    selectedColors.value = []
    emitMultipleValue()
  } else {
    selectedColor.value = null
    emit('update:modelValue', null)
    emit('update:value', null)
  }
}

// Update from native color input
const updateFromColorInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  customColorInput.value = target.value
}

// Common colors
const commonColors = [
  '#000000', '#ffffff', '#ef4444', '#f97316', '#f59e0b', '#eab308',
  '#84cc16', '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9',
  '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899',
  '#f43f5e', '#64748b'
]

// Display value for trigger button
const displayValue = computed(() => {
  if (props.multiple) {
    if (selectedColors.value.length === 0) {
      return props.translations?.placeholder || 'Select colors'
    }
    return `${selectedColors.value.length} color(s) selected`
  }
  return selectedColor.value || props.translations?.placeholder || 'Select color'
})

// Watch for external value changes
watch(() => props.modelValue ?? props.value, (newValue) => {
  if (props.multiple) {
    if (Array.isArray(newValue)) {
      selectedColors.value = [...newValue]
    } else if (newValue) {
      selectedColors.value = [newValue]
    } else {
      selectedColors.value = []
    }
  } else {
    selectedColor.value = Array.isArray(newValue) ? newValue[0] || null : newValue || null
  }
}, { deep: true })
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
    <template v-if="name">
      <template v-if="multiple">
        <input
          v-for="(color, index) in selectedColors"
          :key="index"
          type="hidden"
          :name="`${name}[]`"
          :value="color"
        />
      </template>
      <input
        v-else
        type="hidden"
        :name="name"
        :value="selectedColor || ''"
      />
    </template>

    <!-- Selected colors display (multiple mode) -->
    <div v-if="multiple && selectedColors.length > 0" class="flex flex-wrap gap-2 mb-2">
      <div
        v-for="color in selectedColors"
        :key="color"
        class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-muted text-sm"
      >
        <div
          class="w-4 h-4 rounded border border-border"
          :style="{ backgroundColor: color }"
        />
        <span class="font-mono text-xs">{{ color }}</span>
        <button
          v-if="!disabled"
          type="button"
          class="hover:text-destructive transition-colors"
          @click="removeColor(color)"
        >
          <X class="h-3 w-3" />
        </button>
      </div>
    </div>

    <!-- Color Picker with Popover -->
    <Popover v-model:open="isOpen">
      <PopoverTrigger as-child>
        <Button
          type="button"
          variant="outline"
          :disabled="disabled"
          class="w-full justify-between h-10 px-3 font-normal"
        >
          <div class="flex items-center gap-2">
            <!-- Color preview (single mode) -->
            <div
              v-if="!multiple"
              class="w-6 h-6 rounded border border-border"
              :style="{ backgroundColor: selectedColor || '#e5e7eb' }"
            />
            <!-- Multiple colors preview -->
            <div v-else class="flex -space-x-1">
              <div
                v-for="(color, index) in selectedColors.slice(0, 4)"
                :key="color"
                class="w-5 h-5 rounded-full border-2 border-background"
                :style="{ backgroundColor: color, zIndex: 4 - index }"
              />
              <div
                v-if="selectedColors.length > 4"
                class="w-5 h-5 rounded-full border-2 border-background bg-muted flex items-center justify-center text-[10px]"
              >
                +{{ selectedColors.length - 4 }}
              </div>
              <div
                v-if="selectedColors.length === 0"
                class="w-5 h-5 rounded-full border-2 border-dashed border-muted-foreground"
              />
            </div>

            <!-- Display text -->
            <span :class="{ 'text-muted-foreground': multiple ? selectedColors.length === 0 : !selectedColor }">
              {{ displayValue }}
            </span>
          </div>

          <!-- Clear button -->
          <button
            v-if="(multiple ? selectedColors.length > 0 : selectedColor) && !disabled"
            type="button"
            class="hover:text-destructive transition-colors"
            @click.stop="clearSelection"
          >
            <X class="h-4 w-4" />
          </button>
        </Button>
      </PopoverTrigger>

      <PopoverContent class="w-[320px] p-4" align="start">
        <div class="space-y-4">
          <!-- Custom color input -->
          <div class="flex items-center gap-2">
            <input
              type="color"
              :value="customColorInput || '#000000'"
              @input="updateFromColorInput"
              class="w-10 h-10 rounded cursor-pointer border border-border"
              :disabled="disabled"
            />
            <div class="flex-1">
              <Input
                v-model="customColorInput"
                type="text"
                placeholder="#000000"
                class="font-mono text-sm"
                :disabled="disabled"
                @keyup.enter="addCustomColor"
              />
            </div>
            <Button
              v-if="multiple"
              type="button"
              size="sm"
              :disabled="disabled || !customColorInput || !canAddMore"
              @click="addCustomColor"
            >
              <Plus class="h-4 w-4" />
            </Button>
            <Button
              v-else
              type="button"
              size="sm"
              :disabled="disabled || !customColorInput"
              @click="addCustomColor"
            >
              Set
            </Button>
          </div>

          <!-- Max items info -->
          <p v-if="multiple && maxItems" class="text-xs text-muted-foreground">
            {{ selectedColors.length }} / {{ maxItems }} colors selected
          </p>

          <!-- Custom swatches (if provided) -->
          <div v-if="showSwatches && swatches && swatches.length > 0" class="space-y-2">
            <p class="text-xs text-muted-foreground">{{ props.translations?.swatches || 'Swatches' }}</p>
            <div class="grid grid-cols-6 gap-2">
              <button
                v-for="swatch in swatches"
                :key="swatch"
                type="button"
                class="w-8 h-8 rounded border-2 transition-all hover:scale-110"
                :class="{
                  'border-primary ring-2 ring-primary ring-offset-1': isColorSelected(swatch),
                  'border-border': !isColorSelected(swatch),
                  'opacity-50 cursor-not-allowed': !canAddMore && !isColorSelected(swatch),
                }"
                :style="{ backgroundColor: swatch }"
                :disabled="disabled || (!canAddMore && !isColorSelected(swatch))"
                @click="selectColor(swatch)"
                :title="swatch"
              />
            </div>
          </div>

          <!-- Common colors (always shown) -->
          <div class="space-y-2">
            <p class="text-xs text-muted-foreground">{{ props.translations?.commonColors || 'Common colors' }}</p>
            <div class="grid grid-cols-8 gap-2">
              <button
                v-for="color in commonColors"
                :key="color"
                type="button"
                class="w-6 h-6 rounded border-2 transition-all hover:scale-110"
                :class="{
                  'border-primary ring-1 ring-primary': isColorSelected(color),
                  'border-border': !isColorSelected(color),
                  'opacity-50 cursor-not-allowed': !canAddMore && !isColorSelected(color),
                }"
                :style="{ backgroundColor: color }"
                :disabled="disabled || (!canAddMore && !isColorSelected(color))"
                @click="selectColor(color)"
              />
            </div>
          </div>
        </div>
      </PopoverContent>
    </Popover>

    <!-- Helper text -->
    <p
      v-if="helperText"
      class="text-xs text-muted-foreground mt-1"
    >
      {{ helperText }}
    </p>
  </div>
</template>
