import { resolveComponent } from '@laravilt/support/composables/registry';
import { useMemo } from 'react';

export interface LaraviltComponentRendererProps {
    component: string;
    props?: Record<string, any>;
    componentKey?: string;
}

// Convert PascalCase or snake_case to kebab-case
// Examples: "TextInput" -> "text-input", "date_range_picker" -> "date-range-picker"
const toKebabCase = (str: string) => {
    return str
        .replace(/_/g, '-') // Convert underscores to hyphens
        .replace(/([a-z])([A-Z])/g, '$1-$2') // Convert PascalCase
        .toLowerCase();
};

export default function LaraviltComponentRenderer({ component, props = {}, componentKey = '' }: LaraviltComponentRendererProps) {
    // Resolve the component by name from the global components registry
    const Resolved = useMemo(() => {
        const kebabName = toKebabCase(component);
        const componentName = `laravilt-${kebabName}`;

        const resolved = resolveComponent(componentName);

        if (resolved) {
            return resolved;
        }

        console.error(`Failed to resolve component: ${componentName}`);
        return null; // Fallback to div
    }, [component]);

    if (!Resolved) {
        return <div key={componentKey} />;
    }

    return <Resolved key={componentKey} {...props} />;
}
