import { Clock } from 'lucide-react';
import { useEffect, useRef, useState, type ChangeEvent } from 'react';

export interface TimePickerProps {
    name?: string;
    value?: string | null;
    modelValue?: string | null;
    label?: string;
    helperText?: string;
    required?: boolean;
    disabled?: boolean;
    readonly?: boolean;
    hourCycle?: 12 | 24;
    granularity?: 'hour' | 'minute' | 'second';
    onUpdateModelValue?: (value: string | null) => void;
    onUpdateValue?: (value: string | null) => void;
    [key: string]: any;
}

export default function TimePicker({
    name,
    value = null,
    modelValue = null,
    label,
    helperText,
    required,
    disabled,
    readonly,
    granularity = 'minute',
    onUpdateModelValue,
    onUpdateValue,
}: TimePickerProps) {
    const [internalValue, setInternalValue] = useState<string>(modelValue || value || '');

    // Watch for prop changes
    const incoming = modelValue || value;
    const previousIncoming = useRef(incoming);
    useEffect(() => {
        if (previousIncoming.current === incoming) return;
        previousIncoming.current = incoming;
        setInternalValue(incoming || '');
    }, [incoming]);

    const updateTime = (event: ChangeEvent<HTMLInputElement>) => {
        const target = event.target as HTMLInputElement;
        const next = target.value || null;
        setInternalValue(next || '');
        onUpdateModelValue?.(next);
        onUpdateValue?.(next);
    };

    // Determine step based on granularity
    const step = granularity === 'second' ? 1 : granularity === 'minute' ? 60 : 3600; // hour

    return (
        <div className="w-full space-y-2">
            {/* Label */}
            {label && (
                <label htmlFor={name} className="text-sm font-medium block text-foreground">
                    {label}
                    {required && <span className="text-destructive ms-0.5">*</span>}
                </label>
            )}

            {/* Time Input */}
            <div className="relative">
                <input
                    id={name}
                    name={name}
                    type="time"
                    value={internalValue}
                    onChange={updateTime}
                    disabled={disabled}
                    readOnly={readonly}
                    required={required}
                    step={step}
                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                />
                <div className="pointer-events-none absolute inset-y-0 end-0 flex items-center pe-3">
                    <Clock className="h-4 w-4 text-muted-foreground" />
                </div>
            </div>

            {/* Helper text */}
            {helperText && <p className="text-xs text-muted-foreground mt-1">{helperText}</p>}
        </div>
    );
}
