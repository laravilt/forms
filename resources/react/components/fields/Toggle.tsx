import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { useFieldError } from '@laravilt/support/composables/contexts';
import type { ReactNode } from 'react';
import FieldWrapper from '../FieldWrapper';

export interface ToggleSlotScope {
    name: string;
    label?: string;
    helperText?: string;
    toggleValue: boolean;
    hasError: boolean;
}

export interface ToggleProps {
    name: string;
    label?: string;
    hint?: string;
    helperText?: string;
    required?: boolean;
    disabled?: boolean;
    value?: boolean;
    modelValue?: boolean;
    hidden?: boolean;
    columnSpan?: number | string;
    hintActions?: any[];
    isLive?: boolean;
    isLazy?: boolean;
    liveDebounce?: number;
    onUpdateModelValue?: (value: boolean) => void;
    /** Scoped default slot (Blade mode). */
    children?: (scope: ToggleSlotScope) => ReactNode;
    [key: string]: any;
}

export default function Toggle({
    name,
    label,
    hint,
    helperText,
    required,
    disabled,
    value,
    modelValue,
    hidden,
    columnSpan,
    hintActions,
    onUpdateModelValue,
    children,
}: ToggleProps) {
    const toggleValue = modelValue ?? value ?? false;
    const setToggleValue = (next: boolean) => onUpdateModelValue?.(next);

    // Errors from parent (Vue: inject('errors'))
    const errorMessage = useFieldError(name) || null;
    const hasError = !!errorMessage;

    // Use slot if provided (Blade mode), otherwise use internal template (Direct usage)
    if (children) {
        return <>{children({ name, label, helperText, toggleValue, hasError })}</>;
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
            <div className="flex items-start justify-between gap-2">
                <Label htmlFor={name} className="flex flex-col items-start space-y-1 text-start">
                    <span>{label}</span>
                    {!hasError && helperText && <span className="text-sm font-normal text-muted-foreground">{helperText}</span>}
                </Label>
                <Switch
                    id={name}
                    name={name}
                    checked={!!toggleValue}
                    onCheckedChange={setToggleValue}
                    disabled={disabled}
                    aria-invalid={hasError ? 'true' : 'false'}
                    aria-describedby={hasError ? `${name}-error` : undefined}
                />
            </div>
        </FieldWrapper>
    );
}
