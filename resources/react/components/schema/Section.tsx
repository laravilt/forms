import { cn } from '@/lib/utils';
import { resolveIcon } from '@laravilt/support/lib/icons';
import { ChevronDown } from 'lucide-react';
import { lazy, Suspense, useState, type ComponentType, type LazyExoticComponent } from 'react';
import CheckboxList from '../fields/CheckboxList';

export interface SectionProps {
    heading?: string;
    description?: string;
    icon?: string;
    collapsible?: boolean;
    collapsed?: boolean;
    schema?: Array<any>;
    modelValue?: Record<string, any>;
    disabled?: boolean;
    onUpdateModelValue?: (value: Record<string, any>) => void;
    [key: string]: any;
}

type AnyComponent = ComponentType<any> | LazyExoticComponent<ComponentType<any>>;

// Map component types to their React components (Vue: defineAsyncComponent → React.lazy)
const componentMap: Record<string, AnyComponent> = {
    // Schema layout components
    grid: lazy(() => import('./Grid')),

    // Form field components
    text_input: lazy(() => import('../fields/TextInput')),
    textarea: lazy(() => import('../fields/Textarea')),
    select: lazy(() => import('../fields/Select')),
    checkbox: lazy(() => import('../fields/Checkbox')),
    checkbox_list: CheckboxList,
    radio: lazy(() => import('../fields/Radio')),
    toggle: lazy(() => import('../fields/Toggle')),
    toggle_buttons: lazy(() => import('../fields/ToggleButtons')),
    hidden: lazy(() => import('../fields/Hidden')),
    date_picker: lazy(() => import('../fields/DatePicker')),
    time_picker: lazy(() => import('../fields/TimePicker')),
    date_time_picker: lazy(() => import('../fields/DateTimePicker')),
    date_range_picker: lazy(() => import('../fields/DateRangePicker')),
    color_picker: lazy(() => import('../fields/ColorPicker')),
    file_upload: lazy(() => import('../fields/FileUpload')),
    rich_editor: lazy(() => import('../fields/RichEditor')),
    markdown_editor: lazy(() => import('../fields/MarkdownEditor')),
    code_editor: lazy(() => import('../fields/CodeEditor')),
    slider: lazy(() => import('../fields/Slider')),
    tags_input: lazy(() => import('../fields/TagsInput')),
    key_value: lazy(() => import('../fields/KeyValue')),
    repeater: lazy(() => import('../fields/Repeater')),
    builder: lazy(() => import('../fields/Builder')),
    icon_picker: lazy(() => import('../fields/IconPicker')),
    number_field: lazy(() => import('../fields/NumberField')),
    pin_input: lazy(() => import('../fields/PinInput')),
    rate_input: lazy(() => import('../fields/RateInput')),
};

const getComponent = (component: any): AnyComponent | null => {
    const type = component.component || 'div';
    return componentMap[type] || null;
};

// Get component props, excluding value, modelValue, and disabled since we set them explicitly
const getComponentProps = (component: any) => {
    const { value, modelValue, disabled, ...rest } = component;
    return rest;
};

const isSchemaComponent = (component: any) => {
    const schemaComponents = ['grid'];
    return schemaComponents.includes(component.component);
};

export default function Section({
    heading,
    description,
    icon,
    collapsible,
    collapsed,
    schema,
    modelValue,
    disabled,
    onUpdateModelValue,
}: SectionProps) {
    const [isCollapsed, setIsCollapsed] = useState<boolean>(collapsed || false);

    const toggleCollapse = () => {
        if (collapsible) {
            setIsCollapsed((current) => !current);
        }
    };

    const handleComponentUpdate = (component: any, value: any) => {
        console.log('[Section] handleComponentUpdate', {
            componentName: component.name,
            componentType: component.component,
            isSchemaComponent: isSchemaComponent(component),
            value,
            currentModelValue: modelValue,
        });

        if (isSchemaComponent(component)) {
            // For schema components, merge the entire value object
            const newValue = { ...modelValue, ...value };
            console.log('[Section] Emitting schema component update:', newValue);
            onUpdateModelValue?.(newValue);
        } else {
            // For regular fields, update the specific field
            const newValue = { ...modelValue, [component.name]: value };
            console.log('[Section] Emitting field update:', newValue);
            onUpdateModelValue?.(newValue);
        }
    };

    const IconComponent = icon ? resolveIcon(icon) : null;

    return (
        <div className="bg-card text-card-foreground rounded-xl border shadow-sm">
            {/* Section Header */}
            {heading && (
                <header className={cn('px-6 py-4 transition-all duration-200', isCollapsed ? '' : 'border-b')}>
                    <div
                        className={cn('flex items-center gap-3', collapsible ? 'cursor-pointer select-none' : '')}
                        onClick={() => collapsible && toggleCollapse()}
                    >
                        {icon && IconComponent && (
                            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary flex-shrink-0">
                                <IconComponent className="h-5 w-5" />
                            </div>
                        )}
                        <div className="flex-1 min-w-0">
                            <h3 className="leading-none font-semibold">{heading}</h3>
                            {description && <p className="mt-1 text-sm text-muted-foreground">{description}</p>}
                        </div>
                        {collapsible && (
                            <ChevronDown
                                className={cn(
                                    'h-4 w-4 text-muted-foreground transition-transform duration-200 ease-out flex-shrink-0',
                                    isCollapsed ? '-rotate-90 rtl:rotate-90' : '',
                                )}
                            />
                        )}
                    </div>
                </header>
            )}

            {/* Section Content with smooth collapse */}
            <div className="grid transition-all duration-200 ease-out" style={{ gridTemplateRows: isCollapsed ? '0fr' : '1fr' }}>
                <div className="overflow-hidden">
                    <div className="p-6 space-y-6">
                        {(schema || []).map((component: any, index: number) => {
                            const key = component.name || component.id || index;
                            const Component = getComponent(component);

                            if (!Component) {
                                return <div key={key} />;
                            }

                            return (
                                <Suspense key={key} fallback={null}>
                                    <Component
                                        {...getComponentProps(component)}
                                        value={isSchemaComponent(component) ? undefined : modelValue?.[component.name]}
                                        modelValue={isSchemaComponent(component) ? modelValue : modelValue?.[component.name]}
                                        disabled={disabled || component.disabled}
                                        onUpdateModelValue={(value: any) => handleComponentUpdate(component, value)}
                                    />
                                </Suspense>
                            );
                        })}
                    </div>
                </div>
            </div>
        </div>
    );
}
