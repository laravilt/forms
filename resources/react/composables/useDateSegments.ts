import { CalendarDate, CalendarDateTime } from '@internationalized/date';
import { useEffect, useMemo, useRef, useState, type FocusEvent, type KeyboardEvent } from 'react';

/**
 * React replacement for reka-ui's segmented date field (DatePickerField / DatePickerInput segments):
 * keyboard-editable month/day/year (+ hour/minute/dayPeriod) segments laid out in locale order.
 * Used by DatePicker, DateTimePicker and DateRangePicker.
 */

export type DateSegmentPart = 'year' | 'month' | 'day' | 'hour' | 'minute' | 'dayPeriod' | 'literal';
type NumericPart = 'year' | 'month' | 'day' | 'hour' | 'minute';
type SegmentValues = Record<NumericPart, number | null>;

export interface DateSegmentItem {
    part: DateSegmentPart;
    value: string;
    isPlaceholder: boolean;
    props: Record<string, any>;
}

export interface UseDateSegmentsOptions {
    value: CalendarDate | CalendarDateTime | null;
    granularity?: 'day' | 'minute';
    locale?: string;
    hourCycle?: 12 | 24;
    disabled?: boolean;
    readonly?: boolean;
    onChange: (value: CalendarDate | CalendarDateTime | null) => void;
}

const NUMERIC_PARTS: NumericPart[] = ['year', 'month', 'day', 'hour', 'minute'];
const KNOWN_PARTS = ['year', 'month', 'day', 'hour', 'minute', 'dayPeriod'];

const PLACEHOLDERS: Record<Exclude<DateSegmentPart, 'literal'>, string> = {
    year: 'yyyy',
    month: 'mm',
    day: 'dd',
    hour: '––',
    minute: '––',
    dayPeriod: 'AM',
};

const daysInMonth = (year: number, month: number) => new Date(year, month, 0).getDate();

const fromValue = (value: CalendarDate | CalendarDateTime | null): SegmentValues => ({
    year: value ? value.year : null,
    month: value ? value.month : null,
    day: value ? value.day : null,
    hour: value && 'hour' in value ? value.hour : null,
    minute: value && 'minute' in value ? value.minute : null,
});

