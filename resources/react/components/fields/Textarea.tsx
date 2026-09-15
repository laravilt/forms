import { Textarea as UiTextarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';
import ActionButton from '@laravilt/actions/components/ActionButton';
import { useFieldError } from '@laravilt/support/composables/contexts';
import type { ReactNode } from 'react';
import FieldWrapper from '../FieldWrapper';

export interface TextareaSlotScope {
    id: string;
    name: string;
    textValue: string;
    placeholder?: string;
    rows?: number;
    maxLength?: number;
    showCharacterCount?: boolean;
    showWordCount?: boolean;
    wordCount: number;
    hasError: boolean;
}

export interface TextareaProps {
    id?: string;
    name: string;
    label?: string;
    placeholder?: string;
    hint?: string;
    required?: boolean;
    disabled?: boolean;
    rows?: number;
    helperText?: string;
    value?: string;
    maxLength?: number;
    showCharacterCount?: boolean;
    showWordCount?: boolean;
    hidden?: boolean;
    columnSpan?: number | string;
    hintActions?: any[];
    prefixActions?: any[];
    suffixActions?: any[];
    onUpdateModelValue?: (value: string) => void;
    /** Scoped default slot (Blade mode). */
    children?: (scope: TextareaSlotScope) => ReactNode;
    [key: string]: any;
}

export default function Textarea({
    id,
    name,
    label,
    placeholder,
    hint,
    required,
    disabled,
    rows,
    helperText,
    value,
    maxLength,
    showCharacterCount,
    showWordCount,
    hidden,
    columnSpan,
    hintActions,
    suffixActions,
    onUpdateModelValue,
    children,
}: TextareaProps) {
    const textValue = value || '';
    const setTextValue = (next: string) => onUpdateModelValue?.(next);

    const wordCount = textValue
        .trim()
        .split(/\s+/)
        .filter((word) => word.length > 0).length;

    // Errors from parent (Vue: inject('errors'))
    const errorMessage = useFieldError(name) || null;
    const hasError = !!errorMessage;

    // Use slot if provided (Blade mode), otherwise use internal template (Direct usage)
    if (children) {
        return (
            <>
                {children({
                    id: id || name,
                    name,
                    textValue,
                    placeholder,
                    rows,
                    maxLength,
                    showCharacterCount,
                    showWordCount,
                    wordCount,
                    hasError,
                })}
            </>
        );
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
            hintActions={hintActions}
        >
            <div className="relative flex gap-2">
                {/* Suffix Actions */}
                {suffixActions && suffixActions.length > 0 && (
                    <div className="flex items-start gap-1 pt-2">
                        {suffixActions.map((action: any) => (
                            <ActionButton key={action.name} {...(action as any)} />
                        ))}
                    </div>
                )}

                <UiTextarea
                    id={id || name}
                    name={name}
                    value={textValue}
                    onChange={(e) => setTextValue(e.target.value)}
                    placeholder={placeholder}
                    rows={rows}
                    maxLength={maxLength}
                    className={cn('flex-1', hasError ? 'border-destructive focus-visible:ring-destructive' : '')}
                    aria-invalid={hasError ? 'true' : 'false'}
                    aria-describedby={hasError ? `${name}-error` : undefined}
                />
            </div>

            {(showCharacterCount || showWordCount) && (
                <div className="mt-1.5 flex gap-4 text-xs text-muted-foreground">
                    {showCharacterCount && maxLength ? (
                        <span>
                            {textValue.length} / {maxLength} characters
                        </span>
                    ) : null}
                    {showWordCount && <span> {wordCount} words </span>}
                </div>
            )}
        </FieldWrapper>
    );
}
