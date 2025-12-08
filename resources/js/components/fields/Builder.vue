<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader } from '@/components/ui/card'
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import * as LucideIcons from 'lucide-vue-next'
import {
  Plus,
  X,
  GripVertical,
  ChevronDown,
  ChevronUp,
  Copy,
  ChevronsDown,
  ChevronsUp,
} from 'lucide-vue-next'
import { computed, ref, watch } from 'vue'
import Sortable from 'sortablejs'
import { onMounted } from 'vue'

// Import all form components dynamically
const components: Record<string, any> = {}

interface BlockSchema {
  name: string
  component: string
  [key: string]: any
}

interface BlockDefinition {
  name: string
  label: string
  icon?: string
  schema: BlockSchema[]
  maxItems?: number
  columns?: number
}

interface BlockItem {
  id: string
  type: string
  data: Record<string, any>
}

interface Props {
  name?: string
  value?: BlockItem[]
  modelValue?: BlockItem[]
  label?: string
  helperText?: string
  required?: boolean
  blocks?: BlockDefinition[]
  addActionLabel?: string
  addActionAlignment?: 'start' | 'center' | 'end'
  addable?: boolean
  deletable?: boolean
  reorderable?: boolean
  reorderableWithButtons?: boolean
  reorderableWithDragAndDrop?: boolean
  collapsible?: boolean
  collapsed?: boolean
  cloneable?: boolean
  blockNumbers?: boolean
  blockIcons?: boolean
  blockPreviews?: boolean
  minItems?: number
  maxItems?: number
  blockPickerColumns?: number | Record<string, number>
  blockPickerWidth?: string
  disabled?: boolean
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: () => [],
  blocks: () => [],
  addActionLabel: 'Add Block',
  addActionAlignment: 'center',
  addable: true,
  deletable: true,
  reorderable: true,
  reorderableWithButtons: false,
  reorderableWithDragAndDrop: true,
  collapsible: false,
  collapsed: false,
  cloneable: false,
  blockNumbers: true,
  blockIcons: false,
  blockPreviews: false,
  blockPickerColumns: 1,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: BlockItem[]]
  'update:value': [value: BlockItem[]]
}>()

// Internal state for builder items
const internalItems = ref<BlockItem[]>([])

// Debug: Log blocks prop
console.log('Builder blocks:', props.blocks)
console.log('Builder blocks length:', props.blocks?.length)

// Watch for prop changes and update internal state
watch(() => props.value ?? props.modelValue, (newValue) => {
  internalItems.value = Array.isArray(newValue) ? [...newValue] : []
}, { immediate: true })

const collapsedItems = ref<Set<string>>(new Set())
const showBlockPicker = ref(false)
const sortableContainer = ref<HTMLElement | null>(null)

// Initialize collapsed state
watch(
  () => internalItems.value,
  (newValue) => {
    if (props.collapsed && newValue) {
      newValue.forEach((item) => {
        collapsedItems.value.add(item.id)
      })
    }
  },
  { immediate: true }
)

// Helper to get Lucide icon component by name
const getIconComponent = (iconName?: string) => {
  if (!iconName) return null
  const pascalCase = iconName
    .split(/[-_\s]/)
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join('')
  return (LucideIcons as any)[pascalCase] || null
}

// Helper to get Tailwind color classes for icons
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