export function useDateSegments({
    value,
    granularity = 'day',
    locale = 'en',
    hourCycle = 24,
    disabled,
    readonly,
    onChange,
}: UseDateSegmentsOptions): DateSegmentItem[] {
    const [values, setValuesState] = useState<SegmentValues>(() => fromValue(value));
    const valuesRef = useRef<SegmentValues>(values);
    const [period, setPeriod] = useState<'AM' | 'PM'>('AM');
    const wasComplete = useRef(value !== null);
    const buffer = useRef<{ part: DateSegmentPart | null; text: string }>({ part: null, text: '' });
    const elements = useRef<Array<HTMLElement | null>>([]);
    const onChangeRef = useRef(onChange);
    onChangeRef.current = onChange;

    const setValues = (next: SegmentValues) => {
        valuesRef.current = next;
        setValuesState(next);
    };

    // Sync from the model value. Like reka's passive v-model, a null value leaves the typed segments alone.
    const valueKey = value ? value.toString() : '';
    const syncedKey = useRef(valueKey);
    useEffect(() => {
        if (!value || syncedKey.current === valueKey) {
            syncedKey.current = valueKey;
            return;
        }
        syncedKey.current = valueKey;
        wasComplete.current = true;
        setValues(fromValue(value));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [valueKey]);

    const withTime = granularity === 'minute';

    // Segment order and literals, from the locale
    const layout = useMemo(() => {
        const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: '2-digit', day: '2-digit' };
        if (withTime) {
            options.hour = '2-digit';
            options.minute = '2-digit';
            options.hourCycle = hourCycle === 12 ? 'h12' : 'h23';
        }
        const sample = new Date(2000, 10, 22, 13, 45);
        let parts: Intl.DateTimeFormatPart[];
        try {
            parts = new Intl.DateTimeFormat(locale, options).formatToParts(sample);
        } catch {
            parts = new Intl.DateTimeFormat('en', options).formatToParts(sample);
        }
        return parts.map((part) =>
            KNOWN_PARTS.includes(part.type)
                ? { part: part.type as DateSegmentPart, literal: '' }
                : { part: 'literal' as DateSegmentPart, literal: part.value },
        );
    }, [locale, withTime, hourCycle]);

    const editableIndexes = layout.map((item, index) => (item.part === 'literal' ? -1 : index)).filter((index) => index >= 0);

    const bounds = (part: NumericPart, current: SegmentValues): [number, number] => {
        switch (part) {
            case 'year':
                return [1, 9999];
            case 'month':
                return [1, 12];
            case 'day':
                return [1, daysInMonth(current.year ?? 2000, current.month ?? 1)];
            case 'hour':
                return hourCycle === 12 ? [1, 12] : [0, 23];
            case 'minute':
                return [0, 59];
        }
    };

    const commit = (next: SegmentValues) => {
        setValues(next);

        const required: NumericPart[] = withTime ? NUMERIC_PARTS : ['year', 'month', 'day'];
        if (required.every((part) => next[part] !== null)) {
            const year = next.year as number;
            const month = next.month as number;
            const day = Math.min(next.day as number, daysInMonth(year, month));
            const date = withTime
                ? new CalendarDateTime(year, month, day, next.hour as number, next.minute as number)
                : new CalendarDate(year, month, day);
            wasComplete.current = true;
            syncedKey.current = date.toString();
            onChangeRef.current(date);
        } else if (wasComplete.current) {
            wasComplete.current = false;
            onChangeRef.current(null);
        }
    };

    const focusSibling = (index: number, direction: 1 | -1) => {
        const position = editableIndexes.indexOf(index);
        const target = editableIndexes[position + direction];
        if (target !== undefined) {
            elements.current[target]?.focus();
        }
    };

    const isPm = (current: SegmentValues) => (current.hour !== null ? current.hour >= 12 : period === 'PM');

    // hour is stored 0-23; in 12h mode the displayed/typed value is 1-12
    const toStoredHour = (displayHour: number, pm: boolean) => (hourCycle === 12 ? (displayHour % 12) + (pm ? 12 : 0) : displayHour);

    const displayNumber = (part: NumericPart, current: SegmentValues): number | null => {
        const raw = current[part];
        if (raw === null) return null;
        if (part === 'hour' && hourCycle === 12) return raw % 12 || 12;
        return raw;
    };

    const step = (part: DateSegmentPart, delta: 1 | -1) => {
        const current = valuesRef.current;

        if (part === 'dayPeriod') {
            if (current.hour === null) {
                setPeriod((p) => (p === 'AM' ? 'PM' : 'AM'));
                return;
            }
            commit({ ...current, hour: (current.hour + 12) % 24 });
            return;
        }

        const numericPart = part as NumericPart;
        const now = new Date();
        const nowValues: SegmentValues = {
            year: now.getFullYear(),
            month: now.getMonth() + 1,
            day: now.getDate(),
            hour: now.getHours(),
            minute: now.getMinutes(),
        };

        if (current[numericPart] === null) {
            commit({ ...current, [numericPart]: nowValues[numericPart] });
            return;
        }

        const [min, max] = bounds(numericPart, current);
        const shown = displayNumber(numericPart, current) as number;
        let next = shown + delta;

        if (numericPart === 'year') {
            next = Math.min(Math.max(next, min), max);
        } else if (next > max) {
            next = min;
        } else if (next < min) {
            next = max;
        }

        const stored = numericPart === 'hour' ? toStoredHour(next, isPm(current)) : next;
        commit({ ...current, [numericPart]: stored });
    };

    const typeDigit = (part: NumericPart, index: number, digit: string) => {
        const current = valuesRef.current;
        const [, max] = bounds(part, current);
        const maxDigits = part === 'year' ? 4 : 2;

        let text = (buffer.current.part === part ? buffer.current.text : '') + digit;
        let parsed = Number(text);

        if (part !== 'year' && parsed > max) {
            text = digit;
            parsed = Number(digit);
        }

        buffer.current = { part, text };

        const shouldAdvance = text.length >= maxDigits || parsed * 10 > max;

        // A leading "0" for year/month/day is pending input, not a value
        const isPendingZero = parsed === 0 && (part === 'year' || part === 'month' || part === 'day');
        if (!isPendingZero) {
            const stored = part === 'hour' ? toStoredHour(parsed, isPm(current)) : parsed;
            commit({ ...current, [part]: stored });
        }

        if (shouldAdvance) {
            buffer.current = { part: null, text: '' };
            focusSibling(index, 1);
        }
    };

    const erase = (part: NumericPart) => {
        const current = valuesRef.current;
        const source = buffer.current.part === part ? buffer.current.text : String(displayNumber(part, current) ?? '');
        const text = source.slice(0, -1);
        buffer.current = { part, text };

        if (text === '') {
            commit({ ...current, [part]: null });
            return;
        }

        const parsed = Number(text);
        commit({ ...current, [part]: part === 'hour' ? toStoredHour(parsed, isPm(current)) : parsed });
    };

    const handleKeyDown = (part: DateSegmentPart, index: number) => (event: KeyboardEvent<HTMLElement>) => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            focusSibling(index, event.key === 'ArrowRight' ? 1 : -1);
            return;
        }

        if (disabled || readonly) {
            return;
        }

        if (event.key === 'ArrowUp' || event.key === 'ArrowDown') {
            event.preventDefault();
            buffer.current = { part: null, text: '' };
            step(part, event.key === 'ArrowUp' ? 1 : -1);
            return;
        }

        if (part === 'dayPeriod') {
            const key = event.key.toLowerCase();
            if (key === 'a' || key === 'p') {
                event.preventDefault();
                const current = valuesRef.current;
                const wantPm = key === 'p';
                setPeriod(wantPm ? 'PM' : 'AM');
                if (current.hour !== null && current.hour >= 12 !== wantPm) {
                    commit({ ...current, hour: (current.hour + 12) % 24 });
                }
            }
            return;
        }

        if (event.key === 'Backspace' || event.key === 'Delete') {
            event.preventDefault();
            erase(part as NumericPart);
            return;
        }

        if (/^\d$/.test(event.key)) {
            event.preventDefault();
            typeDigit(part as NumericPart, index, event.key);
        }
    };

    return layout.map((item, index) => {
        if (item.part === 'literal') {
            return { part: 'literal', value: item.literal, isPlaceholder: false, props: { 'aria-hidden': true } };
        }

        const part = item.part;
        let text: string;
        let isPlaceholder = false;

        if (part === 'dayPeriod') {
            text = values.hour !== null ? (values.hour >= 12 ? 'PM' : 'AM') : period;
            isPlaceholder = values.hour === null;
        } else {
            const shown = displayNumber(part as NumericPart, values);
            isPlaceholder = shown === null;
            text = shown === null ? PLACEHOLDERS[part] : String(shown).padStart(part === 'year' ? 4 : 2, '0');
        }

        return {
            part,
            value: text,
            isPlaceholder,
            props: {
                ref: (element: HTMLElement | null) => {
                    elements.current[index] = element;
                },
                role: 'spinbutton',
                tabIndex: disabled ? -1 : 0,
                'aria-label': part,
                'aria-valuenow': part === 'dayPeriod' ? undefined : (values[part as NumericPart] ?? undefined),
                'aria-valuetext': text,
                'aria-disabled': disabled || undefined,
                'aria-readonly': readonly || undefined,
                'data-segment': part,
                'data-placeholder': isPlaceholder ? '' : undefined,
                onKeyDown: handleKeyDown(part, index),
                onFocus: (_event: FocusEvent<HTMLElement>) => {
                    buffer.current = { part: null, text: '' };
                },
            },
        };
    });
}
