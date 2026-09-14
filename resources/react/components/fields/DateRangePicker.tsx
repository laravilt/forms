import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverAnchor, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { CalendarDate, getLocalTimeZone, parseDate, toCalendarDate, today, type DateValue } from '@internationalized/date';
import { CalendarIcon } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import type { DateRange as DayPickerRange } from 'react-day-picker';
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

interface DateRange {
    start: DateValue;
    end: DateValue;
}

type RangeValue = { start: string | null; end: string | null } | null;

export interface DateRangePickerProps {
    name?: string;
    value?: RangeValue;
    modelValue?: RangeValue;
    label?: string;
    helperText?: string;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
    readonly?: boolean;
    minDate?: string | null;
    maxDate?: string | null;
    locale?: string;
    numberOfMonths?: number;
    closeOnSelect?: boolean;
    onUpdateModelValue?: (value: RangeValue) => void;
    onUpdateValue?: (value: RangeValue) => void;
    [key: string]: any;
}

// Parse string date to DateValue
const parseDateString = (dateStr: string | null | undefined): DateValue | null => {
    if (!dateStr) return null;
    try {
        return parseDate(dateStr);
    } catch (e) {
        return null;
    }
};

// Convert DateValue to string
const formatDateValue = (date: DateValue | null | undefined): string | null => {
    if (!date) return null;
    return `${date.year}-${String(date.month).padStart(2, '0')}-${String(date.day).padStart(2, '0')}`;
};

// Parse range object
const parseRangeValue = (range: RangeValue | undefined): DateRange | null => {
    if (!range) {
        return null;
    }

    const start = parseDateString(range.start);
    const end = parseDateString(range.end);

    if (!start || !end) {
        return null;
    }

    return { start, end };
};

// Format range object
const formatRangeValue = (range: DateRange | null | undefined): RangeValue => {
    if (!range) return null;

    return {
        start: formatDateValue(range.start),
        end: formatDateValue(range.end),
    };
};

const rangeKey = (range: RangeValue | undefined) => (range ? `${range.start ?? ''}|${range.end ?? ''}` : '');

