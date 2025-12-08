<script setup lang="ts">
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import * as LucideIcons from 'lucide-vue-next'
import { Plus, X, GripVertical } from 'lucide-vue-next'
import { ref } from 'vue'

interface KeyValuePair {
  key: string
  value: string
}

interface Props {
  name?: string
  value?: Record<string, string> | KeyValuePair[]
  modelValue?: Record<string, string> | KeyValuePair[]
  label?: string
  helperText?: string
  required?: boolean
  keyLabel?: string
  valueLabel?: string
  addButtonLabel?: string
  reorderable?: boolean
  deletable?: boolean
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: () => ({}),
  keyLabel: 'Key',
  valueLabel: 'Value',
  addButtonLabel: 'Add Item',
  reorderable: false,
  deletable: true,
})

const emit = defineEmits<{
  'update:modelValue': [value: Record<string, string> | KeyValuePair[]]
  'update:value': [value: Record<string, string> | KeyValuePair[]]
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

// Convert modelValue to array format for easier manipulation
const pairs = ref<KeyValuePair[]>(
  Array.isArray(props.modelValue)
    ? props.modelValue
    : Object.entries(props.modelValue).map(([key, value]) => ({ key, value }))
)

const addPair = () => {
  pairs.value.push({ key: '', value: '' })
  emitUpdate()
}

const removePair = (index: number) => {
  pairs.value.splice(index, 1)
  emitUpdate()
}

const updatePair = (index: number, field: 'key' | 'value', value: string) => {
  pairs.value[index][field] = value
  emitUpdate()
}

const emitUpdate = () => {
  // Emit as object if original was object, array otherwise
  if (Array.isArray(props.modelValue)) {
    emit('update:modelValue', pairs.value)
    emit('update:value', pairs.value)
  } else {
    const obj: Record<string, string> = {}
    pairs.value.forEach(pair => {
      if (pair.key) {
        obj[pair.key] = pair.value
      }
    })
    emit('update:modelValue', obj)
    emit('update:value', obj)
  }
}
</script>

<template>
  <div class="w-full space-y-3">
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
      :value="JSON.stringify(modelValue)"
    />

    <!-- Header with icons -->
    <div v-if="getIconComponent(props.prefixIcon) || props.suffixIcon" class="flex items-center justify-between">
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

    <!-- Pairs list -->
    <div class="space-y-2">
      <div
        v-for="(pair, index) in pairs"
        :key="index"
        class="flex items-center gap-2"
      >
        <!-- Drag handle (if reorderable) -->
        <button
          v-if="reorderable"
          type="button"
          class="cursor-grab hover:text-primary shrink-0"
        >
          <GripVertical class="h-4 w-4" />
        </button>

        <!-- Key input -->
        <Input
          :model-value="pair.key"
          :placeholder="keyLabel"
          class="flex-1"
          @input="(e) => updatePair(index, 'key', (e.target as HTMLInputElement).value)"
        />

        <!-- Value input -->
        <Input
          :model-value="pair.value"
          :placeholder="valueLabel"
          class="flex-1"
          @input="(e) => updatePair(index, 'value', (e.target as HTMLInputElement).value)"
        />

        <!-- Delete button -->
        <Button
          v-if="deletable"
          type="button"
          variant="ghost"
          size="sm"
          class="shrink-0 h-9 w-9 p-0"
          @click="removePair(index)"
        >
          <X class="h-4 w-4" />
        </Button>
      </div>
    </div>

    <!-- Add button -->
    <Button
      type="button"
      variant="outline"
      size="sm"
      class="w-full"
      @click="addPair"
    >
      <Plus class="h-4 w-4 mr-2" />
      {{ addButtonLabel }}
    </Button>

    <!-- Helper text -->
    <p
      v-if="helperText"
      class="text-xs text-muted-foreground mt-1"
    >
      {{ helperText }}
    </p>
  </div>
</template>
