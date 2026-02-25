<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, inject, Component as VueComponent } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  ComboboxAnchor,
  ComboboxContent,
  ComboboxEmpty,
  ComboboxInput,
  ComboboxItem,
  ComboboxPortal,
  ComboboxRoot,
  ComboboxTrigger,
} from 'reka-ui'
import { Check, ChevronDown, LoaderCircle, Plus, Edit } from 'lucide-vue-next'
import * as LucideIcons from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import TextInput from './TextInput.vue'
import Textarea from './Textarea.vue'
import Toggle from './Toggle.vue'
import { useReactiveField } from '../../composables/useReactiveField'
import { useLocalization } from '@laravilt/support/composables'

// Initialize localization
const { trans } = useLocalization()

interface Option {
  value: string
  label: string
  disabled?: boolean
}

interface OptionGroup {
  label: string
  options: Option[]
}

interface Props {
  name?: string
  value?: string | string[] | number | number[] | null
  modelValue?: string | string[] | number | number[] | null
  label?: string
  resourceSlug?: string
  fieldName?: string
  placeholder?: string
  multiple?: boolean
  disabled?: boolean
  required?: boolean
  helperText?: string
  loadingMessage?: string
  noSearchResultsMessage?: string
  searchPrompt?: string
  searchingMessage?: string
  searchDebounce?: number
  searchableColumns?: string[]
  allowHtml?: boolean
  wrapOptionLabels?: boolean
  selectablePlaceholder?: boolean
  prefix?: string
  suffix?: string
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
  optionsLimit?: number
  minItems?: number
  maxItems?: number
  hasCreateOptionForm?: boolean
  hasEditOptionForm?: boolean
  optionsAreGrouped?: boolean
  options?: Option[] // Static options from backend
  createOptionForm?: any[] // Form schema for creating options
  editOptionForm?: any[] // Form schema for editing options
  dependsOn?: string[] // Fields this select depends on for reactive loading
  optionsUrl?: string // API endpoint for loading dynamic options
  hasDynamicOptions?: boolean // Whether options are closure-based (evaluated server-side)
  relationshipSearchUrl?: string // API endpoint for searching relationship options
  relationshipOptionsUrl?: string // API endpoint for loading specific relationship options by ID
  relationshipModel?: string // The parent model class for relationship selects
  hasMoreOptions?: boolean // Whether there are more options available (closure-based options)
  closureOptionsUrl?: string // API endpoint for loading more closure-based options
  relationManager?: string // Relation manager class name (for Select inside relation managers)
  live?: boolean | string | number // Reactive mode
  isLive?: boolean // Whether field triggers reactive updates
  isLazy?: boolean // Whether field triggers debounced reactive updates
  liveDebounce?: number // Debounce delay for reactive updates
  meta?: Record<string, any> // Additional metadata (e.g., dependentOptions)
  // Text labels for i18n support
  createModalTitle?: string
  createModalDescription?: string
  editModalTitle?: string
  editModalDescription?: string
  cancelButtonLabel?: string
  createButtonLabel?: string
  creatingButtonLabel?: string
  saveChangesButtonLabel?: string
  savingButtonLabel?: string
  scrollForMoreLabel?: string
  searchPlaceholder?: string
  tryAdjustingSearchLabel?: string
  minItemsValidationMessage?: string
  maxItemsValidationMessage?: string
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Select an option',
  multiple: false,
  disabled: false,
  required: false,
  loadingMessage: 'Loading options...',
  noSearchResultsMessage: 'No results found',
  searchPrompt: 'Start typing to search...',
  searchingMessage: 'Searching...',
  searchDebounce: 1000,
  searchableColumns: () => [],
  allowHtml: false,
  wrapOptionLabels: true,
  selectablePlaceholder: true,
  optionsLimit: 50,
  hasCreateOptionForm: false,
  hasEditOptionForm: false,
  optionsAreGrouped: false,
  options: () => [], // Default empty array for static options
  createOptionForm: () => [], // Default empty array for create form
  editOptionForm: () => [], // Default empty array for edit form
  dependsOn: () => [], // Default empty array for dependent fields
  live: false,
  isLive: false,
  isLazy: false,
  liveDebounce: 500,
  meta: () => ({}),
  createModalTitle: 'Create New Option',
  createModalDescription: 'Create a new option for this field. It will be automatically selected after creation.',
  editModalTitle: 'Edit Option',
  editModalDescription: 'Update the option details. Changes will be reflected immediately.',
  cancelButtonLabel: 'Cancel',
  createButtonLabel: 'Create',
  creatingButtonLabel: 'Creating...',
  saveChangesButtonLabel: 'Save Changes',
  savingButtonLabel: 'Saving...',
  scrollForMoreLabel: 'Scroll for more',
  searchPlaceholder: 'Search...',
  tryAdjustingSearchLabel: 'Try adjusting your search',
})

const emit = defineEmits<{
  'update:modelValue': [value: string | string[] | null]
  'update:value': [value: string | string[] | null]
}>()

// Modal state for create/edit
const showCreateModal = ref(false)
const showEditModal = ref(false)
const editingOptionId = ref<string | null>(null)

