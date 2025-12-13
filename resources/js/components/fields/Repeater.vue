<script setup lang="ts">
import { Button } from '@/components/ui/button'
import * as LucideIcons from 'lucide-vue-next'
import { Plus, X, GripVertical, ChevronDown, Copy } from 'lucide-vue-next'
import { ref, computed, Component as VueComponent, watch, onMounted, onBeforeUnmount, provide, inject, markRaw, triggerRef } from 'vue'
import Sortable from 'sortablejs'
import TextInput from './TextInput.vue'
import Textarea from './Textarea.vue'
import Select from './Select.vue'
import Toggle from './Toggle.vue'
import ToggleButtons from './ToggleButtons.vue'
import Slider from './Slider.vue'
import DatePicker from './DatePicker.vue'
import DateTimePicker from './DateTimePicker.vue'
import TimePicker from './TimePicker.vue'
import FileUpload from './FileUpload.vue'
import CheckboxList from './CheckboxList.vue'
import Checkbox from './Checkbox.vue'
import Radio from './Radio.vue'
import TagsInput from './TagsInput.vue'
import KeyValue from './KeyValue.vue'
import ColorPicker from './ColorPicker.vue'
import RichEditor from './RichEditor.vue'
import CodeEditor from './CodeEditor.vue'
import Hidden from './Hidden.vue'
import NumberField from './NumberField.vue'
import IconPicker from './IconPicker.vue'
import MarkdownEditor from './MarkdownEditor.vue'
import PinInput from './PinInput.vue'
import RateInput from './RateInput.vue'
import { useLocalization } from '@/composables/useLocalization'

// Initialize localization
const { trans } = useLocalization()

// Inject parent context for reactive fields
const parentFormController = inject<string | undefined>('formController', undefined)
const parentFormMethod = inject<string | undefined>('formMethod', 'getSchema')
const parentGetFormData = inject<(() => Record<string, any>) | undefined>('getFormData', undefined)
const parentUpdateSchema = inject<((schema: any[]) => void) | undefined>('updateSchema', undefined)

interface FieldSchema {
  component: string
  name: string
  isLive?: boolean
  isLazy?: boolean
  liveDebounce?: number
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
  columns?: number
  // Text labels for i18n support
  itemLabel?: string
  itemsLabel?: string
  minLabel?: string
  maxLabel?: string
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
  columns: undefined,
  itemLabel: 'Item',
  itemsLabel: 'item(s)',
  minLabel: 'Min:',
  maxLabel: 'Max:',
})

