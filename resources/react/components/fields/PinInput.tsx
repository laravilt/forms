import { cn } from '@/lib/utils';
import { OTPInput, REGEXP_ONLY_CHARS, REGEXP_ONLY_DIGITS, REGEXP_ONLY_DIGITS_AND_CHARS } from 'input-otp';
import { useState } from 'react';

export interface PinInputProps {
    name?: string;
    value?: string | null;
    modelValue?: string | null;
    label?: string;
    helperText?: string;
    error?: string;
    required?: boolean;
    disabled?: boolean;
    length?: number;
    mask?: boolean;
    otp?: boolean;
    type?: 'numeric' | 'alpha' | 'alphanumeric';
    align?: 'left' | 'center' | 'right';
    onUpdateModelValue?: (value: string | null) => void;
    onUpdateValue?: (value: string | null) => void;
    [key: string]: any;
}

const alignmentClasses = {
    left: {
        container: 'justify-start',
        text: 'text-start',
    },
    center: {
        container: 'justify-center',
        text: 'text-center',
    },
    right: {
        container: 'justify-end',
        text: 'text-end',
    },
};

const patterns = {
    numeric: REGEXP_ONLY_DIGITS,
    alpha: REGEXP_ONLY_CHARS,
    alphanumeric: REGEXP_ONLY_DIGITS_AND_CHARS,
};

export default function PinInput({
    name,
    value = null,
    modelValue = null,
    label,
    helperText,
    error,
    required,
    disabled,
    length = 4,
    mask = false,
    otp = false,
    type = 'numeric',
    align = 'left',
    onUpdateModelValue,
    onUpdateValue,
}: PinInputProps) {
    const [pinValue, setPinValue] = useState<string[]>(() => modelValue?.split('') || value?.split('') || []);

    const updateValue = (next: string[]) => {
        setPinValue(next);
        const stringValue = next.join('');
        onUpdateModelValue?.(stringValue || null);
        onUpdateValue?.(stringValue || null);
    };

    const alignment = alignmentClasses[align] ?? alignmentClasses.left;
    const isComplete = pinValue.length >= length;

    return (
        <div className="w-full space-y-2">
            {/* Label */}
            {label && (
                <label htmlFor={name} className={cn('text-sm font-medium block text-foreground', alignment.text)}>
                    {label}
                    {required && <span className="text-destructive ms-0.5">*</span>}
                </label>
            )}

            {/* Hidden input for form submission */}
            {name && <input type="hidden" name={name} value={pinValue.join('')} />}

            {/* Pin Input */}
            <div className={cn('flex py-2', alignment.container)}>
                <OTPInput
                    maxLength={length}
                    value={pinValue.join('')}
                    onChange={(next: string) => updateValue(next.split(''))}
                    disabled={disabled}
                    pattern={patterns[type] ?? REGEXP_ONLY_DIGITS}
                    inputMode={type === 'numeric' ? 'numeric' : 'text'}
                    autoComplete={otp ? 'one-time-code' : 'off'}
                    containerClassName="flex items-center gap-3"
                    render={({ slots }) => (
                        <>
                            {slots.map((slot, index) => (
                                <div
                                    key={index}
                                    data-complete={isComplete ? '' : undefined}
                                    data-disabled={disabled ? '' : undefined}
                                    className={cn(
                                        'relative h-14 w-14 rounded-lg border-2 bg-background text-lg font-semibold ring-offset-background transition-all duration-200 file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50 p-0',
                                        error
                                            ? 'border-destructive hover:border-destructive focus-visible:outline-none focus-visible:border-destructive focus-visible:ring-4 focus-visible:ring-destructive/10'
                                            : 'border-input hover:border-primary/50 focus-visible:outline-none focus-visible:border-primary focus-visible:ring-4 focus-visible:ring-primary/10 data-[complete]:border-primary data-[complete]:bg-primary/5',
                                        // The real input is hidden behind input-otp; mirror the focus-visible state on the active slot
                                        slot.isActive &&
                                            (error
                                                ? 'outline-none border-destructive ring-4 ring-destructive/10'
                                                : 'outline-none border-primary ring-4 ring-primary/10'),
                                        disabled && 'cursor-not-allowed opacity-50',
                                    )}
                                    style={{ textAlign: 'center', direction: 'ltr', lineHeight: '56px' }}
                                >
                                    {slot.char !== null ? (mask ? '•' : slot.char) : null}
                                    {slot.hasFakeCaret && (
                                        <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
                                            <div className="h-6 w-px animate-caret-blink bg-foreground duration-1000" />
                                        </div>
                                    )}
                                </div>
                            ))}
                        </>
                    )}
                />
            </div>

            {/* Error message */}
            {error ? (
                <p className={cn('text-xs text-destructive', alignment.text)}>{error}</p>
            ) : helperText ? (
                /* Helper text */
                <p className={cn('text-xs text-muted-foreground', alignment.text)}>{helperText}</p>
            ) : null}
        </div>
    );
}