// Computed translated labels
const translatedPlaceholder = computed(() => props.placeholder !== 'Select an option' ? props.placeholder : trans('forms::forms.select.placeholder'))
const translatedLoadingMessage = computed(() => props.loadingMessage !== 'Loading options...' ? props.loadingMessage : trans('forms::forms.select.loading_message'))
const translatedNoSearchResultsMessage = computed(() => props.noSearchResultsMessage !== 'No results found' ? props.noSearchResultsMessage : trans('forms::forms.select.no_search_results_message'))
const translatedSearchPrompt = computed(() => props.searchPrompt !== 'Start typing to search...' ? props.searchPrompt : trans('forms::forms.select.search_prompt'))
const translatedSearchingMessage = computed(() => props.searchingMessage !== 'Searching...' ? props.searchingMessage : trans('forms::forms.select.searching_message'))
const translatedCreateModalTitle = computed(() => props.createModalTitle !== 'Create New Option' ? props.createModalTitle : trans('forms::forms.select.create_modal_title'))
const translatedCreateModalDescription = computed(() => props.createModalDescription !== 'Create a new option for this field. It will be automatically selected after creation.' ? props.createModalDescription : trans('forms::forms.select.create_modal_description'))
const translatedEditModalTitle = computed(() => props.editModalTitle !== 'Edit Option' ? props.editModalTitle : trans('forms::forms.select.edit_modal_title'))
const translatedEditModalDescription = computed(() => props.editModalDescription !== 'Update the option details. Changes will be reflected immediately.' ? props.editModalDescription : trans('forms::forms.select.edit_modal_description'))
const translatedCancelButtonLabel = computed(() => props.cancelButtonLabel !== 'Cancel' ? props.cancelButtonLabel : trans('forms::forms.select.cancel_button_label'))
const translatedCreateButtonLabel = computed(() => props.createButtonLabel !== 'Create' ? props.createButtonLabel : trans('forms::forms.select.create_button_label'))
const translatedCreatingButtonLabel = computed(() => props.creatingButtonLabel !== 'Creating...' ? props.creatingButtonLabel : trans('forms::forms.select.creating_button_label'))
const translatedSaveChangesButtonLabel = computed(() => props.saveChangesButtonLabel !== 'Save Changes' ? props.saveChangesButtonLabel : trans('forms::forms.select.save_changes_button_label'))
const translatedSavingButtonLabel = computed(() => props.savingButtonLabel !== 'Saving...' ? props.savingButtonLabel : trans('forms::forms.select.saving_button_label'))
const translatedScrollForMoreLabel = computed(() => props.scrollForMoreLabel !== 'Scroll for more' ? props.scrollForMoreLabel : trans('forms::forms.select.scroll_for_more_label'))
const translatedSearchPlaceholder = computed(() => props.searchPlaceholder !== 'Search...' ? props.searchPlaceholder : trans('forms::forms.select.search_placeholder'))
const translatedTryAdjustingSearchLabel = computed(() => props.tryAdjustingSearchLabel !== 'Try adjusting your search' ? props.tryAdjustingSearchLabel : trans('forms::forms.select.try_adjusting_search_label'))

// Component mapping for dynamic form rendering
const componentMap: Record<string, VueComponent> = {
  'TextInput': TextInput,
  'Textarea': Textarea,
  'Toggle': Toggle,
}

// Helper to get Vue component from schema
const getComponent = (componentName: string): VueComponent | null => {
  return componentMap[componentName] || null
}

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

const open = ref(false)
const searchTerm = ref('')
const options = ref<Option[]>([])
const selectedOptionsCache = ref<Map<string, string>>(new Map()) // Cache selected option labels
const loading = ref(false)
const hasMore = ref(false)
const page = ref(1)
const initialLoadComplete = ref(false)
const scrollContainer = ref<HTMLElement | null>(null)

// Internal state for selected values
const internalSelectedValues = ref<string[]>([])

// Watch for prop changes and update internal state
watch(() => props.value ?? props.modelValue, (newValue) => {
  if (newValue === null || newValue === undefined || newValue === '') {
    internalSelectedValues.value = []
  } else if (Array.isArray(newValue)) {
    internalSelectedValues.value = newValue.map(v => String(v)).filter(v => v !== '')
  } else {
    internalSelectedValues.value = [String(newValue)]
  }
}, { immediate: true })

// Selected values computed - reads from internal state, writes to both internal state and emits
const selectedValues = computed<string[]>({
  get: () => internalSelectedValues.value,
  set: (value: string | string[]) => {
    // Normalize to array
    let values: string[]
    if (Array.isArray(value)) {
      values = value
    } else if (value !== null && value !== undefined) {
      values = [String(value)]
    } else {
      values = []
    }

    // Filter out empty strings and invalid values
    const validValues = values.filter(v => v && String(v).trim() !== '')

    // Update internal state
    internalSelectedValues.value = validValues

    // Emit to parent
    if (props.multiple) {
      emit('update:modelValue', validValues.length > 0 ? validValues : null)
      emit('update:value', validValues.length > 0 ? validValues : null)
    } else {
      emit('update:modelValue', validValues.length > 0 ? validValues[0] : null)
      emit('update:value', validValues.length > 0 ? validValues[0] : null)
    }
  }
})

// Inject getFormData from FormRenderer (for reactive fields)
const getFormData = inject<(() => Record<string, any>) | undefined>('getFormData', undefined)
const formController = inject<string | undefined>('formController', undefined)
const formMethod = inject<string | undefined>('formMethod', 'getSchema')

// Inject updateSchema from FormRenderer (for updating schema after reactive field changes)
const updateSchema = inject<((schema: any[]) => void) | undefined>('updateSchema', undefined)

// Reactive field logic: Watch for changes and POST to reactive endpoint
if ((props.isLive || props.isLazy) && props.name) {
  let reloadTimeout: ReturnType<typeof setTimeout> | null = null
  let isUpdating = ref(false) // Flag to prevent duplicate updates

  // Determine debounce delay
  const debounceMs = props.isLazy
    ? props.liveDebounce
    : (props.isLive && props.liveDebounce > 0 ? props.liveDebounce : 0)

  watch(selectedValues, async (newValue, oldValue) => {
    // Skip if already updating
    if (isUpdating.value) {
      return
    }

    // Clear existing timeout
    if (reloadTimeout) {
      clearTimeout(reloadTimeout)
    }

    // Function to trigger reactive field update
    const triggerUpdate = async () => {
      // Skip if no formController is configured
      if (!formController) {
        console.warn('[Select] No formController configured, skipping reactive field update')
        return
      }

      isUpdating.value = true
      const formData = getFormData ? getFormData() : {}

      try {
        // POST to reactive field endpoint
        const response = await fetch('/reactive-fields/update', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          },
          body: JSON.stringify({
            controller: formController,
            method: formMethod || 'getSchema',
            data: formData,
            changed_field: props.name,
          }),
        })

        if (!response.ok) {
          throw new Error('Failed to update reactive fields')
        }

        const result = await response.json()

        // Update the schema with the new data
        if (updateSchema && result.schema) {
          updateSchema(result.schema)
        }
      } catch (error) {
        console.error('Error updating reactive fields:', error)
      } finally {
        // Reset the flag after a short delay to allow schema updates to complete
        setTimeout(() => {
          isUpdating.value = false
        }, 100)
      }
    }

    // Apply debounce if needed
    if (debounceMs > 0) {
      reloadTimeout = setTimeout(triggerUpdate, debounceMs)
    } else {
      await triggerUpdate()
    }
  })
}