// Generate unique ID
const generateId = () => {
  return `block_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
}

// Get block definition
const getBlockDefinition = (blockType: string): BlockDefinition | undefined => {
  return props.blocks.find((b) => b.name === blockType)
}

// Get block label
const getBlockLabel = (item: BlockItem, index: number): string => {
  const block = getBlockDefinition(item.type)
  if (!block) return item.type

  // If label is dynamic based on state
  if (typeof block.label === 'function') {
    return block.label(item.data) || block.label(null)
  }

  return block.label
}

// Check if block can be added
const canAddBlock = (blockType: string): boolean => {
  const block = getBlockDefinition(blockType)
  if (!block || !block.maxItems) return true

  const count = internalItems.value.filter((item) => item.type === blockType).length
  return count < block.maxItems
}

// Check if can add any block
const canAddAnyBlock = computed(() => {
  if (!props.addable) return false
  if (props.maxItems && internalItems.value.length >= props.maxItems) return false
  if (!props.blocks || props.blocks.length === 0) return false
  return props.blocks.some((block) => canAddBlock(block.name))
})

// Add block
const addBlock = (blockType: string) => {
  if (!canAddBlock(blockType)) return

  const block = getBlockDefinition(blockType)
  if (!block) return

  // Initialize data with default values from schema
  const data: Record<string, any> = {}
  block.schema.forEach((field) => {
    data[field.name] = field.defaultValue ?? null
  })

  const newItem: BlockItem = {
    id: generateId(),
    type: blockType,
    data,
  }

  const newValue = [...internalItems.value, newItem]
  internalItems.value = newValue
  emit('update:modelValue', newValue)
  emit('update:value', newValue)
  showBlockPicker.value = false

  // Auto-expand if not collapsed by default
  if (!props.collapsed && props.collapsible) {
    collapsedItems.value.delete(newItem.id)
  } else if (props.collapsed && props.collapsible) {
    collapsedItems.value.add(newItem.id)
  }
}

// Clone block
const cloneBlock = (index: number) => {
  const item = internalItems.value[index]
  const clonedItem: BlockItem = {
    id: generateId(),
    type: item.type,
    data: { ...item.data },
  }

  const newValue = [...internalItems.value]
  newValue.splice(index + 1, 0, clonedItem)
  internalItems.value = newValue
  emit('update:modelValue', newValue)
  emit('update:value', newValue)
}

// Delete block
const deleteBlock = (index: number) => {
  const newValue = [...internalItems.value]
  const removed = newValue.splice(index, 1)
  if (removed[0]) {
    collapsedItems.value.delete(removed[0].id)
  }
  internalItems.value = newValue
  emit('update:modelValue', newValue)
  emit('update:value', newValue)
}

// Move block up
const moveUp = (index: number) => {
  if (index === 0) return
  const newValue = [...internalItems.value]
  ;[newValue[index - 1], newValue[index]] = [newValue[index], newValue[index - 1]]
  internalItems.value = newValue
  emit('update:modelValue', newValue)
  emit('update:value', newValue)
}

// Move block down
const moveDown = (index: number) => {
  if (index === internalItems.value.length - 1) return
  const newValue = [...internalItems.value]
  ;[newValue[index], newValue[index + 1]] = [newValue[index + 1], newValue[index]]
  internalItems.value = newValue
  emit('update:modelValue', newValue)
  emit('update:value', newValue)
}

// Toggle collapse
const toggleCollapse = (id: string) => {
  if (collapsedItems.value.has(id)) {
    collapsedItems.value.delete(id)
  } else {
    collapsedItems.value.add(id)
  }
}

// Collapse all
const collapseAll = () => {
  internalItems.value.forEach((item) => {
    collapsedItems.value.add(item.id)
  })
}

// Expand all
const expandAll = () => {
  collapsedItems.value.clear()
}

// Is collapsed
const isCollapsed = (id: string): boolean => {
  return collapsedItems.value.has(id)
}

// Update field data
const updateFieldData = (itemId: string, fieldName: string, value: any) => {
  const newValue = internalItems.value.map((item) => {
    if (item.id === itemId) {
      return {
        ...item,
        data: {
          ...item.data,
          [fieldName]: value,
        },
      }
    }
    return item
  })
  internalItems.value = newValue
  emit('update:modelValue', newValue)
  emit('update:value', newValue)
}

// Block picker grid columns class
const blockPickerGridClass = computed(() => {
  const cols = props.blockPickerColumns
  if (typeof cols === 'number') {
    return `grid-cols-${cols}`
  }
  // Handle responsive columns
  const classes: string[] = []
  Object.entries(cols).forEach(([breakpoint, count]) => {
    if (breakpoint === 'default') {
      classes.push(`grid-cols-${count}`)
    } else {
      classes.push(`${breakpoint}:grid-cols-${count}`)
    }
  })
  return classes.join(' ')
})

// Block picker max width class
const blockPickerMaxWidth = computed(() => {
  const widths: Record<string, string> = {
    xs: 'max-w-xs',
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    '3xl': 'max-w-3xl',
    '4xl': 'max-w-4xl',
    '5xl': 'max-w-5xl',
    '6xl': 'max-w-6xl',
    '7xl': 'max-w-7xl',
  }
  return props.blockPickerWidth ? widths[props.blockPickerWidth] : 'max-w-md'
})

// Add action alignment class
const addActionAlignmentClass = computed(() => {
  const alignments: Record<string, string> = {
    start: 'justify-start',
    center: 'justify-center',
    end: 'justify-end',
  }
  return alignments[props.addActionAlignment] || 'justify-center'
})

// Initialize drag and drop
onMounted(() => {
  if (props.reorderableWithDragAndDrop && sortableContainer.value) {
    Sortable.create(sortableContainer.value, {
      animation: 150,
      handle: '.drag-handle',
      ghostClass: 'opacity-50',
      onEnd: (event) => {
        const { oldIndex, newIndex } = event
        if (oldIndex !== undefined && newIndex !== undefined && oldIndex !== newIndex) {
          const newValue = [...internalItems.value]
          const [moved] = newValue.splice(oldIndex, 1)
          newValue.splice(newIndex, 0, moved)
          internalItems.value = newValue
          emit('update:modelValue', newValue)
          emit('update:value', newValue)
        }
      },
    })
  }
})

// Import form components
import TextInput from './TextInput.vue'
import Textarea from './Textarea.vue'
import Select from './Select.vue'
import SelectSimple from './SelectSimple.vue'
import Toggle from './Toggle.vue'
import DatePicker from './DatePicker.vue'
import FileUpload from './FileUpload.vue'
import RichEditor from './RichEditor.vue'
import MarkdownEditor from './MarkdownEditor.vue'
import ColorPicker from './ColorPicker.vue'
import Slider from './Slider.vue'
import TagsInput from './TagsInput.vue'
import KeyValue from './KeyValue.vue'
import CodeEditor from './CodeEditor.vue'
import Repeater from './Repeater.vue'
import ToggleButtons from './ToggleButtons.vue'
import Radio from './Radio.vue'
import Checkbox from './Checkbox.vue'
import CheckboxList from './CheckboxList.vue'

// Component mapping
const componentMap: Record<string, any> = {
  TextInput,
  Textarea,
  Select,
  SelectSimple,
  Toggle,
  DatePicker,
  FileUpload,
  RichEditor,
  MarkdownEditor,
  ColorPicker,
  Slider,
  TagsInput,
  KeyValue,
  CodeEditor,
  Repeater,
  ToggleButtons,
  Radio,
  Checkbox,
  CheckboxList,
}

// Helper to get Vue component from schema (handles both PascalCase and snake_case)
const getComponent = (componentName: string): any | null => {
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
      :value="JSON.stringify(internalItems)"
    />

    <!-- Header with icons and collapse all/expand all -->
    <div
      v-if="getIconComponent(prefixIcon) || getIconComponent(suffixIcon) || (collapsible && internalItems.length > 0)"
      class="flex items-center justify-between"
    >
      <component
        v-if="getIconComponent(prefixIcon)"
        :is="getIconComponent(prefixIcon)"
        class="h-4 w-4"
        :class="getIconColorClass(prefixIconColor)"
      />

      <div v-if="collapsible && internalItems.length > 0" class="flex items-center gap-1">
        <Button type="button" variant="ghost" size="sm" @click="collapseAll">
          <ChevronsDown class="h-4 w-4 mr-1" />
          Collapse All
        </Button>
        <Button type="button" variant="ghost" size="sm" @click="expandAll">
          <ChevronsUp class="h-4 w-4 mr-1" />
          Expand All
        </Button>
      </div>

      <component
        v-if="getIconComponent(suffixIcon)"
        :is="getIconComponent(suffixIcon)"
        class="h-4 w-4"
        :class="getIconColorClass(suffixIconColor)"
      />
    </div>

    <!-- Blocks list -->
    <div ref="sortableContainer" class="space-y-2">
      <Collapsible
        v-for="(item, index) in internalItems"
        :key="item.id"
        :open="!isCollapsed(item.id)"
        as-child
      >
        <Card>
          <CardHeader class="p-3">
            <div class="flex items-center gap-2">
              <!-- Drag handle -->
              <button
                v-if="reorderable && reorderableWithDragAndDrop"
                type="button"
                class="drag-handle cursor-grab hover:text-primary shrink-0"
                :disabled="disabled"
              >
                <GripVertical class="h-4 w-4" />
              </button>

              <!-- Block number -->
              <span v-if="blockNumbers" class="text-xs text-muted-foreground shrink-0">
                {{ index + 1 }}.
              </span>

              <!-- Block icon -->
              <component
                v-if="blockIcons && getIconComponent(getBlockDefinition(item.type)?.icon)"
                :is="getIconComponent(getBlockDefinition(item.type)?.icon)"
                class="h-4 w-4 shrink-0 text-muted-foreground"
              />

              <!-- Block label / Collapse trigger -->
              <CollapsibleTrigger v-if="collapsible" as-child>
                <button
                  type="button"
                  class="flex-1 flex items-center gap-2 text-sm font-medium text-left hover:text-primary transition-colors"
                  @click="toggleCollapse(item.id)"
                >
                  <ChevronDown
                    class="h-4 w-4 transition-transform shrink-0"
                    :class="{ '-rotate-90': isCollapsed(item.id) }"
                  />
                  {{ getBlockLabel(item, index) }}
                </button>
              </CollapsibleTrigger>

              <span v-else class="flex-1 text-sm font-medium">
                {{ getBlockLabel(item, index) }}
              </span>

              <!-- Action buttons -->
              <div class="flex items-center gap-1 shrink-0">
                <!-- Move up button -->
                <Button
                  v-if="reorderableWithButtons && index > 0"
                  type="button"
                  variant="ghost"
                  size="sm"
                  class="h-8 w-8 p-0"
                  :disabled="disabled"
                  @click="moveUp(index)"
                >
                  <ChevronUp class="h-4 w-4" />
                </Button>

                <!-- Move down button -->
                <Button
                  v-if="reorderableWithButtons && index < internalItems.length - 1"
                  type="button"
                  variant="ghost"
                  size="sm"
                  class="h-8 w-8 p-0"
                  :disabled="disabled"
                  @click="moveDown(index)"
                >
                  <ChevronDown class="h-4 w-4" />
                </Button>

                <!-- Clone button -->
                <Button
                  v-if="cloneable"
                  type="button"
                  variant="ghost"
                  size="sm"
                  class="h-8 w-8 p-0"
                  :disabled="disabled"
                  @click="cloneBlock(index)"
                >
                  <Copy class="h-4 w-4" />
                </Button>

                <!-- Delete button -->
                <Button
                  v-if="deletable"
                  type="button"
                  variant="ghost"
                  size="sm"
                  class="h-8 w-8 p-0 hover:text-destructive"
                  :disabled="disabled || (minItems && internalItems.length <= minItems)"
                  @click="deleteBlock(index)"
                >
                  <X class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </CardHeader>

          <CollapsibleContent as-child>
            <CardContent class="p-3 pt-0">
              <!-- Render block fields dynamically -->
              <div
                class="grid gap-4"
                :class="{
                  'grid-cols-1': !getBlockDefinition(item.type)?.columns,
                  'grid-cols-2': getBlockDefinition(item.type)?.columns === 2,
                  'grid-cols-3': getBlockDefinition(item.type)?.columns === 3,
                }"
              >
                <component
                  v-for="field in getBlockDefinition(item.type)?.schema || []"
                  :key="field.name"
                  :is="getComponent(field.component)"
                  :model-value="item.data[field.name]"
                  v-bind="field"
                  @update:model-value="updateFieldData(item.id, field.name, $event)"
                />
              </div>
            </CardContent>
          </CollapsibleContent>
        </Card>
      </Collapsible>
    </div>

    <!-- Empty state -->
    <div
      v-if="internalItems.length === 0"
      class="border-2 border-dashed border-border rounded-lg p-8 text-center"
    >
      <p class="text-sm text-muted-foreground">No blocks added yet</p>
    </div>

    <!-- Add block button -->
    <div v-if="canAddAnyBlock" class="flex" :class="addActionAlignmentClass">
      <Popover v-model:open="showBlockPicker">
        <PopoverTrigger as-child>
          <Button
            type="button"
            variant="outline"
            :disabled="disabled"
          >
            <Plus class="h-4 w-4 mr-2" />
            {{ addActionLabel }}
          </Button>
        </PopoverTrigger>
        <PopoverContent :class="blockPickerMaxWidth" align="start">
          <div class="space-y-2">
            <h4 class="font-medium text-sm">Select a block type</h4>
            <div class="grid gap-2" :class="blockPickerGridClass">
              <Button
                v-for="block in blocks"
                :key="block.name"
                type="button"
                variant="outline"
                class="justify-start h-auto py-3"
                :disabled="!canAddBlock(block.name)"
                @click="addBlock(block.name)"
              >
                <div class="flex flex-col items-start gap-1 w-full">
                  <div class="flex items-center gap-2">
                    <component
                      v-if="getIconComponent(block.icon)"
                      :is="getIconComponent(block.icon)"
                      class="h-4 w-4"
                    />
                    <span class="font-medium text-sm">{{ block.label }}</span>
                  </div>
                  <span
                    v-if="block.maxItems"
                    class="text-xs text-muted-foreground"
                  >
                    {{ internalItems.filter(i => i.type === block.name).length }} /
                    {{ block.maxItems }} used
                  </span>
                </div>
              </Button>
            </div>
          </div>
        </PopoverContent>
      </Popover>
    </div>

    <!-- Validation messages -->
    <div v-if="minItems && internalItems.length < minItems" class="text-sm text-destructive">
      At least {{ minItems }} block{{ minItems === 1 ? '' : 's' }} required
    </div>
    <div v-if="maxItems && internalItems.length >= maxItems" class="text-sm text-muted-foreground">
      Maximum of {{ maxItems }} blocks reached
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
