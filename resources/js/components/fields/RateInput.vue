<script setup lang="ts">
import { ref, computed } from 'vue'
import { Star, Heart, ThumbsUp, Flame, Trophy } from 'lucide-vue-next'

interface Props {
  name?: string
  value?: number | null
  modelValue?: number | null
  label?: string
  helperText?: string
  required?: boolean
  disabled?: boolean
  readonly?: boolean
  max?: number
  allowHalf?: boolean
  icon?: string
  color?: string | null
  showValue?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  value: null,
  max: 5,
  allowHalf: false,
  icon: 'star',
  color: null,
  showValue: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: number | null]
  'update:value': [value: number | null]
}>()

const rating = ref<number>(props.modelValue ?? props.value ?? 0)
const hoverRating = ref<number>(0)

const iconMap: Record<string, any> = {
  star: Star,
  heart: Heart,
  'thumbs-up': ThumbsUp,
  flame: Flame,
  trophy: Trophy,
}

const IconComponent = computed(() => iconMap[props.icon] || Star)

const updateRating = (value: number) => {
  if (props.disabled || props.readonly) return

  rating.value = value
  emit('update:modelValue', value)
  emit('update:value', value)
}

const handleClick = (index: number, half: boolean = false) => {
  if (props.disabled || props.readonly) return

  let newRating = index + 1

  if (props.allowHalf && half) {
    newRating = index + 0.5
  }

  // Toggle off if clicking the same rating
  if (rating.value === newRating) {
    updateRating(0)
  } else {
    updateRating(newRating)
  }
}

const handleMouseMove = (index: number, event: MouseEvent, half: boolean = false) => {
  if (props.disabled || props.readonly) return

  if (props.allowHalf) {
    const target = event.currentTarget as HTMLElement
    const rect = target.getBoundingClientRect()
    const x = event.clientX - rect.left
    const isHalf = x < rect.width / 2
    hoverRating.value = index + (isHalf ? 0.5 : 1)
  } else {
    hoverRating.value = index + 1
  }
}

const handleMouseLeave = () => {
  hoverRating.value = 0
}

const isFilled = (index: number): boolean => {
  const currentRating = hoverRating.value || rating.value
  return currentRating >= index + 1
}

const isHalfFilled = (index: number): boolean => {
  const currentRating = hoverRating.value || rating.value
  return currentRating >= index + 0.5 && currentRating < index + 1
}

const fillColor = computed(() => {
  if (props.color) return props.color
  return '#facc15' // Default yellow color
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
      :value="rating"
    />

    <!-- Rating Input -->
    <div class="flex items-center gap-1">
      <button
        v-for="index in max"
        :key="index"
        type="button"
        @click="handleClick(index - 1)"
        @mousemove="(e) => handleMouseMove(index - 1, e)"
        @mouseleave="handleMouseLeave"
        :disabled="disabled"
        :class="[
          'relative p-1 rounded transition-transform hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
          disabled || readonly ? 'cursor-default opacity-50' : 'cursor-pointer',
        ]"
      >
        <!-- Full icon -->
        <component
          :is="IconComponent"
          class="h-6 w-6 transition-colors"
          :class="isFilled(index - 1) ? 'fill-current' : 'fill-none'"
          :style="{ color: isFilled(index - 1) ? fillColor : 'currentColor' }"
        />

        <!-- Half icon overlay (if half ratings enabled) -->
        <div
          v-if="allowHalf && isHalfFilled(index - 1)"
          class="absolute inset-0 overflow-hidden p-1"
          style="width: 50%"
        >
          <component
            :is="IconComponent"
            class="h-6 w-6 fill-current transition-colors"
            :style="{ color: fillColor }"
          />
        </div>
      </button>

      <!-- Show numeric value -->
      <span
        v-if="showValue"
        class="ms-2 text-sm font-medium text-foreground"
      >
        {{ rating }} / {{ max }}
      </span>
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
