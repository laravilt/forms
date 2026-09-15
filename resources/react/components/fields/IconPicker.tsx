import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { resolveIcon } from '@laravilt/support/lib/icons';
import { Search, X } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';

type IconValue = string | string[] | null;

export interface IconPickerProps {
    name?: string;
    value?: IconValue;
    modelValue?: IconValue;
    label?: string;
    helperText?: string;
    required?: boolean;
    icons?: string[];
    searchable?: boolean;
    gridColumns?: number;
    showIconName?: boolean;
    disabled?: boolean;
    prefixIcon?: string;
    suffixIcon?: string;
    prefixIconColor?: string;
    suffixIconColor?: string;
    multiple?: boolean;
    maxItems?: number | null;
    minItems?: number | null;
    translations?: {
        placeholder?: string;
        searchPlaceholder?: string;
        noIconsFound?: string;
    };
    onUpdateModelValue?: (value: IconValue) => void;
    onUpdateValue?: (value: IconValue) => void;
    [key: string]: any;
}

const EMPTY_ICONS: string[] = [];

// Get Tailwind color classes for icons
const getIconColorClass = (color?: string) => {
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

    return colorMap[color] || 'text-muted-foreground';
};

// Grid columns class
const gridColumnClasses: Record<number, string> = {
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

export default function IconPicker({
    name,
    value,
    modelValue = null,
    label,
    helperText,
    required,
    icons = EMPTY_ICONS,
    searchable = true,
    gridColumns = 8,
    showIconName = true,
    disabled = false,
    prefixIcon,
    suffixIcon,
    prefixIconColor,
    suffixIconColor,
    multiple = false,
    maxItems = null,
    translations,
    onUpdateModelValue,
    onUpdateValue,
}: IconPickerProps) {
    const [searchQuery, setSearchQuery] = useState('');
    const [isOpen, setIsOpen] = useState(false);

    // Initialize for single mode
    const [selectedIcon, setSelectedIcon] = useState<string | null>(() => {
        if (multiple) return null;
        const initial = modelValue || value;
        if (Array.isArray(initial)) return initial?.[0] || null;
        return initial || null;
    });

    // Initialize for multiple mode
    const [selectedIcons, setSelectedIcons] = useState<string[]>(() => {
        if (!multiple) return [];
        const initial = modelValue || value;
        if (Array.isArray(initial)) return [...(initial || [])];
        return initial ? [initial] : [];
    });

    // Filter icons based on search query
    const filteredIcons = useMemo(() => {
        if (!searchable || !searchQuery) {
            return icons;
        }

        const query = searchQuery.toLowerCase();
        return icons.filter((icon) => icon.toLowerCase().includes(query));
    }, [icons, searchable, searchQuery]);

    const gridClass = gridColumnClasses[gridColumns] || 'grid-cols-8';

    // Check if icon is selected
    const isIconSelected = (icon: string) => {
        if (multiple) {
            return selectedIcons.includes(icon);
        }
        return selectedIcon === icon;
    };

    // Check if can add more icons
    const canAddMore = !multiple ? true : maxItems === null ? true : selectedIcons.length < maxItems;

    // Emit value for multiple mode
    const emitMultipleValue = (next: string[]) => {
        const emitted = next.length > 0 ? [...next] : null;
        onUpdateModelValue?.(emitted);
        onUpdateValue?.(emitted);
    };

    // Toggle icon in multiple mode
    const toggleIcon = (icon: string) => {
        const next = [...selectedIcons];
        const index = next.indexOf(icon);
        if (index > -1) {
            // Remove icon
            next.splice(index, 1);
        } else if (canAddMore) {
            // Add icon
            next.push(icon);
        }
        setSelectedIcons(next);
        emitMultipleValue(next);
    };

    // Select/toggle icon
    const selectIcon = (icon: string) => {
        if (multiple) {
            toggleIcon(icon);
        } else {
            setSelectedIcon(icon);
            onUpdateModelValue?.(icon);
            onUpdateValue?.(icon);
            setIsOpen(false);
            setSearchQuery('');
        }
    };

    // Remove icon from selection (multiple mode)
    const removeIcon = (icon: string) => {
        const index = selectedIcons.indexOf(icon);
        if (index > -1) {
            const next = [...selectedIcons];
            next.splice(index, 1);
            setSelectedIcons(next);
            emitMultipleValue(next);
        }
    };

    // Clear selection
    const clearSelection = () => {
        if (multiple) {
            setSelectedIcons([]);
            emitMultipleValue([]);
        } else {
            setSelectedIcon(null);
            onUpdateModelValue?.(null);
            onUpdateValue?.(null);
        }
    };

    // Display value for trigger button
    const displayValue = multiple
        ? selectedIcons.length === 0
            ? translations?.placeholder || 'Select icons'
            : `${selectedIcons.length} icon(s) selected`
        : selectedIcon || translations?.placeholder || 'Select an icon';

    // Get selected icon component (single mode)
    const SelectedIconComponent = resolveIcon(selectedIcon || undefined);

    // Watch for external value changes
    const externalValue = modelValue ?? value;
    const previousExternalValue = useRef(externalValue);
    useEffect(() => {
        if (previousExternalValue.current === externalValue) return;
        previousExternalValue.current = externalValue;

        if (multiple) {
            if (Array.isArray(externalValue)) {
                setSelectedIcons([...externalValue]);
            } else if (externalValue) {
                setSelectedIcons([externalValue]);
            } else {
                setSelectedIcons([]);
            }
        } else {
            setSelectedIcon(Array.isArray(externalValue) ? externalValue[0] || null : externalValue || null);
        }
    }, [externalValue, multiple]);

    const PrefixIcon = resolveIcon(prefixIcon);
    const SuffixIcon = resolveIcon(suffixIcon);

    return (
        <div className="w-full space-y-2">
            {/* Label */}
            {label && (
                <label htmlFor={name} className="text-sm font-medium block text-foreground">
                    {label}
                    {required && <span className="text-destructive ms-0.5">*</span>}
                </label>
            )}

            {/* Hidden input for form submission */}
            {name &&
                (multiple ? (
                    selectedIcons.map((icon, index) => <input key={index} type="hidden" name={`${name}[]`} value={icon} />)
                ) : (
                    <input type="hidden" name={name} value={selectedIcon || ''} />
                ))}

            {/* Selected icons display (multiple mode) */}
            {multiple && selectedIcons.length > 0 && (
                <div className="flex flex-wrap gap-2 mb-2">
                    {selectedIcons.map((icon) => {
                        const IconComponent = resolveIcon(icon);
                        return (
                            <div key={icon} className="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-muted text-sm">
                                {IconComponent && <IconComponent className="h-4 w-4 shrink-0" />}
                                <span className="text-xs">{icon}</span>
                                {!disabled && (
                                    <button type="button" className="hover:text-destructive transition-colors" onClick={() => removeIcon(icon)}>
                                        <X className="h-3 w-3" />
                                    </button>
                                )}
                            </div>
                        );
                    })}
                </div>
            )}

            {/* Icon Picker Trigger */}
            <Popover open={isOpen} onOpenChange={setIsOpen}>
                <PopoverTrigger asChild>
                    <Button
                        type="button"
                        variant="outline"
                        disabled={disabled}
                        className={cn('w-full justify-between h-10 px-3 font-normal', {
                            'text-muted-foreground': multiple ? selectedIcons.length === 0 : !selectedIcon,
                        })}
                    >
                        <div className="flex items-center gap-2">
                            {/* Prefix Icon */}
                            {PrefixIcon && <PrefixIcon className={cn('h-4 w-4 shrink-0', getIconColorClass(prefixIconColor))} />}

                            {/* Selected Icon (single mode) */}
                            {!multiple && SelectedIconComponent && <SelectedIconComponent className="h-4 w-4 shrink-0" />}

                            {/* Multiple icons preview */}
                            {multiple && (
                                <div className="flex -space-x-1">
                                    {selectedIcons.slice(0, 3).map((icon, index) => {
                                        const IconComponent = resolveIcon(icon);
                                        return (
                                            <div
                                                key={icon}
                                                className="w-6 h-6 rounded-full bg-muted border-2 border-background flex items-center justify-center"
                                                style={{ zIndex: 3 - index }}
                                            >
                                                {IconComponent && <IconComponent className="h-3 w-3" />}
                                            </div>
                                        );
                                    })}
                                    {selectedIcons.length > 3 && (
                                        <div className="w-6 h-6 rounded-full border-2 border-background bg-muted flex items-center justify-center text-[10px]">
                                            +{selectedIcons.length - 3}
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* Placeholder or selected icon name */}
                            <span className="truncate">{displayValue}</span>
                        </div>

                        <div className="flex items-center gap-1">
                            {/* Clear button */}
                            {(multiple ? selectedIcons.length > 0 : selectedIcon) && !disabled && (
                                <button
                                    type="button"
                                    className="hover:text-destructive transition-colors"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        clearSelection();
                                    }}
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            )}

                            {/* Suffix Icon */}
                            {SuffixIcon && <SuffixIcon className={cn('h-4 w-4 shrink-0', getIconColorClass(suffixIconColor))} />}
                        </div>
                    </Button>
                </PopoverTrigger>

                <PopoverContent className="w-[400px] p-0" align="start">
                    <div className="flex flex-col">
                        {/* Search input */}
                        {searchable && (
                            <div className="p-3 border-b border-border">
                                <div className="relative">
                                    <Search className="absolute start-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        value={searchQuery}
                                        onChange={(e) => setSearchQuery(e.target.value)}
                                        type="text"
                                        placeholder={translations?.searchPlaceholder || 'Search icons...'}
                                        className="ps-9 h-9"
                                    />
                                </div>
                            </div>
                        )}

                        {/* Max items info */}
                        {multiple && maxItems ? (
                            <div className="px-3 pt-2">
                                <p className="text-xs text-muted-foreground">
                                    {selectedIcons.length} / {maxItems} icons selected
                                </p>
                            </div>
                        ) : null}

                        {/* Icons grid */}
                        <div className="p-3 max-h-[300px] overflow-y-auto">
                            {filteredIcons.length === 0 ? (
                                <div className="text-center py-6 text-sm text-muted-foreground">
                                    {translations?.noIconsFound || 'No icons found'}
                                </div>
                            ) : (
                                <div className={cn('grid gap-2', gridClass)}>
                                    {filteredIcons.map((icon) => {
                                        const IconComponent = resolveIcon(icon);
                                        return (
                                            <button
                                                key={icon}
                                                type="button"
                                                className={cn('flex flex-col items-center justify-center p-2 rounded-md hover:bg-accent transition-colors', {
                                                    'bg-accent ring-2 ring-primary': isIconSelected(icon),
                                                    'opacity-50 cursor-not-allowed': !canAddMore && !isIconSelected(icon),
                                                })}
                                                disabled={disabled || (!canAddMore && !isIconSelected(icon))}
                                                onClick={() => selectIcon(icon)}
                                            >
                                                {IconComponent && (
                                                    <IconComponent
                                                        className={cn('h-5 w-5 mb-1', {
                                                            'text-primary': isIconSelected(icon),
                                                            'text-muted-foreground': !isIconSelected(icon),
                                                        })}
                                                    />
                                                )}
                                                {showIconName && (
                                                    <span
                                                        className={cn('text-[10px] text-center truncate w-full', {
                                                            'text-primary font-medium': isIconSelected(icon),
                                                            'text-muted-foreground': !isIconSelected(icon),
                                                        })}
                                                    >
                                                        {icon}
                                                    </span>
                                                )}
                                            </button>
                                        );
                                    })}
                                </div>
                            )}
                        </div>
                    </div>
                </PopoverContent>
            </Popover>

            {/* Helper text */}
            {helperText && <p className="text-xs text-muted-foreground mt-1">{helperText}</p>}
        </div>
    );
}