// Watch for options changes and validate current selection
// This ensures dependent fields reset when their parent field changes
watch(() => props.options, (newOptions, oldOptions) => {
  // Update the internal options ref with new options from props
  if (hasStaticOptions.value) {
    options.value = newOptions || []

    // Update cache with new option labels
    selectedOptionsCache.value.clear()
    options.value.forEach((option) => {
      selectedOptionsCache.value.set(option.value, option.label)
    })
  }

  if (!newOptions || newOptions.length === 0) {
    // No options available, clear selection
    if (selectedValues.value.length > 0) {
      selectedValues.value = []
    }
    return
  }

  // Check if current selected values are still valid in new options
  const validValues = selectedValues.value.filter(val =>
    newOptions.some(opt => opt.value === val)
  )

  // If any values became invalid, update selection to only keep valid ones
  if (validValues.length !== selectedValues.value.length) {
    selectedValues.value = validValues
  }
}, { deep: true })

// Get label for a value with HTML support
const getLabelForValue = (val: string): string => {
  // First try to find in current options
  const found = options.value.find(opt => opt.value === val)
  if (found) {
    return found.label
  }
  // Fallback to cache
  const cached = selectedOptionsCache.value.get(val)
  if (cached) {
    return cached
  }
  return `ID: ${val}`
}

// Display value for trigger (actual selected labels, empty if none selected)
const displayValue = computed(() => {
  if (selectedValues.value.length === 0) {
    return ''
  }

  const labels = selectedValues.value
    .map(val => getLabelForValue(val))
    .filter(Boolean)

  if (labels.length === 0) {
    return ''
  }

  if (props.multiple) {
    return labels.join(', ')
  }

  return labels[0]
})

// Display text for the input (shows label or placeholder)
const displayText = computed(() => {
  return displayValue.value || translatedPlaceholder.value
})

// Current search query (separate from ComboboxRoot's internal state)
const currentSearch = ref('')

// Fetch options from API
const fetchOptions = async (reset: boolean = false, searchQuery?: string) => {
  if (loading.value) return

  // Use provided search query, otherwise use current search
  const search = searchQuery !== undefined ? searchQuery : currentSearch.value

  if (reset) {
    page.value = 1
    options.value = []
  }

  loading.value = true

  try {
    let url: string
    let responseData: any

    // Check if we have a relationship search URL (for relationship selects)
    if (props.relationshipSearchUrl) {
      // Get current panel path from window location
      const panelPath = window.location.pathname.split('/')[1] || 'admin'
      const params = new URLSearchParams()
      if (search.trim()) {
        params.append('search', search.trim())
      }
      params.append('limit', '50')

      url = `/${panelPath}/${props.relationshipSearchUrl}&${params.toString()}`

      const response = await fetch(url)
      if (!response.ok) {
        throw new Error('Failed to fetch relationship options')
      }

      const data = await response.json()
      responseData = {
        options: data.options || [],
        has_more: data.hasMore || false,
      }
    } else if (props.closureOptionsUrl && props.fieldName) {
      // Closure-based options with pagination
      const panelPath = window.location.pathname.split('/')[1] || 'admin'
      const params = new URLSearchParams()
      // resourceSlug is optional - backend can use field name convention as fallback
      if (props.resourceSlug) {
        params.append('resource', props.resourceSlug)
      }
      params.append('field', props.fieldName)
      params.append('limit', '50')
      // Calculate offset based on current options count (for pagination)
      params.append('offset', reset ? '0' : options.value.length.toString())
      if (search.trim()) {
        params.append('search', search.trim())
      }
      // Pass relation manager class if this is inside a relation manager
      if (props.relationManager) {
        params.append('relationManager', props.relationManager)
      }

      url = `/${panelPath}/${props.closureOptionsUrl}?${params.toString()}`

      const response = await fetch(url)
      if (!response.ok) {
        throw new Error('Failed to fetch closure options')
      }

      const data = await response.json()
      responseData = {
        options: data.options || [],
        has_more: data.hasMore || false,
      }
    } else if (props.resourceSlug && props.fieldName) {
      // Legacy: Use dashboard select-options endpoint
      const params = new URLSearchParams({
        page: page.value.toString(),
      })

      if (search.trim()) {
        params.append('search', search.trim())
      }

      url = `/dashboard/${props.resourceSlug}/select-options/${props.fieldName}?${params}`

      const response = await fetch(url)
      if (!response.ok) {
        throw new Error('Failed to fetch options')
      }

      responseData = await response.json()
    } else {
      // No way to fetch options
      loading.value = false
      return
    }

    // Cache all option labels for selected values
    responseData.options.forEach((option: Option) => {
      selectedOptionsCache.value.set(option.value, option.label)
    })

    if (reset) {
      options.value = responseData.options
    } else {
      options.value = [...options.value, ...responseData.options]
    }

    hasMore.value = responseData.has_more || false
  } catch (error) {
    console.error('[Select] Error fetching options:', error)
  } finally {
    loading.value = false
  }
}

// Load more options
const loadMore = () => {
  if (hasMore.value && !loading.value) {
    page.value++
    fetchOptions()
  }
}

// Handle infinite scroll
const handleScroll = (event: Event) => {
  const target = event.target as HTMLElement
  const scrollTop = target.scrollTop
  const scrollHeight = target.scrollHeight
  const clientHeight = target.clientHeight

  // Load more when scrolled to bottom (with 50px threshold)
  const isNearBottom = scrollTop + clientHeight >= scrollHeight - 50

  if (isNearBottom && hasMore.value && !loading.value) {
    loadMore()
  }
}

// Watch search term changes directly
let searchDebounceTimeout: ReturnType<typeof setTimeout> | undefined
watch(searchTerm, (newValue) => {
  // Update current search
  currentSearch.value = newValue

  // Only search if dropdown is open
  if (!open.value) return

  clearTimeout(searchDebounceTimeout)
  searchDebounceTimeout = setTimeout(() => {
    fetchOptions(true, newValue)
  }, props.searchDebounce)
})

