<script setup lang="ts">
import { ref } from 'vue'

interface Props {
  name?: string
  label?: string
  icons?: string[]
}

const props = withDefaults(defineProps<Props>(), {
  icons: () => []
})

console.log('IconPickerSimple loaded!', props)
</script>

<template>
  <div class="p-4 border border-blue-500 rounded">
    <h3 class="font-bold text-blue-600">IconPicker Simple Test</h3>
    <p>Name: {{ name }}</p>
    <p>Label: {{ label }}</p>
    <p>Icons count: {{ icons?.length || 0 }}</p>
    <p>First icons: {{ icons?.slice(0, 5).join(', ') }}</p>
  </div>
</template>
