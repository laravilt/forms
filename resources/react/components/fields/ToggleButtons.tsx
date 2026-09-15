import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { resolveIcon } from '@laravilt/support/lib/icons';
import { useEffect, useRef, useState } from 'react';

interface Option {
    value: string | number;
    label: string;
    disabled?: boolean;
    icon?: string;
}

type ToggleValue = string | number | (string | number)[];

export interface ToggleButtonsProps {
    name?: string;
    value?: ToggleValue;
    modelValue?: ToggleValue;
    options: Option[];
    multiple?: boolean;
    inline?: boolean;
    disabled?: boolean;
    prefixIcon?: string;
    suffixIcon?: string;
    prefixIconColor?: string;
    suffixIconColor?: string;
    onUpdateModelValue?: (value: ToggleValue) => void;
    onUpdateValue?: (value: ToggleValue) => void;
    [key: string]: any;
}

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

    return colorMap[color] || 'text-muted-foreground';
};

export default function ToggleButtons({
    name,
    value,
    modelValue,
    options,
    multiple = false,
    inline = true,
    disabled = false,
    prefixIcon,
    suffixIcon,
    prefixIconColor,
    suffixIconColor,
    onUpdateModelValue,
    onUpdateValue,
}: ToggleButtonsProps) {
    const incoming = value ?? modelValue;

    // Internal state for the current value
    const [internalValue, setInternalValue] = useState<ToggleValue>(incoming ?? (multiple ? [] : ''));

    // Watch for prop changes and update internal state
    const previousIncoming = useRef(incoming);
    useEffect(() => {
        if (previousIncoming.current === incoming) return;
        previousIncoming.current = incoming;
        setInternalValue(incoming ?? (multiple ? [] : ''));
    }, [incoming, multiple]);

    // Use internal value for display and form submission
    const currentValue = internalValue;

    const isSelected = (optionValue: string | number) => {
        if (multiple && Array.isArray(internalValue)) {
            return internalValue.includes(optionValue);
        }
        return internalValue === optionValue;
    };

    const handleToggle = (optionValue: string | number) => {
        if (multiple) {
            const currentValues = Array.isArray(internalValue) ? internalValue : [];
            const newValue = currentValues.includes(optionValue)
                ? currentValues.filter((v) => v !== optionValue)
                : [...currentValues, optionValue];

            setInternalValue(newValue);
            onUpdateModelValue?.(newValue);
            onUpdateValue?.(newValue);
        } else {
            setInternalValue(optionValue);
            onUpdateModelValue?.(optionValue);
            onUpdateValue?.(optionValue);
        }
    };

    const PrefixIcon = resolveIcon(prefixIcon);
    const SuffixIcon = resolveIcon(suffixIcon);
    const optionList: Option[] = Array.isArray(options) ? options : Object.values(options || {});

    return (
        <div className="w-full space-y-2">
            {/* Hidden input for form submission */}
            {/* Multiple mode submits an array (`name[]`), matching the array the component emits */}
            {name &&
                (multiple ? (
                    (Array.isArray(currentValue) ? currentValue : []).map((item) => (
                        <input key={String(item)} type="hidden" name={`${name}[]`} value={String(item)} />
                    ))
                ) : (
                    <input type="hidden" name={name} value={String(currentValue ?? '')} />
                ))}

            {/* Header icons */}
            {(PrefixIcon || SuffixIcon) && (
                <div className="flex items-center justify-between">
                    {PrefixIcon && <PrefixIcon className={cn('h-4 w-4', getIconColorClass(prefixIconColor))} />}
                    {SuffixIcon && <SuffixIcon className={cn('h-4 w-4', getIconColorClass(suffixIconColor))} />}
                </div>
            )}

            {/* Button group */}
            <div className={cn('inline-flex rounded-md shadow-sm', inline ? 'flex-wrap gap-1' : 'flex-col gap-1')} role="group">
                {optionList.map((option) => {
                    const OptionIcon = resolveIcon(option.icon);
                    return (
                        <Button
                            key={option.value}
                            type="button"
                            variant={isSelected(option.value) ? 'default' : 'outline'}
                            disabled={disabled || option.disabled}
                            className="gap-2"
                            onClick={() => handleToggle(option.value)}
                        >
                            {OptionIcon && <OptionIcon className="h-4 w-4" />}
                            {option.label}
                        </Button>
                    );
                })}
            </div>
        </div>
    );
}
