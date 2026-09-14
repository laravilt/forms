import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { useLocalization } from '@laravilt/support/composables/useLocalization';
import { resolveIcon } from '@laravilt/support/lib/icons';
import { GripVertical, Plus, X } from 'lucide-react';
import { useState } from 'react';

interface KeyValuePair {
    key: string;
    value: string;
}

type KeyValueModel = Record<string, string> | KeyValuePair[];

export interface KeyValueProps {
    name?: string;
    value?: KeyValueModel;
    modelValue?: KeyValueModel;
    label?: string;
    helperText?: string;
    required?: boolean;
    keyLabel?: string;
    valueLabel?: string;
    addButtonLabel?: string;
    reorderable?: boolean;
    deletable?: boolean;
    prefixIcon?: string;
    suffixIcon?: string;
    prefixIconColor?: string;
    suffixIconColor?: string;
    onUpdateModelValue?: (value: KeyValueModel) => void;
    onUpdateValue?: (value: KeyValueModel) => void;
    [key: string]: any;
}

const EMPTY_MODEL: Record<string, string> = {};

// Helper to get Tailwind color classes for icons
const getIconColorClass = (color?: string) => {
    if (!color) return 'text-muted-foreground';

    // Map common color names to Tailwind classes
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

export default function KeyValue({
    name,
    modelValue = EMPTY_MODEL,
    label,
    helperText,
    required,
    keyLabel = 'Key',
    valueLabel = 'Value',
    addButtonLabel = 'Add Item',
    reorderable = false,
    deletable = true,
    prefixIcon,
    suffixIcon,
    prefixIconColor,
    suffixIconColor,
    onUpdateModelValue,
    onUpdateValue,
}: KeyValueProps) {
    // Initialize localization
    const { trans } = useLocalization();

    // Translated labels
    const translatedKeyLabel = keyLabel !== 'Key' ? keyLabel : trans('key_value.key_label');
    const translatedValueLabel = valueLabel !== 'Value' ? valueLabel : trans('key_value.value_label');
    const translatedAddButtonLabel = addButtonLabel !== 'Add Item' ? addButtonLabel : trans('key_value.add_button_label');

    // Convert modelValue to array format for easier manipulation
    const [pairs, setPairs] = useState<KeyValuePair[]>(() =>
        Array.isArray(modelValue) ? modelValue : Object.entries(modelValue).map(([key, value]) => ({ key, value })),
    );

    const emitUpdate = (nextPairs: KeyValuePair[]) => {
        // Emit as object if original was object, array otherwise
        if (Array.isArray(modelValue)) {
            onUpdateModelValue?.(nextPairs);
            onUpdateValue?.(nextPairs);
        } else {
            const obj: Record<string, string> = {};
            nextPairs.forEach((pair) => {
                if (pair.key) {
                    obj[pair.key] = pair.value;
                }
            });
            onUpdateModelValue?.(obj);
            onUpdateValue?.(obj);
        }
    };

    const addPair = () => {
        const next = [...pairs, { key: '', value: '' }];
        setPairs(next);
        emitUpdate(next);
    };

    const removePair = (index: number) => {
        const next = [...pairs];
        next.splice(index, 1);
        setPairs(next);
        emitUpdate(next);
    };

    const updatePair = (index: number, field: 'key' | 'value', value: string) => {
        const next = pairs.map((pair, i) => (i === index ? { ...pair, [field]: value } : pair));
        setPairs(next);
        emitUpdate(next);
    };

    const PrefixIcon = resolveIcon(prefixIcon);
    const SuffixIcon = resolveIcon(suffixIcon);

    return (
        <div className="w-full space-y-3">
            {/* Label */}
            {label && (
                <label htmlFor={name} className="text-sm font-medium block text-foreground">
                    {label}
                    {required && <span className="text-destructive ms-0.5">*</span>}
                </label>
            )}

            {/* Hidden input for form submission */}
            {name && <input type="hidden" name={name} value={JSON.stringify(modelValue)} />}

            {/* Header with icons */}
            {(PrefixIcon || suffixIcon) && (
                <div className="flex items-center justify-between">
                    {PrefixIcon && <PrefixIcon className={cn('h-4 w-4', getIconColorClass(prefixIconColor))} />}
                    {SuffixIcon && <SuffixIcon className={cn('h-4 w-4', getIconColorClass(suffixIconColor))} />}
                </div>
            )}

            {/* Pairs list */}
            <div className="space-y-2">
                {pairs.map((pair, index) => (
                    <div key={index} className="flex items-center gap-2">
                        {/* Drag handle (if reorderable) */}
                        {reorderable && (
                            <button type="button" className="cursor-grab hover:text-primary shrink-0">
                                <GripVertical className="h-4 w-4" />
                            </button>
                        )}

                        {/* Key input */}
                        <Input
                            value={pair.key}
                            placeholder={translatedKeyLabel}
                            className="flex-1"
                            onChange={(e) => updatePair(index, 'key', e.target.value)}
                        />

                        {/* Value input */}
                        <Input
                            value={pair.value}
                            placeholder={translatedValueLabel}
                            className="flex-1"
                            onChange={(e) => updatePair(index, 'value', e.target.value)}
                        />

                        {/* Delete button */}
                        {deletable && (
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                className="shrink-0 h-9 w-9 p-0"
                                onClick={() => removePair(index)}
                            >
                                <X className="h-4 w-4" />
                            </Button>
                        )}
                    </div>
                ))}
            </div>

            {/* Add button */}
            <Button type="button" variant="outline" size="sm" className="w-full" onClick={addPair}>
                <Plus className="h-4 w-4 mr-2" />
                {translatedAddButtonLabel}
            </Button>

            {/* Helper text */}
            {helperText && <p className="text-xs text-muted-foreground mt-1">{helperText}</p>}
        </div>
    );
}
