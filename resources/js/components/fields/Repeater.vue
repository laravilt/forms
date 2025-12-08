<script setup lang="ts">
import { Button } from '@/components/ui/button'
import * as LucideIcons from 'lucide-vue-next'
import { Plus, X, GripVertical, ChevronDown, Copy } from 'lucide-vue-next'
import { ref, computed, Component as VueComponent, watch, onMounted, onBeforeUnmount } from 'vue'
import Sortable from 'sortablejs'
import TextInput from './TextInput.vue'
import Textarea from './Textarea.vue'
import Select from './Select.vue'
import Toggle from './Toggle.vue'
import ToggleButtons from './ToggleButtons.vue'
import Slider from './Slider.vue'
import DatePicker from './DatePicker.vue'
import FileUpload from './FileUpload.vue'
import CheckboxList from './CheckboxList.vue'
import Radio from './Radio.vue'
import TagsInput from './TagsInput.vue'
import KeyValue from './KeyValue.vue'
import ColorPicker from './ColorPicker.vue'
import RichEditor from './RichEditor.vue'
import CodeEditor from './CodeEditor.vue'

interface FieldSchema {
  component: string
  name: string
  [key: string]: any
}

interface Props {
  name?: string
  value?: any[]
  modelValue?: any[]
  label?: string
  helperText?: string
  required?: boolean
  schema?: FieldSchema[]
  addButtonLabel?: string
  deleteButtonLabel?: string
  reorderable?: boolean
  collapsible?: boolean
  cloneable?: boolean
  deletable?: boolean
  minItems?: number
  maxItems?: number
  disabled?: boolean
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: () => [],
  schema: () => [],
  addButtonLabel: 'Add',
  deleteButtonLabel: 'Delete',
  reorderable: false,
  collapsible: false,
  cloneable: false,
  deletable: true,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: any[]]
  'update:value': [value: any[]]
}>()

// Internal state for repeater items
const internalItems = ref<any[]>([])

// Watch for prop changes and update internal state
watch(() => props.value ?? props.modelValue, (newValue) => {
  internalItems.value = Array.isArray(newValue) ? [...newValue] : []
}, { immediate: true })

const openItems = ref<Set<number>>(new Set())
const itemsContainer = ref<HTMLElement>()
let sortableInstance: Sortable | null = null

// Component mapping for dynamic rendering
const componentMap: Record<string, VueComponent> = {
  'TextInput': TextInput,
  'Textarea': Textarea,
  'Select': Select,
  'Toggle': Toggle,
  'ToggleButtons': ToggleButtons,
  'Slider': Slider,
  'DatePicker': DatePicker,
  'FileUpload': FileUpload,
  'CheckboxList': CheckboxList,
  'Radio': Radio,
  'TagsInput': TagsInput,
  'KeyValue': KeyValue,
  'ColorPicker': ColorPicker,
  'RichEditor': RichEditor,
  'CodeEditor': CodeEditor,
}

// Setup drag-and-drop sorting after mount
onMounted(() => {
  if (props.reorderable && itemsContainer.value) {
    sortableInstance = new Sortable(itemsContainer.value, {
      animation: 150,
      handle: '.drag-handle',
      ghostClass: 'sortable-ghost',
      dragClass: 'sortable-drag',
      onEnd: (evt) => {
        const oldIndex = evt.oldIndex
        const newIndex = evt.newIndex

        if (oldIndex !== undefined && newIndex !== undefined && oldIndex !== newIndex) {
          const newValue = [...internalItems.value]
          const [movedItem] = newValue.splice(oldIndex, 1)
          newValue.splice(newIndex, 0, movedItem)
          internalItems.value = newValue
          emit('update:modelValue', newValue)
          emit('update:value', newValue)
        }
      },
    })
  }
})

// Cleanup sortable instance
onBeforeUnmount(() => {
  if (sortableInstance) {
    sortableInstance.destroy()
    sortableInstance = null
  }
})

// Helper to get Vue component from schema
const getComponent = (componentName: string): VueComponent | null => {
  // Handle both snake_case (from PHP) and PascalCase
  if (componentMap[componentName]) {
    return componentMap[componentName]
  }

  // Convert snake_case to PascalCase (e.g., "text_input" => "TextInput")
  const pascalCase = componentName
    .split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join('')

  return componentMap[pascalCase] || null
}

// Helper to get Lucide icon component by name
const getIconComponent = (iconName?: string) => {
  if (!iconName) return null
  const pascalCase = iconName
    .split(/[-_\s]/)
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join('')
  return (LucideIcons as any)[pascalCase] || null
}

