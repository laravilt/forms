import { Checkbox as UiCheckbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { useFieldError } from '@laravilt/support/composables/contexts';
import type { ReactNode } from 'react';
import FieldWrapper from '../FieldWrapper';

export interface CheckboxSlotScope {
    name: string;
    label?: string;
    options?: Record<string, string>;
    inline?: boolean;
    hasError: boolean;
}

export interface CheckboxProps {
    name: string;
    label?: string;
    hint?: string;
    options?: Record<string, string>;
    inline?: boolean;
    required?: boolean;
    disabled?: boolean;
    helperText?: string;
    hidden?: boolean;
    columnSpan?: number | string;
    defaultValue?: any;
    value?: any;
    checkedValue?: any;
    uncheckedValue?: any;
    hintActions?: any[];
    isLive?: boolean;
    isLazy?: boolean;
    liveDebounce?: number;
    onUpdateModelValue?: (value: any) => void;
    /** Scoped default slot (Blade mode). */
    children?: (scope: CheckboxSlotScope) => ReactNode;
    [key: string]: any;
}

// reka-ui's Checkbox accepted any truthy model value; Radix requires boolean | 'indeterminate'
const toCheckedState = (value: any): boolean | 'indeterminate' => {
    if (value === 'indeterminate') return 'indeterminate';
    if (value === '0' || value === 'false') return false;
    return !!value;
};

export default function Checkbox({
    name,
    label,
    hint,
    options,
    inline,
    required,
    disabled,
    helperText,
    hidden,
    columnSpan,
    value,
    checkedValue,
    uncheckedValue,
    hintActions,
    onUpdateModelValue,
    children,
}: CheckboxProps) {
    const checkboxValue = value;
    const setCheckboxValue = (next: any) => onUpdateModelValue?.(next);

    // Errors from parent (Vue: inject('errors'))
    const errorMessage = useFieldError(name) || null;
    const hasError = !!errorMessage;

    // Use slot if provided (Blade mode), otherwise use internal template (Direct usage)
    if (children) {
        return <>{children({ name, label, options, inline, hasError })}</>;
    }

    const hasNoOptions = !options || Object.keys(options).length === 0;

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
            hiddenLabel={hasNoOptions}
            hintActions={hintActions}
        >
            {hasNoOptions ? (
                /* Single Checkbox (when no options provided) */
                <div className="flex items-center space-x-2">
                    {/* Hidden input for unchecked value (false) */}
                    <input type="hidden" name={name} value={String(uncheckedValue ?? false)} />

                    <UiCheckbox
                        id={name}
                        name={name}
                        checked={toCheckedState(checkboxValue)}
                        onCheckedChange={(checked) => setCheckboxValue(checked)}
                        value={String(checkedValue ?? true)}
                        disabled={disabled}
                        aria-invalid={hasError ? 'true' : 'false'}
                    />
                    {label && (
                        <Label
                            htmlFor={name}
                            className="text-sm leading-none font-normal peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            {label}
                        </Label>
                    )}
                </div>
            ) : (
                /* Checkbox List (when options provided) */
                <div>
                    <div className={inline ? 'flex flex-wrap gap-4' : 'space-y-3'}>
                        {Object.entries(options).map(([optValue, optLabel]) => (
                            <div key={optValue} className="flex items-center space-x-2">
                                <UiCheckbox
                                    id={`${name}_${optValue}`}
                                    name={`${name}[]`}
                                    value={optValue}
                                    disabled={disabled}
                                    aria-invalid={hasError ? 'true' : 'false'}
                                />
                                <Label
                                    htmlFor={`${name}_${optValue}`}
                                    className="text-sm leading-none font-normal peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                >
                                    {optLabel}
                                </Label>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </FieldWrapper>
    );
}
