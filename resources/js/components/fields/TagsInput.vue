<script setup lang="ts">
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import * as LucideIcons from 'lucide-vue-next'
import { X } from 'lucide-vue-next'
import { ref, watch } from 'vue'

interface Props {
  name?: string
  value?: string[]
  modelValue?: string[]
  label?: string
  placeholder?: string
  disabled?: boolean
  required?: boolean
  helperText?: string
  separator?: string
  suggestions?: string[]
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: () => [],
  placeholder: 'Add tags...',
  disabled: false,
  separator: ',',
})

const emit = defineEmits<{
  'update:modelValue': [value: string[]]
  'update:value': [value: string[]]
}>()

const inputValue = ref('')

// Internal state for tags
const internalTags = ref<string[]>([])

// Watch for prop changes and update internal state
watch(() => props.value ?? props.modelValue, (newValue) => {
  internalTags.value = Array.isArray(newValue) ? [...newValue] : []
}, { immediate: true })

// Computed current tags that reads from internal state
const currentTags = ref<string[]>([])
watch(internalTags, (newTags) => {
  currentTags.value = newTags
}, { immediate: true })

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

const addTag = (tag: string) => {
  const trimmedTag = tag.trim()
  if (trimmedTag && !internalTags.value.includes(trimmedTag)) {
    const newTags = [...internalTags.value, trimmedTag]
    internalTags.value = newTags
    emit('update:modelValue', newTags)
    emit('update:value', newTags)
  }
  inputValue.value = ''
}

const removeTag = (index: number) => {
  const newTags = [...internalTags.value]
  newTags.splice(index, 1)
  internalTags.value = newTags
  emit('update:modelValue', newTags)
  emit('update:value', newTags)
}

const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Enter' || event.key === props.separator) {
    event.preventDefault()
    addTag(inputValue.value)
  } else if (event.key === 'Backspace' && !inputValue.value && internalTags.value.length > 0) {
    // Remove last tag on backspace when input is empty
    removeTag(internalTags.value.length - 1)
  }
}

const handleBlur = () => {
  if (inputValue.value.trim()) {
    addTag(inputValue.value)
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
      :value="internalTags.join(',')"
    />

    <div
      class="flex flex-wrap items-center gap-2 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-within:outline-none focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2"
      :class="{ 'opacity-50 cursor-not-allowed': disabled }"
    >
      <!-- Prefix icon -->
      <component
        v-if="getIconComponent(props.prefixIcon)"
        :is="getIconComponent(props.prefixIcon)"
        class="h-4 w-4 shrink-0"
        :class="getIconColorClass(props.prefixIconColor)"
      />

      <!-- Tags -->
      <Badge
        v-for="(tag, index) in internalTags"
        :key="index"
        variant="secondary"
        class="gap-1 px-2 py-1"
      >
        {{ tag }}
        <button
          type="button"
          class="hover:bg-secondary/80 rounded-sm transition-colors"
          :disabled="disabled"
          @click="removeTag(index)"
        >
          <X class="h-3 w-3" />
        </button>
      </Badge>

      <!-- Input -->
      <input
        v-model="inputValue"
        type="text"
        :placeholder="internalTags.length === 0 ? placeholder : ''"
        :disabled="disabled"
        class="flex-1 min-w-[120px] bg-transparent outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed"
        @keydown="handleKeydown"
        @blur="handleBlur"
      />

      <!-- Suffix icon -->
      <component
        v-if="getIconComponent(props.suffixIcon)"
        :is="getIconComponent(props.suffixIcon)"
        class="h-4 w-4 shrink-0"
        :class="getIconColorClass(props.suffixIconColor)"
      />
    </div>

    <!-- Suggestions -->
    <div v-if="suggestions && suggestions.length > 0" class="mt-2 flex flex-wrap gap-2">
      <Badge
        v-for="suggestion in suggestions"
        :key="suggestion"
        variant="outline"
        class="cursor-pointer hover:bg-accent"
        @click="addTag(suggestion)"
      >
        {{ suggestion }}
      </Badge>
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
