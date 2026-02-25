<script setup lang="ts">
import { computed, ref, reactive, watch } from 'vue'
import { Input } from '@/components/ui/input'
import * as LucideIcons from 'lucide-vue-next'
import { useLocalization } from '@laravilt/support/composables'

// Initialize localization
const { trans } = useLocalization()

interface Option {
  value: string | number
  label: string
  disabled?: boolean
  group?: string
  groupLabel?: string
  action?: string
  permissionName?: string
}

interface Props {
  modelValue?: (string | number)[]
  options: Option[]
  disabled?: boolean
  inline?: boolean
  columns?: number
  gridDirection?: 'row' | 'column'
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
  searchable?: boolean
  bulkToggleable?: boolean
  groupBy?: string
  groupByResource?: boolean
  groupLabels?: Record<string, string>
  groupSelectAll?: boolean
  defaultGroup?: string
  collapsible?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: () => [],
  disabled: false,
  inline: false,
  columns: 1,
  gridDirection: 'column',
  searchable: false,
  bulkToggleable: false,
  groupByResource: false,
  groupSelectAll: true,
  collapsible: true,
})

const emit = defineEmits<{
  'update:modelValue': [value: (string | number)[]]
}>()

// Use a local ref that syncs with props for proper reactivity
const localValue = ref<(string | number)[]>([...props.modelValue || []])

// Watch for external changes - immediate: true ensures we catch initial value from async component loading
watch(() => props.modelValue, (newVal) => {
  if (JSON.stringify(newVal) !== JSON.stringify(localValue.value)) {
    localValue.value = [...(newVal || [])]
  }
}, { deep: true, immediate: true })

// Helper to update value and emit
const updateValue = (newValue: (string | number)[]) => {
  localValue.value = [...newValue]
  emit('update:modelValue', newValue)
}

const searchQuery = ref('')

// Track collapsed state for each group
const collapsedGroups = reactive<Record<string, boolean>>({})

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

// Filter options by search query
const filteredOptions = computed(() => {
  if (!searchQuery.value) return props.options
  const query = searchQuery.value.toLowerCase()
  return props.options.filter(option =>
    option.label.toLowerCase().includes(query) ||
    option.permissionName?.toLowerCase().includes(query) ||
    option.groupLabel?.toLowerCase().includes(query)
  )
})

// Group options by the group field
const groupedOptions = computed(() => {
  if (!props.groupBy && !props.groupByResource) {
    return { _default: filteredOptions.value }
  }

  const groups: Record<string, Option[]> = {}
  for (const option of filteredOptions.value) {
    const groupKey = option.group || '_default'
    if (!groups[groupKey]) {
      groups[groupKey] = []
    }
    groups[groupKey].push(option)
  }

  // Sort groups alphabetically, but put _default/_other at the end
  const sortedGroups: Record<string, Option[]> = {}
  const keys = Object.keys(groups).sort((a, b) => {
    if (a === '_default' || a === '_other') return 1
    if (b === '_default' || b === '_other') return -1
    return a.localeCompare(b)
  })
  for (const key of keys) {
    sortedGroups[key] = groups[key]
  }

  return sortedGroups
})

