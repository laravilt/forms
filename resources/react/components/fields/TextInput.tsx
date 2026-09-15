import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import ActionButton from '@laravilt/actions/components/ActionButton';
import { useFieldError, useSchemaContext } from '@laravilt/support/composables/contexts';
import { Eye, EyeOff } from 'lucide-react';
import { useEffect, useLayoutEffect, useRef, useState, type ReactNode } from 'react';
import FieldWrapper from '../FieldWrapper';
import PhoneInput from '../PhoneInput';

export interface TextInputSlotScope {
    id: string;
    name: string;
    type: string;
    inputValue: string;
    placeholder?: string;
    readonly?: boolean;
    autofocus?: boolean;
    autocomplete?: string;
    minLength?: number;
    maxLength?: number;
    pattern?: string;
    prefixText?: string;
    showCharacterCount?: boolean;
    characterCount: number;
    hasError: boolean;
    extraAttributes?: Record<string, any>;
}

export interface TextInputProps {
    id?: string;
    name: string;
    type?: string;
    label?: string;
    placeholder?: string;
    hint?: string;
    required?: boolean;
    disabled?: boolean;
    readonly?: boolean;
    autofocus?: boolean;
    autocomplete?: string;
    minLength?: number;
    maxLength?: number;
    pattern?: string;
    prefixText?: string;
    helperText?: string;
    showCharacterCount?: boolean;
    value?: string;
    extraAttributes?: Record<string, any>;
    hidden?: boolean;
    columnSpan?: number | string;
    hintActions?: any[];
    prefixActions?: any[];
    suffixActions?: any[];
    isLive?: boolean;
    isLazy?: boolean;
    liveDebounce?: number;
    isRevealable?: boolean;
    onUpdateModelValue?: (value: string) => void;
    /** Scoped default slot (Blade mode). */
    children?: (scope: TextInputSlotScope) => ReactNode;
    [key: string]: any;
}

// Split `class` out of server-provided extra attributes so React receives `className`
const splitExtraAttributes = (extraAttributes?: Record<string, any>) => {
    if (!extraAttributes) {
        return { attributes: {}, className: undefined as string | undefined };
    }
    const { class: klass, className, ...attributes } = extraAttributes;
    return { attributes, className: cn(klass, className) || undefined };
};

