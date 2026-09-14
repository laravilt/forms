import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverAnchor, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { CalendarDate, getLocalTimeZone, parseDate, toCalendarDate, today, type DateValue } from '@internationalized/date';
import { CalendarIcon, ChevronLeft, ChevronRight } from 'lucide-react';
import { createContext, useContext, useEffect, useMemo, useRef, useState, type HTMLAttributes } from 'react';
import { useDayPicker, type CalendarMonth, type Matcher } from 'react-day-picker';
import { useDateSegments } from '../../composables/useDateSegments';

export interface DatePickerProps {
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
    locale?: string;
    onUpdateModelValue?: (value: string | null) => void;
    onUpdateValue?: (value: string | null) => void;
    [key: string]: any;
}

// Generate months
export const months = [
    { value: 1, label: 'January' },
    { value: 2, label: 'February' },
    { value: 3, label: 'March' },
    { value: 4, label: 'April' },
    { value: 5, label: 'May' },
    { value: 6, label: 'June' },
    { value: 7, label: 'July' },
    { value: 8, label: 'August' },
    { value: 9, label: 'September' },
    { value: 10, label: 'October' },
    { value: 11, label: 'November' },
    { value: 12, label: 'December' },
];

// Generate years range (50 years back, 10 years forward)
export const buildYears = (): number[] => {
    const currentYear = today(getLocalTimeZone()).year;
    const startYear = currentYear - 50;
    const endYear = currentYear + 10;
    const yearsList = [];
    for (let year = startYear; year <= endYear; year++) {
        yearsList.push(year);
    }
    return yearsList;
};

export const toJsDate = (date: { year: number; month: number; day: number }) => new Date(date.year, date.month - 1, date.day);

export const fromJsDate = (date: Date) => new CalendarDate(date.getFullYear(), date.getMonth() + 1, date.getDate());

export const buildDisabledMatchers = (min: DateValue | null, max: DateValue | null): Matcher[] => [
    ...(min ? [{ before: toJsDate(min) }] : []),
    ...(max ? [{ after: toJsDate(max) }] : []),
];

export const fieldClass =
    'flex items-center gap-2 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2';
export const triggerClass =
    'inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50';
export const contentClass = 'z-50 mt-2 w-auto rounded-md border bg-popover p-3 text-popover-foreground shadow-md outline-none';
const navButtonClass =
    'inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-7 w-7';
const selectClass =
    'text-sm font-medium border border-input bg-background text-foreground rounded-md px-2 py-1 cursor-pointer transition-colors hover:bg-accent hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-0';

export interface CalendarCaptionState {
    locale: string;
    years: number[];
    selectedMonth: number;
    selectedYear: number;
    setMonth: (month: number) => void;
    setYear: (year: number) => void;
}

export const CalendarCaptionContext = createContext<CalendarCaptionState | null>(null);

/**
 * Month header shared by the date pickers (reka DatePickerHeader: Prev, month/year selects, Next).
 * With several months, the first shows Prev + selects, the others a heading, and the last one Next.
 */
export function CalendarCaption({
    calendarMonth,
    displayIndex,
}: { calendarMonth: CalendarMonth; displayIndex: number } & HTMLAttributes<HTMLDivElement>) {
    const state = useContext(CalendarCaptionContext);
    const { months: displayedMonths, goToMonth, previousMonth, nextMonth } = useDayPicker();

    const isFirst = displayIndex === 0;
    const isLast = displayIndex === displayedMonths.length - 1;

    return (
        <div className="flex items-center justify-between mb-2">
            {isFirst ? (
                <button
                    type="button"
                    className={navButtonClass}
                    disabled={!previousMonth}
                    aria-label="Previous page"
                    onClick={() => previousMonth && goToMonth(previousMonth)}
                >
                    <ChevronLeft className="h-4 w-4" />
                </button>
            ) : (
                <div className="w-7" />
            )}

            {isFirst && state ? (
                <div className="flex items-center gap-2">
                    {/* Month Selector */}
                    <select className={selectClass} value={state.selectedMonth} onChange={(e) => state.setMonth(Number(e.target.value))}>
                        {months.map((month) => (
                            <option key={month.value} value={month.value}>
                                {month.label}
                            </option>
                        ))}
                    </select>

                    {/* Year Selector */}
                    <select className={selectClass} value={state.selectedYear} onChange={(e) => state.setYear(Number(e.target.value))}>
                        {state.years.map((year) => (
                            <option key={year} value={year}>
                                {year}
                            </option>
                        ))}
                    </select>
                </div>
            ) : (
                <div className="text-sm font-medium">
                    {new Intl.DateTimeFormat(state?.locale || 'en', { month: 'long', year: 'numeric' }).format(calendarMonth.date)}
                </div>
            )}

            {isLast ? (
                <button
                    type="button"
                    className={navButtonClass}
                    disabled={!nextMonth}
                    aria-label="Next page"
                    onClick={() => nextMonth && goToMonth(nextMonth)}
                >
                    <ChevronRight className="h-4 w-4" />
                </button>
            ) : (
                <div className="w-7" />
            )}
        </div>
    );
}

