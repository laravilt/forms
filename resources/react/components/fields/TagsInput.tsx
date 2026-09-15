import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import { resolveIcon } from '@laravilt/support/lib/icons';
import { X } from 'lucide-react';
import { useEffect, useId, useRef, useState, type KeyboardEvent } from 'react';

export interface TagsInputProps {
    name?: string;
    value?: string[];
    modelValue?: string[];
    label?: string;
    placeholder?: string;
    disabled?: boolean;
    required?: boolean;
    helperText?: string;
    separator?: string;
    suggestions?: string[];
    prefixIcon?: string;
    suffixIcon?: string;
    prefixIconColor?: string;
    suffixIconColor?: string;
    onUpdateModelValue?: (value: string[]) => void;
    onUpdateValue?: (value: string[]) => void;
    [key: string]: any;
}

const EMPTY_TAGS: string[] = [];

// Helper to get Tailwind color classes for icons
const getIconColorClass = (color?: string) => {
    if (!color) return 'text-muted-foreground';

    // Map common color names to Tailwind classes
    const colorMap: Record<string, string> = {
        primary: 'text-primary',
        secondary: 'text-secondary',
        success: 'text-green-600',
        danger: 'text-red-600',
        warning: 'text-yellow-600',
        info: 'text-blue-600',
        muted: 'text-muted-foreground',
        destructive: 'text-destructive',
    };

    return colorMap[color] || 'text-muted-foreground';
};

export default function TagsInput({
    name,
    value,
    modelValue = EMPTY_TAGS,
    label,
    placeholder = 'Add tags...',
    disabled = false,
    required,
    helperText,
    separator = ',',
    suggestions,
    prefixIcon,
    suffixIcon,
    prefixIconColor,
    suffixIconColor,
    onUpdateModelValue,
    onUpdateValue,
}: TagsInputProps) {
    const [inputValue, setInputValue] = useState('');

    // Labels need a control id even when the field is used without a submission name
    const generatedId = useId();
    const controlId = name || generatedId;

    const incoming = value ?? modelValue;

    // Internal state for tags
    const [internalTags, setInternalTags] = useState<string[]>(() => (Array.isArray(incoming) ? [...incoming] : []));

    // Watch for prop changes and update internal state
    const previousIncoming = useRef(incoming);
    useEffect(() => {
        if (previousIncoming.current === incoming) return;
        previousIncoming.current = incoming;
        setInternalTags(Array.isArray(incoming) ? [...incoming] : []);
    }, [incoming]);

    const addTag = (tag: string) => {
        const trimmedTag = tag.trim();
        if (trimmedTag && !internalTags.includes(trimmedTag)) {
            const newTags = [...internalTags, trimmedTag];
            setInternalTags(newTags);
            onUpdateModelValue?.(newTags);
            onUpdateValue?.(newTags);
        }
        setInputValue('');
    };

    const removeTag = (index: number) => {
        const newTags = [...internalTags];
        newTags.splice(index, 1);
        setInternalTags(newTags);
        onUpdateModelValue?.(newTags);
        onUpdateValue?.(newTags);
    };

    const handleKeydown = (event: KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'Enter' || event.key === separator) {
            event.preventDefault();
            addTag(inputValue);
        } else if (event.key === 'Backspace' && !inputValue && internalTags.length > 0) {
            // Remove last tag on backspace when input is empty
            removeTag(internalTags.length - 1);
        }
    };

    const handleBlur = () => {
        if (inputValue.trim()) {
            addTag(inputValue);
        }
    };

    const PrefixIcon = resolveIcon(prefixIcon);
    const SuffixIcon = resolveIcon(suffixIcon);

    return (
        <div className="w-full space-y-2">
            {/* Label */}
            {label && (
                <label htmlFor={controlId} className="text-sm font-medium block text-foreground">
                    {label}
                    {required && <span className="text-destructive ms-0.5">*</span>}
                </label>
            )}

            {/* Hidden input for form submission */}
            {name && <input type="hidden" name={name} value={internalTags.join(',')} />}

            <div
                className={cn(
                    'flex flex-wrap items-center gap-2 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-within:outline-none focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2',
                    { 'opacity-50 cursor-not-allowed': disabled },
                )}
            >
                {/* Prefix icon */}
                {PrefixIcon && <PrefixIcon className={cn('h-4 w-4 shrink-0', getIconColorClass(prefixIconColor))} />}

                {/* Tags */}
                {internalTags.map((tag, index) => (
                    <Badge key={index} variant="secondary" className="gap-1 px-2 py-1">
                        {tag}
                        <button
                            type="button"
                            className="hover:bg-secondary/80 rounded-sm transition-colors"
                            aria-label={`Remove ${tag}`}
                            disabled={disabled}
                            onClick={() => removeTag(index)}
                        >
                            <X className="h-3 w-3" />
                        </button>
                    </Badge>
                ))}

                {/* Input */}
                <input
                    id={controlId}
                    value={inputValue}
                    onChange={(e) => setInputValue(e.target.value)}
                    type="text"
                    placeholder={internalTags.length === 0 ? placeholder : ''}
                    disabled={disabled}
                    className="flex-1 min-w-[120px] bg-transparent outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed"
                    onKeyDown={handleKeydown}
                    onBlur={handleBlur}
                />

                {/* Suffix icon */}
                {SuffixIcon && <SuffixIcon className={cn('h-4 w-4 shrink-0', getIconColorClass(suffixIconColor))} />}
            </div>

            {/* Suggestions */}
            {suggestions && suggestions.length > 0 && (
                <div className="mt-2 flex flex-wrap gap-2">
                    {suggestions.map((suggestion) => (
                        <button
                            key={suggestion}
                            type="button"
                            className="rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed"
                            disabled={disabled}
                            onClick={() => addTag(suggestion)}
                        >
                            <Badge variant="outline" className="cursor-pointer hover:bg-accent">
                                {suggestion}
                            </Badge>
                        </button>
                    ))}
                </div>
            )}

            {/* Helper text */}
            {helperText && <p className="text-xs text-muted-foreground mt-1">{helperText}</p>}
        </div>
    );
}
