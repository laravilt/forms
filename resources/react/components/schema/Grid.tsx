import { resolveComponent } from '@laravilt/support/composables/registry';

export interface GridProps {
    columns: number | Record<string, number>;
    schema: Array<any>;
    modelValue?: Record<string, any>;
    disabled?: boolean;
    onUpdateModelValue?: (value: Record<string, any>) => void;
    [key: string]: any;
}

// Get component props, excluding value, modelValue, and disabled since we set them explicitly
const getComponentProps = (component: any) => {
    const { value, modelValue, disabled, ...rest } = component;
    return rest;
};

// Schema components (Tabs, Section, Grid) take the whole model, not a single field value
const schemaComponentTypes = ['tabs', 'section', 'grid'];
const isSchemaComponent = (component: any) => schemaComponentTypes.includes(component.component);

const toLaraviltName = (name: any) => {
    if (!name) return;
    return 'laravilt-' + String(name).replaceAll('_', '-');
};

const getColumnSpanClass = (child: any): string => {
    if (!child.columnSpan) return '';

    // Handle 'full' string value from columnSpanFull()
    if (child.columnSpan === 'full') {
        return 'col-span-full';
    }

    if (typeof child.columnSpan === 'number') {
        return `col-span-${child.columnSpan}`;
    }

    if (typeof child.columnSpan === 'object') {
        const classes: string[] = [];
        if (child.columnSpan.default) classes.push(`col-span-${child.columnSpan.default}`);
        if (child.columnSpan.sm) classes.push(`sm:col-span-${child.columnSpan.sm}`);
        if (child.columnSpan.md) classes.push(`md:col-span-${child.columnSpan.md}`);
        if (child.columnSpan.lg) classes.push(`lg:col-span-${child.columnSpan.lg}`);
        return classes.join(' ');
    }

    return '';
};

export default function Grid({ columns, schema, modelValue, disabled, onUpdateModelValue }: GridProps) {
    // Filter out hidden fields from schema
    const visibleSchema = (schema || []).filter((child) => child && child.hidden !== true);

    const updateValue = (name: string, value: any) => {
        const newValue = {
            ...(modelValue || {}),
            [name]: value,
        };
        onUpdateModelValue?.(newValue);
    };

    // Nested schema components emit the whole (merged) model
    const updateSchemaValue = (value: Record<string, any>) => {
        onUpdateModelValue?.({ ...(modelValue || {}), ...(value || {}) });
    };

    const gridClasses = (() => {
        const classes = ['grid', 'gap-6'];

        if (typeof columns === 'number') {
            classes.push(`grid-cols-1`);
            if (columns > 1) {
                classes.push(`md:grid-cols-${columns}`);
            }
        } else if (typeof columns === 'object' && columns) {
            if (columns.default) classes.push(`grid-cols-${columns.default}`);
            if (columns.sm) classes.push(`sm:grid-cols-${columns.sm}`);
            if (columns.md) classes.push(`md:grid-cols-${columns.md}`);
            if (columns.lg) classes.push(`lg:grid-cols-${columns.lg}`);
            if (columns.xl) classes.push(`xl:grid-cols-${columns.xl}`);
        }

        return classes.join(' ');
    })();

    if (visibleSchema.length === 0) {
        return null;
    }

    return (
        <div className={gridClasses}>
            {visibleSchema.map((child, index) => {
                const Component = resolveComponent(toLaraviltName(child.component));

                return (
                    <div key={child.name || child.id || index} className={getColumnSpanClass(child)}>
                        {Component ? (
                            <Component
                                {...getComponentProps(child)}
                                value={isSchemaComponent(child) ? undefined : modelValue?.[child.name]}
                                modelValue={isSchemaComponent(child) ? modelValue : modelValue?.[child.name]}
                                disabled={disabled || child.disabled}
                                onUpdateModelValue={(value: any) =>
                                    isSchemaComponent(child) ? updateSchemaValue(value) : updateValue(child.name, value)
                                }
                            />
                        ) : (
                            <div />
                        )}
                    </div>
                );
            })}
        </div>
    );
}