// Watch open state to fetch initial data (preload)
watch(open, (isOpen) => {
  if (isOpen) {
    // Clear search term when opening for fresh search
    searchTerm.value = ''
    currentSearch.value = ''
    page.value = 1
    hasMore.value = false

    // Fetch options when opening if:
    // 1. We have a relationship search URL (searchable relationship select)
    // 2. We have a closure options URL (closure-based searchable select)
    // 3. OR we don't have static options AND not using dependent options
    if (props.relationshipSearchUrl) {
      // Relationship select with search - fetch fresh options when opening
      fetchOptions(true, '')
    } else if (props.closureOptionsUrl && props.fieldName) {
      // Closure-based select with searchable options - fetch fresh options when opening
      fetchOptions(true, '')
    } else if (!hasStaticOptions.value && !props.optionsUrl && !props.hasDynamicOptions) {
      // Legacy: Fetch fresh data when opening
      fetchOptions(true, '')
    }
  }
})

// Inject form data for dependent field watching
const formData = inject<Record<string, any>>('formData', null)

// Computed property to check if select should be disabled due to missing dependencies
const isDisabledByDependency = computed(() => {
  if (!props.dependsOn || props.dependsOn.length === 0 || !formData) {
    return false
  }

  // Check if all dependencies have values
  return props.dependsOn.some((fieldName) => {
    const value = formData[fieldName]
    return value === null || value === undefined || value === ''
  })
})

// Combined disabled state
const disabled = computed(() => {
  return props.disabled || isDisabledByDependency.value
})

// Fetch options based on dependent field values
const fetchDependentOptions = async () => {
  if (!props.dependsOn || props.dependsOn.length === 0) {
    return
  }

  if (!formData) {
    console.warn('Form data not provided via inject. Dependent selects will not work.')
    return
  }

  // Check if all dependencies have values
  let hasRequiredValues = true
  props.dependsOn.forEach((fieldName) => {
    const value = formData[fieldName]
    if (value === null || value === undefined || value === '') {
      hasRequiredValues = false
    }
  })

  // If no dependent values, clear options and don't fetch
  if (!hasRequiredValues) {
    options.value = []
    return
  }

  loading.value = true

  try {
    let url: string
    let requestOptions: RequestInit

    // Choose fetch method based on optionsUrl vs hasDynamicOptions
    if (props.optionsUrl) {
      // Direct API endpoint (e.g., /api/locations/states)
      const params = new URLSearchParams()
      props.dependsOn.forEach((fieldName) => {
        const value = formData[fieldName]
        if (value !== null && value !== undefined && value !== '') {
          params.append(fieldName, String(value))
        }
      })

      url = `${props.optionsUrl}?${params.toString()}`
      requestOptions = {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
      }
    } else if (props.hasDynamicOptions) {
      // Closure-based options - use evaluation API
      url = '/api/form/evaluate-field'

      // Get CSRF token from meta tag
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

      requestOptions = {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken || '',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
          resource: props.resourceSlug,
          field_name: props.fieldName,
          form_state: formData,
          property: 'options',
        }),
      }
    } else {
      // No way to fetch options
      return
    }

    const response = await fetch(url, requestOptions)

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()

    // Handle different response formats
    let optionsArray: any[]

    if (props.hasDynamicOptions && data.options) {
      // Evaluation API returns {options: [...]}
      optionsArray = data.options
    } else if (Array.isArray(data)) {
      // Direct API returns [...]
      optionsArray = data
    } else {
      options.value = []
      return
    }

    // Transform to options format
    if (Array.isArray(optionsArray)) {
      options.value = optionsArray.map((item: any) => ({
        value: String(item.id || item.value),
        label: item.name || item.label || item.title,
        disabled: item.disabled || false,
      }))
    }
  } catch (error) {
    console.error('[Select] Error fetching dependent options:', error)
    options.value = []
  } finally {
    loading.value = false
  }
}

// Watch dependent fields for cascading select behavior
if (props.dependsOn && props.dependsOn.length > 0 && (props.optionsUrl || props.hasDynamicOptions)) {
  // Watch each dependent field
  props.dependsOn.forEach((fieldName) => {
    // Track if this is the first run (mount)
    let isFirstRun = true

    watch(
      () => formData?.[fieldName],
      async (newValue, oldValue) => {
        // On first run (mount), just fetch options without clearing
        if (isFirstRun) {
          isFirstRun = false

          // Fetch options if dependency has a value
          if (newValue !== null && newValue !== undefined && newValue !== '') {
            await fetchDependentOptions()
          }
          return
        }

        // When a dependent field changes (not first run), clear and fetch new options
        if (newValue !== oldValue) {
          // Clear current selection when dependency changes
          selectedValues.value = []

          // Emit the cleared value to update the form
          emit('update:modelValue', props.multiple ? [] : null)

          // Clear current options
          options.value = []

          // Fetch new options if the dependent field has a value
          if (newValue !== null && newValue !== undefined && newValue !== '') {
            await fetchDependentOptions()
          }
        }
      },
      { immediate: true } // Fetch on mount if dependency already has value
    )
  })
}

// Fetch selected options by IDs (for edit mode)
const fetchSelectedOptions = async (ids: string[]) => {
  if (ids.length === 0) return

  loading.value = true
  try {
    let url: string
    let responseOptions: Option[] = []

    // Check if we have a relationship options URL
    if (props.relationshipOptionsUrl) {
      // Get current panel path from window location
      const panelPath = window.location.pathname.split('/')[1] || 'admin'
      const params = new URLSearchParams()
      ids.forEach(id => params.append('ids[]', id))

      url = `/${panelPath}/${props.relationshipOptionsUrl}&${params.toString()}`

      const response = await fetch(url)
      if (!response.ok) {
        throw new Error('Failed to fetch selected relationship options')
      }

      const data = await response.json()
      responseOptions = data.options || []
    } else if (props.resourceSlug && props.fieldName) {
      // Legacy endpoint
      const params = new URLSearchParams()
      ids.forEach(id => params.append('ids[]', id))

      url = `/dashboard/${props.resourceSlug}/select-options/${props.fieldName}?${params}`

      const response = await fetch(url)
      if (!response.ok) {
        throw new Error('Failed to fetch selected options')
      }

      const data = await response.json()
      responseOptions = data.options || []
    }

    // Cache all option labels
    responseOptions.forEach((option: Option) => {
      selectedOptionsCache.value.set(option.value, option.label)
    })

    // Add these options to our options array
    options.value = responseOptions
  } catch (error) {
    console.error('[Select] Error fetching selected options:', error)
  } finally {
    loading.value = false
  }
}