// Compute grid column classes based on columns prop
const gridColumnsClass = computed(() => {
  if (!props.columns) return ''
  const columnMap: Record<number, string> = {
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
  return columnMap[props.columns] || `grid-cols-${props.columns}`
})

// Get column span class for a field
const getColumnSpanClass = (field: FieldSchema) => {
  if (!props.columns) return ''
  const colSpan = field.columnSpan || field.colSpan || 1
  if (colSpan === 'full' || field.columnSpanFull) {
    return `col-span-full`
  }
  const spanMap: Record<number, string> = {
    1: 'col-span-1',
    2: 'col-span-2',
    3: 'col-span-3',
    4: 'col-span-4',
    5: 'col-span-5',
    6: 'col-span-6',
    7: 'col-span-7',
    8: 'col-span-8',
    9: 'col-span-9',
    10: 'col-span-10',
    11: 'col-span-11',
    12: 'col-span-12',
  }
  return spanMap[colSpan] || `col-span-${colSpan}`
}

const emit = defineEmits<{
  'update:modelValue': [value: any[]]
  'update:value': [value: any[]]
}>()

// Computed translated labels
const translatedAddButtonLabel = computed(() => props.addButtonLabel !== 'Add' ? props.addButtonLabel : trans('forms::forms.repeater.add_button_label'))
const translatedDeleteButtonLabel = computed(() => props.deleteButtonLabel !== 'Delete' ? props.deleteButtonLabel : trans('forms::forms.repeater.delete_button_label'))
const translatedItemLabel = computed(() => props.itemLabel !== 'Item' ? props.itemLabel : trans('forms::forms.repeater.item_label'))
const translatedItemsLabel = computed(() => props.itemsLabel !== 'item(s)' ? props.itemsLabel : trans('forms::forms.repeater.items_label'))
const translatedMinLabel = computed(() => props.minLabel !== 'Min:' ? props.minLabel : trans('forms::forms.repeater.min_label'))
const translatedMaxLabel = computed(() => props.maxLabel !== 'Max:' ? props.maxLabel : trans('forms::forms.repeater.max_label'))

// Internal state for repeater items
const internalItems = ref<any[]>([])

// Version counter to force Vue reactivity updates
const itemsVersion = ref(0)

// Flag to prevent watch from re-initializing during reactive updates
const isUpdating = ref(false)

// Track pending reactive updates to prevent duplicates
const pendingReactiveUpdate = ref<string | null>(null)

// Generate stable unique IDs for each item
const itemIds = ref<string[]>([])

const generateItemId = () => `item-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`

// Watch for prop changes and update internal state
watch(() => props.value ?? props.modelValue, (newValue, oldValue) => {
  // Skip if we're in the middle of an internal update
  if (isUpdating.value) {
    return
  }

  const newItems = Array.isArray(newValue) ? [...newValue] : []

  // If the length changed, it's likely an external update (initial load, add, remove)
  // If length is same but we're not updating, it could be a form reset or external change
  const lengthChanged = newItems.length !== internalItems.value.length
  const isInitialLoad = oldValue === undefined

  if (isInitialLoad || lengthChanged) {
    internalItems.value = newItems

    // Generate IDs for new items
    while (itemIds.value.length < newItems.length) {
      itemIds.value.push(generateItemId())
    }
    // Trim IDs if items were removed
    if (itemIds.value.length > newItems.length) {
      itemIds.value = itemIds.value.slice(0, newItems.length)
    }
  }
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
  'DateTimePicker': DateTimePicker,
  'TimePicker': TimePicker,
  'FileUpload': FileUpload,
  'CheckboxList': CheckboxList,
  'Checkbox': Checkbox,
  'Radio': Radio,
  'TagsInput': TagsInput,
  'KeyValue': KeyValue,
  'ColorPicker': ColorPicker,
  'RichEditor': RichEditor,
  'CodeEditor': CodeEditor,
  'Hidden': Hidden,
  'NumberField': NumberField,
  'IconPicker': IconPicker,
  'MarkdownEditor': MarkdownEditor,
  'PinInput': PinInput,
  'RateInput': RateInput,
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
          // Set flag to prevent watch interference
          isUpdating.value = true

          try {
            const newValue = [...internalItems.value]
            const [movedItem] = newValue.splice(oldIndex, 1)
            newValue.splice(newIndex, 0, movedItem)
            internalItems.value = newValue

            // Also reorder IDs
            const [movedId] = itemIds.value.splice(oldIndex, 1)
            itemIds.value.splice(newIndex, 0, movedId)

            emit('update:modelValue', newValue)
            emit('update:value', newValue)
          } finally {
            setTimeout(() => {
              isUpdating.value = false
            }, 100)
          }
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

  // Set flag to prevent watch interference
  isUpdating.value = true

  try {
    // Create new item with default values from schema
    const newItem: Record<string, any> = {}
    props.schema.forEach(field => {
      newItem[field.name] = field.defaultValue ?? null
    })

    const newValue = [...internalItems.value, newItem]
    internalItems.value = newValue
    itemIds.value.push(generateItemId())
    emit('update:modelValue', newValue)
    emit('update:value', newValue)

    // Auto-expand new item if collapsible
    if (props.collapsible) {
      openItems.value.add(internalItems.value.length - 1)
    }
  } finally {
    setTimeout(() => {
      isUpdating.value = false
    }, 100)
  }
}

const removeItem = (index: number) => {
  if (!canDeleteItem.value) return

  // Set flag to prevent watch interference
  isUpdating.value = true

  try {
    const newValue = [...internalItems.value]
    newValue.splice(index, 1)
    internalItems.value = newValue
    itemIds.value.splice(index, 1)
    emit('update:modelValue', newValue)
    emit('update:value', newValue)
    openItems.value.delete(index)
  } finally {
    setTimeout(() => {
      isUpdating.value = false
    }, 100)
  }
}

const cloneItem = (index: number) => {
  if (!canAddItem.value) return

  // Set flag to prevent watch interference
  isUpdating.value = true

  try {
    const itemToClone = internalItems.value[index]
    const clonedItem = JSON.parse(JSON.stringify(itemToClone))
    const newValue = [...internalItems.value]
    newValue.splice(index + 1, 0, clonedItem)
    internalItems.value = newValue
    itemIds.value.splice(index + 1, 0, generateItemId())
    emit('update:modelValue', newValue)
    emit('update:value', newValue)

    // Auto-expand cloned item if collapsible
    if (props.collapsible) {
      openItems.value.add(index + 1)
    }
  } finally {
    setTimeout(() => {
      isUpdating.value = false
    }, 100)
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


// Trigger reactive field update for live/lazy fields inside Repeater
const triggerReactiveFieldUpdate = async (itemIndex: number, fieldName: string, field: FieldSchema) => {
  // Skip if no form controller is configured
  if (!parentFormController) {
    return
  }

  // Create a unique key for this update to prevent duplicates
  const updateKey = `${itemIndex}.${fieldName}`

  // Skip if we already have a pending update for this field
  if (pendingReactiveUpdate.value === updateKey) {
    return
  }

  // Mark this update as pending
  pendingReactiveUpdate.value = updateKey

  try {
    // Get full form data from parent
    const fullFormData = parentGetFormData ? parentGetFormData() : {}

    // Update the repeater data in the form data
    fullFormData[props.name || 'items'] = internalItems.value

    // The changed field path is: repeaterName.itemIndex.fieldName
    const changedFieldPath = `${props.name || 'items'}.${itemIndex}.${fieldName}`

    const payload = {
      controller: parentFormController,
      method: parentFormMethod || 'getSchema',
      data: fullFormData,
      changed_field: changedFieldPath,
      repeater_name: props.name || 'items',
      repeater_index: itemIndex,
      field_name: fieldName,
    }

    const response = await fetch('/reactive-fields/update', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify(payload),
    })

    if (!response.ok) {
      throw new Error('Failed to update reactive fields')
    }

    const result = await response.json()

    // If backend returned updated data, apply it to the repeater item
    if (result.data) {
      const repeaterData = result.data[props.name || 'items']

      if (repeaterData && Array.isArray(repeaterData) && repeaterData[itemIndex]) {
        // Update only the specific item that was affected
        const newItems = [...internalItems.value]
        const updatedItem = {
          ...newItems[itemIndex],
          ...repeaterData[itemIndex],
        }

        // Update the item in place
        newItems[itemIndex] = updatedItem

        // Force Vue to detect the change by:
        // 1. Assigning a new array reference
        internalItems.value = [...newItems]

        // 2. Incrementing version to force computed properties to re-evaluate
        itemsVersion.value++

        // 3. Trigger ref to ensure Vue picks up the change
        triggerRef(internalItems)
      }
    }

    // Note: We intentionally do NOT call parentUpdateSchema for Repeater reactive updates
    // because we're only updating data within the item, not changing the schema structure.
    // Calling updateSchema would cause a full re-render and scroll issues.
  } catch (error) {
    console.error('[Repeater] Error updating reactive fields:', error)
  } finally {
    // Clear the pending update flag
    pendingReactiveUpdate.value = null
  }
}

// Update field value within an item
const updateFieldValue = async (itemIndex: number, fieldName: string, value: any, field?: FieldSchema) => {
  // Set flag to prevent watch from re-initializing during our update
  isUpdating.value = true

  try {
    const newValue = [...internalItems.value]
    newValue[itemIndex] = {
      ...newValue[itemIndex],
      [fieldName]: value,
    }
    internalItems.value = newValue

    // Check if this field is reactive (live/lazy) and trigger update
    if (field && (field.isLive || field.isLazy)) {
      // For reactive fields, we trigger the update but DON'T emit to parent
      // This prevents parent re-render and component remount
      // The data is tracked internally and will be submitted via hidden input
      await triggerReactiveFieldUpdate(itemIndex, fieldName, field)
    } else {
      // For non-reactive fields, emit normally so parent stays in sync
      emit('update:modelValue', newValue)
      emit('update:value', newValue)
    }
  } finally {
    // Reset flag after a short delay to allow Vue to process updates
    setTimeout(() => {
      isUpdating.value = false
    }, 100)
  }
}

// Get field value from item
const getFieldValue = (itemIndex: number, fieldName: string) => {
  return internalItems.value[itemIndex]?.[fieldName]
}

// Get field props with reactive flags and value stripped
// This prevents child components from triggering their own reactive updates
// The Repeater handles reactivity at its level via updateFieldValue
// We strip 'value' and 'modelValue' so our explicit bindings take precedence
const getFieldProps = (field: FieldSchema) => {
  const { isLive, isLazy, liveDebounce, label, helperText, value, modelValue, defaultValue, ...rest } = field
  return rest
}

// Provide a null formController to child components
// This prevents them from making their own reactive API calls
// The Repeater handles reactivity internally
provide('formController', undefined)
provide('formMethod', undefined)
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
        :key="itemIds[index] || `fallback-${index}`"
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
            class="flex-1 flex items-center gap-2 text-sm font-medium text-start hover:text-foreground transition-colors"
            @click="toggleItem(index)"
          >
            <ChevronDown
              class="h-4 w-4 transition-transform"
              :class="{ '-rotate-90': !isOpen(index) }"
            />
            <span>{{ translatedItemLabel }} {{ index + 1 }}</span>
          </button>

          <span v-else class="flex-1 text-sm font-medium text-foreground">
            {{ translatedItemLabel }} {{ index + 1 }}
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
          class="p-4"
          :class="columns ? `grid gap-4 ${gridColumnsClass}` : 'space-y-4'"
        >
          <!-- Render schema fields dynamically -->
          <template
            v-for="field in schema"
            :key="field.name"
          >
            <!-- Hidden fields - render without wrapper/label -->
            <component
              v-if="field.hidden || field.component === 'Hidden' || field.component === 'hidden'"
              :is="getComponent(field.component)"
              :key="`${field.name}-${index}-${itemsVersion}`"
              :id="`${field.name}-${index}`"
              :value="getFieldValue(index, field.name)"
              :model-value="getFieldValue(index, field.name)"
              v-bind="getFieldProps(field)"
              @update:model-value="updateFieldValue(index, field.name, $event, field)"
            />

            <!-- Visible fields - render with wrapper/label -->
            <div
              v-else
              class="space-y-1.5"
              :class="getColumnSpanClass(field)"
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
                :key="`${field.name}-${index}-${itemsVersion}`"
                :id="`${field.name}-${index}`"
                :value="getFieldValue(index, field.name)"
                :model-value="getFieldValue(index, field.name)"
                v-bind="getFieldProps(field)"
                @update:model-value="updateFieldValue(index, field.name, $event, field)"
              />

              <p
                v-if="field.helperText"
                class="text-xs text-muted-foreground mt-1"
              >
                {{ field.helperText }}
              </p>
            </div>
          </template>
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
      <span>{{ translatedAddButtonLabel }}</span>
    </Button>

    <!-- Validation hints -->
    <div v-if="minItems || maxItems" class="flex items-center gap-1 text-xs text-muted-foreground px-1">
      <span v-if="minItems">{{ translatedMinLabel }} {{ minItems }}</span>
      <span v-if="minItems && maxItems">•</span>
      <span v-if="maxItems">{{ translatedMaxLabel }} {{ maxItems }}</span>
      <span v-if="internalItems.length > 0" class="ml-auto">{{ internalItems.length }} {{ translatedItemsLabel }}</span>
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
