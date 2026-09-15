<script setup lang="ts">
import { provide, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps<{
    errors?: Record<string, string | string[]>
}>()

const page = usePage()

// Provide errors to all child components: the given errors, else the Inertia page's validation errors
const errorsComputed = computed(() => props.errors ?? (page.props.errors as Record<string, string | string[]> | undefined) ?? {})
provide('errors', errorsComputed)
</script>

<template>
    <slot />
</template>
