import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Minus, Plus } from 'lucide-react';
import { useEffect, useMemo, useState, type KeyboardEvent } from 'react';

export interface NumberFieldProps {
    name?: string;
    value?: number | null;
    modelValue?: number | null;
    label?: string;
    helperText?: string;
    required?: boolean;
    disabled?: boolean;
    readonly?: boolean;
    min?: number | null;
    max?: number | null;
    step?: number;
    formatOptions?: Record<string, any>;
    locale?: string | null;
    prefix?: string | null;
    suffix?: string | null;
    onUpdateModelValue?: (value: number | null) => void;
    onUpdateValue?: (value: number | null) => void;
    [key: string]: any;
}

const EMPTY_FORMAT_OPTIONS: Record<string, any> = {};

const decimalsOf = (n: number) => {
    const text = String(n);
    const index = text.indexOf('.');
    return index === -1 ? 0 : text.length - index - 1;
};

/**
 * React twin of the radix-vue NumberField (Root/Input/Increment/Decrement), which has no Radix React equivalent:
 * a locale-formatted text input that commits on blur/Enter and steps with the buttons or arrow keys.
 */
export default function NumberField({
    name,
    value = null,
    modelValue = null,
    label,
    helperText,
    required,
    disabled,
    min = null,
    max = null,
    step = 1,
    formatOptions = EMPTY_FORMAT_OPTIONS,
    locale = null,
    prefix = null,
    suffix = null,
    onUpdateModelValue,
    onUpdateValue,
}: NumberFieldProps) {
    const [numberValue, setNumberValue] = useState<number | undefined>(modelValue ?? value ?? undefined);
    // Text being edited (null = show the formatted value)
    const [inputText, setInputText] = useState<string | null>(null);

    // Watch for external value changes (e.g., when editing)
    const incoming = modelValue ?? value;
    useEffect(() => {
        if (incoming !== null && incoming !== undefined) {
            setNumberValue(incoming);
        }
    }, [incoming]);

    const formatOptionsKey = JSON.stringify(formatOptions || {});
    const formatter = useMemo(
        () => new Intl.NumberFormat(locale ?? undefined, JSON.parse(formatOptionsKey)),
        [locale, formatOptionsKey],
    );

    const clamp = (n: number) => {
        let result = n;
        if (min !== null && min !== undefined && result < min) result = min;
        if (max !== null && max !== undefined && result > max) result = max;
        return result;
    };

    const parseNumber = (text: string): number | undefined => {
        const parts = formatter.formatToParts(-12345.6);
        const group = parts.find((p) => p.type === 'group')?.value ?? ',';
        const decimal = parts.find((p) => p.type === 'decimal')?.value ?? '.';
        const cleaned = text
            .split(group)
            .join('')
            .replace(decimal, '.')
            .replace(/[^\d.-]/g, '');

        if (cleaned === '' || cleaned === '-' || cleaned === '.') return undefined;

        let parsed = parseFloat(cleaned);
        if (Number.isNaN(parsed)) return undefined;
        if (formatOptions?.style === 'percent') parsed = parsed / 100;

        return parsed;
    };

    const updateValue = (next: number | undefined) => {
        setNumberValue(next);
        onUpdateModelValue?.(next ?? null);
        onUpdateValue?.(next ?? null);
    };

    const commitInput = () => {
        if (inputText === null) return;
        const parsed = parseNumber(inputText);
        setInputText(null);
        const next = parsed === undefined ? undefined : clamp(parsed);
        if (next !== numberValue) {
            updateValue(next);
        }
    };

    const stepBy = (direction: 1 | -1) => {
        if (disabled) return;
        const base = inputText !== null ? (parseNumber(inputText) ?? numberValue) : numberValue;
        const start = base ?? min ?? 0;
        const precision = Math.max(decimalsOf(step), decimalsOf(start));
        const next = clamp(Number((start + direction * step).toFixed(precision)));
        setInputText(null);
        updateValue(next);
    };

    const handleKeyDown = (event: KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'ArrowUp') {
            event.preventDefault();
            stepBy(1);
        } else if (event.key === 'ArrowDown') {
            event.preventDefault();
            stepBy(-1);
        } else if (event.key === 'Enter') {
            commitInput();
        }
    };

    const displayValue =
        inputText !== null ? inputText : numberValue === undefined || Number.isNaN(numberValue) ? '' : formatter.format(numberValue);

    const atMin = min !== null && min !== undefined && numberValue !== undefined && numberValue <= min;
    const atMax = max !== null && max !== undefined && numberValue !== undefined && numberValue >= max;

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
            {name && <input type="hidden" name={name} value={numberValue ?? ''} />}

            {/* Number Field */}
            <div className="relative flex items-center" data-disabled={disabled ? '' : undefined}>
                {/* Decrement Button */}
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    className="h-10 w-10 rounded-e-none border-e-0"
                    disabled={disabled || atMin}
                    tabIndex={-1}
                    aria-label="Decrease"
                    onClick={() => stepBy(-1)}
                >
                    <Minus className="h-4 w-4" />
                </Button>

                {/* Input with prefix/suffix */}
                <div className="relative flex-1">
                    {prefix && (
                        <span className="absolute start-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground pointer-events-none">
                            {prefix}
                        </span>
                    )}

                    <input
                        type="text"
                        role="spinbutton"
                        inputMode="decimal"
                        autoComplete="off"
                        autoCorrect="off"
                        spellCheck={false}
                        aria-valuenow={numberValue}
                        aria-valuemin={min ?? undefined}
                        aria-valuemax={max ?? undefined}
                        disabled={disabled}
                        value={displayValue}
                        onChange={(e) => setInputText(e.target.value)}
                        onBlur={commitInput}
                        onKeyDown={handleKeyDown}
                        className={cn(
                            'flex h-10 w-full rounded-none border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 text-center',
                            {
                                'ps-8': prefix,
                                'pe-8': suffix,
                            },
                        )}
                    />

                    {suffix && (
                        <span className="absolute end-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground pointer-events-none">
                            {suffix}
                        </span>
                    )}
                </div>

                {/* Increment Button */}
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    className="h-10 w-10 rounded-s-none border-s-0"
                    disabled={disabled || atMax}
                    tabIndex={-1}
                    aria-label="Increase"
                    onClick={() => stepBy(1)}
                >
                    <Plus className="h-4 w-4" />
                </Button>
            </div>

            {/* Helper text */}
            {helperText && <p className="text-xs text-muted-foreground mt-1">{helperText}</p>}
        </div>
    );
}
