import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverAnchor, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { CalendarDateTime, getLocalTimeZone, now, parseDateTime, toCalendarDate, type CalendarDate } from '@internationalized/date';
import { CalendarIcon } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { useDateSegments } from '../../composables/useDateSegments';
import {
    buildDisabledMatchers,
    buildYears,
    CalendarCaption,
    CalendarCaptionContext,
    contentClass,
    fieldClass,
    fromJsDate,
    toJsDate,
    triggerClass,
    type CalendarCaptionState,
} from './DatePicker';

export interface DateTimePickerProps {
    name?: string;
    value?: string | null;
    modelValue?: string | null;
    label?: string;
    helperText?: string;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
    readonly?: boolean;
    minDate?: string | null;
    maxDate?: string | null;
    hourCycle?: 12 | 24;
    locale?: string;
    onUpdateModelValue?: (value: string | null) => void;
    onUpdateValue?: (value: string | null) => void;
    [key: string]: any;
}

// Parse string datetime to CalendarDateTime
const parseDateTimeString = (dateTimeStr: string | null | undefined): CalendarDateTime | null => {
    if (!dateTimeStr) return null;
    try {
        // Expected format: YYYY-MM-DDTHH:mm or YYYY-MM-DD HH:mm
        const normalized = dateTimeStr.replace(' ', 'T');
        return parseDateTime(normalized);
    } catch (e) {
        return null;
    }
};

// Convert CalendarDateTime to string
const formatDateTimeValue = (dateTime: CalendarDateTime | null | undefined): string | null => {
    if (!dateTime) return null;

    const year = dateTime.year;
    const month = String(dateTime.month).padStart(2, '0');
    const day = String(dateTime.day).padStart(2, '0');
    const hour = String(dateTime.hour).padStart(2, '0');
    const minute = String(dateTime.minute).padStart(2, '0');

    return `${year}-${month}-${day} ${hour}:${minute}`;
};

export default function DateTimePicker({
    name,
    value = null,
    modelValue = null,
    label,
    helperText,
    required,
    disabled,
    readonly,
    minDate,
    maxDate,
    hourCycle = 24,
    locale = 'en',
    onUpdateModelValue,
    onUpdateValue,
}: DateTimePickerProps) {
    const [selectedDateTime, setSelectedDateTime] = useState<CalendarDateTime | null>(() =>
        parseDateTimeString(modelValue || value),
    );
    const [open, setOpen] = useState(false);

    // Watch for prop changes
    const incoming = modelValue || value;
    const previousIncoming = useRef(incoming);
    useEffect(() => {
        if (previousIncoming.current === incoming) return;
        previousIncoming.current = incoming;
        setSelectedDateTime(parseDateTimeString(incoming));
    }, [incoming]);

    const minDateValue = useMemo(() => parseDateTimeString(minDate), [minDate]);
    const maxDateValue = useMemo(() => parseDateTimeString(maxDate), [maxDate]);

    // Month/Year navigation
    const [placeholder, setPlaceholder] = useState<CalendarDate>(() =>
        toCalendarDate(selectedDateTime || now(getLocalTimeZone())),
    );

    // reka keeps the calendar placeholder on the selected value
    const selectedKey = selectedDateTime ? selectedDateTime.toString() : '';
    useEffect(() => {
        if (selectedDateTime) {
            setPlaceholder(toCalendarDate(selectedDateTime));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [selectedKey]);

    const years = useMemo(buildYears, []);

    const updateDateTime = (dateTime: CalendarDateTime | null | undefined) => {
        setSelectedDateTime(dateTime ?? null);
        const dateTimeStr = formatDateTimeValue(dateTime);
        onUpdateModelValue?.(dateTimeStr);
        onUpdateValue?.(dateTimeStr);
    };

    const segments = useDateSegments({
        value: selectedDateTime,
        granularity: 'minute',
        hourCycle,
        locale,
        disabled,
        readonly,
        onChange: (dateTime) => updateDateTime(dateTime as CalendarDateTime | null),
    });

    const captionState: CalendarCaptionState = {
        locale,
        years,
        selectedMonth: placeholder.month,
        selectedYear: placeholder.year,
        setMonth: (month: number) => setPlaceholder(placeholder.set({ month })),
        setYear: (year: number) => setPlaceholder(placeholder.set({ year })),
    };

    return (
        <div className="w-full space-y-2">
            {/* Label */}
            {label && (
                <label htmlFor={name} className="text-sm font-medium block text-foreground">
                    {label}
                    {required && <span className="text-destructive ms-0.5">*</span>}
                </label>
            )}

            {/* Hidden input for form submission */}
            {name && <input type="hidden" name={name} value={formatDateTimeValue(selectedDateTime) || ''} />}

            {/* DateTime Picker */}
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverAnchor asChild>
                    <div className={cn(fieldClass, { 'opacity-50 cursor-not-allowed': disabled })} role="group">
                        <div className="flex-1 flex items-center gap-1">
                            {segments.map((item, index) =>
                                item.part === 'literal' ? (
                                    <span key={index} {...item.props}>
                                        {item.value}
                                    </span>
                                ) : (
                                    <span
                                        key={index}
                                        {...item.props}
                                        className={cn('px-1 tabular-nums outline-none focus:bg-accent rounded', {
                                            'min-w-[2ch]': ['hour', 'minute'].includes(item.part),
                                            uppercase: item.part === 'dayPeriod',
                                        })}
                                    >
                                        {item.value}
                                    </span>
                                ),
                            )}
                        </div>

                        <PopoverTrigger asChild>
                            <button type="button" className={triggerClass} disabled={disabled || readonly}>
                                <CalendarIcon className="h-4 w-4 text-muted-foreground" />
                            </button>
                        </PopoverTrigger>
                    </div>
                </PopoverAnchor>

                <PopoverContent align="start" className={contentClass}>
                    <CalendarCaptionContext.Provider value={captionState}>
                        <Calendar
                            mode="single"
                            className="space-y-4 p-0"
                            selected={selectedDateTime ? toJsDate(selectedDateTime) : undefined}
                            onSelect={(date: Date | undefined) => {
                                if (!date) {
                                    updateDateTime(null);
                                    return;
                                }
                                const day = fromJsDate(date);
                                updateDateTime(
                                    new CalendarDateTime(
                                        day.year,
                                        day.month,
                                        day.day,
                                        selectedDateTime?.hour ?? 0,
                                        selectedDateTime?.minute ?? 0,
                                    ),
                                );
                            }}
                            month={toJsDate(placeholder)}
                            onMonthChange={(month: Date) => setPlaceholder(fromJsDate(month))}
                            disabled={buildDisabledMatchers(minDateValue, maxDateValue)}
                            hideNavigation
                            components={{ MonthCaption: CalendarCaption }}
                        />
                    </CalendarCaptionContext.Provider>
                </PopoverContent>
            </Popover>

            {/* Helper text */}
            {helperText && <p className="text-xs text-muted-foreground mt-1">{helperText}</p>}
        </div>
    );
}
