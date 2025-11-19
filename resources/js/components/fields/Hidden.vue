<template>
    <!-- Use slot if provided (Blade mode), otherwise use internal template (Direct Vue mode) -->
    <slot v-if="$slots.default"
        :id="id || name"
        :name="name"
        :value="value"
    />

    <!-- Internal template for direct Vue usage -->
    <input
        v-else
        type="hidden"
        :id="id || name"
        :name="name"
        :value="value"
    />
</template>

<script setup lang="ts">
const props = defineProps<{
    id?: string
    name: string
    value?: string
}>()
</script>