// Get display label for a group
const getGroupLabel = (groupKey: string) => {
  if (groupKey === '_default') return 'Other'
  if (groupKey === '_other') return 'Other'

  // Check if any option in this group has a groupLabel
  const groupOptions = groupedOptions.value[groupKey] || []
  if (groupOptions.length > 0 && groupOptions[0].groupLabel) {
    return groupOptions[0].groupLabel
  }

  // Check custom labels
  if (props.groupLabels?.[groupKey]) {
    return props.groupLabels[groupKey]
  }

  // Fallback: format the group key
  return groupKey.split('_').map(word =>
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ')
}

// Check if a group is collapsed
const isGroupCollapsed = (groupKey: string) => {
  return collapsedGroups[groupKey] ?? false
}

// Toggle group collapsed state
const toggleGroupCollapse = (groupKey: string) => {
  collapsedGroups[groupKey] = !collapsedGroups[groupKey]
}

// Computed Set of checked values for reactive tracking
const checkedSet = computed(() => {
  return new Set((localValue.value || []).map(v => String(v)))
})

const isChecked = (value: string | number) => {
  // Use the computed Set for proper reactivity
  return checkedSet.value.has(String(value))
}

const handleChange = (optionValue: string | number, checked: boolean | 'indeterminate') => {
  const currentValues = [...(localValue.value || [])]
  let newValues: (string | number)[]

  if (checked === true) {
    // Check if value already exists using string comparison
    const alreadyExists = currentValues.some(v => String(v) === String(optionValue))
    if (!alreadyExists) {
      newValues = [...currentValues, optionValue]
    } else {
      newValues = currentValues
    }
  } else {
    // Remove using string comparison
    newValues = currentValues.filter(v => String(v) !== String(optionValue))
  }

  updateValue(newValues)
}

// Check if all options in a group are selected
const isGroupAllSelected = (groupKey: string) => {
  const groupOptions = groupedOptions.value[groupKey] || []
  if (groupOptions.length === 0) return false
  return groupOptions.every(option => isChecked(option.value))
}

// Check if some (but not all) options in a group are selected
const isGroupPartiallySelected = (groupKey: string) => {
  const groupOptions = groupedOptions.value[groupKey] || []
  if (groupOptions.length === 0) return false
  const selectedCount = groupOptions.filter(option => isChecked(option.value)).length
  return selectedCount > 0 && selectedCount < groupOptions.length
}

// Get selected count for a group
const getGroupSelectedCount = (groupKey: string) => {
  const groupOptions = groupedOptions.value[groupKey] || []
  return groupOptions.filter(option => isChecked(option.value)).length
}

// Toggle all options in a group
const toggleGroupAll = (groupKey: string) => {
  const groupOptions = groupedOptions.value[groupKey] || []
  const groupValues = groupOptions.map(o => o.value)
  const currentValues = [...(localValue.value || [])]

  const allSelected = isGroupAllSelected(groupKey)

  let newValues: (string | number)[]
  if (allSelected) {
    // Deselect all in this group
    newValues = currentValues.filter(v => !groupValues.includes(v))
  } else {
    // Select all in this group
    const toAdd = groupValues.filter(v => !currentValues.includes(v))
    newValues = [...currentValues, ...toAdd]
  }

  updateValue(newValues)
}

// Handle group toggle from checkbox event
const handleGroupToggle = (groupKey: string, checked: boolean | 'indeterminate') => {
  const groupOptions = groupedOptions.value[groupKey] || []
  const groupValues = groupOptions.map(o => o.value)
  const groupValuesStr = groupValues.map(v => String(v))
  const currentValues = [...(localValue.value || [])]

  let newValues: (string | number)[]
  if (checked === true) {
    // Select all in this group - add values that aren't already selected
    const toAdd = groupValues.filter(v => !currentValues.some(cv => String(cv) === String(v)))
    newValues = [...currentValues, ...toAdd]
  } else {
    // Deselect all in this group - remove values that are in this group
    newValues = currentValues.filter(v => !groupValuesStr.includes(String(v)))
  }

  updateValue(newValues)
}

// Select all globally using click handler
const selectAllGlobal = () => {
  if (props.disabled) return

  const allValues = filteredOptions.value.map(o => o.value)

  if (isAllSelected.value) {
    updateValue([])
  } else {
    updateValue(allValues)
  }
}

// Select all in a group using click handler
const selectAllInGroup = (groupKey: string) => {
  if (props.disabled) return

  const groupOptions = groupedOptions.value[groupKey] || []
  const groupValues = groupOptions.map(o => o.value)
  const groupValuesStr = groupValues.map(v => String(v))
  const currentValues = [...(localValue.value || [])]

  const allSelected = isGroupAllSelected(groupKey)

  let newValues: (string | number)[]
  if (allSelected) {
    // Deselect all in this group
    newValues = currentValues.filter(v => !groupValuesStr.includes(String(v)))
  } else {
    // Select all in this group
    const toAdd = groupValues.filter(v => !currentValues.some(cv => String(cv) === String(v)))
    newValues = [...currentValues, ...toAdd]
  }

  updateValue(newValues)
}

// Legacy handlers for backwards compatibility
const handleToggleAll = selectAllGlobal
const toggleAll = selectAllGlobal

// Check if all options are selected (use localValue for immediate reactivity)
const isAllSelected = computed(() => {
  if (filteredOptions.value.length === 0) return false
  return filteredOptions.value.every(option =>
    localValue.value?.some(v => String(v) === String(option.value)) ?? false
  )
})

// Check if some options are selected (use localValue for immediate reactivity)
const isPartiallySelected = computed(() => {
  if (filteredOptions.value.length === 0) return false
  const selectedCount = filteredOptions.value.filter(option =>
    localValue.value?.some(v => String(v) === String(option.value)) ?? false
  ).length
  return selectedCount > 0 && selectedCount < filteredOptions.value.length
})

const gridClass = computed(() => {
  if (props.inline) {
    return 'flex flex-wrap gap-4'
  }
  if (props.columns > 1) {
    if (props.gridDirection === 'row') {
      return 'grid gap-2 grid-flow-row'
    } else {
      return 'grid gap-2 grid-flow-col'
    }
  }
  return 'flex flex-col gap-2'
})

const gridStyle = computed(() => {
  if (props.inline || props.columns <= 1) {
    return {}
  }

  if (props.gridDirection === 'row') {
    return {
      gridTemplateColumns: `repeat(${props.columns}, minmax(0, 1fr))`
    }
  } else {
    const optionCount = (props.groupBy || props.groupByResource) ?
      Math.max(...Object.values(groupedOptions.value).map(g => g.length)) :
      props.options.length
    const rows = Math.ceil(optionCount / props.columns)
    return {
      gridTemplateRows: `repeat(${rows}, minmax(0, auto))`,
      gridTemplateColumns: `repeat(${props.columns}, minmax(0, 1fr))`
    }
  }
})

// Check if we should show grouped layout
const hasGroups = computed(() => {
  return (props.groupBy || props.groupByResource) && Object.keys(groupedOptions.value).length > 0
})
</script>

<template>
  <div class="space-y-3">
    <!-- Search input -->
    <div v-if="searchable" class="relative">
      <component
        :is="getIconComponent('search')"
        class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground"
      />
      <Input
        v-model="searchQuery"
        type="text"
        :placeholder="trans('forms::forms.checkbox_list.search_placeholder')"
        class="pl-9"
      />
    </div>

    <!-- Global Select All -->
    <div v-if="bulkToggleable" class="flex items-center justify-between pb-3 border-b mb-3">
      <button
        type="button"
        class="flex items-center gap-2 cursor-pointer select-none hover:bg-muted/50 rounded px-2 py-1 -mx-2 transition-colors"
        :disabled="disabled"
        @click="selectAllGlobal"
      >
        <div class="flex items-center justify-center size-4 rounded-[4px] border shadow-xs shrink-0" :class="isAllSelected ? 'bg-primary border-primary text-primary-foreground' : (isPartiallySelected ? 'bg-primary border-primary text-primary-foreground' : 'border-input')">
          <component v-if="isAllSelected" :is="getIconComponent('check')" class="size-3" />
          <component v-else-if="isPartiallySelected" :is="getIconComponent('minus')" class="size-3" />
        </div>
        <span class="text-sm font-semibold">
          {{ trans('forms::forms.checkbox_list.select_all_permissions') }}
        </span>
      </button>
      <span class="text-xs text-muted-foreground">
        {{ localValue?.length || 0 }}/{{ filteredOptions.length }} {{ trans('forms::forms.checkbox_list.selected') }}
      </span>
    </div>

    <!-- Grouped Layout -->
    <template v-if="hasGroups">
      <div
        v-for="(groupOptions, groupKey) in groupedOptions"
        :key="groupKey"
        class="rounded-lg border bg-card text-card-foreground shadow-sm"
      >
        <!-- Group Header -->
        <div
          class="flex items-center justify-between bg-muted/50 px-4 py-2.5"
          :class="{ 'cursor-pointer': collapsible }"
          @click="collapsible ? toggleGroupCollapse(groupKey as string) : null"
        >
          <div class="flex items-center gap-2">
            <!-- Collapse Toggle -->
            <button
              v-if="collapsible"
              type="button"
              class="p-0.5 hover:bg-muted rounded transition-colors"
              @click.stop="toggleGroupCollapse(groupKey as string)"
            >
              <component
                :is="getIconComponent(isGroupCollapsed(groupKey as string) ? 'chevron-right' : 'chevron-down')"
                class="h-4 w-4 text-muted-foreground transition-transform"
              />
            </button>

            <!-- Group Icon & Label -->
            <component
              :is="getIconComponent('shield')"
              class="h-4 w-4 text-primary"
            />
            <span class="text-sm font-semibold">
              {{ getGroupLabel(groupKey as string) }}
            </span>
            <span class="text-xs text-muted-foreground">
              ({{ getGroupSelectedCount(groupKey as string) }}/{{ groupOptions.length }})
            </span>
          </div>

          <!-- Select All Toggle -->
          <button
            v-if="groupSelectAll"
            type="button"
            class="flex items-center gap-2 cursor-pointer select-none hover:bg-muted rounded px-2 py-1 transition-colors"
            :disabled="disabled"
            @click.stop="selectAllInGroup(groupKey as string)"
          >
            <div class="flex items-center justify-center size-4 rounded-[4px] border shadow-xs shrink-0" :class="isGroupAllSelected(groupKey as string) ? 'bg-primary border-primary text-primary-foreground' : (isGroupPartiallySelected(groupKey as string) ? 'bg-primary border-primary text-primary-foreground' : 'border-input')">
              <component v-if="isGroupAllSelected(groupKey as string)" :is="getIconComponent('check')" class="size-3" />
              <component v-else-if="isGroupPartiallySelected(groupKey as string)" :is="getIconComponent('minus')" class="size-3" />
            </div>
            <span class="text-xs font-medium text-muted-foreground">
              {{ trans('forms::forms.checkbox_list.select_all') }}
            </span>
          </button>
        </div>

        <!-- Group Content (Collapsible) -->
        <div
          v-show="!isGroupCollapsed(groupKey as string)"
          class="p-3"
        >
          <div :class="gridClass" :style="gridStyle">
            <label
              v-for="option in groupOptions"
              :key="option.value"
              :for="`checkbox-${option.value}`"
              class="flex items-center gap-2 cursor-pointer hover:bg-muted/50 rounded px-2 py-1.5 -mx-2 transition-colors"
              :class="{ 'opacity-50 cursor-not-allowed': disabled || option.disabled }"
            >
              <!-- Checkbox (custom for proper reactivity) -->
              <button
                type="button"
                :id="`checkbox-${option.value}`"
                role="checkbox"
                :aria-checked="checkedSet.has(String(option.value))"
                :disabled="disabled || option.disabled"
                class="peer size-4 shrink-0 rounded-[4px] border shadow-xs transition-shadow outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 flex items-center justify-center"
                :class="checkedSet.has(String(option.value)) ? 'bg-primary border-primary text-primary-foreground' : 'border-input'"
                @click="handleChange(option.value, !checkedSet.has(String(option.value)))"
              >
                <component
                  v-if="checkedSet.has(String(option.value))"
                  :is="getIconComponent('check')"
                  class="size-3"
                />
              </button>

              <!-- Label -->
              <span class="text-sm font-medium leading-none select-none">
                {{ option.label }}
              </span>
            </label>
          </div>
        </div>
      </div>
    </template>

    <!-- Non-grouped Layout -->
    <div v-else :class="gridClass" :style="gridStyle">
      <label
        v-for="option in filteredOptions"
        :key="option.value"
        :for="`checkbox-${option.value}`"
        class="flex items-center gap-2 cursor-pointer hover:bg-muted/50 rounded px-2 py-1.5 transition-colors"
        :class="{ 'opacity-50 cursor-not-allowed': disabled || option.disabled }"
      >
        <!-- Prefix icon -->
        <component
          v-if="getIconComponent(prefixIcon)"
          :is="getIconComponent(prefixIcon)"
          class="h-4 w-4"
          :class="getIconColorClass(prefixIconColor)"
        />

        <!-- Checkbox (custom for proper reactivity) -->
        <button
          type="button"
          :id="`checkbox-${option.value}`"
          role="checkbox"
          :aria-checked="checkedSet.has(String(option.value))"
          :disabled="disabled || option.disabled"
          class="peer size-4 shrink-0 rounded-[4px] border shadow-xs transition-shadow outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 flex items-center justify-center"
          :class="checkedSet.has(String(option.value)) ? 'bg-primary border-primary text-primary-foreground' : 'border-input'"
          @click="handleChange(option.value, !checkedSet.has(String(option.value)))"
        >
          <component
            v-if="checkedSet.has(String(option.value))"
            :is="getIconComponent('check')"
            class="size-3"
          />
        </button>

        <!-- Label -->
        <span class="text-sm font-medium leading-none select-none">
          {{ option.label }}
        </span>

        <!-- Suffix icon -->
        <component
          v-if="getIconComponent(suffixIcon)"
          :is="getIconComponent(suffixIcon)"
          class="h-4 w-4"
          :class="getIconColorClass(suffixIconColor)"
        />
      </label>
    </div>

    <!-- Empty state -->
    <div v-if="filteredOptions.length === 0" class="text-center py-8 text-muted-foreground">
      <component
        :is="getIconComponent('search-x')"
        class="h-10 w-10 mx-auto mb-3 opacity-40"
      />
      <p class="text-sm">No permissions found</p>
    </div>
  </div>
</template>
