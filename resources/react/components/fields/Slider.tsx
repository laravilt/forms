import { cn } from '@/lib/utils';
import { resolveIcon } from '@laravilt/support/lib/icons';
import { Slider as SliderPrimitive } from 'radix-ui';
import { useEffect, useRef, useState } from 'react';

export interface SliderProps {
    name?: string;
    value?: number;
    modelValue?: number;
    min?: number;
    max?: number;
    step?: number;
    marks?: number[];
    showValue?: boolean;
    disabled?: boolean;
    prefixIcon?: string;
    suffixIcon?: string;
    prefixIconColor?: string;
    suffixIconColor?: string;
    onUpdateModelValue?: (value: number) => void;
    onUpdateValue?: (value: number) => void;
    [key: string]: any;
}

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

    return colorMap[color] || `text-${color}`;
};

export default function Slider({
    name,
    value,
    modelValue = 0,
    min = 0,
    max = 100,
    step = 1,
    marks,
    showValue = false,
    disabled = false,
    prefixIcon,
    suffixIcon,
    prefixIconColor,
    suffixIconColor,
    onUpdateModelValue,
    onUpdateValue,
}: SliderProps) {
    const incoming = value ?? modelValue;

    // Internal state for slider value
    const [internalValue, setInternalValue] = useState<number>(incoming ?? 0);

    // Watch for prop changes and update internal state
    const previousIncoming = useRef(incoming);
    useEffect(() => {
        if (previousIncoming.current === incoming) return;
        previousIncoming.current = incoming;
        setInternalValue(incoming ?? 0);
    }, [incoming]);

    const handleChange = (next: number[]) => {
        setInternalValue(next[0]);
        onUpdateModelValue?.(next[0]);
        onUpdateValue?.(next[0]);
    };

    const sliderValue = [internalValue];

    const PrefixIcon = resolveIcon(prefixIcon);
    const SuffixIcon = resolveIcon(suffixIcon);

    return (
        <div className="w-full space-y-2">
            {/* Hidden input for form submission */}
            {name && <input type="hidden" name={name} value={internalValue} />}

            <div className="flex items-center gap-4">
                {/* Prefix icon */}
                {PrefixIcon && <PrefixIcon className={cn('h-4 w-4 shrink-0', getIconColorClass(prefixIconColor))} />}

                {/* Slider */}
                <SliderPrimitive.Root
                    value={sliderValue}
                    min={min}
                    max={max}
                    step={step}
                    disabled={disabled}
                    className="relative flex items-center w-full touch-none select-none"
                    onValueChange={handleChange}
                >
                    <SliderPrimitive.Track className="relative h-2 w-full grow overflow-hidden rounded-full bg-secondary">
                        <SliderPrimitive.Range className="absolute h-full bg-primary" />
                    </SliderPrimitive.Track>
                    <SliderPrimitive.Thumb className="block h-5 w-5 rounded-full border-2 border-primary bg-background ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50" />
                </SliderPrimitive.Root>

                {/* Value display */}
                {showValue && (
                    <span className="text-sm font-medium text-muted-foreground min-w-[3ch] text-end shrink-0">{internalValue}</span>
                )}

                {/* Suffix icon */}
                {SuffixIcon && <SuffixIcon className={cn('h-4 w-4 shrink-0', getIconColorClass(suffixIconColor))} />}
            </div>

            {/* Marks */}
            {marks && marks.length > 0 && (
                <div className="flex justify-between px-2">
                    {marks.map((mark) => (
                        <span key={mark} className="text-xs text-muted-foreground">
                            {mark}
                        </span>
                    ))}
                </div>
            )}
        </div>
    );
}
