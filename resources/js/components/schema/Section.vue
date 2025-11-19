<template>
    <div class="space-y-6">
        <!-- Section Header -->
        <header v-if="heading">
            <div
                :class="[
                    'flex items-center gap-2',
                    collapsible ? 'cursor-pointer' : ''
                ]"
                @click="collapsible && toggleCollapse()"
            >
                <component
                    v-if="icon && getIconComponent(icon)"
                    :is="getIconComponent(icon)"
                    class="h-4 w-4 text-muted-foreground flex-shrink-0"
                />
                <h3 class="mb-0.5 text-base font-medium">
                    {{ heading }}
                </h3>
                <ChevronDown
                    v-if="collapsible"
                    :class="[
                        'h-4 w-4 text-muted-foreground transition-transform ml-auto',
                        isCollapsed ? '-rotate-90' : ''
                    ]"
                />
            </div>
            <p v-if="description" class="text-sm text-muted-foreground">
                {{ description }}
            </p>
        </header>

        <!-- Section Content -->
        <div v-show="!isCollapsed">
            <FormRenderer v-if="schema" :schema="schema" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { ChevronDown } from 'lucide-vue-next'
import FormRenderer from '../FormRenderer.vue'
import * as LucideIcons from 'lucide-vue-next'

const props = defineProps<{
    heading?: string
    description?: string
    icon?: string
    collapsible?: boolean
    collapsed?: boolean
    schema?: Array<any>
}>()

const isCollapsed = ref(props.collapsed || false)

const toggleCollapse = () => {
    if (props.collapsible) {
        isCollapsed.value = !isCollapsed.value
    }
}

// Convert kebab-case or snake_case icon names to PascalCase for lucide-vue-next
const getIconComponent = (iconName: string) => {
    if (!iconName) return null

    // Convert formats like 'user', 'id-card', 'map-pin' to PascalCase
    const pascalCase = iconName
        .split(/[-_]/)
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join('')

    return (LucideIcons as any)[pascalCase] || null
}
</script>