// Parse string date to DateValue
const parseDateString = (dateStr: string | null | undefined): DateValue | null => {
    if (!dateStr) return null;
    try {
        // Handle various date formats from database
        // - ISO format with time: 2024-01-15T00:00:00.000000Z
        // - MySQL datetime: 2024-01-15 00:00:00
        // - Simple date: 2024-01-15
        let dateOnly = dateStr;

        // Extract just the date part (YYYY-MM-DD)
        if (dateStr.includes('T')) {
            dateOnly = dateStr.split('T')[0];
        } else if (dateStr.includes(' ')) {
            dateOnly = dateStr.split(' ')[0];
        }

        return parseDate(dateOnly);
    } catch (e) {
        console.warn('DatePicker: Failed to parse date:', dateStr, e);
        return null;
    }
};

// Convert DateValue to string
const formatDateValue = (date: DateValue | null | undefined): string | null => {
    if (!date) return null;
    return `${date.year}-${String(date.month).padStart(2, '0')}-${String(date.day).padStart(2, '0')}`;
};

export default function DatePicker({
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
    locale = 'en',
    onUpdateModelValue,
    onUpdateValue,
}: DatePickerProps) {
    const [selectedDate, setSelectedDate] = useState<DateValue | null>(() => parseDateString(modelValue || value));
    const [open, setOpen] = useState(false);

    // Watch for prop changes
    const incoming = modelValue || value;
    const previousIncoming = useRef(incoming);
    useEffect(() => {
        if (previousIncoming.current === incoming) return;
        previousIncoming.current = incoming;
        setSelectedDate(parseDateString(incoming));
    }, [incoming]);

    const minDateValue = useMemo(() => parseDateString(minDate), [minDate]);
    const maxDateValue = useMemo(() => parseDateString(maxDate), [maxDate]);

    // Month/Year navigation
    const [placeholder, setPlaceholder] = useState<CalendarDate>(() =>
        selectedDate ? toCalendarDate(selectedDate) : today(getLocalTimeZone()),
    );

    // reka keeps the calendar placeholder on the selected date
    const selectedKey = selectedDate ? selectedDate.toString() : '';
    useEffect(() => {
        if (selectedDate) {
            setPlaceholder(toCalendarDate(selectedDate));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [selectedKey]);

    const years = useMemo(buildYears, []);

    const updateDate = (date: DateValue | null | undefined) => {
        setSelectedDate(date ?? null);
        const dateStr = formatDateValue(date);
        onUpdateModelValue?.(dateStr);
        onUpdateValue?.(dateStr);
    };

    const segments = useDateSegments({
        value: selectedDate ? toCalendarDate(selectedDate) : null,
        granularity: 'day',
        locale,
        disabled,
        readonly,
        onChange: (date) => updateDate(date),
    });

    const captionState: CalendarCaptionState = {
        locale,
        years,
        selectedMonth: placeholder.month,
        selectedYear: placeholder.year,
        setMonth: (month: number) => setPlaceholder(new CalendarDate(placeholder.year, month, 1)),
        setYear: (year: number) => setPlaceholder(new CalendarDate(year, placeholder.month, 1)),
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
            {name && <input type="hidden" name={name} value={formatDateValue(selectedDate) || ''} />}

            {/* Date Picker */}
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
                                    <span key={index} {...item.props} className="px-1 tabular-nums outline-none focus:bg-accent rounded">
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
                            selected={selectedDate ? toJsDate(selectedDate) : undefined}
                            onSelect={(date: Date | undefined) => updateDate(date ? fromJsDate(date) : null)}
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
