import { ChevronDown, Copy, GripVertical, Plus, X } from 'lucide-react';
import { Fragment, useEffect, useMemo, useRef, useState, type ComponentType } from 'react';
import Sortable from 'sortablejs';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { SchemaContext, useSchemaContext, type SchemaContextValue } from '@laravilt/support/composables/contexts';
import { useLatest } from '@laravilt/support/composables/hooks';
import { useLocalization } from '@laravilt/support/composables/useLocalization';

import Checkbox from './Checkbox';
import CheckboxList from './CheckboxList';
import CodeEditor from './CodeEditor';
import ColorPicker from './ColorPicker';
import DatePicker from './DatePicker';
import DateTimePicker from './DateTimePicker';
import FileUpload from './FileUpload';
import Hidden from './Hidden';
import IconPicker from './IconPicker';
import KeyValue from './KeyValue';
import MarkdownEditor from './MarkdownEditor';
import NumberField from './NumberField';
import PinInput from './PinInput';
import Radio from './Radio';
import RateInput from './RateInput';
import RichEditor from './RichEditor';
import Select from './Select';
import Slider from './Slider';
import TagsInput from './TagsInput';
import Textarea from './Textarea';
import TextInput from './TextInput';
import TimePicker from './TimePicker';
import Toggle from './Toggle';
import ToggleButtons from './ToggleButtons';

// Scoped <style> block of Repeater.vue. Vue scoped these rules to the Repeater's own elements;
// the `hover:border-primary/50` / `active:cursor-grabbing` utilities only exist on Repeater items
// and drag handles, so they keep the rules from leaking to other sortable lists (e.g. Builder).
const REPEATER_STYLES = `
/* Smooth transitions for drag and drop */
.sortable-ghost.hover\\:border-primary\\/50 {
  opacity: 0.4;
}

.sortable-drag.hover\\:border-primary\\/50 {
  opacity: 0;
}

/* Drag handle cursor */
.drag-handle.active\\:cursor-grabbing:active {
  cursor: grabbing !important;
}
`;

interface FieldSchema {
    component: string;
    name: string;
    isLive?: boolean;
    isLazy?: boolean;
    liveDebounce?: number;
    [key: string]: any;
}

export interface RepeaterProps {
    name?: string;
    value?: any[];
    modelValue?: any[];
    label?: string;
    helperText?: string;
    required?: boolean;
    schema?: FieldSchema[];
    addButtonLabel?: string;
    deleteButtonLabel?: string;
    reorderable?: boolean;
    collapsible?: boolean;
    cloneable?: boolean;
    deletable?: boolean;
    minItems?: number;
    maxItems?: number;
    disabled?: boolean;
    prefixIcon?: string;
    suffixIcon?: string;
    prefixIconColor?: string;
    suffixIconColor?: string;
    columns?: number;
    // Text labels for i18n support
    itemLabel?: string;
    itemsLabel?: string;
    minLabel?: string;
    maxLabel?: string;
    onUpdateModelValue?: (value: any[]) => void;
    onUpdateValue?: (value: any[]) => void;
    [key: string]: any;
}

// Stable defaults (a fresh [] per render would re-trigger the value watcher)
const EMPTY_ITEMS: any[] = [];
const EMPTY_SCHEMA: FieldSchema[] = [];

// Component mapping for dynamic rendering
const componentMap: Record<string, ComponentType<any>> = {
    TextInput: TextInput,
    Textarea: Textarea,
    Select: Select,
    Toggle: Toggle,
    ToggleButtons: ToggleButtons,
    Slider: Slider,
    DatePicker: DatePicker,
    DateTimePicker: DateTimePicker,
    TimePicker: TimePicker,
    FileUpload: FileUpload,
    CheckboxList: CheckboxList,
    Checkbox: Checkbox,
    Radio: Radio,
    TagsInput: TagsInput,
    KeyValue: KeyValue,
    ColorPicker: ColorPicker,
    RichEditor: RichEditor,
    CodeEditor: CodeEditor,
    Hidden: Hidden,
    NumberField: NumberField,
    IconPicker: IconPicker,
    MarkdownEditor: MarkdownEditor,
    PinInput: PinInput,
    RateInput: RateInput,
};

