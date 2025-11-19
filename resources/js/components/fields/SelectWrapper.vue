<script setup lang="ts">
import { computed } from 'vue'
import Select from './Select.vue'
import SelectSimple from './SelectSimple.vue'

interface Props {
  native?: boolean
  searchable?: boolean
  [key: string]: any
}

const props = defineProps<Props>()

// Use SelectSimple when native=true OR when not searchable
// Use full Select.vue when searchable=true (requires ComboboxRoot)
const shouldUseSimple = computed(() => {
  // If explicitly set to native, use simple
  if (props.native === true) {
    return true
  }

  // If searchable, must use full Select
  if (props.searchable === true) {
    return false
  }

  // Default to simple for static options
  return true
})

const componentToUse = computed(() => {
  return shouldUseSimple.value ? SelectSimple : Select
})
</script>

<template>
  <component :is="componentToUse" v-bind="$props" />
</template>
