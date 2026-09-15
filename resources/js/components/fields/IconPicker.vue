<script setup lang="ts">
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import * as LucideIcons from 'lucide-vue-next'
import { Search, X } from 'lucide-vue-next'
import { ref, computed, watch } from 'vue'

interface Props {
  name?: string
  value?: string | string[] | null
  modelValue?: string | string[] | null
  label?: string
  helperText?: string
  required?: boolean
  icons?: string[]
  searchable?: boolean
  gridColumns?: number
  showIconName?: boolean
  disabled?: boolean
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
  multiple?: boolean
  maxItems?: number | null
  minItems?: number | null
  translations?: {
    placeholder?: string
    searchPlaceholder?: string
    noIconsFound?: string
  }
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  icons: () => [],
  searchable: true,
  gridColumns: 8,
  showIconName: true,
  disabled: false,
  multiple: false,
  maxItems: null,
  minItems: null,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | string[] | null]
  'update:value': [value: string | string[] | null]
}>()

const searchQuery = ref('')
const isOpen = ref(false)

// Initialize for single mode
const selectedIcon = ref<string | null>(
  !props.multiple
    ? (Array.isArray(props.modelValue || props.value)
        ? (props.modelValue || props.value)?.[0] || null
        : (props.modelValue || props.value) || null)
    : null
)

// Initialize for multiple mode
const selectedIcons = ref<string[]>(
  props.multiple
    ? (Array.isArray(props.modelValue || props.value)
        ? [...(props.modelValue || props.value || [])]
        : (props.modelValue || props.value ? [props.modelValue || props.value] : []))
    : []
)

// Convert icon name to PascalCase for Lucide
const getIconComponent = (iconName?: string) => {
  if (!iconName) return null
  const pascalCase = iconName
    .split(/[-_\s]/)
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join('')
  return (LucideIcons as any)[pascalCase] || null
}

// Get Tailwind color classes for icons
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

  return colorMap[color] || 'text-muted-foreground'
}

// Filter icons based on search query
const filteredIcons = computed(() => {
  if (!props.searchable || !searchQuery.value) {
    return props.icons
  }

  const query = searchQuery.value.toLowerCase()
  return props.icons.filter((icon) => icon.toLowerCase().includes(query))
})

// Grid columns class
const gridClass = computed(() => {
  const columns = {
    1: 'grid-cols-1',
    2: 'grid-cols-2',
    3: 'grid-cols-3',
    4: 'grid-cols-4',
    5: 'grid-cols-5',
    6: 'grid-cols-6',
    7: 'grid-cols-7',
    8: 'grid-cols-8',
    9: 'grid-cols-9',
    10: 'grid-cols-10',
    11: 'grid-cols-11',
    12: 'grid-cols-12',
  }
  return columns[props.gridColumns as keyof typeof columns] || 'grid-cols-8'
})

// Check if icon is selected
const isIconSelected = (icon: string) => {
  if (props.multiple) {
    return selectedIcons.value.includes(icon)
  }
  return selectedIcon.value === icon
}

// Check if can add more icons
const canAddMore = computed(() => {
  if (!props.multiple) return true
  if (props.maxItems === null) return true
  return selectedIcons.value.length < props.maxItems
})

// Select/toggle icon
const selectIcon = (icon: string) => {
  if (props.multiple) {
    toggleIcon(icon)
  } else {
    selectedIcon.value = icon
    emit('update:modelValue', icon)
    emit('update:value', icon)
    isOpen.value = false
    searchQuery.value = ''
  }
}

// Toggle icon in multiple mode
const toggleIcon = (icon: string) => {
  const index = selectedIcons.value.indexOf(icon)
  if (index > -1) {
    // Remove icon
    selectedIcons.value.splice(index, 1)
  } else if (canAddMore.value) {
    // Add icon
    selectedIcons.value.push(icon)
  }
  emitMultipleValue()
}

// Remove icon from selection (multiple mode)
const removeIcon = (icon: string) => {
  const index = selectedIcons.value.indexOf(icon)
  if (index > -1) {
    selectedIcons.value.splice(index, 1)
    emitMultipleValue()
  }
}

// Emit value for multiple mode
const emitMultipleValue = () => {
  const value = selectedIcons.value.length > 0 ? [...selectedIcons.value] : null
  emit('update:modelValue', value)
  emit('update:value', value)
}

// Clear selection
const clearSelection = () => {
  if (props.multiple) {
    selectedIcons.value = []
    emitMultipleValue()
  } else {
    selectedIcon.value = null
    emit('update:modelValue', null)
    emit('update:value', null)
  }
}

// Display value for trigger button
const displayValue = computed(() => {
  if (props.multiple) {
    if (selectedIcons.value.length === 0) {
      return props.translations?.placeholder || 'Select icons'
    }
    return `${selectedIcons.value.length} icon(s) selected`
  }
  return selectedIcon.value || props.translations?.placeholder || 'Select an icon'
})

// Get selected icon component (single mode)
const selectedIconComponent = computed(() => {
  return getIconComponent(selectedIcon.value || undefined)
})