export default function TextInput(props: TextInputProps) {
    const {
        id,
        name,
        type,
        label,
        placeholder,
        hint,
        required,
        disabled,
        readonly,
        autofocus,
        autocomplete,
        minLength,
        maxLength,
        pattern,
        prefixText,
        helperText,
        showCharacterCount,
        value,
        extraAttributes,
        hidden,
        columnSpan,
        hintActions,
        prefixActions,
        suffixActions,
        isLive,
        isLazy,
        liveDebounce,
        isRevealable,
        onUpdateModelValue,
        children,
    } = props;

    // Ref for measuring prefix width
    const prefixRef = useRef<HTMLDivElement | null>(null);
    const [prefixWidth, setPrefixWidth] = useState(0);

    // Calculate padding based on prefix width (prefix width + extra space for padding)
    // Base padding (12px = pl-3) + prefix width + small gap (8px)
    const prefixPadding = prefixWidth > 0 ? prefixWidth + 8 : 32;

    // Measure prefix width after mount and when prefix changes
    useLayoutEffect(() => {
        if (prefixRef.current) {
            setPrefixWidth(prefixRef.current.offsetWidth);
        }
    }, [prefixText]);

    // Password visibility toggle for revealable password fields
    const [isPasswordVisible, setIsPasswordVisible] = useState(false);

    // Compute actual input type (toggle between password and text when revealable)
    const actualInputType = type === 'password' && isRevealable && isPasswordVisible ? 'text' : type || 'text';

    const togglePasswordVisibility = () => {
        setIsPasswordVisible((visible) => !visible);
    };

    // Use local state for input to avoid cursor position issues with reactive fields
    const [localValue, setLocalValueState] = useState<string>(value || '');
    const localValueRef = useRef<string>(value || '');
    const setLocalValue = (next: string) => {
        localValueRef.current = next;
        setLocalValueState(next);
    };
    const debounceTimeout = useRef<ReturnType<typeof setTimeout> | null>(null);
    const isUserTyping = useRef(false);

    // Sync local value when prop changes from external source (not from user input)
    const previousValue = useRef(value);
    useEffect(() => {
        if (previousValue.current === value) return;
        previousValue.current = value;

        // Don't update if user is actively typing
        if (isUserTyping.current) {
            return;
        }

        // Only update local value if it's different
        if (value !== localValueRef.current) {
            setLocalValue(value || '');
        }
    }, [value]);

    useEffect(
        () => () => {
            if (debounceTimeout.current) {
                clearTimeout(debounceTimeout.current);
            }
        },
        [],
    );

    const handleInput = (newValue: string | null) => {
        // Mark that user is typing
        isUserTyping.current = true;

        // Update local value immediately for smooth typing
        setLocalValue(newValue || '');

        // Clear existing debounce timeout
        if (debounceTimeout.current) {
            clearTimeout(debounceTimeout.current);
        }

        // For live/lazy fields, debounce the emit
        if (isLive || isLazy) {
            const delay = isLazy ? liveDebounce || 500 : liveDebounce || 300;
            debounceTimeout.current = setTimeout(() => {
                onUpdateModelValue?.(newValue || '');
                // Allow prop updates after a short delay
                setTimeout(() => {
                    isUserTyping.current = false;
                }, 100);
            }, delay);
        } else {
            // For non-reactive fields, emit immediately
            onUpdateModelValue?.(newValue || '');
            // Allow prop updates after a short delay
            setTimeout(() => {
                isUserTyping.current = false;
            }, 100);
        }
    };

    const inputValue = localValue;

    const characterCount = localValue.length;

    // Dependencies for reactive fields (Vue: inject('getFormData') / inject('updateSchema'))
    const { getFormData } = useSchemaContext();

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
                    type: type || 'text',
                    inputValue,
                    placeholder,
                    readonly,
                    autofocus,
                    autocomplete,
                    minLength,
                    maxLength,
                    pattern,
                    prefixText,
                    showCharacterCount,
                    characterCount,
                    hasError,
                    extraAttributes,
                })}
            </>
        );
    }

    const extra = splitExtraAttributes(extraAttributes);

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
            {/* Phone Input for tel type */}
            {type === 'tel' ? (
                <PhoneInput
                    name={name}
                    modelValue={localValue}
                    onUpdateModelValue={handleInput}
                    placeholder={placeholder}
                    disabled={disabled}
                    readonly={readonly}
                />
            ) : (
                /* Regular input for other types */
                <div className="relative flex items-center gap-2">
                    {/* Prefix Actions */}
                    {prefixActions && prefixActions.length > 0 && (
                        <div className="flex items-center gap-1">
                            {prefixActions.map((action: any) => (
                                <ActionButton key={action.name} {...(action as any)} />
                            ))}
                        </div>
                    )}

                    <div className="relative flex-1">
                        {prefixText && (
                            <div
                                ref={prefixRef}
                                className="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-sm text-muted-foreground z-10"
                            >
                                {prefixText}
                            </div>
                        )}

                        <Input
                            id={id || name}
                            name={name}
                            type={actualInputType}
                            value={inputValue}
                            onChange={(e) => handleInput(e.target.value)}
                            placeholder={placeholder}
                            required={required}
                            disabled={disabled}
                            readOnly={readonly}
                            autoFocus={autofocus}
                            autoComplete={autocomplete}
                            minLength={minLength}
                            maxLength={maxLength}
                            pattern={pattern}
                            {...extra.attributes}
                            className={cn(
                                extra.className,
                                hasError ? 'border-destructive focus-visible:ring-destructive' : '',
                                type === 'password' && isRevealable ? 'pe-10' : '',
                            )}
                            style={prefixText ? { paddingInlineStart: `${prefixPadding}px` } : {}}
                            aria-invalid={hasError ? 'true' : 'false'}
                            aria-describedby={hasError ? `${name}-error` : undefined}
                        />

                        {/* Password reveal toggle button */}
                        {type === 'password' && isRevealable && (
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                className="absolute inset-y-0 end-0 flex h-full w-10 items-center justify-center text-muted-foreground hover:text-foreground"
                                onClick={togglePasswordVisibility}
                                aria-label={isPasswordVisible ? 'Hide password' : 'Show password'}
                                tabIndex={-1}
                            >
                                {isPasswordVisible ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                            </Button>
                        )}
                    </div>

                    {/* Suffix Actions */}
                    {suffixActions && suffixActions.length > 0 && (
                        <div className="flex items-center gap-1">
                            {suffixActions.map((action: any) => (
                                <ActionButton key={action.name} {...({ ...action, getFormData } as any)} />
                            ))}
                        </div>
                    )}
                </div>
            )}

            {showCharacterCount && maxLength && type !== 'tel' ? (
                <div className="mt-1.5 text-end text-xs text-muted-foreground">
                    {characterCount} / {maxLength}
                </div>
            ) : null}
        </FieldWrapper>
    );
}
