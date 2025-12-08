<script setup lang="ts">
import { ref, computed } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import { X } from 'lucide-vue-next'

interface Props {
  name?: string
  value?: string | null
  modelValue?: string | null
  label?: string
  helperText?: string
  required?: boolean
  disabled?: boolean
  swatches?: string[]
  showSwatches?: boolean
  alpha?: boolean
  format?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  value: null,
  swatches: () => [],
  showSwatches: false,
  alpha: false,
  format: 'hex',
})

const emit = defineEmits<{
  'update:modelValue': [value: string | null]
  'update:value': [value: string | null]
}>()

const selectedColor = ref<string | null>(props.modelValue || props.value || null)
const isOpen = ref(false)

// Select color
const selectColor = (color: string) => {
  selectedColor.value = color
  emit('update:modelValue', color)
  emit('update:value', color)
  if (props.showSwatches) {
    isOpen.value = false
  }
}

// Clear selection
const clearSelection = () => {
  selectedColor.value = null
  emit('update:modelValue', null)
  emit('update:value', null)
}

// Update from input
const updateColor = (event: Event) => {
  const target = event.target as HTMLInputElement
  selectColor(target.value)
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
      :value="selectedColor || ''"
    />

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
            <!-- Color preview -->
            <div
              class="w-6 h-6 rounded border border-border"
              :style="{ backgroundColor: selectedColor || '#e5e7eb' }"
            />

            <!-- Selected color text -->
            <span :class="{ 'text-muted-foreground': !selectedColor }">
              {{ selectedColor || 'Select color' }}
            </span>
          </div>

          <!-- Clear button -->
          <button
            v-if="selectedColor && !disabled"
            type="button"
            class="hover:text-destructive transition-colors"
            @click.stop="clearSelection"
          >
            <X class="h-4 w-4" />
          </button>
        </Button>
      </PopoverTrigger>

      <PopoverContent class="w-[280px] p-4" align="start">
        <div class="space-y-4">
          <!-- Native color picker and text input -->
          <div class="flex items-center gap-3">
            <input
              type="color"
              :value="selectedColor || '#000000'"
              @input="updateColor"
              class="w-12 h-12 rounded cursor-pointer border border-border"
              :disabled="disabled"
            />
            <div class="flex-1">
              <Input
                type="text"
                :value="selectedColor || ''"
                @input="updateColor"
                placeholder="#000000"
                class="font-mono text-sm"
                :disabled="disabled"
              />
            </div>
          </div>

          <!-- Custom swatches (if provided) -->
          <div v-if="showSwatches && swatches && swatches.length > 0" class="space-y-2">
            <p class="text-xs text-muted-foreground">Swatches</p>
            <div class="grid grid-cols-6 gap-2">
              <button
                v-for="swatch in swatches"
                :key="swatch"
                type="button"
                class="w-8 h-8 rounded border-2 transition-all hover:scale-110"
                :class="{
                  'border-primary ring-2 ring-primary ring-offset-1': selectedColor === swatch,
                  'border-border': selectedColor !== swatch,
                }"
                :style="{ backgroundColor: swatch }"
                :disabled="disabled"
                @click="selectColor(swatch)"
                :title="swatch"
              />
            </div>
          </div>

          <!-- Common colors (always shown) -->
          <div class="space-y-2">
            <p class="text-xs text-muted-foreground">Common colors</p>
            <div class="grid grid-cols-8 gap-2">
              <button
                v-for="color in [
                  '#000000', '#ffffff', '#ef4444', '#f97316', '#f59e0b', '#eab308',
                  '#84cc16', '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9',
                  '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899',
                  '#f43f5e', '#64748b'
                ]"
                :key="color"
                type="button"
                class="w-6 h-6 rounded border-2 transition-all hover:scale-110"
                :class="{
                  'border-primary ring-1 ring-primary': selectedColor === color,
                  'border-border': selectedColor !== color,
                }"
                :style="{ backgroundColor: color }"
                :disabled="disabled"
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
