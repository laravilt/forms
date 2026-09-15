import { cn } from '@/lib/utils';
import { Flame, Heart, Star, ThumbsUp, Trophy, type LucideIcon } from 'lucide-react';
import { useEffect, useState, type MouseEvent } from 'react';

export interface RateInputProps {
    name?: string;
    value?: number | null;
    modelValue?: number | null;
    label?: string;
    helperText?: string;
    required?: boolean;
    disabled?: boolean;
    readonly?: boolean;
    max?: number;
    allowHalf?: boolean;
    icon?: string;
    color?: string | null;
    showValue?: boolean;
    onUpdateModelValue?: (value: number | null) => void;
    onUpdateValue?: (value: number | null) => void;
    [key: string]: any;
}

const iconMap: Record<string, LucideIcon> = {
    star: Star,
    heart: Heart,
    'thumbs-up': ThumbsUp,
    flame: Flame,
    trophy: Trophy,
};

export default function RateInput({
    name,
    value = null,
    modelValue = null,
    label,
    helperText,
    required,
    disabled,
    readonly,
    max = 5,
    allowHalf = false,
    icon = 'star',
    color = null,
    showValue = false,
    onUpdateModelValue,
    onUpdateValue,
}: RateInputProps) {
    const [rating, setRating] = useState<number>(modelValue ?? value ?? 0);
    const [hoverRating, setHoverRating] = useState<number>(0);

    // Watch for external value changes (e.g., when editing)
    const incoming = modelValue ?? value;
    useEffect(() => {
        if (incoming !== null && incoming !== undefined) {
            setRating(incoming);
        }
    }, [incoming]);

    const IconComponent = iconMap[icon] || Star;

    const updateRating = (next: number) => {
        if (disabled || readonly) return;

        setRating(next);
        onUpdateModelValue?.(next);
        onUpdateValue?.(next);
    };

    const handleClick = (index: number, event: MouseEvent<HTMLButtonElement>) => {
        if (disabled || readonly) return;

        let newRating = index + 1;

        // Same half detection as the hover preview; keyboard clicks (detail 0) have no pointer position
        if (allowHalf && event.detail > 0) {
            const rect = event.currentTarget.getBoundingClientRect();
            if (event.clientX - rect.left < rect.width / 2) {
                newRating = index + 0.5;
            }
        }

        // Toggle off if clicking the same rating
        if (rating === newRating) {
            updateRating(0);
        } else {
            updateRating(newRating);
        }
    };

    const handleMouseMove = (index: number, event: MouseEvent<HTMLButtonElement>) => {
        if (disabled || readonly) return;

        if (allowHalf) {
            const target = event.currentTarget as HTMLElement;
            const rect = target.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const isHalf = x < rect.width / 2;
            setHoverRating(index + (isHalf ? 0.5 : 1));
        } else {
            setHoverRating(index + 1);
        }
    };

    const handleMouseLeave = () => {
        setHoverRating(0);
    };

    const isFilled = (index: number): boolean => {
        const currentRating = hoverRating || rating;
        return currentRating >= index + 1;
    };

    const isHalfFilled = (index: number): boolean => {
        const currentRating = hoverRating || rating;
        return currentRating >= index + 0.5 && currentRating < index + 1;
    };

    const fillColor = color ? color : '#facc15'; // Default yellow color

    // There is no single labelable control, so the label names the button group
    const labelId = label && name ? `${name}-label` : undefined;

    return (
        <div className="w-full space-y-2">
            {/* Label */}
            {label && (
                <span id={labelId} className="text-sm font-medium block text-foreground">
                    {label}
                    {required && <span className="text-destructive ms-0.5">*</span>}
                </span>
            )}

            {/* Hidden input for form submission */}
            {name && <input type="hidden" name={name} value={rating} />}

            {/* Rating Input */}
            <div className="flex items-center gap-1" role="group" aria-labelledby={labelId}>
                {Array.from({ length: max }, (_, i) => i + 1).map((index) => (
                    <button
                        key={index}
                        type="button"
                        aria-label={`Rate ${index} of ${max}`}
                        aria-pressed={Math.ceil(rating) === index}
                        onClick={(e) => handleClick(index - 1, e)}
                        onMouseMove={(e) => handleMouseMove(index - 1, e)}
                        onMouseLeave={handleMouseLeave}
                        disabled={disabled}
                        className={cn(
                            'relative p-1 rounded transition-transform hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                            disabled || readonly ? 'cursor-default opacity-50' : 'cursor-pointer',
                        )}
                    >
                        {/* Full icon */}
                        <IconComponent
                            className={cn('h-6 w-6 transition-colors', isFilled(index - 1) ? 'fill-current' : 'fill-none')}
                            style={{ color: isFilled(index - 1) ? fillColor : 'currentColor' }}
                        />

                        {/* Half icon overlay (if half ratings enabled) */}
                        {allowHalf && isHalfFilled(index - 1) && (
                            <div className="absolute inset-0 overflow-hidden p-1" style={{ width: '50%' }}>
                                <IconComponent className="h-6 w-6 fill-current transition-colors" style={{ color: fillColor }} />
                            </div>
                        )}
                    </button>
                ))}

                {/* Show numeric value */}
                {showValue && (
                    <span className="ms-2 text-sm font-medium text-foreground">
                        {rating} / {max}
                    </span>
                )}
            </div>

            {/* Helper text */}
            {helperText && <p className="text-xs text-muted-foreground mt-1">{helperText}</p>}
        </div>
    );
}
