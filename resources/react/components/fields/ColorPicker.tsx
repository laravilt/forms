import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { Plus, X } from 'lucide-react';
import { useEffect, useRef, useState, type ChangeEvent } from 'react';

type ColorValue = string | string[] | null;

export interface ColorPickerProps {
    name?: string;
    value?: ColorValue;
    modelValue?: ColorValue;
    label?: string;
    helperText?: string;
    required?: boolean;
    disabled?: boolean;
    swatches?: string[];
    showSwatches?: boolean;
    alpha?: boolean;
    format?: string;
    multiple?: boolean;
    maxItems?: number | null;
    minItems?: number | null;
    translations?: {
        placeholder?: string;
        swatches?: string;
        commonColors?: string;
    };
    onUpdateModelValue?: (value: ColorValue) => void;
    onUpdateValue?: (value: ColorValue) => void;
    [key: string]: any;
}

const EMPTY_SWATCHES: string[] = [];

// Common colors
const commonColors = [
    '#000000', '#ffffff', '#ef4444', '#f97316', '#f59e0b', '#eab308',
    '#84cc16', '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9',
    '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899',
    '#f43f5e', '#64748b',
];

export default function ColorPicker({
    name,
    value = null,
    modelValue = null,
    label,
    helperText,
    required,
    disabled,
    swatches = EMPTY_SWATCHES,
    showSwatches = false,
    multiple = false,
    maxItems = null,
    translations,
    onUpdateModelValue,
    onUpdateValue,
}: ColorPickerProps) {
    const [selectedColors, setSelectedColors] = useState<string[]>(() => {
        if (!multiple) return [];
        const initial = modelValue || value;
        if (Array.isArray(initial)) return [...(initial || [])];
        return initial ? [initial] : [];
    });
    const [selectedColor, setSelectedColor] = useState<string | null>(() => {
        if (multiple) return null;
        const initial = modelValue || value;
        if (Array.isArray(initial)) return initial?.[0] || null;
        return initial || null;
    });
    const [isOpen, setIsOpen] = useState(false);
    const [customColorInput, setCustomColorInput] = useState('');

    // Check if color is selected (for multiple mode)
    const isColorSelected = (color: string) => {
        if (multiple) {
            return selectedColors.includes(color);
        }
        return selectedColor === color;
    };

    // Check if can add more colors
    const canAddMore = !multiple ? true : maxItems === null ? true : selectedColors.length < maxItems;

    // Emit value for multiple mode
    const emitMultipleValue = (colors: string[]) => {
        const next = colors.length > 0 ? [...colors] : null;
        onUpdateModelValue?.(next);
        onUpdateValue?.(next);
    };

    // Toggle color in multiple mode
    const toggleColor = (color: string) => {
        const next = [...selectedColors];
        const index = next.indexOf(color);
        if (index > -1) {
            // Remove color
            next.splice(index, 1);
        } else if (canAddMore) {
            // Add color
            next.push(color);
        }
        setSelectedColors(next);
        emitMultipleValue(next);
    };

    // Select/toggle color
    const selectColor = (color: string) => {
        if (multiple) {
            toggleColor(color);
        } else {
            setSelectedColor(color);
            onUpdateModelValue?.(color);
            onUpdateValue?.(color);
            if (showSwatches) {
                setIsOpen(false);
            }
        }
    };

    // Add custom color
    const addCustomColor = () => {
        if (!customColorInput) return;
        const color = customColorInput.startsWith('#') ? customColorInput : `#${customColorInput}`;

        if (multiple) {
            if (!selectedColors.includes(color) && canAddMore) {
                const next = [...selectedColors, color];
                setSelectedColors(next);
                emitMultipleValue(next);
            }
        } else {
            selectColor(color);
        }
        setCustomColorInput('');
    };

    // Remove color from selection (multiple mode)
    const removeColor = (color: string) => {
        const index = selectedColors.indexOf(color);
        if (index > -1) {
            const next = [...selectedColors];
            next.splice(index, 1);
            setSelectedColors(next);
            emitMultipleValue(next);
        }
    };

    // Clear selection
    const clearSelection = () => {
        if (multiple) {
            setSelectedColors([]);
            emitMultipleValue([]);
        } else {
            setSelectedColor(null);
            onUpdateModelValue?.(null);
            onUpdateValue?.(null);
        }
    };

    // Update from native color input
    const updateFromColorInput = (event: ChangeEvent<HTMLInputElement>) => {
        const target = event.target as HTMLInputElement;
        setCustomColorInput(target.value);
    };

    // Display value for trigger button
    const displayValue = multiple
        ? selectedColors.length === 0
            ? translations?.placeholder || 'Select colors'
            : `${selectedColors.length} color(s) selected`
        : selectedColor || translations?.placeholder || 'Select color';

    // Watch for external value changes
    const externalValue = modelValue ?? value;
    const previousExternalValue = useRef(externalValue);
    useEffect(() => {
        if (previousExternalValue.current === externalValue) return;
        previousExternalValue.current = externalValue;

        if (multiple) {
            if (Array.isArray(externalValue)) {
                setSelectedColors([...externalValue]);
            } else if (externalValue) {
                setSelectedColors([externalValue]);
            } else {
                setSelectedColors([]);
            }
        } else {
            setSelectedColor(Array.isArray(externalValue) ? externalValue[0] || null : externalValue || null);
        }
    }, [externalValue, multiple]);

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
                    selectedColors.map((color, index) => <input key={index} type="hidden" name={`${name}[]`} value={color} />)
                ) : (
                    <input type="hidden" name={name} value={selectedColor || ''} />
                ))}

            {/* Selected colors display (multiple mode) */}
            {multiple && selectedColors.length > 0 && (
                <div className="flex flex-wrap gap-2 mb-2">
                    {selectedColors.map((color) => (
                        <div key={color} className="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-muted text-sm">
                            <div className="w-4 h-4 rounded border border-border" style={{ backgroundColor: color }} />
                            <span className="font-mono text-xs">{color}</span>
                            {!disabled && (
                                <button type="button" className="hover:text-destructive transition-colors" onClick={() => removeColor(color)}>
                                    <X className="h-3 w-3" />
                                </button>
                            )}
                        </div>
                    ))}
                </div>
            )}

            {/* Color Picker with Popover */}
            <Popover open={isOpen} onOpenChange={setIsOpen}>
                <PopoverTrigger asChild>
                    <Button type="button" variant="outline" disabled={disabled} className="w-full justify-between h-10 px-3 font-normal">
                        <div className="flex items-center gap-2">
                            {/* Color preview (single mode) */}
                            {!multiple ? (
                                <div className="w-6 h-6 rounded border border-border" style={{ backgroundColor: selectedColor || '#e5e7eb' }} />
                            ) : (
                                /* Multiple colors preview */
                                <div className="flex -space-x-1">
                                    {selectedColors.slice(0, 4).map((color, index) => (
                                        <div
                                            key={color}
                                            className="w-5 h-5 rounded-full border-2 border-background"
                                            style={{ backgroundColor: color, zIndex: 4 - index }}
                                        />
                                    ))}
                                    {selectedColors.length > 4 && (
                                        <div className="w-5 h-5 rounded-full border-2 border-background bg-muted flex items-center justify-center text-[10px]">
                                            +{selectedColors.length - 4}
                                        </div>
                                    )}
                                    {selectedColors.length === 0 && (
                                        <div className="w-5 h-5 rounded-full border-2 border-dashed border-muted-foreground" />
                                    )}
                                </div>
                            )}

                            {/* Display text */}
                            <span className={cn({ 'text-muted-foreground': multiple ? selectedColors.length === 0 : !selectedColor })}>
                                {displayValue}
                            </span>
                        </div>

                        {/* Clear button */}
                        {(multiple ? selectedColors.length > 0 : selectedColor) && !disabled && (
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
                    </Button>
                </PopoverTrigger>

                <PopoverContent className="w-[320px] p-4" align="start">
                    <div className="space-y-4">
                        {/* Custom color input */}
                        <div className="flex items-center gap-2">
                            <input
                                type="color"
                                value={customColorInput || '#000000'}
                                onChange={updateFromColorInput}
                                className="w-10 h-10 rounded cursor-pointer border border-border"
                                disabled={disabled}
                            />
                            <div className="flex-1">
                                <Input
                                    value={customColorInput}
                                    onChange={(e) => setCustomColorInput(e.target.value)}
                                    type="text"
                                    placeholder="#000000"
                                    className="font-mono text-sm"
                                    disabled={disabled}
                                    onKeyUp={(e) => {
                                        if (e.key === 'Enter') addCustomColor();
                                    }}
                                />
                            </div>
                            {multiple ? (
                                <Button type="button" size="sm" disabled={disabled || !customColorInput || !canAddMore} onClick={addCustomColor}>
                                    <Plus className="h-4 w-4" />
                                </Button>
                            ) : (
                                <Button type="button" size="sm" disabled={disabled || !customColorInput} onClick={addCustomColor}>
                                    Set
                                </Button>
                            )}
                        </div>

                        {/* Max items info */}
                        {multiple && maxItems ? (
                            <p className="text-xs text-muted-foreground">
                                {selectedColors.length} / {maxItems} colors selected
                            </p>
                        ) : null}

                        {/* Custom swatches (if provided) */}
                        {showSwatches && swatches && swatches.length > 0 && (
                            <div className="space-y-2">
                                <p className="text-xs text-muted-foreground">{translations?.swatches || 'Swatches'}</p>
                                <div className="grid grid-cols-6 gap-2">
                                    {swatches.map((swatch) => (
                                        <button
                                            key={swatch}
                                            type="button"
                                            className={cn('w-8 h-8 rounded border-2 transition-all hover:scale-110', {
                                                'border-primary ring-2 ring-primary ring-offset-1': isColorSelected(swatch),
                                                'border-border': !isColorSelected(swatch),
                                                'opacity-50 cursor-not-allowed': !canAddMore && !isColorSelected(swatch),
                                            })}
                                            style={{ backgroundColor: swatch }}
                                            disabled={disabled || (!canAddMore && !isColorSelected(swatch))}
                                            onClick={() => selectColor(swatch)}
                                            title={swatch}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Common colors (always shown) */}
                        <div className="space-y-2">
                            <p className="text-xs text-muted-foreground">{translations?.commonColors || 'Common colors'}</p>
                            <div className="grid grid-cols-8 gap-2">
                                {commonColors.map((color) => (
                                    <button
                                        key={color}
                                        type="button"
                                        className={cn('w-6 h-6 rounded border-2 transition-all hover:scale-110', {
                                            'border-primary ring-1 ring-primary': isColorSelected(color),
                                            'border-border': !isColorSelected(color),
                                            'opacity-50 cursor-not-allowed': !canAddMore && !isColorSelected(color),
                                        })}
                                        style={{ backgroundColor: color }}
                                        disabled={disabled || (!canAddMore && !isColorSelected(color))}
                                        onClick={() => selectColor(color)}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>
                </PopoverContent>
            </Popover>

            {/* Helper text */}
            {helperText && <p className="text-xs text-muted-foreground mt-1">{helperText}</p>}
        </div>
    );
}
