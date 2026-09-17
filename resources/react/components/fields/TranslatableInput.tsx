import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea as UiTextarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';
import ActionButton from '@laravilt/actions/components/ActionButton';
import { useErrors, useSchemaContext } from '@laravilt/support/composables/contexts';
import { useLocalization } from '@laravilt/support/composables/useLocalization';
import { Globe } from 'lucide-react';
import { useEffect, useMemo, useRef, useState, type ReactNode } from 'react';
import { useReactiveField } from '../../composables/useReactiveField';
import FieldWrapper from '../FieldWrapper';

/** Locale metadata as serialized by TranslatableInput::toLaraviltProps(). */
export interface TranslatableLocale {
    code: string;
    name?: string;
    label?: string;
    direction?: 'ltr' | 'rtl' | string;
}

export type Translations = Record<string, string>;

export interface TranslatableInputSlotScope {
    id: string;
    name: string;
    translations: Translations;
    activeLocale: string;
    locales: TranslatableLocale[];
    multiline?: boolean;
    rows?: number;
    placeholder?: string;
    readonly?: boolean;
    maxLength?: number;
    hasError: boolean;
    setLocale: (code: string, value: string) => void;
    extraAttributes?: Record<string, any>;
}

export interface TranslatableInputProps {
    id?: string;
    name: string;
    label?: string;
    placeholder?: string;
    hint?: string;
    required?: boolean;
    disabled?: boolean;
    readonly?: boolean;
    helperText?: string;
    value?: Translations | string | null;
    modelValue?: Translations | string | null;
    /** Allowed locales: metadata objects from PHP, or plain locale codes. */
    locales?: (TranslatableLocale | string)[];
    /** Locale edited by the main (collapsed) input. */
    activeLocale?: string;
    requiredLocales?: string[];
    multiline?: boolean;
    rows?: number;
    maxLength?: number;
    extraAttributes?: Record<string, any>;
    hidden?: boolean;
    columnSpan?: number | string;
    hintActions?: any[];
    prefixActions?: any[];
    suffixActions?: any[];
    isLive?: boolean;
    isLazy?: boolean;
    liveDebounce?: number;
    onUpdateModelValue?: (value: Translations) => void;
    onUpdateValue?: (value: Translations) => void;
    /** Scoped default slot (Blade mode). */
    children?: (scope: TranslatableInputSlotScope) => ReactNode;
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

// Normalize the locale list: plain codes become minimal metadata objects
const normalizeLocales = (locales?: (TranslatableLocale | string)[], activeLocale?: string): TranslatableLocale[] => {
    const list = (locales || [])
        .map((locale) => (typeof locale === 'string' ? { code: locale } : locale))
        .filter((locale): locale is TranslatableLocale => !!locale && !!locale.code);

    if (list.length) {
        return list;
    }

    // No metadata from the server: fall back to the active locale so the field still works
    return [{ code: activeLocale || 'en' }];
};

// Pick the main input's locale: the requested one if allowed, else the first allowed locale
const preferredActive = (codes: string[], activeLocale?: string): string => {
    if (activeLocale && codes.includes(activeLocale)) return activeLocale;
    return codes[0] ?? activeLocale ?? 'en';
};

// Parse whatever the server/parent hands us into a dict with every allowed locale present
const parseTranslations = (value: unknown, codes: string[], fallbackLocale: string): Translations => {
    let dict: Record<string, unknown> = {};

    if (value && typeof value === 'object' && !Array.isArray(value)) {
        dict = value as Record<string, unknown>;
    } else if (typeof value === 'string' && value !== '') {
        try {
            const parsed = value.trim().startsWith('{') ? JSON.parse(value) : null;
            dict = parsed && typeof parsed === 'object' ? parsed : { [fallbackLocale]: value };
        } catch {
            dict = { [fallbackLocale]: value };
        }
    }

    return Object.fromEntries(codes.map((code) => [code, dict[code] == null ? '' : String(dict[code])]));
};

export default function TranslatableInput(props: TranslatableInputProps) {
    const {
        id,
        name,
        label,
        placeholder,
        hint,
        required,
        disabled,
        readonly,
        helperText,
        value,
        modelValue,
        locales,
        activeLocale,
        requiredLocales,
        multiline,
        rows,
        maxLength,
        extraAttributes,
        hidden,
        columnSpan,
        hintActions,
        prefixActions,
        suffixActions,
        isLive,
        isLazy,
        liveDebounce,
        onUpdateModelValue,
        onUpdateValue,
        children,
    } = props;

    const { trans } = useLocalization();

    const availableLocales = useMemo(() => normalizeLocales(locales, activeLocale), [locales, activeLocale]);
    const localeCodes = useMemo(() => availableLocales.map((locale) => locale.code), [availableLocales]);
    const hasLocaleSwitcher = availableLocales.length > 1;

    const findLocale = (code: string) => availableLocales.find((locale) => locale.code === code);
    const labelOf = (code: string) => findLocale(code)?.label || code.split(/[-_]/)[0].toUpperCase();
    const directionOf = (code: string) => findLocale(code)?.direction || 'ltr';
    const isLocaleRequired = (code: string) => (requiredLocales ? requiredLocales.includes(code) : !!required);

    const incoming = modelValue ?? value;

    const [active, setActive] = useState<string>(() => preferredActive(localeCodes, activeLocale));
    const [dialogOpen, setDialogOpen] = useState(false);
    const [localValue, setLocalValueState] = useState<Translations>(() =>
        parseTranslations(incoming, localeCodes, preferredActive(localeCodes, activeLocale)),
    );
    const localValueRef = useRef<Translations>(localValue);
    const setLocalValue = (next: Translations) => {
        localValueRef.current = next;
        setLocalValueState(next);
    };

    // Sync local value when the prop changes from an external source (Vue: watch on modelValue/value)
    const incomingKey = typeof incoming === 'string' ? incoming : JSON.stringify(incoming ?? null);
    useEffect(() => {
        const next = parseTranslations(incoming, localeCodes, preferredActive(localeCodes, activeLocale));
        if (JSON.stringify(next) !== JSON.stringify(localValueRef.current)) {
            setLocalValue(next);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [incomingKey, localeCodes]);

    // Keep the active locale valid when the allowed set or preference changes
    useEffect(() => {
        if (!localeCodes.includes(active)) {
            setActive(preferredActive(localeCodes, activeLocale));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [localeCodes, activeLocale]);

    const setLocale = (code: string, next: string) => {
        const translations = { ...localValueRef.current, [code]: next };
        setLocalValue(translations);
        onUpdateModelValue?.({ ...translations });
        onUpdateValue?.({ ...translations });
    };

    // Reactive field support (live/lazy): re-evaluate the schema when the value changes
    useReactiveField(name, localValue, { isLive, isLazy, liveDebounce });

    // Dependencies for suffix actions (Vue: inject('getFormData'))
    const { getFormData } = useSchemaContext();

    // Errors from parent (Vue: inject('errors')), including per-locale keys (name.en, name.ar, ...)
    const errors = useErrors() || {};
    const firstError = (key: string): string | null => {
        const error = (errors as Record<string, string | string[]>)[key];
        if (!error) return null;
        return Array.isArray(error) ? error[0] : error;
    };
    const localeError = (code: string) => firstError(`${name}.${code}`);
    const localeHasError = (code: string) => !!localeError(code);
    const localeErrorMessage = (() => {
        for (const code of localeCodes) {
            const error = localeError(code);
            if (error) return `${labelOf(code)}: ${error}`;
        }
        return null;
    })();
    const errorMessage = firstError(name) || localeErrorMessage;
    const hasError = !!errorMessage;

    // Use slot if provided (Blade mode), otherwise use internal template (Direct usage)
    if (children) {
        return (
            <>
                {children({
                    id: id || name,
                    name,
                    translations: localValue,
                    activeLocale: active,
                    locales: availableLocales,
                    multiline,
                    rows,
                    placeholder,
                    readonly,
                    maxLength,
                    hasError,
                    setLocale,
                    extraAttributes,
                })}
            </>
        );
    }

    const extra = splitExtraAttributes(extraAttributes);
    const fieldId = id || name;

    const mainClassName = cn(
        'w-full',
        extra.className,
        hasError ? 'border-destructive focus-visible:ring-destructive' : '',
        hasLocaleSwitcher ? 'pe-12' : '',
    );

    return (
        <FieldWrapper
            id={id}
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
            <div className="relative flex items-center gap-2">
                {/* Prefix Actions */}
                {prefixActions && prefixActions.length > 0 && (
                    <div className="flex items-center gap-1">
                        {prefixActions.map((action: any) => (
                            <ActionButton key={action.name} {...(action as any)} />
                        ))}
                    </div>
                )}

                {/* The wrapper takes the active locale's direction so the globe (end) and the input's end padding agree */}
                <div className="relative flex flex-1 items-center" dir={directionOf(active)}>
                    {/* Main input edits the active locale */}
                    {multiline ? (
                        <UiTextarea
                            id={fieldId}
                            value={localValue[active] ?? ''}
                            onChange={(e) => setLocale(active, e.target.value)}
                            placeholder={placeholder}
                            rows={rows}
                            maxLength={maxLength}
                            disabled={disabled}
                            readOnly={readonly}
                            dir={directionOf(active)}
                            {...extra.attributes}
                            className={mainClassName}
                            aria-invalid={hasError ? 'true' : 'false'}
                            aria-describedby={hasError ? `${name}-error` : undefined}
                        />
                    ) : (
                        <Input
                            id={fieldId}
                            type="text"
                            value={localValue[active] ?? ''}
                            onChange={(e) => setLocale(active, e.target.value)}
                            placeholder={placeholder}
                            required={required}
                            disabled={disabled}
                            readOnly={readonly}
                            maxLength={maxLength}
                            dir={directionOf(active)}
                            {...extra.attributes}
                            className={mainClassName}
                            aria-invalid={hasError ? 'true' : 'false'}
                            aria-describedby={hasError ? `${name}-error` : undefined}
                        />
                    )}

                    {/* Globe button opens the per-locale dialog (hidden with a single locale) */}
                    {hasLocaleSwitcher && (
                        <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                            <DialogTrigger asChild>
                                <button
                                    type="button"
                                    className={cn(
                                        'absolute end-1.5 flex items-center gap-0.5 text-muted-foreground transition-colors hover:text-foreground disabled:pointer-events-none disabled:opacity-50',
                                        multiline ? 'top-2' : 'top-1/2 -translate-y-1/2',
                                    )}
                                    disabled={disabled}
                                    aria-label={trans('forms::forms.translatable_input.translations')}
                                    tabIndex={-1}
                                >
                                    <Globe className="h-3.5 w-3.5" />
                                    <span className="text-[9px] font-bold uppercase tracking-wide">{labelOf(active)}</span>
                                </button>
                            </DialogTrigger>
                            <DialogContent className="flex max-h-[85vh] flex-col gap-0 p-0 sm:max-w-xl">
                                {/* Logical alignment + end padding so the title follows the UI direction and clears the close button */}
                                <DialogHeader className="border-b px-6 py-4 pe-12 text-start sm:text-start">
                                    <DialogTitle>{label || trans('forms::forms.translatable_input.translations')}</DialogTitle>
                                    <DialogDescription>{trans('forms::forms.translatable_input.description')}</DialogDescription>
                                </DialogHeader>
                                <div className="flex-1 space-y-4 overflow-y-auto px-6 py-4">
                                    {availableLocales.map((locale) => (
                                        <div key={locale.code} className="space-y-1.5">
                                            <Label
                                                htmlFor={`${fieldId}-${locale.code}`}
                                                className="flex items-center justify-between text-sm"
                                            >
                                                <span>{locale.name || locale.code}</span>
                                                <span className="text-[10px] font-bold uppercase text-muted-foreground">
                                                    {locale.label || locale.code}
                                                    {isLocaleRequired(locale.code) && <span className="text-destructive">*</span>}
                                                </span>
                                            </Label>
                                            {multiline ? (
                                                <UiTextarea
                                                    id={`${fieldId}-${locale.code}`}
                                                    value={localValue[locale.code] ?? ''}
                                                    onChange={(e) => setLocale(locale.code, e.target.value)}
                                                    rows={rows}
                                                    maxLength={maxLength}
                                                    disabled={disabled}
                                                    readOnly={readonly}
                                                    dir={locale.direction || 'ltr'}
                                                    className={cn(
                                                        localeHasError(locale.code) ? 'border-destructive focus-visible:ring-destructive' : '',
                                                    )}
                                                />
                                            ) : (
                                                <Input
                                                    id={`${fieldId}-${locale.code}`}
                                                    type="text"
                                                    value={localValue[locale.code] ?? ''}
                                                    onChange={(e) => setLocale(locale.code, e.target.value)}
                                                    maxLength={maxLength}
                                                    disabled={disabled}
                                                    readOnly={readonly}
                                                    dir={locale.direction || 'ltr'}
                                                    className={cn(
                                                        localeHasError(locale.code) ? 'border-destructive focus-visible:ring-destructive' : '',
                                                    )}
                                                />
                                            )}
                                            {localeError(locale.code) && (
                                                <p className="text-xs text-destructive">{localeError(locale.code)}</p>
                                            )}
                                        </div>
                                    ))}
                                </div>
                                <DialogFooter className="border-t px-6 py-4">
                                    <Button type="button" onClick={() => setDialogOpen(false)}>
                                        {trans('forms::forms.translatable_input.done')}
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
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

            {/* Per-locale errors (name.en, ...) are not shown by FieldWrapper, so surface the first one here */}
            {localeErrorMessage && <p className="mt-1.5 text-xs text-destructive">{localeErrorMessage}</p>}

            {/* Hidden inputs so native (non-Inertia) form posts submit every locale */}
            {name &&
                availableLocales.map((locale) => (
                    <input key={locale.code} type="hidden" name={`${name}[${locale.code}]`} value={localValue[locale.code] ?? ''} />
                ))}
        </FieldWrapper>
    );
}
