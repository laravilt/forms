<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Clock } from 'lucide-vue-next'

interface Props {
  name?: string
  value?: string | null
  modelValue?: string | null
  label?: string
  helperText?: string
  required?: boolean
  disabled?: boolean
  readonly?: boolean
  hourCycle?: 12 | 24
  granularity?: 'hour' | 'minute' | 'second'
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  value: null,
  hourCycle: 24,
  granularity: 'minute',
})

const emit = defineEmits<{
  'update:modelValue': [value: string | null]
  'update:value': [value: string | null]
}>()

const internalValue = ref(props.modelValue || props.value || '')

// Watch for prop changes
watch(
  () => props.modelValue || props.value,
  (newValue) => {
    internalValue.value = newValue || ''
  }
)

const updateTime = (event: Event) => {
  const target = event.target as HTMLInputElement
  const value = target.value || null
  internalValue.value = value || ''
  emit('update:modelValue', value)
  emit('update:value', value)
}

// Determine step based on granularity
const step = computed(() => {
  if (props.granularity === 'second') return 1
  if (props.granularity === 'minute') return 60
  return 3600 // hour
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

    <!-- Time Input -->
    <div class="relative">
      <input
        :id="name"
        :name="name"
        type="time"
        :value="internalValue"
        @input="updateTime"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :step="step"
        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
      />
      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
        <Clock class="h-4 w-4 text-muted-foreground" />
      </div>
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
