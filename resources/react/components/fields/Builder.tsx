import { ChevronDown, ChevronsDown, ChevronsUp, ChevronUp, Copy, GripVertical, Plus, X } from 'lucide-react';
import { useEffect, useRef, useState, type ComponentType } from 'react';
import Sortable from 'sortablejs';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { useLatest } from '@laravilt/support/composables/hooks';
import { resolveIcon } from '@laravilt/support/lib/icons';

// Import form components
import Checkbox from './Checkbox';
import CheckboxList from './CheckboxList';
import CodeEditor from './CodeEditor';
import ColorPicker from './ColorPicker';
import DatePicker from './DatePicker';
import FileUpload from './FileUpload';
import KeyValue from './KeyValue';
import MarkdownEditor from './MarkdownEditor';
import Radio from './Radio';
import Repeater from './Repeater';
import RichEditor from './RichEditor';
import Select from './Select';
import SelectSimple from './SelectSimple';
import Slider from './Slider';
import TagsInput from './TagsInput';
import Textarea from './Textarea';
import TextInput from './TextInput';
import Toggle from './Toggle';
import ToggleButtons from './ToggleButtons';

interface BlockSchema {
    name: string;
    component: string;
    [key: string]: any;
}

interface BlockDefinition {
    name: string;
    label: string;
    icon?: string;
    schema: BlockSchema[];
    maxItems?: number;
    columns?: number;
}

export interface BlockItem {
    id: string;
    type: string;
    data: Record<string, any>;
}

export interface BuilderProps {
    name?: string;
    value?: BlockItem[];
    modelValue?: BlockItem[];
    label?: string;
    helperText?: string;
    required?: boolean;
    blocks?: BlockDefinition[];
    addActionLabel?: string;
    addActionAlignment?: 'start' | 'center' | 'end';
    addable?: boolean;
    deletable?: boolean;
    reorderable?: boolean;
    reorderableWithButtons?: boolean;
    reorderableWithDragAndDrop?: boolean;
    collapsible?: boolean;
    collapsed?: boolean;
    cloneable?: boolean;
    blockNumbers?: boolean;
    blockIcons?: boolean;
    blockPreviews?: boolean;
    minItems?: number;
    maxItems?: number;
    blockPickerColumns?: number | Record<string, number>;
    blockPickerWidth?: string;
    disabled?: boolean;
    prefixIcon?: string;
    suffixIcon?: string;
    prefixIconColor?: string;
    suffixIconColor?: string;
    onUpdateModelValue?: (value: BlockItem[]) => void;
    onUpdateValue?: (value: BlockItem[]) => void;
    [key: string]: any;
}

// Stable defaults (a fresh [] per render would re-trigger the value watcher)
const EMPTY_ITEMS: BlockItem[] = [];
const EMPTY_BLOCKS: BlockDefinition[] = [];

// Component mapping
const componentMap: Record<string, ComponentType<any>> = {
    TextInput,
    Textarea,
    Select,
    SelectSimple,
    Toggle,
    DatePicker,
    FileUpload,
    RichEditor,
    MarkdownEditor,
    ColorPicker,
    Slider,
    TagsInput,
    KeyValue,
    CodeEditor,
    Repeater,
    ToggleButtons,
    Radio,
    Checkbox,
    CheckboxList,
};

// Helper to get the component from schema (handles both PascalCase and snake_case)
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

// Helper to get Tailwind color classes for icons
const getIconColorClass = (color?: string): string => {
    if (!color) return 'text-muted-foreground';

    const colorMap: Record<string, string> = {
        primary: 'text-primary',
        secondary: 'text-secondary',
        success: 'text-green-600',
        danger: 'text-red-600',
        warning: 'text-yellow-600',
        info: 'text-blue-600',
        muted: 'text-muted-foreground',
        destructive: 'text-destructive',
    };

    return colorMap[color] || `text-${color}`;
};