export default function DateRangePicker({
    name,
    value = null,
    modelValue = null,
    label,
    helperText,
    placeholder: placeholderText = 'Select date range',
    required,
    disabled,
    readonly,
    minDate,
    maxDate,
    locale = 'en',
    numberOfMonths = 2,
    closeOnSelect = false,
    onUpdateModelValue,
    onUpdateValue,
}: DateRangePickerProps) {
    const [selectedRange, setSelectedRange] = useState<DateRange | null>(() => parseRangeValue(modelValue || value));
    const [open, setOpen] = useState(false);

    // Watch for prop changes
    const incoming = modelValue || value;
    const incomingKey = rangeKey(incoming);
    const previousIncomingKey = useRef(incomingKey);
    useEffect(() => {
        if (previousIncomingKey.current === incomingKey) return;
        previousIncomingKey.current = incomingKey;
        setSelectedRange(parseRangeValue(incoming));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [incomingKey]);

    const minDateValue = useMemo(() => parseDateString(minDate), [minDate]);
    const maxDateValue = useMemo(() => parseDateString(maxDate), [maxDate]);

    // Month/Year navigation
    const [placeholder, setPlaceholder] = useState<CalendarDate>(() =>
        selectedRange?.start ? toCalendarDate(selectedRange.start) : today(getLocalTimeZone()),
    );

    // Range being picked in the calendar (react-day-picker needs the partial `from` between clicks)
    const [draftRange, setDraftRange] = useState<DayPickerRange | undefined>(() =>
        selectedRange ? { from: toJsDate(selectedRange.start), to: toJsDate(selectedRange.end) } : undefined,
    );

    const selectedKey = selectedRange ? `${selectedRange.start.toString()}|${selectedRange.end.toString()}` : '';
    useEffect(() => {
        if (selectedRange) {
            setDraftRange({ from: toJsDate(selectedRange.start), to: toJsDate(selectedRange.end) });
            setPlaceholder(toCalendarDate(selectedRange.start));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [selectedKey]);

    const years = useMemo(buildYears, []);

    const updateRange = (range: DateRange | null | undefined) => {
        setSelectedRange(range ?? null);
        const rangeObj = formatRangeValue(range);
        onUpdateModelValue?.(rangeObj);
        onUpdateValue?.(rangeObj);
    };

    // Formatted display value (computed but not rendered in the Vue template either)
    const displayValue = (() => {
        if (!selectedRange || !selectedRange.start || !selectedRange.end) {
            return placeholderText;
        }

        const formatter = new Intl.DateTimeFormat(locale, {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });

        return `${formatter.format(toJsDate(selectedRange.start))} - ${formatter.format(toJsDate(selectedRange.end))}`;
    })();
    void displayValue;

    // Segment fields for start and end
    const segmentStart = useRef<DateValue | null>(selectedRange?.start ?? null);
    const segmentEnd = useRef<DateValue | null>(selectedRange?.end ?? null);
    segmentStart.current = selectedRange?.start ?? segmentStart.current;
    segmentEnd.current = selectedRange?.end ?? segmentEnd.current;

    const handleSegmentChange = (which: 'start' | 'end', date: DateValue | null) => {
        if (which === 'start') {
            segmentStart.current = date;
        } else {
            segmentEnd.current = date;
        }

        if (segmentStart.current && segmentEnd.current) {
            updateRange({ start: segmentStart.current, end: segmentEnd.current });
        } else {
            const partial = { start: formatDateValue(segmentStart.current), end: formatDateValue(segmentEnd.current) };
            onUpdateModelValue?.(partial);
            onUpdateValue?.(partial);
        }
    };

    const startSegments = useDateSegments({
        value: selectedRange ? toCalendarDate(selectedRange.start) : null,
        locale,
        disabled,
        readonly,
        onChange: (date) => handleSegmentChange('start', date),
    });

    const endSegments = useDateSegments({
        value: selectedRange ? toCalendarDate(selectedRange.end) : null,
        locale,
        disabled,
        readonly,
        onChange: (date) => handleSegmentChange('end', date),
    });

    const captionState: CalendarCaptionState = {
        locale,
        years,
        selectedMonth: placeholder.month,
        selectedYear: placeholder.year,
        setMonth: (month: number) => setPlaceholder(placeholder.set({ month })),
        setYear: (year: number) => setPlaceholder(placeholder.set({ year })),
    };

    const renderSegments = (segments: ReturnType<typeof useDateSegments>, type: 'start' | 'end') =>
        segments.map((item, index) =>
            item.part === 'literal' ? (
                <span key={`${type}-${index}`} {...item.props}>
                    {item.value}
                </span>
            ) : (
                <span key={`${type}-${index}`} {...item.props} className="px-1 tabular-nums outline-none focus:bg-accent rounded">
                    {item.value}
                </span>
            ),
        );

    return (
        <div className="w-full space-y-2">
            {/* Label */}
            {label && (
                <label htmlFor={name} className="text-sm font-medium block text-foreground">
                    {label}
                    {required && <span className="text-destructive ms-0.5">*</span>}
                </label>
            )}

            {/* Hidden inputs for form submission */}
            {name && (
                <>
                    <input type="hidden" name={`${name}[start]`} value={selectedRange?.start ? formatDateValue(selectedRange.start) || '' : ''} />
                    <input type="hidden" name={`${name}[end]`} value={selectedRange?.end ? formatDateValue(selectedRange.end) || '' : ''} />
                </>
            )}

            {/* Date Range Picker */}
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverAnchor asChild>
                    <div className={cn(fieldClass, { 'opacity-50 cursor-not-allowed': disabled })} role="group">
                        <div className="flex-1 flex items-center gap-1">
                            {renderSegments(startSegments, 'start')}

                            <span className="text-muted-foreground px-1">-</span>

                            {renderSegments(endSegments, 'end')}
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
                            mode="range"
                            className="flex gap-3 p-0"
                            numberOfMonths={numberOfMonths}
                            selected={draftRange}
                            onSelect={(range: DayPickerRange | undefined) => {
                                setDraftRange(range);
                                if (range?.from && range?.to) {
                                    updateRange({ start: fromJsDate(range.from), end: fromJsDate(range.to) });
                                    if (closeOnSelect) {
                                        setOpen(false);
                                    }
                                }
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