// Watch for external value changes
watch(() => props.modelValue ?? props.value, (newValue) => {
  if (props.multiple) {
    if (Array.isArray(newValue)) {
      selectedIcons.value = [...newValue]
    } else if (newValue) {
      selectedIcons.value = [newValue]
    } else {
      selectedIcons.value = []
    }
  } else {
    selectedIcon.value = Array.isArray(newValue) ? newValue[0] || null : newValue || null
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
          v-for="(icon, index) in selectedIcons"
          :key="index"
          type="hidden"
          :name="`${name}[]`"
          :value="icon"
        />
      </template>
      <input
        v-else
        type="hidden"
        :name="name"
        :value="selectedIcon || ''"
      />
    </template>

    <!-- Selected icons display (multiple mode) -->
    <div v-if="multiple && selectedIcons.length > 0" class="flex flex-wrap gap-2 mb-2">
      <div
        v-for="icon in selectedIcons"
        :key="icon"
        class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-muted text-sm"
      >
        <component
          v-if="getIconComponent(icon)"
          :is="getIconComponent(icon)"
          class="h-4 w-4 shrink-0"
        />
        <span class="text-xs">{{ icon }}</span>
        <button
          v-if="!disabled"
          type="button"
          class="hover:text-destructive transition-colors"
          @click="removeIcon(icon)"
        >
          <X class="h-3 w-3" />
        </button>
      </div>
    </div>

    <!-- Icon Picker Trigger -->
    <Popover v-model:open="isOpen">
      <PopoverTrigger as-child>
        <Button
          type="button"
          variant="outline"
          :disabled="disabled"
          class="w-full justify-between h-10 px-3 font-normal"
          :class="{
            'text-muted-foreground': multiple ? selectedIcons.length === 0 : !selectedIcon,
          }"
        >
          <div class="flex items-center gap-2">
            <!-- Prefix Icon -->
            <component
              v-if="getIconComponent(prefixIcon)"
              :is="getIconComponent(prefixIcon)"
              class="h-4 w-4 shrink-0"
              :class="getIconColorClass(prefixIconColor)"
            />

            <!-- Selected Icon (single mode) -->
            <component
              v-if="!multiple && selectedIconComponent"
              :is="selectedIconComponent"
              class="h-4 w-4 shrink-0"
            />

            <!-- Multiple icons preview -->
            <div v-if="multiple" class="flex -space-x-1">
              <div
                v-for="(icon, index) in selectedIcons.slice(0, 3)"
                :key="icon"
                class="w-6 h-6 rounded-full bg-muted border-2 border-background flex items-center justify-center"
                :style="{ zIndex: 3 - index }"
              >
                <component
                  v-if="getIconComponent(icon)"
                  :is="getIconComponent(icon)"
                  class="h-3 w-3"
                />
              </div>
              <div
                v-if="selectedIcons.length > 3"
                class="w-6 h-6 rounded-full border-2 border-background bg-muted flex items-center justify-center text-[10px]"
              >
                +{{ selectedIcons.length - 3 }}
              </div>
            </div>

            <!-- Placeholder or selected icon name -->
            <span class="truncate">
              {{ displayValue }}
            </span>
          </div>

          <div class="flex items-center gap-1">
            <!-- Clear button -->
            <button
              v-if="(multiple ? selectedIcons.length > 0 : selectedIcon) && !disabled"
              type="button"
              class="hover:text-destructive transition-colors"
              @click.stop="clearSelection"
            >
              <X class="h-4 w-4" />
            </button>

            <!-- Suffix Icon -->
            <component
              v-if="getIconComponent(suffixIcon)"
              :is="getIconComponent(suffixIcon)"
              class="h-4 w-4 shrink-0"
              :class="getIconColorClass(suffixIconColor)"
            />
          </div>
        </Button>
      </PopoverTrigger>

      <PopoverContent class="w-[400px] p-0" align="start">
        <div class="flex flex-col">
          <!-- Search input -->
          <div v-if="searchable" class="p-3 border-b border-border">
            <div class="relative">
              <Search class="absolute start-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="searchQuery"
                type="text"
                :placeholder="props.translations?.searchPlaceholder || 'Search icons...'"
                class="ps-9 h-9"
              />
            </div>
          </div>

          <!-- Max items info -->
          <div v-if="multiple && maxItems" class="px-3 pt-2">
            <p class="text-xs text-muted-foreground">
              {{ selectedIcons.length }} / {{ maxItems }} icons selected
            </p>
          </div>

          <!-- Icons grid -->
          <div class="p-3 max-h-[300px] overflow-y-auto">
            <div v-if="filteredIcons.length === 0" class="text-center py-6 text-sm text-muted-foreground">
              {{ props.translations?.noIconsFound || 'No icons found' }}
            </div>

            <div v-else class="grid gap-2" :class="gridClass">
              <button
                v-for="icon in filteredIcons"
                :key="icon"
                type="button"
                class="flex flex-col items-center justify-center p-2 rounded-md hover:bg-accent transition-colors"
                :class="{
                  'bg-accent ring-2 ring-primary': isIconSelected(icon),
                  'opacity-50 cursor-not-allowed': !canAddMore && !isIconSelected(icon),
                }"
                :disabled="disabled || (!canAddMore && !isIconSelected(icon))"
                @click="selectIcon(icon)"
              >
                <component
                  :is="getIconComponent(icon)"
                  class="h-5 w-5 mb-1"
                  :class="{
                    'text-primary': isIconSelected(icon),
                    'text-muted-foreground': !isIconSelected(icon),
                  }"
                />
                <span
                  v-if="showIconName"
                  class="text-[10px] text-center truncate w-full"
                  :class="{
                    'text-primary font-medium': isIconSelected(icon),
                    'text-muted-foreground': !isIconSelected(icon),
                  }"
                >
                  {{ icon }}
                </span>
              </button>
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