// Fetch selected options by IDs for closure-based selects
const fetchClosureSelectedOptions = async (ids: string[]) => {
  // Only need closureOptionsUrl and fieldName - resourceSlug is optional for ID-based lookups
  if (ids.length === 0 || !props.closureOptionsUrl || !props.fieldName) {
    return
  }

  try {
    const panelPath = window.location.pathname.split('/')[1] || 'admin'
    const params = new URLSearchParams()
    params.append('field', props.fieldName)
    ids.forEach(id => params.append('ids[]', id))

    // Pass resource context if available (for closure evaluation)
    if (props.resourceSlug) {
      params.append('resource', props.resourceSlug)
    }
    // Pass relation manager class if this is inside a relation manager
    if (props.relationManager) {
      params.append('relationManager', props.relationManager)
    }

    const url = `/${panelPath}/${props.closureOptionsUrl}?${params.toString()}`

    const response = await fetch(url)
    if (!response.ok) {
      throw new Error('Failed to fetch closure selected options')
    }

    const data = await response.json()
    const responseOptions: Option[] = data.options || []

    // Cache and add these options to our options array
    responseOptions.forEach((option: Option) => {
      selectedOptionsCache.value.set(option.value, option.label)
      // Add to options if not already there
      if (!options.value.find(o => o.value === option.value)) {
        options.value.push(option)
      }
    })
  } catch (error) {
    console.error('[Select] Error fetching closure selected options:', error)
  }
}

// Check if we have static options (non-searchable/non-relationship)
// Returns false if relationshipSearchUrl is set, even if initial options are provided
const hasStaticOptions = computed(() => {
  // If we have a relationship search URL, options are NOT static (they're searchable)
  if (props.relationshipSearchUrl) {
    return false
  }
  // If hasDynamicOptions is true but no dependsOn, options were pre-evaluated and passed from backend
  // Treat them as static options
  if (props.hasDynamicOptions && (!props.dependsOn || props.dependsOn.length === 0)) {
    return props.options && props.options.length > 0
  }
  return props.options && props.options.length > 0
})

// Preload initial options on mount for better UX
onMounted(async () => {
  if (props.relationshipSearchUrl) {
    // Relationship select with search
    // Use initial options from backend if provided
    if (props.options && props.options.length > 0) {
      options.value = props.options
      // Cache all option labels
      options.value.forEach((option) => {
        selectedOptionsCache.value.set(option.value, option.label)
      })
    }
    // Note: Selected value label fetching is now handled by the watcher
    initialLoadComplete.value = true
  } else if (hasStaticOptions.value) {
    // Use static options provided from backend
    options.value = props.options || []

    // Cache all static option labels
    options.value.forEach((option) => {
      selectedOptionsCache.value.set(option.value, option.label)
    })

    initialLoadComplete.value = true
  } else if (props.meta?.dependentOptions) {
    // This select has dependent options passed via meta
    // Use the reactive composable to filter options based on dependencies
    options.value = getFilteredOptions(props.meta.dependentOptions)

    // Cache the option labels
    options.value.forEach((option) => {
      selectedOptionsCache.value.set(option.value, option.label)
    })

    initialLoadComplete.value = true
  } else {
    // For closure-based selects with NO dependencies, the options are already in props
    // They were pre-evaluated by the backend
    if (props.hasDynamicOptions && (!props.dependsOn || props.dependsOn.length === 0)) {
      // Use the pre-evaluated options from props
      if (props.options && props.options.length > 0) {
        options.value = props.options
        options.value.forEach((option) => {
          selectedOptionsCache.value.set(option.value, option.label)
        })
      }
      // Set hasMore if backend indicates more options are available
      hasMore.value = props.hasMoreOptions || false

      // Note: Selected value label fetching is now handled by the watcher
      initialLoadComplete.value = true
    } else if (props.dependsOn && props.dependsOn.length > 0) {
      // Dependent selects - wait for dependencies to trigger fetch
      initialLoadComplete.value = true
    } else if (selectedValues.value.length > 0) {
      // If there's a pre-selected value (edit mode) and it's a relationship select,
      // fetch the specific selected options by ID to display labels
      await fetchSelectedOptions(selectedValues.value)
      initialLoadComplete.value = true
    } else {
      initialLoadComplete.value = true
    }
  }
})

// Watch for selected value changes to fetch labels
// This is needed when the value is set after mount (e.g., in edit modals)
// Always fetch from API to ensure we have the correct label
watch(selectedValues, async (newValues) => {
  if (newValues.length === 0) return

  // Handle closure-based selects
  if (props.closureOptionsUrl && props.fieldName) {
    await fetchClosureSelectedOptions(newValues)
    return
  }

  // Handle relationship selects
  if (props.relationshipOptionsUrl) {
    const missingIds = newValues.filter(id => !selectedOptionsCache.value.has(id))
    if (missingIds.length > 0) {
      await fetchSelectedOptions(missingIds)
    }
    return
  }
}, { deep: true, immediate: true })

// Listen for field-changed events from dependent fields (for reactive selects)
if (props.dependsOn && props.dependsOn.length > 0 && props.meta?.dependentOptions) {
  const handleFieldChange = (event: CustomEvent) => {
    const { fieldName } = event.detail

    // Check if the changed field is one we depend on
    if (props.dependsOn.includes(fieldName)) {
      // Re-filter options based on new form data
      options.value = getFilteredOptions(props.meta.dependentOptions)

      // Clear current selection when dependency changes
      if (selectedValues.value.length > 0) {
        selectedValues.value = []
        emit('update:modelValue', props.multiple ? [] : null)
      }

      // Update cache
      selectedOptionsCache.value.clear()
      options.value.forEach((option) => {
        selectedOptionsCache.value.set(option.value, option.label)
      })
    }
  }

  onMounted(() => {
    window.addEventListener('field-changed', handleFieldChange as EventListener)
  })

  onUnmounted(() => {
    window.removeEventListener('field-changed', handleFieldChange as EventListener)
  })
}