// Helper to get Tailwind color classes for icons
const getIconColorClass = (color?: string) => {
  if (!color) return 'text-muted-foreground'

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

// Check if we can add more items
const canAddItem = computed(() => {
  if (props.disabled) return false
  if (props.maxItems === undefined || props.maxItems === null) return true
  return internalItems.value.length < props.maxItems
})

// Check if we can delete items
const canDeleteItem = computed(() => {
  if (props.disabled || !props.deletable) return false
  if (props.minItems === undefined || props.minItems === null) return true
  return internalItems.value.length > props.minItems
})

const addItem = () => {
  if (!canAddItem.value) return

  // Create new item with default values from schema
  const newItem: Record<string, any> = {}
  props.schema.forEach(field => {
    newItem[field.name] = field.defaultValue ?? null
  })

  const newValue = [...internalItems.value, newItem]
  internalItems.value = newValue
  emit('update:modelValue', newValue)
  emit('update:value', newValue)

  // Auto-expand new item if collapsible
  if (props.collapsible) {
    openItems.value.add(internalItems.value.length - 1)
  }
}

const removeItem = (index: number) => {
  if (!canDeleteItem.value) return

  const newValue = [...internalItems.value]
  newValue.splice(index, 1)
  internalItems.value = newValue
  emit('update:modelValue', newValue)
  emit('update:value', newValue)
  openItems.value.delete(index)
}

const cloneItem = (index: number) => {
  if (!canAddItem.value) return

  const itemToClone = internalItems.value[index]
  const clonedItem = JSON.parse(JSON.stringify(itemToClone))
  const newValue = [...internalItems.value]
  newValue.splice(index + 1, 0, clonedItem)
  internalItems.value = newValue
  emit('update:modelValue', newValue)
  emit('update:value', newValue)

  // Auto-expand cloned item if collapsible
  if (props.collapsible) {
    openItems.value.add(index + 1)
  }
}

const toggleItem = (index: number) => {
  if (openItems.value.has(index)) {
    openItems.value.delete(index)
  } else {
    openItems.value.add(index)
  }
}

const isOpen = (index: number) => {
  return !props.collapsible || openItems.value.has(index)
}

// Update field value within an item
const updateFieldValue = (itemIndex: number, fieldName: string, value: any) => {
  const newValue = [...internalItems.value]
  newValue[itemIndex] = {
    ...newValue[itemIndex],
    [fieldName]: value,
  }
  internalItems.value = newValue
  emit('update:modelValue', newValue)
  emit('update:value', newValue)
}

// Get field value from item
const getFieldValue = (itemIndex: number, fieldName: string) => {
  return internalItems.value[itemIndex]?.[fieldName]
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
      :value="JSON.stringify(internalItems)"
    />

    <!-- Items list -->
    <div ref="itemsContainer" class="space-y-2">
      <div
        v-for="(item, index) in internalItems"
        :key="index"
        class="group relative rounded-lg border border-border bg-card transition-colors hover:border-primary/50"
      >
        <!-- Header -->
        <div class="flex items-center gap-2 border-b border-border bg-muted/30 px-3 py-2.5">
          <!-- Drag handle -->
          <button
            v-if="reorderable"
            type="button"
            class="drag-handle cursor-grab active:cursor-grabbing text-muted-foreground hover:text-foreground transition-colors shrink-0"
            :disabled="disabled"
          >
            <GripVertical class="h-4 w-4" />
          </button>

          <!-- Collapse trigger -->
          <button
            v-if="collapsible"
            type="button"
            class="flex-1 flex items-center gap-2 text-sm font-medium text-left hover:text-foreground transition-colors"
            @click="toggleItem(index)"
          >
            <ChevronDown
              class="h-4 w-4 transition-transform"
              :class="{ '-rotate-90': !isOpen(index) }"
            />
            <span>Item {{ index + 1 }}</span>
          </button>

          <span v-else class="flex-1 text-sm font-medium text-foreground">
            Item {{ index + 1 }}
          </span>

          <!-- Action buttons -->
          <div class="flex items-center gap-1">
            <!-- Clone button -->
            <Button
              v-if="cloneable"
              type="button"
              variant="ghost"
              size="sm"
              class="h-7 w-7 p-0 text-muted-foreground hover:text-foreground"
              :disabled="!canAddItem"
              @click="cloneItem(index)"
            >
              <Copy class="h-3.5 w-3.5" />
            </Button>

            <!-- Delete button -->
            <Button
              v-if="deletable"
              type="button"
              variant="ghost"
              size="sm"
              class="h-7 w-7 p-0 text-muted-foreground hover:text-destructive"
              :disabled="!canDeleteItem"
              @click="removeItem(index)"
            >
              <X class="h-3.5 w-3.5" />
            </Button>
          </div>
        </div>

        <!-- Content (collapsible) -->
        <div
          v-if="isOpen(index)"
          class="p-4 space-y-4"
        >
          <!-- Render schema fields dynamically -->
          <div
            v-for="field in schema"
            :key="field.name"
            class="space-y-1.5"
          >
            <label
              v-if="field.label"
              :for="`${field.name}-${index}`"
              class="text-sm font-medium block text-foreground"
            >
              {{ field.label }}
              <span v-if="field.required" class="text-destructive ms-0.5">*</span>
            </label>

            <component
              :is="getComponent(field.component)"
              v-if="getComponent(field.component)"
              :id="`${field.name}-${index}`"
              :model-value="getFieldValue(index, field.name)"
              v-bind="{ ...field, label: undefined, helperText: undefined }"
              @update:model-value="updateFieldValue(index, field.name, $event)"
            />

            <p
              v-if="field.helperText"
              class="text-xs text-muted-foreground mt-1"
            >
              {{ field.helperText }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Add button -->
    <Button
      type="button"
      variant="outline"
      class="w-full justify-center gap-2 border-dashed hover:border-solid hover:bg-accent/50"
      :disabled="!canAddItem"
      @click="addItem"
    >
      <Plus class="h-4 w-4" />
      <span>{{ addButtonLabel }}</span>
    </Button>

    <!-- Validation hints -->
    <div v-if="minItems || maxItems" class="flex items-center gap-1 text-xs text-muted-foreground px-1">
      <span v-if="minItems">Min: {{ minItems }}</span>
      <span v-if="minItems && maxItems">•</span>
      <span v-if="maxItems">Max: {{ maxItems }}</span>
      <span v-if="internalItems.length > 0" class="ml-auto">{{ internalItems.length }} item(s)</span>
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

<style scoped>
/* Smooth transitions for drag and drop */
.sortable-ghost {
  opacity: 0.4;
}

.sortable-drag {
  opacity: 0;
}

/* Drag handle cursor */
.drag-handle:active {
  cursor: grabbing !important;
}
</style>
