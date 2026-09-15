import ActionButton from '@laravilt/actions/components/ActionButton';
import { cn } from '@/lib/utils';
import { useErrors } from '@laravilt/support/composables/contexts';
import type { ReactNode } from 'react';

export interface FieldWrapperProps {
    id?: string;
    name: string;
    label?: string;
    helperText?: string;
    hint?: string;
    required?: boolean;
    disabled?: boolean;
    hidden?: boolean;
    columnSpan?: number | string;
    fieldType?: string;
    hiddenLabel?: boolean;
    labelSrOnly?: boolean;
    hintActions?: any[];
    // Named slots
    aboveLabel?: ReactNode;
    beforeLabel?: ReactNode;
    afterLabel?: ReactNode;
    belowLabel?: ReactNode;
    aboveContent?: ReactNode;
    beforeContent?: ReactNode;
    afterContent?: ReactNode;
    belowContent?: ReactNode;
    children?: ReactNode;
}

// Column span classes
const getColumnSpanClass = (columnSpan: number | string | undefined) => {
    switch (columnSpan) {
        case 1:
            return 'col-span-1';
        case 2:
            return 'col-span-2';
        case 3:
            return 'col-span-3';
        case 4:
            return 'col-span-4';
        case 6:
            return 'col-span-6';
        case 12:
        case 'full':
            return 'col-span-12';
        default:
            return 'col-span-1';
    }
};

export default function FieldWrapper({
    id,
    name,
    label,
    helperText,
    hint,
    required,
    disabled,
    hidden,
    columnSpan,
    fieldType,
    hiddenLabel,
    labelSrOnly,
    hintActions,
    aboveLabel,
    beforeLabel,
    afterLabel,
    belowLabel,
    aboveContent,
    beforeContent,
    afterContent,
    belowContent,
    children,
}: FieldWrapperProps) {
    // Match the id the controls render (`id || name`) so the label targets them
    const fieldId = id || name || undefined;

    // Errors from parent (Vue: inject('errors'))
    const errors = useErrors();

    const hasError = !!errors && errors[name] !== undefined;

    const errorMessage = (() => {
        if (!hasError) return null;
        const error = errors[name];
        return Array.isArray(error) ? error[0] : error;
    })();

    return (
        <div
            className={cn('laravilt-field-wrapper', getColumnSpanClass(columnSpan), { hidden: hidden })}
            data-field-name={name}
            data-field-type={fieldType}
        >
            {/* Above Label Slot */}
            {aboveLabel != null && <div className="field-above-label mb-1">{aboveLabel}</div>}

            {/* Label Section */}
            {label && !hiddenLabel && (
                <div className="field-label-section mb-2">
                    {/* Before Label Slot */}
                    {beforeLabel != null && <div className="field-before-label me-2 inline-block">{beforeLabel}</div>}

                    <div className="flex items-center justify-between">
                        <label
                            htmlFor={fieldId}
                            className={cn(
                                'flex items-center gap-2 text-sm leading-none font-medium text-foreground select-none',
                                { 'sr-only': labelSrOnly },
                            )}
                            data-slot="label"
                        >
                            {label}

                            {required && (
                                <span className="ms-0.5 text-destructive" aria-label="required">
                                    *
                                </span>
                            )}

                            {hint && <span className="ms-2 text-xs font-normal text-muted-foreground">{hint}</span>}
                        </label>

                        {/* Hint Actions */}
                        {hintActions && hintActions.length > 0 && (
                            <div className="flex items-center gap-2">
                                {hintActions.map((action: any) => (
                                    <ActionButton
                                        key={action.name}
                                        {...({ ...action, size: 'sm', className: '!h-auto !p-0' } as any)}
                                    />
                                ))}
                            </div>
                        )}
                    </div>

                    {/* After Label Slot */}
                    {afterLabel != null && <div className="field-after-label ms-2 inline-block">{afterLabel}</div>}
                </div>
            )}

            {/* Below Label Slot */}
            {belowLabel != null && <div className="field-below-label mb-2">{belowLabel}</div>}

            {/* Above Content Slot */}
            {aboveContent != null && <div className="field-above-content mb-2">{aboveContent}</div>}

            {/* Content Section */}
            <div className={cn('field-content', { 'cursor-not-allowed opacity-60': disabled })}>
                {/* Before Content Slot */}
                {beforeContent != null && <div className="field-before-content mb-2">{beforeContent}</div>}

                {/* Main Content (Input Field) */}
                {children}

                {/* After Content Slot */}
                {afterContent != null && <div className="field-after-content mt-2">{afterContent}</div>}
            </div>

            {/* Below Content Slot */}
            {belowContent != null && <div className="field-below-content mt-2">{belowContent}</div>}

            {/* Helper Text */}
            {helperText && !hasError && <p className="mt-1.5 text-xs text-muted-foreground">{helperText}</p>}

            {/* Error Message */}
            {hasError && errorMessage && (
                <p id={`${name}-error`} className="mt-1.5 text-xs text-destructive" role="alert">
                    {errorMessage}
                </p>
            )}
        </div>
    );
}