// Handle search term change from ComboboxRoot
let searchTimeout: ReturnType<typeof setTimeout> | undefined
const handleSearchChange = (value: string) => {
  // Update both refs
  searchTerm.value = value
  currentSearch.value = value

  // Only search if dropdown is open
  if (!open.value) return

  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    // Backend search - reset to page 1 with new search query (clears options and fetches fresh)
    fetchOptions(true, value)
  }, props.searchDebounce)
}

// Remove a value from selection (for badges)
const removeValue = (valueToRemove: string) => {
  const newValues = selectedValues.value.filter(v => v !== valueToRemove)
  selectedValues.value = newValues
}

// Validation errors
const validationError = ref<string | null>(null)

// Check min/max items validation
const checkValidation = () => {
  if (!props.multiple) {
    validationError.value = null
    return
  }

  const count = selectedValues.value.length

  if (props.minItems && count < props.minItems) {
    validationError.value = `Please select at least ${props.minItems} item${props.minItems > 1 ? 's' : ''}`
    return
  }

  if (props.maxItems && count > props.maxItems) {
    validationError.value = `You can only select up to ${props.maxItems} item${props.maxItems > 1 ? 's' : ''}`
    return
  }

  validationError.value = null
}

// Watch selected values for validation
watch(selectedValues, () => {
  checkValidation()
})

// Handle selection change from ComboboxRoot
const handleValueChange = (value: string | string[]) => {
  // Convert to array and filter valid values
  let newValues: string[]
  if (Array.isArray(value)) {
    newValues = value.filter(v => v && String(v).trim() !== '')
  } else if (value !== null && value !== undefined && String(value).trim() !== '') {
    newValues = [String(value)]
  } else {
    newValues = []
  }

  // Check if we're exceeding maxItems
  if (props.multiple && props.maxItems && newValues.length > props.maxItems) {
    newValues = newValues.slice(0, props.maxItems)
  }

  // Update selectedValues (which will trigger the emit)
  selectedValues.value = newValues

  if (!props.multiple) {
    open.value = false
    // Clear search term when closing after selection
    searchTerm.value = ''
  }
  // For multiple select, keep dropdown open and don't clear search
}

// Create/Edit option functionality
const createFormData = ref<Record<string, any>>({})
const editFormData = ref<Record<string, any>>({})
const savingOption = ref(false)

// Open create modal
const openCreateModal = () => {
  createFormData.value = {}
  showCreateModal.value = true
}

// Open edit modal
const openEditModal = async (optionId: string) => {
  editingOptionId.value = optionId
  editFormData.value = {}
  showEditModal.value = true

  // Fetch existing option data
  try {
    const response = await fetch(`/dashboard/${props.resourceSlug}/select-options/${props.fieldName}/${optionId}`, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })

    if (!response.ok) {
      throw new Error('Failed to fetch option data')
    }

    const data = await response.json()
    editFormData.value = data
  } catch (error) {
    console.error('[Select] Error fetching option data:', error)
    // Continue with empty form if fetch fails
  }
}

// Save new option
const saveNewOption = async () => {
  if (savingOption.value) return

  savingOption.value = true
  try {
    const response = await fetch(`/dashboard/${props.resourceSlug}/select-options/${props.fieldName}/create`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify(createFormData.value),
    })

    if (!response.ok) {
      throw new Error('Failed to create option')
    }

    const data = await response.json()

    // Add new option to list and cache
    const newOption: Option = {
      value: String(data.id),
      label: data.label,
    }
    options.value.unshift(newOption)
    selectedOptionsCache.value.set(newOption.value, newOption.label)

    // Auto-select the new option
    if (props.multiple) {
      selectedValues.value = [...selectedValues.value, newOption.value]
    } else {
      selectedValues.value = [newOption.value]
    }

    showCreateModal.value = false
    createFormData.value = {}
  } catch (error) {
    console.error('[Select] Error creating option:', error)
    alert('Failed to create option. Please try again.')
  } finally {
    savingOption.value = false
  }
}

