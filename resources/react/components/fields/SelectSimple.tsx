import { cn } from '@/lib/utils';
import { resolveIcon } from '@laravilt/support/lib/icons';
import { useEffect, useRef, useState, type ChangeEvent } from 'react';

interface Option {
    value: string | number;
    label: string;
    disabled?: boolean;
}

type SelectValue = string | number | (string | number)[] | null;

export interface SelectSimpleProps {
    name?: string;
    value?: SelectValue;
    modelValue?: SelectValue;
    label?: string;
    options: Option[];
    placeholder?: string;
    multiple?: boolean;
    disabled?: boolean;
    required?: boolean;
    helperText?: string;
    native?: boolean;
    searchable?: boolean;
    prefixIcon?: string;
    suffixIcon?: string;
    prefixIconColor?: string;
    suffixIconColor?: string;
    onUpdateModelValue?: (value: SelectValue) => void;
    onUpdateValue?: (value: SelectValue) => void;
    [key: string]: any;
}

// Helper to get Tailwind color classes for icons
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

    return colorMap[color] || `text-${color}`;
};

const selectClass =
    'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

export default function SelectSimple({
    name,
    value,
    modelValue,
    label,
    options,
    placeholder = 'Select an option',
    multiple = false,
    disabled = false,
    required,
    helperText,
    prefixIcon,
    suffixIcon,
    prefixIconColor,
    suffixIconColor,
    onUpdateModelValue,
    onUpdateValue,
}: SelectSimpleProps) {
    const incoming = value ?? modelValue;

    // Internal state for the current value
    const [internalValue, setInternalValue] = useState<SelectValue>(incoming ?? (multiple ? [] : null));

    // Watch for prop changes and update internal state
    const previousIncoming = useRef(incoming);
    useEffect(() => {
        if (previousIncoming.current === incoming) return;
        previousIncoming.current = incoming;
        setInternalValue(incoming ?? (multiple ? [] : null));
    }, [incoming, multiple]);

    // Use internal value for display and form submission
    const currentValue = internalValue;

    const handleChange = (event: ChangeEvent<HTMLSelectElement>) => {
        const target = event.target as HTMLSelectElement;

        if (multiple) {
            const selectedOptions = Array.from(target.selectedOptions).map((opt) => opt.value);
            setInternalValue(selectedOptions);
            onUpdateModelValue?.(selectedOptions.length > 0 ? selectedOptions : null);
            onUpdateValue?.(selectedOptions.length > 0 ? selectedOptions : null);
        } else {
            const next = target.value === '' ? null : target.value;
            setInternalValue(next);
            onUpdateModelValue?.(next);
            onUpdateValue?.(next);
        }
    };

    const PrefixIcon = resolveIcon(prefixIcon);
    const SuffixIcon = resolveIcon(suffixIcon);
    const optionList: Option[] = options || [];

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
            {name && (
                <input
                    type="hidden"
                    name={name}
                    value={multiple ? (Array.isArray(currentValue) ? currentValue.join(',') : '') : String(currentValue || '')}
                />
            )}

            <div className="flex items-center gap-2 w-full">
                {/* Prefix icon */}
                {PrefixIcon && <PrefixIcon className={cn('h-4 w-4 shrink-0', getIconColorClass(prefixIconColor))} />}

                {/* Native select */}
                {multiple ? (
                    <select
                        id={name}
                        multiple={true}
                        disabled={disabled}
                        value={Array.isArray(currentValue) ? currentValue.map((v) => String(v)) : []}
                        className={selectClass}
                        onChange={handleChange}
                    >
                        {optionList.map((option) => (
                            <option key={option.value} value={option.value} disabled={option.disabled}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                ) : (
                    <select
                        id={name}
                        disabled={disabled}
                        value={Array.isArray(currentValue) ? '' : String(currentValue || '')}
                        className={selectClass}
                        onChange={handleChange}
                    >
                        <option value="">{placeholder}</option>
                        {optionList.map((option) => (
                            <option key={option.value} value={option.value} disabled={option.disabled}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                )}

                {/* Suffix icon */}
                {SuffixIcon && <SuffixIcon className={cn('h-4 w-4 shrink-0', getIconColorClass(suffixIconColor))} />}
            </div>

            {/* Helper text */}
            {helperText && <p className="text-xs text-muted-foreground mt-1">{helperText}</p>}
        </div>
    );
}
