<script setup>
import { computed, getCurrentInstance } from 'vue';

const props = defineProps({
    component: {
        type: String,
        required: true
    },
    props: {
        type: Object,
        default: () => ({})
    },
    componentKey: {
        type: String,
        default: ''
    }
});

// Get the current instance to access the app context
const instance = getCurrentInstance();

// Convert PascalCase to kebab-case (e.g., "TextInput" -> "text-input")
const toKebabCase = (str) => {
    return str.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
};

// Resolve the component by name from the global components registry
const resolvedComponent = computed(() => {
    const kebabName = toKebabCase(props.component);
    const componentName = `laravilt-${kebabName}`;
    console.log('Trying to resolve component:', componentName, 'from', props.component);

    // Try to get the component from the app context
    const component = instance?.appContext.components[componentName];

    if (component) {
        console.log('✓ Resolved component:', componentName);
        return component;
    }

    console.error(`Failed to resolve component: ${componentName}`);
    console.log('Available components:', Object.keys(instance?.appContext.components || {}));
    return 'div'; // Fallback to div
});

const componentProps = computed(() => props.props);
</script>

<template>
    <component
        :is="resolvedComponent"
        v-bind="componentProps"
        :key="componentKey"
    />
</template>