// Update existing option
const updateOption = async () => {
  if (savingOption.value || !editingOptionId.value) return

  savingOption.value = true
  try {
    const response = await fetch(`/dashboard/${props.resourceSlug}/select-options/${props.fieldName}/${editingOptionId.value}/edit`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify(editFormData.value),
    })

    if (!response.ok) {
      throw new Error('Failed to update option')
    }

    const data = await response.json()

    // Update option in list and cache
    const optionIndex = options.value.findIndex(opt => opt.value === editingOptionId.value)
    if (optionIndex !== -1) {
      options.value[optionIndex].label = data.label
    }
    selectedOptionsCache.value.set(String(data.id), data.label)

    showEditModal.value = false
    editFormData.value = {}
    editingOptionId.value = null
  } catch (error) {
    console.error('[Select] Error updating option:', error)
    alert('Failed to update option. Please try again.')
  } finally {
    savingOption.value = false
  }
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
    :value="multiple ? (selectedValues.length > 0 ? selectedValues.join(',') : '') : (selectedValues.length > 0 ? selectedValues[0] : '')"
  />

  <div class="flex items-center gap-2">
  <ComboboxRoot
    v-model="selectedValues"
    v-model:open="open"
    v-model:search-term="searchTerm"
    :multiple="multiple"
    :disabled="disabled"
    :filter-function="() => true"
    class="relative w-full flex-1"
    @update:model-value="handleValueChange"
    @update:search-term="handleSearchChange"
  >
    <ComboboxAnchor
      class="group w-full rounded-md border border-input bg-background text-sm shadow-sm ring-offset-background transition-all hover:border-ring focus-within:outline-none focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 data-[state=open]:border-ring data-[state=open]:ring-2 data-[state=open]:ring-ring cursor-pointer"
      :class="cn(
        disabled && 'cursor-not-allowed opacity-50',
        props.multiple ? 'flex flex-col items-start' : 'inline-flex items-center gap-2',
        props.multiple && selectedValues.length > 0 ? 'px-2 py-2' : 'px-3 py-2'
      )"
      @click="() => { if (!disabled && !open) open = true }"
    >
      <!-- Multiple select: Show badges + input -->
      <div v-if="props.multiple" class="w-full flex flex-col gap-2">
        <!-- Selected badges -->
        <div v-if="selectedValues.length > 0" class="flex flex-wrap gap-1">
          <span
            v-for="value in selectedValues"
            :key="value"
            class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-primary/10 text-primary text-xs font-medium"
          >
            <span v-if="props.allowHtml" v-html="getLabelForValue(value)" />
            <span v-else>{{ getLabelForValue(value) }}</span>

            <!-- Edit button for multi-select badges -->
            <button
              v-if="props.hasEditOptionForm"
              type="button"
              class="hover:bg-primary/20 rounded-sm p-0.5 transition-colors"
              @click.stop="openEditModal(value)"
            >
              <Edit class="h-3 w-3" />
            </button>

            <!-- Remove button -->
            <button
              type="button"
              class="hover:bg-primary/20 rounded-sm p-0.5 transition-colors"
              @click.stop="removeValue(value)"
            >
              <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </span>
        </div>
        <!-- Search input (always visible for multiple) -->
        <div class="flex items-center gap-2 w-full">
          <!-- Prefix icon or text -->
          <span v-if="getIconComponent(props.prefixIcon) || props.prefix" class="shrink-0 flex items-center gap-1">
            <component
              v-if="getIconComponent(props.prefixIcon)"
              :is="getIconComponent(props.prefixIcon)"
              class="h-4 w-4"
              :class="getIconColorClass(props.prefixIconColor)"
            />
            <span v-if="props.prefix" class="text-sm text-muted-foreground">{{ props.prefix }}</span>
          </span>
          <ComboboxInput
            v-model="searchTerm"
            :placeholder="selectedValues.length > 0 ? translatedSearchPlaceholder : translatedPlaceholder"
            class="flex-1 min-w-0 bg-transparent outline-none placeholder:text-muted-foreground text-sm"
            :disabled="disabled"
          />
          <!-- Suffix icon or text -->
          <span v-if="getIconComponent(props.suffixIcon) || props.suffix" class="shrink-0 flex items-center gap-1">
            <span v-if="props.suffix" class="text-sm text-muted-foreground">{{ props.suffix }}</span>
            <component
              v-if="getIconComponent(props.suffixIcon)"
              :is="getIconComponent(props.suffixIcon)"
              class="h-4 w-4"
              :class="getIconColorClass(props.suffixIconColor)"
            />
          </span>
          <ComboboxTrigger class="shrink-0" as-child>
            <button type="button" :disabled="disabled" @click.stop="open = !open">
              <ChevronDown
                class="h-4 w-4 opacity-50 transition-transform duration-200 group-data-[state=open]:rotate-180"
              />
            </button>
          </ComboboxTrigger>
        </div>
      </div>

      <!-- Single select: Show value or input -->
      <template v-else>
        <!-- Prefix icon or text -->
        <span v-if="getIconComponent(props.prefixIcon) || props.prefix" class="shrink-0 flex items-center gap-1">
          <component
            v-if="getIconComponent(props.prefixIcon)"
            :is="getIconComponent(props.prefixIcon)"
            class="h-4 w-4"
            :class="getIconColorClass(props.prefixIconColor)"
          />
          <span v-if="props.prefix" class="text-sm text-muted-foreground">{{ props.prefix }}</span>
        </span>
        <span
          v-if="!open && displayValue && props.allowHtml"
          class="flex-1 min-w-0 truncate text-sm"
          v-html="displayValue"
        />
        <span
          v-else-if="!open && displayValue"
          class="flex-1 min-w-0 truncate text-sm"
        >
          {{ displayValue }}
        </span>
        <span
          v-else-if="!open"
          class="flex-1 min-w-0 text-sm text-muted-foreground"
        >
          {{ translatedPlaceholder }}
        </span>
        <ComboboxInput
          v-else
          v-model="searchTerm"
          :placeholder="translatedPlaceholder"
          class="flex-1 min-w-0 bg-transparent outline-none placeholder:text-muted-foreground text-sm"
          :disabled="disabled"
          auto-focus
        />
        <!-- Suffix icon or text -->
        <span v-if="getIconComponent(props.suffixIcon) || props.suffix" class="shrink-0 flex items-center gap-1">
          <span v-if="props.suffix" class="text-sm text-muted-foreground">{{ props.suffix }}</span>
          <component
            v-if="getIconComponent(props.suffixIcon)"
            :is="getIconComponent(props.suffixIcon)"
            class="h-4 w-4"
            :class="getIconColorClass(props.suffixIconColor)"
          />
        </span>
        <ComboboxTrigger class="shrink-0" as-child>
          <button type="button" :disabled="disabled" @click.stop="open = !open">
            <ChevronDown
              class="h-4 w-4 opacity-50 transition-transform duration-200 group-data-[state=open]:rotate-180"
            />
          </button>
        </ComboboxTrigger>
      </template>
    </ComboboxAnchor>

    <ComboboxPortal>
      <ComboboxContent
        class="w-full min-w-[var(--reka-combobox-trigger-width)] rounded-md border border-border bg-popover text-popover-foreground shadow-lg outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[state=closed]:slide-out-to-top-2 data-[state=open]:slide-in-from-top-2 z-50"
        position="popper"
        side="bottom"
        :side-offset="4"
        align="start"
        :collision-padding="8"
        :avoid-collisions="true"
      >
        <!-- Initial loading state -->
        <div
          v-if="loading && options.length === 0"
          class="flex flex-col items-center justify-center gap-2 py-8"
        >
          <LoaderCircle class="h-5 w-5 animate-spin text-muted-foreground" />
          <span class="text-sm text-muted-foreground">{{ translatedLoadingMessage }}</span>
        </div>

        <!-- Search results / Options list -->
        <div
          v-else-if="options.length > 0"
          ref="scrollContainer"
          class="max-h-[300px] overflow-y-auto overscroll-contain"
          @scroll="handleScroll"
        >
          <div class="p-1">
            <ComboboxItem
              v-for="option in options"
              :key="option.value"
              :value="option.value"
              :disabled="option.disabled"
              class="relative flex cursor-pointer select-none items-center gap-2 rounded-sm px-2 py-2 text-sm outline-none transition-colors data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
            >
              <Check
                :class="cn(
                  'h-4 w-4 shrink-0 transition-opacity',
                  selectedValues.includes(option.value) ? 'opacity-100' : 'opacity-0'
                )"
              />
              <span
                v-if="props.allowHtml"
                class="flex-1"
                :class="props.wrapOptionLabels ? '' : 'truncate'"
                v-html="option.label"
              />
              <span
                v-else
                class="flex-1"
                :class="props.wrapOptionLabels ? '' : 'truncate'"
              >
                {{ option.label }}
              </span>
            </ComboboxItem>

            <!-- Loading more indicator (infinite scroll) -->
            <div
              v-if="loading || hasMore"
              class="flex items-center justify-center gap-2 px-2 py-3 text-sm text-muted-foreground"
            >
              <LoaderCircle v-if="loading" class="h-4 w-4 animate-spin" />
              <span v-if="loading">{{ searchTerm ? translatedSearchingMessage : translatedLoadingMessage }}</span>
              <span v-else-if="hasMore" class="text-xs">{{ translatedScrollForMoreLabel }}</span>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <ComboboxEmpty
          v-else
          class="flex flex-col items-center justify-center gap-3 py-8 px-4"
        >
          <div class="text-muted-foreground text-sm text-center">
            <p v-if="searchTerm" class="font-medium">{{ translatedNoSearchResultsMessage }}</p>
            <p v-else class="font-medium">{{ translatedSearchPrompt }}</p>
            <p v-if="searchTerm" class="text-xs mt-1">{{ translatedTryAdjustingSearchLabel }}</p>
          </div>

          <!-- Create new option button -->
          <Button
            v-if="props.hasCreateOptionForm"
            type="button"
            variant="outline"
            size="sm"
            class="gap-2"
            @click.stop="openCreateModal"
          >
            <Plus class="h-4 w-4" />
            {{ translatedCreateButtonLabel }}
          </Button>
        </ComboboxEmpty>
      </ComboboxContent>
    </ComboboxPortal>
  </ComboboxRoot>

  <!-- Create button (suffix) -->
  <Button
    v-if="props.hasCreateOptionForm"
    type="button"
    variant="outline"
    size="icon"
    class="shrink-0"
    @click="openCreateModal"
  >
    <Plus class="h-4 w-4" />
  </Button>

  <!-- Edit button (suffix) - only for single select with a selected value -->
  <Button
    v-if="props.hasEditOptionForm && !props.multiple && selectedValues.length > 0"
    type="button"
    variant="outline"
    size="icon"
    class="shrink-0"
    @click="openEditModal(selectedValues[0])"
  >
    <Edit class="h-3.5 w-3.5" />
  </Button>
  </div>

  <!-- Validation error message -->
  <p v-if="validationError" class="mt-1 text-sm text-destructive">
    {{ validationError }}
  </p>

  <!-- Create Option Modal -->
  <Dialog v-model:open="showCreateModal">
    <DialogContent class="sm:max-w-[525px]">
      <DialogHeader>
        <DialogTitle>{{ translatedCreateModalTitle }}</DialogTitle>
        <DialogDescription>
          {{ translatedCreateModalDescription }}
        </DialogDescription>
      </DialogHeader>
      <div class="grid gap-4 py-4">
        <div
          v-for="(field, index) in props.createOptionForm"
          :key="index"
          class="space-y-2"
        >
          <label v-if="field.label" :for="`create-${field.name}`" class="text-sm font-medium block">
            {{ field.label }}
            <span v-if="field.required" class="text-destructive ms-0.5">*</span>
          </label>
          <component
            :is="getComponent(field.component)"
            v-if="getComponent(field.component)"
            :id="`create-${field.name}`"
            v-model="createFormData[field.name]"
            v-bind="field"
          />
          <p v-if="field.helperText" class="text-xs text-muted-foreground">
            {{ field.helperText }}
          </p>
        </div>
      </div>
      <DialogFooter>
        <Button
          type="button"
          variant="outline"
          @click="showCreateModal = false"
        >
          {{ translatedCancelButtonLabel }}
        </Button>
        <Button
          type="button"
          :disabled="savingOption"
          @click="saveNewOption"
        >
          <LoaderCircle v-if="savingOption" class="h-4 w-4 animate-spin mr-2" />
          {{ savingOption ? translatedCreatingButtonLabel : translatedCreateButtonLabel }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>

  <!-- Edit Option Modal -->
  <Dialog v-model:open="showEditModal">
    <DialogContent class="sm:max-w-[525px]">
      <DialogHeader>
        <DialogTitle>{{ translatedEditModalTitle }}</DialogTitle>
        <DialogDescription>
          {{ translatedEditModalDescription }}
        </DialogDescription>
      </DialogHeader>
      <div class="grid gap-4 py-4">
        <div
          v-for="(field, index) in props.editOptionForm"
          :key="index"
          class="space-y-2"
        >
          <label v-if="field.label" :for="`edit-${field.name}`" class="text-sm font-medium block">
            {{ field.label }}
            <span v-if="field.required" class="text-destructive ms-0.5">*</span>
          </label>
          <component
            :is="getComponent(field.component)"
            v-if="getComponent(field.component)"
            :id="`edit-${field.name}`"
            v-model="editFormData[field.name]"
            v-bind="field"
          />
          <p v-if="field.helperText" class="text-xs text-muted-foreground">
            {{ field.helperText }}
          </p>
        </div>
      </div>
      <DialogFooter>
        <Button
          type="button"
          variant="outline"
          @click="showEditModal = false"
        >
          {{ translatedCancelButtonLabel }}
        </Button>
        <Button
          type="button"
          :disabled="savingOption"
          @click="updateOption"
        >
          <LoaderCircle v-if="savingOption" class="h-4 w-4 animate-spin mr-2" />
          {{ savingOption ? translatedSavingButtonLabel : translatedSaveChangesButtonLabel }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>

  <!-- Helper text -->
  <p
    v-if="helperText"
    class="text-xs text-muted-foreground mt-1"
  >
    {{ helperText }}
  </p>
  </div>
</template>