// Generate unique ID
const generateId = () => {
    return `block_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
};

// Sortable moves the dragged node itself. Put it back so React re-orders the list from state
// (React has to own the DOM order, otherwise its keyed reconciliation and Sortable disagree).
const restoreSortableDom = (evt: Sortable.SortableEvent, oldIndex: number) => {
    const { item, from } = evt;
    from.removeChild(item);
    from.insertBefore(item, from.children[oldIndex] ?? null);
};

export default function Builder({
    name,
    value,
    modelValue = EMPTY_ITEMS,
    label,
    helperText,
    required,
    blocks = EMPTY_BLOCKS,
    addActionLabel = 'Add Block',
    addActionAlignment = 'center',
    addable = true,
    deletable = true,
    reorderable = true,
    reorderableWithButtons = false,
    reorderableWithDragAndDrop = true,
    collapsible = false,
    collapsed = false,
    cloneable = false,
    blockNumbers = true,
    blockIcons = false,
    minItems,
    maxItems,
    blockPickerColumns = 1,
    blockPickerWidth,
    disabled = false,
    prefixIcon,
    suffixIcon,
    prefixIconColor,
    suffixIconColor,
    onUpdateModelValue,
    onUpdateValue,
}: BuilderProps) {
    const externalValue = value ?? modelValue;

    // Internal state for builder items (ref mirror for the Sortable callback)
    const [internalItems, setInternalItemsState] = useState<BlockItem[]>(() =>
        Array.isArray(externalValue) ? [...externalValue] : [],
    );
    const internalItemsRef = useRef<BlockItem[]>(internalItems);
    const setInternalItems = (next: BlockItem[]) => {
        internalItemsRef.current = next;
        setInternalItemsState(next);
    };

    // Watch for prop changes and update internal state
    const hasWatchedValue = useRef(false);
    useEffect(() => {
        // The first (immediate) run matches the useState initialiser above.
        if (!hasWatchedValue.current) {
            hasWatchedValue.current = true;
            return;
        }

        setInternalItems(Array.isArray(externalValue) ? [...externalValue] : []);
    }, [externalValue]);

    const [collapsedItems, setCollapsedItems] = useState<Set<string>>(
        () => new Set(collapsed ? internalItems.map((item) => item.id) : []),
    );
    const [showBlockPicker, setShowBlockPicker] = useState(false);
    const sortableContainer = useRef<HTMLDivElement>(null);

    // Initialize collapsed state (re-applied whenever the items array changes, like the Vue watcher)
    useEffect(() => {
        if (collapsed && internalItems) {
            setCollapsedItems((previous) => {
                if (internalItems.every((item) => previous.has(item.id))) {
                    return previous;
                }

                const next = new Set(previous);
                internalItems.forEach((item) => next.add(item.id));
                return next;
            });
        }
    }, [internalItems]);

    const emitRef = useLatest({ onUpdateModelValue, onUpdateValue });
    const emitUpdate = (next: BlockItem[]) => {
        emitRef.current.onUpdateModelValue?.(next);
        emitRef.current.onUpdateValue?.(next);
    };

    // Get block definition
    const getBlockDefinition = (blockType: string): BlockDefinition | undefined => {
        return blocks.find((b) => b.name === blockType);
    };

    // Get block label
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const getBlockLabel = (item: BlockItem, index: number): string => {
        const block = getBlockDefinition(item.type);
        if (!block) return item.type;

        // If label is dynamic based on state
        const blockLabel: any = block.label;
        if (typeof blockLabel === 'function') {
            return blockLabel(item.data) || blockLabel(null);
        }

        return block.label;
    };

    // Check if block can be added
    const canAddBlock = (blockType: string): boolean => {
        const block = getBlockDefinition(blockType);
        if (!block || !block.maxItems) return true;

        const count = internalItems.filter((item) => item.type === blockType).length;
        return count < block.maxItems;
    };

    // Check if can add any block
    const canAddAnyBlock = (() => {
        if (!addable) return false;
        if (maxItems && internalItems.length >= maxItems) return false;
        if (!blocks || blocks.length === 0) return false;
        return blocks.some((block) => canAddBlock(block.name));
    })();

    // Add block
    const addBlock = (blockType: string) => {
        if (!canAddBlock(blockType)) return;

        const block = getBlockDefinition(blockType);
        if (!block) return;

        // Initialize data with default values from schema
        const data: Record<string, any> = {};
        block.schema.forEach((field) => {
            data[field.name] = field.defaultValue ?? null;
        });

        const newItem: BlockItem = {
            id: generateId(),
            type: blockType,
            data,
        };

        const newValue = [...internalItemsRef.current, newItem];
        setInternalItems(newValue);
        emitUpdate(newValue);
        setShowBlockPicker(false);

        // Auto-expand if not collapsed by default
        if (!collapsed && collapsible) {
            setCollapsedItems((previous) => {
                const next = new Set(previous);
                next.delete(newItem.id);
                return next;
            });
        } else if (collapsed && collapsible) {
            setCollapsedItems((previous) => new Set(previous).add(newItem.id));
        }
    };

    // Check if a block can be cloned (same limits as adding one)
    const canCloneBlock = (index: number): boolean => {
        const item = internalItems[index];
        if (!item) return false;
        if (maxItems && internalItems.length >= maxItems) return false;
        return canAddBlock(item.type);
    };

    // Clone block
    const cloneBlock = (index: number) => {
        if (!canCloneBlock(index)) return;

        const item = internalItemsRef.current[index];
        const clonedItem: BlockItem = {
            id: generateId(),
            type: item.type,
            data: { ...item.data },
        };

        const newValue = [...internalItemsRef.current];
        newValue.splice(index + 1, 0, clonedItem);
        setInternalItems(newValue);
        emitUpdate(newValue);
    };

    // Delete block
    const deleteBlock = (index: number) => {
        const newValue = [...internalItemsRef.current];
        const removed = newValue.splice(index, 1);
        if (removed[0]) {
            const removedId = removed[0].id;
            setCollapsedItems((previous) => {
                const next = new Set(previous);
                next.delete(removedId);
                return next;
            });
        }
        setInternalItems(newValue);
        emitUpdate(newValue);
    };

    // Move block up
    const moveUp = (index: number) => {
        if (index === 0) return;
        const newValue = [...internalItemsRef.current];
        [newValue[index - 1], newValue[index]] = [newValue[index], newValue[index - 1]];
        setInternalItems(newValue);
        emitUpdate(newValue);
    };

    // Move block down
    const moveDown = (index: number) => {
        if (index === internalItemsRef.current.length - 1) return;
        const newValue = [...internalItemsRef.current];
        [newValue[index], newValue[index + 1]] = [newValue[index + 1], newValue[index]];
        setInternalItems(newValue);
        emitUpdate(newValue);
    };

    // Toggle collapse
    const toggleCollapse = (id: string) => {
        setCollapsedItems((previous) => {
            const next = new Set(previous);
            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }
            return next;
        });
    };

    // Collapse all
    const collapseAll = () => {
        setCollapsedItems((previous) => {
            const next = new Set(previous);
            internalItemsRef.current.forEach((item) => next.add(item.id));
            return next;
        });
    };

    // Expand all
    const expandAll = () => {
        setCollapsedItems(new Set());
    };

    // Is collapsed
    const isCollapsed = (id: string): boolean => {
        return collapsedItems.has(id);
    };

    // Update field data
    const updateFieldData = (itemId: string, fieldName: string, fieldValue: any) => {
        const newValue = internalItemsRef.current.map((item) => {
            if (item.id === itemId) {
                return {
                    ...item,
                    data: {
                        ...item.data,
                        [fieldName]: fieldValue,
                    },
                };
            }
            return item;
        });
        setInternalItems(newValue);
        emitUpdate(newValue);
    };

    // Block picker grid columns class
    const blockPickerGridClass = (() => {
        const cols = blockPickerColumns;
        if (typeof cols === 'number') {
            return `grid-cols-${cols}`;
        }
        // Handle responsive columns
        const classes: string[] = [];
        Object.entries(cols).forEach(([breakpoint, count]) => {
            if (breakpoint === 'default') {
                classes.push(`grid-cols-${count}`);
            } else {
                classes.push(`${breakpoint}:grid-cols-${count}`);
            }
        });
        return classes.join(' ');
    })();

    // Block picker max width class
    const blockPickerMaxWidth = (() => {
        const widths: Record<string, string> = {
            xs: 'max-w-xs',
            sm: 'max-w-sm',
            md: 'max-w-md',
            lg: 'max-w-lg',
            xl: 'max-w-xl',
            '2xl': 'max-w-2xl',
            '3xl': 'max-w-3xl',
            '4xl': 'max-w-4xl',
            '5xl': 'max-w-5xl',
            '6xl': 'max-w-6xl',
            '7xl': 'max-w-7xl',
        };
        return blockPickerWidth ? widths[blockPickerWidth] : 'max-w-md';
    })();

    // Add action alignment class
    const addActionAlignmentClass =
        ({ start: 'justify-start', center: 'justify-center', end: 'justify-end' } as Record<string, string>)[addActionAlignment] ||
        'justify-center';

    // Initialize drag and drop (destroyed on unmount so StrictMode remounts don't stack instances)
    useEffect(() => {
        if (!reorderableWithDragAndDrop || !sortableContainer.current) {
            return;
        }

        const sortable = Sortable.create(sortableContainer.current, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'opacity-50',
            onEnd: (event) => {
                const { oldIndex, newIndex } = event;
                if (oldIndex !== undefined && newIndex !== undefined && oldIndex !== newIndex) {
                    restoreSortableDom(event, oldIndex);

                    const newValue = [...internalItemsRef.current];
                    const [moved] = newValue.splice(oldIndex, 1);
                    newValue.splice(newIndex, 0, moved);
                    setInternalItems(newValue);
                    emitUpdate(newValue);
                }
            },
        });

        return () => {
            sortable.destroy();
        };
    }, []);

    const PrefixIcon = resolveIcon(prefixIcon);
    const SuffixIcon = resolveIcon(suffixIcon);

    return (
        <div className="w-full space-y-3">
            {/* Label */}
            {label && (
                <label htmlFor={name} className="text-sm font-medium block text-foreground">
                    {label}{' '}
                    {required && <span className="text-destructive ms-0.5">*</span>}
                </label>
            )}

            {/* Hidden input for form submission */}
            {name && <input type="hidden" name={name} value={JSON.stringify(internalItems)} />}

            {/* Header with icons and collapse all/expand all */}
            {(PrefixIcon || SuffixIcon || (collapsible && internalItems.length > 0)) && (
                <div className="flex items-center justify-between">
                    {PrefixIcon && <PrefixIcon className={cn('h-4 w-4', getIconColorClass(prefixIconColor))} />}

                    {collapsible && internalItems.length > 0 && (
                        <div className="flex items-center gap-1">
                            <Button type="button" variant="ghost" size="sm" onClick={collapseAll}>
                                <ChevronsDown className="h-4 w-4 mr-1" />
                                Collapse All
                            </Button>
                            <Button type="button" variant="ghost" size="sm" onClick={expandAll}>
                                <ChevronsUp className="h-4 w-4 mr-1" />
                                Expand All
                            </Button>
                        </div>
                    )}

                    {SuffixIcon && <SuffixIcon className={cn('h-4 w-4', getIconColorClass(suffixIconColor))} />}
                </div>
            )}

            {/* Blocks list */}
            <div ref={sortableContainer} className="space-y-2">
                {internalItems.map((item, index) => {
                    const blockDefinition = getBlockDefinition(item.type);
                    const BlockIcon = blockIcons ? resolveIcon(blockDefinition?.icon) : null;

                    return (
                        <Collapsible key={item.id} open={!isCollapsed(item.id)} asChild>
                            <Card>
                                <CardHeader className="p-3">
                                    <div className="flex items-center gap-2">
                                        {/* Drag handle */}
                                        {reorderable && reorderableWithDragAndDrop && (
                                            <button
                                                type="button"
                                                className="drag-handle cursor-grab hover:text-primary shrink-0"
                                                disabled={disabled}
                                            >
                                                <GripVertical className="h-4 w-4" />
                                            </button>
                                        )}

                                        {/* Block number */}
                                        {blockNumbers && <span className="text-xs text-muted-foreground shrink-0">{index + 1}.</span>}

                                        {/* Block icon */}
                                        {BlockIcon && <BlockIcon className="h-4 w-4 shrink-0 text-muted-foreground" />}

                                        {/* Block label / Collapse trigger */}
                                        {collapsible ? (
                                            <CollapsibleTrigger asChild>
                                                <button
                                                    type="button"
                                                    className="flex-1 flex items-center gap-2 text-sm font-medium text-start hover:text-primary transition-colors"
                                                    onClick={() => toggleCollapse(item.id)}
                                                >
                                                    <ChevronDown
                                                        className={cn('h-4 w-4 transition-transform shrink-0', {
                                                            '-rotate-90': isCollapsed(item.id),
                                                        })}
                                                    />
                                                    {getBlockLabel(item, index)}
                                                </button>
                                            </CollapsibleTrigger>
                                        ) : (
                                            <span className="flex-1 text-sm font-medium">{getBlockLabel(item, index)}</span>
                                        )}

                                        {/* Action buttons */}
                                        <div className="flex items-center gap-1 shrink-0">
                                            {/* Move up button */}
                                            {reorderableWithButtons && index > 0 && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    className="h-8 w-8 p-0"
                                                    disabled={disabled}
                                                    onClick={() => moveUp(index)}
                                                >
                                                    <ChevronUp className="h-4 w-4" />
                                                </Button>
                                            )}

                                            {/* Move down button */}
                                            {reorderableWithButtons && index < internalItems.length - 1 && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    className="h-8 w-8 p-0"
                                                    disabled={disabled}
                                                    onClick={() => moveDown(index)}
                                                >
                                                    <ChevronDown className="h-4 w-4" />
                                                </Button>
                                            )}

                                            {/* Clone button */}
                                            {cloneable && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    className="h-8 w-8 p-0"
                                                    disabled={disabled || !canCloneBlock(index)}
                                                    onClick={() => cloneBlock(index)}
                                                >
                                                    <Copy className="h-4 w-4" />
                                                </Button>
                                            )}

                                            {/* Delete button */}
                                            {deletable && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    className="h-8 w-8 p-0 hover:text-destructive"
                                                    disabled={disabled || !!(minItems && internalItems.length <= minItems)}
                                                    onClick={() => deleteBlock(index)}
                                                >
                                                    <X className="h-4 w-4" />
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                </CardHeader>

                                <CollapsibleContent asChild>
                                    <CardContent className="p-3 pt-0">
                                        {/* Render block fields dynamically */}
                                        <div
                                            className={cn('grid gap-4', {
                                                'grid-cols-1': !blockDefinition?.columns,
                                                'grid-cols-2': blockDefinition?.columns === 2,
                                                'grid-cols-3': blockDefinition?.columns === 3,
                                            })}
                                        >
                                            {(blockDefinition?.schema || []).map((field) => {
                                                const FieldComponent = getComponent(field.component);

                                                return FieldComponent ? (
                                                    <FieldComponent
                                                        key={field.name}
                                                        modelValue={item.data[field.name]}
                                                        {...field}
                                                        onUpdateModelValue={(updated: any) => updateFieldData(item.id, field.name, updated)}
                                                    />
                                                ) : null;
                                            })}
                                        </div>
                                    </CardContent>
                                </CollapsibleContent>
                            </Card>
                        </Collapsible>
                    );
                })}
            </div>

            {/* Empty state */}
            {internalItems.length === 0 && (
                <div className="border-2 border-dashed border-border rounded-lg p-8 text-center">
                    <p className="text-sm text-muted-foreground">No blocks added yet</p>
                </div>
            )}

            {/* Add block button */}
            {canAddAnyBlock && (
                <div className={cn('flex', addActionAlignmentClass)}>
                    <Popover open={showBlockPicker} onOpenChange={setShowBlockPicker}>
                        <PopoverTrigger asChild>
                            <Button type="button" variant="outline" disabled={disabled}>
                                <Plus className="h-4 w-4 mr-2" />
                                {addActionLabel}
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent className={blockPickerMaxWidth} align="start">
                            <div className="space-y-2">
                                <h4 className="font-medium text-sm">Select a block type</h4>
                                <div className={cn('grid gap-2', blockPickerGridClass)}>
                                    {blocks.map((block) => {
                                        const PickerIcon = resolveIcon(block.icon);

                                        return (
                                            <Button
                                                key={block.name}
                                                type="button"
                                                variant="outline"
                                                className="justify-start h-auto py-3"
                                                disabled={!canAddBlock(block.name)}
                                                onClick={() => addBlock(block.name)}
                                            >
                                                <div className="flex flex-col items-start gap-1 w-full">
                                                    <div className="flex items-center gap-2">
                                                        {PickerIcon && <PickerIcon className="h-4 w-4" />}
                                                        <span className="font-medium text-sm">{block.label}</span>
                                                    </div>
                                                    {block.maxItems ? (
                                                        <span className="text-xs text-muted-foreground">
                                                            {internalItems.filter((i) => i.type === block.name).length} / {block.maxItems} used
                                                        </span>
                                                    ) : null}
                                                </div>
                                            </Button>
                                        );
                                    })}
                                </div>
                            </div>
                        </PopoverContent>
                    </Popover>
                </div>
            )}

            {/* Validation messages */}
            {minItems && internalItems.length < minItems ? (
                <div className="text-sm text-destructive">
                    At least {minItems} block{minItems === 1 ? '' : 's'} required
                </div>
            ) : null}
            {maxItems && internalItems.length >= maxItems ? (
                <div className="text-sm text-muted-foreground">Maximum of {maxItems} blocks reached</div>
            ) : null}

            {/* Helper text */}
            {helperText && <p className="text-xs text-muted-foreground mt-1">{helperText}</p>}
        </div>
    );
}
