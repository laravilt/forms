import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { useFieldError } from '@laravilt/support/composables/contexts';
import type { ReactNode } from 'react';
import FieldWrapper from '../FieldWrapper';

export interface RadioSlotScope {
    name: string;
    label?: string;
    options?: Record<string, string>;
    descriptions?: Record<string, string>;
    defaultValue?: string;
    inline?: boolean;
    hasError: boolean;
}

export interface RadioProps {
    name: string;
    label?: string;
    hint?: string;
    options?: Record<string, string>;
    descriptions?: Record<string, string>;
    defaultValue?: string;
    value?: string;
    inline?: boolean;
    required?: boolean;
    disabled?: boolean;
    helperText?: string;
    hidden?: boolean;
    columnSpan?: number | string;
    hintActions?: any[];
    onUpdateModelValue?: (value: string) => void;
    /** Scoped default slot (Blade mode). */
    children?: (scope: RadioSlotScope) => ReactNode;
    [key: string]: any;
}

export default function Radio({
    name,
    label,
    hint,
    options,
    descriptions,
    defaultValue,
    value,
    inline,
    required,
    disabled,
    helperText,
    hidden,
    columnSpan,
    hintActions,
    onUpdateModelValue,
    children,
}: RadioProps) {
    const radioValue = value ?? defaultValue;
    const setRadioValue = (next: string) => onUpdateModelValue?.(next);

    // Errors from parent (Vue: inject('errors'))
    const errorMessage = useFieldError(name) || null;
    const hasError = !!errorMessage;

    // Use slot if provided (Blade mode), otherwise use internal template (Direct usage)
    if (children) {
        return <>{children({ name, label, options, descriptions, defaultValue, inline, hasError })}</>;
    }

    return (
        <FieldWrapper
            name={name}
            label={label}
            helperText={helperText}
            hint={hint}
            required={required}
            disabled={disabled}
            hidden={hidden}
            columnSpan={columnSpan}
            hiddenLabel={true}
            hintActions={hintActions}
        >
            <div>
                {label && <Label className="mb-3 block text-sm font-medium">{label}</Label>}
                <RadioGroup
                    value={radioValue === undefined || radioValue === null ? '' : String(radioValue)}
                    onValueChange={setRadioValue}
                    name={name}
                    disabled={disabled}
                    aria-invalid={hasError ? 'true' : 'false'}
                    aria-describedby={hasError ? `${name}-error` : undefined}
                >
                    <div className={inline ? 'flex flex-wrap gap-4' : 'space-y-3'}>
                        {Object.entries(options || {}).map(([optValue, optLabel]) => (
                            <div key={optValue} className="flex items-start space-x-2">
                                <RadioGroupItem id={`${name}_${optValue}`} value={String(optValue)} className="mt-0.5" />
                                <div className="grid gap-1.5 leading-none">
                                    <Label
                                        htmlFor={`${name}_${optValue}`}
                                        className="text-sm leading-none font-normal peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        {optLabel}
                                    </Label>
                                    {descriptions && descriptions[optValue] && (
                                        <p className="text-sm text-muted-foreground">{descriptions[optValue]}</p>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </RadioGroup>
            </div>
        </FieldWrapper>
    );
}
