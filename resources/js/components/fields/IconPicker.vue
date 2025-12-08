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
import { ref, computed } from 'vue'

interface Props {
  name?: string
  value?: string | null
  modelValue?: string | null
  label?: string
  helperText?: string
  required?: boolean
  placeholder?: string
  icons?: string[]
  searchable?: boolean
  gridColumns?: number
  showIconName?: boolean
  disabled?: boolean
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  placeholder: 'Select an icon...',
  icons: () => [],
  searchable: true,
  gridColumns: 8,
  showIconName: true,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | null]
  'update:value': [value: string | null]
}>()

const searchQuery = ref('')
const isOpen = ref(false)
const selectedIcon = ref<string | null>(props.modelValue || props.value || null)

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

  return colorMap[color] || `text-${color}`
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

// Select icon
const selectIcon = (icon: string) => {
  selectedIcon.value = icon
  emit('update:modelValue', icon)
  emit('update:value', icon)
  isOpen.value = false
  searchQuery.value = ''
}

// Clear selection
const clearSelection = () => {
  selectedIcon.value = null
  emit('update:modelValue', null)
  emit('update:value', null)
}

// Get selected icon component
const selectedIconComponent = computed(() => {
  return getIconComponent(selectedIcon.value || undefined)
})
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
      :value="selectedIcon || ''"
    />

    <!-- Icon Picker Trigger -->
    <Popover v-model:open="isOpen">
      <PopoverTrigger as-child>
        <Button
          type="button"
          variant="outline"
          :disabled="disabled"
          class="w-full justify-between h-10 px-3 font-normal"
          :class="{
            'text-muted-foreground': !selectedIcon,
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

            <!-- Selected Icon -->
            <component
              v-if="selectedIconComponent"
              :is="selectedIconComponent"
              class="h-4 w-4 shrink-0"
            />

            <!-- Placeholder or selected icon name -->
            <span class="truncate">
              {{ selectedIcon || placeholder }}
            </span>
          </div>

          <div class="flex items-center gap-1">
            <!-- Clear button -->
            <button
              v-if="selectedIcon && !disabled"
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
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="searchQuery"
                type="text"
                placeholder="Search icons..."
                class="pl-9 h-9"
              />
            </div>
          </div>

          <!-- Icons grid -->
          <div class="p-3 max-h-[300px] overflow-y-auto">
            <div v-if="filteredIcons.length === 0" class="text-center py-6 text-sm text-muted-foreground">
              No icons found
            </div>

            <div v-else class="grid gap-2" :class="gridClass">
              <button
                v-for="icon in filteredIcons"
                :key="icon"
                type="button"
                class="flex flex-col items-center justify-center p-2 rounded-md hover:bg-accent transition-colors"
                :class="{
                  'bg-accent': selectedIcon === icon,
                }"
                @click="selectIcon(icon)"
              >
                <component
                  :is="getIconComponent(icon)"
                  class="h-5 w-5 mb-1"
                  :class="{
                    'text-primary': selectedIcon === icon,
                    'text-muted-foreground': selectedIcon !== icon,
                  }"
                />
                <span
                  v-if="showIconName"
                  class="text-[10px] text-center truncate w-full"
                  :class="{
                    'text-primary font-medium': selectedIcon === icon,
                    'text-muted-foreground': selectedIcon !== icon,
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