// Helper to get the component from schema
const getComponent = (componentName: string): ComponentType<any> | null => {
    // Handle both snake_case (from PHP) and PascalCase
    if (componentMap[componentName]) {
        return componentMap[componentName];
    }

    // Convert snake_case to PascalCase (e.g., "text_input" => "TextInput")
    const pascalCase = componentName
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join('');

    return componentMap[pascalCase] || null;
};

const generateItemId = () => `item-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

const GRID_COLUMNS: Record<number, string> = {
    1: 'grid-cols-1',
    2: 'grid-cols-2',
    3: 'grid-cols-3',
    4: 'grid-cols-4',
    5: 'grid-cols-5',
    6: 'grid-cols-6',
    7: 'grid-cols-7',
    8: 'grid-cols-8',
    9: 'grid-cols-9',
    10: 'grid-cols-10',
    11: 'grid-cols-11',
    12: 'grid-cols-12',
};

const COLUMN_SPANS: Record<number, string> = {
    1: 'col-span-1',
    2: 'col-span-2',
    3: 'col-span-3',
    4: 'col-span-4',
    5: 'col-span-5',
    6: 'col-span-6',
    7: 'col-span-7',
    8: 'col-span-8',
    9: 'col-span-9',
    10: 'col-span-10',
    11: 'col-span-11',
    12: 'col-span-12',
};

// Sortable moves the dragged node itself. Put it back so React re-orders the list from state
// (React has to own the DOM order, otherwise its keyed reconciliation and Sortable disagree).
const restoreSortableDom = (evt: Sortable.SortableEvent, oldIndex: number) => {
    const { item, from } = evt;
    from.removeChild(item);
    from.insertBefore(item, from.children[oldIndex] ?? null);
};

// Get field props with reactive flags and value stripped
// This prevents child components from triggering their own reactive updates
// The Repeater handles reactivity at its level via updateFieldValue
// We strip 'value' and 'modelValue' so our explicit bindings take precedence
const getFieldProps = (field: FieldSchema): Record<string, any> => {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const { isLive, isLazy, liveDebounce, label, helperText, value, modelValue, defaultValue, ...rest } = field;
    return rest;
};

export default function Repeater({
    name,
    value,
    modelValue = EMPTY_ITEMS,
    label,
    helperText,
    required,
    schema = EMPTY_SCHEMA,
    addButtonLabel = 'Add',
    deleteButtonLabel = 'Delete',
    reorderable = false,
    collapsible = false,
    cloneable = false,
    deletable = true,
    minItems,
    maxItems,
    disabled = false,
    columns = undefined,
    itemLabel = 'Item',
    itemsLabel = 'item(s)',
    minLabel = 'Min:',
    maxLabel = 'Max:',
    onUpdateModelValue,
    onUpdateValue,
}: RepeaterProps) {
    // Initialize localization
    const { trans } = useLocalization();

    // Inject parent context for reactive fields
    const parentContext = useSchemaContext();
    const parentFormController = parentContext.formController;
    const parentFormMethod = parentContext.formMethod;
    const parentGetFormData = parentContext.getFormData;

    // Compute grid column classes based on columns prop
    const gridColumnsClass = !columns ? '' : GRID_COLUMNS[columns] || `grid-cols-${columns}`;

    // Get column span class for a field
    const getColumnSpanClass = (field: FieldSchema): string => {
        if (!columns) return '';
        const colSpan = field.columnSpan || field.colSpan || 1;
        if (colSpan === 'full' || field.columnSpanFull) {
            return `col-span-full`;
        }
        return COLUMN_SPANS[colSpan] || `col-span-${colSpan}`;
    };

    // Translated labels
    const translatedAddButtonLabel = addButtonLabel !== 'Add' ? addButtonLabel : trans('forms::forms.repeater.add_button_label');
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const translatedDeleteButtonLabel =
        deleteButtonLabel !== 'Delete' ? deleteButtonLabel : trans('forms::forms.repeater.delete_button_label');
    const translatedItemLabel = itemLabel !== 'Item' ? itemLabel : trans('forms::forms.repeater.item_label');
    const translatedItemsLabel = itemsLabel !== 'item(s)' ? itemsLabel : trans('forms::forms.repeater.items_label');
    const translatedMinLabel = minLabel !== 'Min:' ? minLabel : trans('forms::forms.repeater.min_label');
    const translatedMaxLabel = maxLabel !== 'Max:' ? maxLabel : trans('forms::forms.repeater.max_label');

    const externalValue = value ?? modelValue;

    // Internal state for repeater items (ref mirror = Vue's always-current `internalItems.value`)
    const [internalItems, setInternalItemsState] = useState<any[]>(() => (Array.isArray(externalValue) ? [...externalValue] : []));
    const internalItemsRef = useRef<any[]>(internalItems);
    const setInternalItems = (next: any[]) => {
        internalItemsRef.current = next;
        setInternalItemsState(next);
    };

    // Version counter to force re-mounting the item fields after reactive updates
    const [itemsVersion, setItemsVersion] = useState(0);

    // Flag to prevent the value watcher from re-initializing during internal updates
    const isUpdating = useRef(false);

    // Track pending reactive updates to prevent duplicates
    const pendingReactiveUpdate = useRef<string | null>(null);

    // Generate stable unique IDs for each item
    const [itemIds, setItemIdsState] = useState<string[]>(() =>
        (Array.isArray(externalValue) ? externalValue : []).map(() => generateItemId()),
    );
    const itemIdsRef = useRef<string[]>(itemIds);
    const setItemIds = (next: string[]) => {
        itemIdsRef.current = next;
        setItemIdsState(next);
    };

    // Keyed by stable item id so expanded state follows the item through remove/clone/reorder
    const [openItems, setOpenItems] = useState<Set<string>>(() => new Set());
    const itemsContainer = useRef<HTMLDivElement>(null);

    const emitRef = useLatest({ onUpdateModelValue, onUpdateValue });
    const emitUpdate = (next: any[]) => {
        emitRef.current.onUpdateModelValue?.(next);
        emitRef.current.onUpdateValue?.(next);
    };

    const releaseUpdatingFlag = () => {
        setTimeout(() => {
            isUpdating.current = false;
        }, 100);
    };

    // Watch for prop changes and update internal state
    const hasWatchedValue = useRef(false);
    useEffect(() => {
        // First run is the Vue `immediate` call; state was already initialised from the same value above.
        const isInitialLoad = !hasWatchedValue.current;
        hasWatchedValue.current = true;

        // Skip if we're in the middle of an internal update
        if (isUpdating.current || isInitialLoad) {
            return;
        }

        const newItems = Array.isArray(externalValue) ? [...externalValue] : [];

        // If the length changed, it's likely an external update (initial load, add, remove)
        // If length is same but we're not updating, it could be a form reset or external change
        const lengthChanged = newItems.length !== internalItemsRef.current.length;

        if (lengthChanged) {
            setInternalItems(newItems);

            let ids = [...itemIdsRef.current];
            // Generate IDs for new items
            while (ids.length < newItems.length) {
                ids.push(generateItemId());
            }
            // Trim IDs if items were removed
            if (ids.length > newItems.length) {
                ids = ids.slice(0, newItems.length);
            }
            setItemIds(ids);
        }
    }, [externalValue]);

    // Setup drag-and-drop sorting after mount, cleanup on unmount
    useEffect(() => {
        if (!reorderable || !itemsContainer.current) {
            return;
        }

        const sortableInstance = new Sortable(itemsContainer.current, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: (evt) => {
                const oldIndex = evt.oldIndex;
                const newIndex = evt.newIndex;

                if (oldIndex !== undefined && newIndex !== undefined && oldIndex !== newIndex) {
                    restoreSortableDom(evt, oldIndex);

                    // Set flag to prevent watch interference
                    isUpdating.current = true;

                    try {
                        const newValue = [...internalItemsRef.current];
                        const [movedItem] = newValue.splice(oldIndex, 1);
                        newValue.splice(newIndex, 0, movedItem);
                        setInternalItems(newValue);

                        // Also reorder IDs
                        const ids = [...itemIdsRef.current];
                        const [movedId] = ids.splice(oldIndex, 1);
                        ids.splice(newIndex, 0, movedId);
                        setItemIds(ids);

                        emitUpdate(newValue);
                    } finally {
                        releaseUpdatingFlag();
                    }
                }
            },
        });

        return () => {
            sortableInstance.destroy();
        };
    }, []);

    // Check if we can add more items
    const canAddItem = (() => {
        if (disabled) return false;
        if (maxItems === undefined || maxItems === null) return true;
        return internalItems.length < maxItems;
    })();

    // Check if we can delete items
    const canDeleteItem = (() => {
        if (disabled || !deletable) return false;
        if (minItems === undefined || minItems === null) return true;
        return internalItems.length > minItems;
    })();

    const addItem = () => {
        if (!canAddItem) return;

        // Set flag to prevent watch interference
        isUpdating.current = true;

        try {
            // Create new item with default values from schema
            const newItem: Record<string, any> = {};
            schema.forEach((field) => {
                newItem[field.name] = field.defaultValue ?? null;
            });

            const newValue = [...internalItemsRef.current, newItem];
            const newId = generateItemId();
            setInternalItems(newValue);
            setItemIds([...itemIdsRef.current, newId]);
            emitUpdate(newValue);

            // Auto-expand new item if collapsible
            if (collapsible) {
                setOpenItems((previous) => new Set(previous).add(newId));
            }
        } finally {
            releaseUpdatingFlag();
        }
    };

    const removeItem = (index: number) => {
        if (!canDeleteItem) return;

        // Set flag to prevent watch interference
        isUpdating.current = true;

        try {
            const newValue = [...internalItemsRef.current];
            newValue.splice(index, 1);
            setInternalItems(newValue);
            const ids = [...itemIdsRef.current];
            const [removedId] = ids.splice(index, 1);
            setItemIds(ids);
            emitUpdate(newValue);
            setOpenItems((previous) => {
                const next = new Set(previous);
                next.delete(removedId);
                return next;
            });
        } finally {
            releaseUpdatingFlag();
        }
    };

    const cloneItem = (index: number) => {
        if (!canAddItem) return;

        // Set flag to prevent watch interference
        isUpdating.current = true;

        try {
            const itemToClone = internalItemsRef.current[index];
            const clonedItem = JSON.parse(JSON.stringify(itemToClone));
            const newValue = [...internalItemsRef.current];
            newValue.splice(index + 1, 0, clonedItem);
            setInternalItems(newValue);
            const ids = [...itemIdsRef.current];
            const clonedId = generateItemId();
            ids.splice(index + 1, 0, clonedId);
            setItemIds(ids);
            emitUpdate(newValue);

            // Auto-expand cloned item if collapsible
            if (collapsible) {
                setOpenItems((previous) => new Set(previous).add(clonedId));
            }
        } finally {
            releaseUpdatingFlag();
        }
    };

    const toggleItem = (index: number) => {
        const id = itemIdsRef.current[index];
        if (!id) return;
        setOpenItems((previous) => {
            const next = new Set(previous);
            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }
            return next;
        });
    };

    const isOpen = (index: number) => {
        return !collapsible || openItems.has(itemIds[index]);
    };

    // Trigger reactive field update for live/lazy fields inside Repeater
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const triggerReactiveFieldUpdate = async (itemIndex: number, fieldName: string, field: FieldSchema) => {
        // Skip if no form controller is configured
        if (!parentFormController) {
            return;
        }

        // Create a unique key for this update to prevent duplicates
        const updateKey = `${itemIndex}.${fieldName}`;

        // Skip if we already have a pending update for this field
        if (pendingReactiveUpdate.current === updateKey) {
            return;
        }

        // Mark this update as pending
        pendingReactiveUpdate.current = updateKey;

        try {
            // Get full form data from parent
            const fullFormData = parentGetFormData ? parentGetFormData() : {};

            // Update the repeater data in the form data
            fullFormData[name || 'items'] = internalItemsRef.current;

            // The changed field path is: repeaterName.itemIndex.fieldName
            const changedFieldPath = `${name || 'items'}.${itemIndex}.${fieldName}`;

            const payload = {
                controller: parentFormController,
                method: parentFormMethod || 'getSchema',
                data: fullFormData,
                changed_field: changedFieldPath,
                repeater_name: name || 'items',
                repeater_index: itemIndex,
                field_name: fieldName,
            };

            const response = await fetch('/reactive-fields/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                throw new Error('Failed to update reactive fields');
            }

            const result = await response.json();

            // If backend returned updated data, apply it to the repeater item
            if (result.data) {
                const repeaterData = result.data[name || 'items'];

                if (repeaterData && Array.isArray(repeaterData) && repeaterData[itemIndex]) {
                    // Update only the specific item that was affected
                    const newItems = [...internalItemsRef.current];
                    const updatedItem = {
                        ...newItems[itemIndex],
                        ...repeaterData[itemIndex],
                    };

                    // Update the item in place
                    newItems[itemIndex] = updatedItem;

                    // Assign a new array reference
                    setInternalItems([...newItems]);

                    // Increment version to re-mount the item fields with the new values
                    setItemsVersion((version) => version + 1);
                }
            }

            // Note: We intentionally do NOT call updateSchema for Repeater reactive updates
            // because we're only updating data within the item, not changing the schema structure.
            // Calling updateSchema would cause a full re-render and scroll issues.
        } catch (error) {
            console.error('[Repeater] Error updating reactive fields:', error);
        } finally {
            // Clear the pending update flag
            pendingReactiveUpdate.current = null;
        }
    };

    // Update field value within an item
    const updateFieldValue = async (itemIndex: number, fieldName: string, fieldValue: any, field?: FieldSchema) => {
        // Set flag to prevent watch from re-initializing during our update
        isUpdating.current = true;

        try {
            const newValue = [...internalItemsRef.current];
            newValue[itemIndex] = {
                ...newValue[itemIndex],
                [fieldName]: fieldValue,
            };
            setInternalItems(newValue);

            // Check if this field is reactive (live/lazy) and trigger update
            if (field && (field.isLive || field.isLazy)) {
                // For reactive fields, we trigger the update but DON'T emit to parent
                // This prevents parent re-render and component remount
                // The data is tracked internally and will be submitted via hidden input
                await triggerReactiveFieldUpdate(itemIndex, fieldName, field);
            } else {
                // For non-reactive fields, emit normally so parent stays in sync
                emitUpdate(newValue);
            }
        } finally {
            // Reset flag after a short delay
            releaseUpdatingFlag();
        }
    };

    // Get field value from item
    const getFieldValue = (itemIndex: number, fieldName: string) => {
        return internalItems[itemIndex]?.[fieldName];
    };

    // Provide a null formController to child components
    // This prevents them from making their own reactive API calls
    // The Repeater handles reactivity internally
    const childSchemaContext = useMemo<SchemaContextValue>(
        () => ({ ...parentContext, formController: undefined, formMethod: undefined }),
        [parentContext],
    );

    return (
        <SchemaContext.Provider value={childSchemaContext}>
            <div className="w-full space-y-2">
                <style href="laravilt-forms-repeater" precedence="default">
                    {REPEATER_STYLES}
                </style>

                {/* Label */}
                {label && (
                    <label htmlFor={name} className="text-sm font-medium block text-foreground">
                        {label}{' '}
                        {required && <span className="text-destructive ms-0.5">*</span>}
                    </label>
                )}

                {/* Hidden input for form submission */}
                {name && <input type="hidden" name={name} value={JSON.stringify(internalItems)} />}

                {/* Items list */}
                <div ref={itemsContainer} className="space-y-2">
                    {internalItems.map((_item, index) => (
                        <div
                            key={itemIds[index] || `fallback-${index}`}
                            className="group relative rounded-lg border border-border bg-card transition-colors hover:border-primary/50"
                        >
                            {/* Header */}
                            <div className="flex items-center gap-2 border-b border-border bg-muted/30 px-3 py-2.5">
                                {/* Drag handle */}
                                {reorderable && (
                                    <button
                                        type="button"
                                        className="drag-handle cursor-grab active:cursor-grabbing text-muted-foreground hover:text-foreground transition-colors shrink-0"
                                        disabled={disabled}
                                    >
                                        <GripVertical className="h-4 w-4" />
                                    </button>
                                )}

                                {/* Collapse trigger */}
                                {collapsible ? (
                                    <button
                                        type="button"
                                        className="flex-1 flex items-center gap-2 text-sm font-medium text-start hover:text-foreground transition-colors"
                                        onClick={() => toggleItem(index)}
                                    >
                                        <ChevronDown className={cn('h-4 w-4 transition-transform', { '-rotate-90': !isOpen(index) })} />
                                        <span>
                                            {translatedItemLabel} {index + 1}
                                        </span>
                                    </button>
                                ) : (
                                    <span className="flex-1 text-sm font-medium text-foreground">
                                        {translatedItemLabel} {index + 1}
                                    </span>
                                )}

                                {/* Action buttons */}
                                <div className="flex items-center gap-1">
                                    {/* Clone button */}
                                    {cloneable && (
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            className="h-7 w-7 p-0 text-muted-foreground hover:text-foreground"
                                            disabled={!canAddItem}
                                            onClick={() => cloneItem(index)}
                                        >
                                            <Copy className="h-3.5 w-3.5" />
                                        </Button>
                                    )}

                                    {/* Delete button */}
                                    {deletable && (
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            className="h-7 w-7 p-0 text-muted-foreground hover:text-destructive"
                                            disabled={!canDeleteItem}
                                            onClick={() => removeItem(index)}
                                        >
                                            <X className="h-3.5 w-3.5" />
                                        </Button>
                                    )}
                                </div>
                            </div>

                            {/* Content (collapsible) */}
                            {isOpen(index) && (
                                <div className={cn('p-4', columns ? `grid gap-4 ${gridColumnsClass}` : 'space-y-4')}>
                                    {/* Render schema fields dynamically */}
                                    {schema.map((field) => {
                                        const FieldComponent = getComponent(field.component);
                                        const fieldValue = getFieldValue(index, field.name);
                                        const handleUpdate = (updated: any) => updateFieldValue(index, field.name, updated, field);

                                        // Hidden fields - render without wrapper/label
                                        if (field.hidden || field.component === 'Hidden' || field.component === 'hidden') {
                                            return (
                                                <Fragment key={field.name}>
                                                    {FieldComponent && (
                                                        <FieldComponent
                                                            key={`${field.name}-${index}-${itemsVersion}`}
                                                            id={`${field.name}-${index}`}
                                                            value={fieldValue}
                                                            modelValue={fieldValue}
                                                            {...getFieldProps(field)}
                                                            onUpdateModelValue={handleUpdate}
                                                        />
                                                    )}
                                                </Fragment>
                                            );
                                        }

                                        // Visible fields - render with wrapper/label
                                        return (
                                            <Fragment key={field.name}>
                                                <div className={cn('space-y-1.5', getColumnSpanClass(field))}>
                                                    {field.label && (
                                                        <label
                                                            htmlFor={`${field.name}-${index}`}
                                                            className="text-sm font-medium block text-foreground"
                                                        >
                                                            {field.label}{' '}
                                                            {field.required && <span className="text-destructive ms-0.5">*</span>}
                                                        </label>
                                                    )}

                                                    {FieldComponent && (
                                                        <FieldComponent
                                                            key={`${field.name}-${index}-${itemsVersion}`}
                                                            id={`${field.name}-${index}`}
                                                            value={fieldValue}
                                                            modelValue={fieldValue}
                                                            {...getFieldProps(field)}
                                                            onUpdateModelValue={handleUpdate}
                                                        />
                                                    )}

                                                    {field.helperText && (
                                                        <p className="text-xs text-muted-foreground mt-1">{field.helperText}</p>
                                                    )}
                                                </div>
                                            </Fragment>
                                        );
                                    })}
                                </div>
                            )}
                        </div>
                    ))}
                </div>

                {/* Add button */}
                <Button
                    type="button"
                    variant="outline"
                    className="w-full justify-center gap-2 border-dashed hover:border-solid hover:bg-accent/50"
                    disabled={!canAddItem}
                    onClick={addItem}
                >
                    <Plus className="h-4 w-4" />
                    <span>{translatedAddButtonLabel}</span>
                </Button>

                {/* Validation hints */}
                {minItems || maxItems ? (
                    <div className="flex items-center gap-1 text-xs text-muted-foreground px-1">
                        {minItems ? (
                            <span>
                                {translatedMinLabel} {minItems}
                            </span>
                        ) : null}
                        {minItems && maxItems ? <span>•</span> : null}
                        {maxItems ? (
                            <span>
                                {translatedMaxLabel} {maxItems}
                            </span>
                        ) : null}
                        {internalItems.length > 0 && (
                            <span className="ml-auto">
                                {internalItems.length} {translatedItemsLabel}
                            </span>
                        )}
                    </div>
                ) : null}

                {/* Helper text */}
                {helperText && <p className="text-xs text-muted-foreground mt-1">{helperText}</p>}
            </div>
        </SchemaContext.Provider>
    );
}
