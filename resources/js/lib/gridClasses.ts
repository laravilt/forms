// Tailwind only generates classes that appear as complete strings in scanned source files, so
// grid classes are looked up in these static maps instead of being built from template strings.

type Breakpoint = 'default' | 'sm' | 'md' | 'lg' | 'xl' | '2xl';

const GRID_COLS: Record<Breakpoint, string[]> = {
    default: [
        'grid-cols-1', 'grid-cols-2', 'grid-cols-3', 'grid-cols-4', 'grid-cols-5', 'grid-cols-6',
        'grid-cols-7', 'grid-cols-8', 'grid-cols-9', 'grid-cols-10', 'grid-cols-11', 'grid-cols-12',
    ],
    sm: [
        'sm:grid-cols-1', 'sm:grid-cols-2', 'sm:grid-cols-3', 'sm:grid-cols-4', 'sm:grid-cols-5', 'sm:grid-cols-6',
        'sm:grid-cols-7', 'sm:grid-cols-8', 'sm:grid-cols-9', 'sm:grid-cols-10', 'sm:grid-cols-11', 'sm:grid-cols-12',
    ],
    md: [
        'md:grid-cols-1', 'md:grid-cols-2', 'md:grid-cols-3', 'md:grid-cols-4', 'md:grid-cols-5', 'md:grid-cols-6',
        'md:grid-cols-7', 'md:grid-cols-8', 'md:grid-cols-9', 'md:grid-cols-10', 'md:grid-cols-11', 'md:grid-cols-12',
    ],
    lg: [
        'lg:grid-cols-1', 'lg:grid-cols-2', 'lg:grid-cols-3', 'lg:grid-cols-4', 'lg:grid-cols-5', 'lg:grid-cols-6',
        'lg:grid-cols-7', 'lg:grid-cols-8', 'lg:grid-cols-9', 'lg:grid-cols-10', 'lg:grid-cols-11', 'lg:grid-cols-12',
    ],
    xl: [
        'xl:grid-cols-1', 'xl:grid-cols-2', 'xl:grid-cols-3', 'xl:grid-cols-4', 'xl:grid-cols-5', 'xl:grid-cols-6',
        'xl:grid-cols-7', 'xl:grid-cols-8', 'xl:grid-cols-9', 'xl:grid-cols-10', 'xl:grid-cols-11', 'xl:grid-cols-12',
    ],
    '2xl': [
        '2xl:grid-cols-1', '2xl:grid-cols-2', '2xl:grid-cols-3', '2xl:grid-cols-4', '2xl:grid-cols-5', '2xl:grid-cols-6',
        '2xl:grid-cols-7', '2xl:grid-cols-8', '2xl:grid-cols-9', '2xl:grid-cols-10', '2xl:grid-cols-11', '2xl:grid-cols-12',
    ],
};

const COL_SPANS: Record<Breakpoint, string[]> = {
    default: [
        'col-span-1', 'col-span-2', 'col-span-3', 'col-span-4', 'col-span-5', 'col-span-6',
        'col-span-7', 'col-span-8', 'col-span-9', 'col-span-10', 'col-span-11', 'col-span-12',
    ],
    sm: [
        'sm:col-span-1', 'sm:col-span-2', 'sm:col-span-3', 'sm:col-span-4', 'sm:col-span-5', 'sm:col-span-6',
        'sm:col-span-7', 'sm:col-span-8', 'sm:col-span-9', 'sm:col-span-10', 'sm:col-span-11', 'sm:col-span-12',
    ],
    md: [
        'md:col-span-1', 'md:col-span-2', 'md:col-span-3', 'md:col-span-4', 'md:col-span-5', 'md:col-span-6',
        'md:col-span-7', 'md:col-span-8', 'md:col-span-9', 'md:col-span-10', 'md:col-span-11', 'md:col-span-12',
    ],
    lg: [
        'lg:col-span-1', 'lg:col-span-2', 'lg:col-span-3', 'lg:col-span-4', 'lg:col-span-5', 'lg:col-span-6',
        'lg:col-span-7', 'lg:col-span-8', 'lg:col-span-9', 'lg:col-span-10', 'lg:col-span-11', 'lg:col-span-12',
    ],
    xl: [
        'xl:col-span-1', 'xl:col-span-2', 'xl:col-span-3', 'xl:col-span-4', 'xl:col-span-5', 'xl:col-span-6',
        'xl:col-span-7', 'xl:col-span-8', 'xl:col-span-9', 'xl:col-span-10', 'xl:col-span-11', 'xl:col-span-12',
    ],
    '2xl': [
        '2xl:col-span-1', '2xl:col-span-2', '2xl:col-span-3', '2xl:col-span-4', '2xl:col-span-5', '2xl:col-span-6',
        '2xl:col-span-7', '2xl:col-span-8', '2xl:col-span-9', '2xl:col-span-10', '2xl:col-span-11', '2xl:col-span-12',
    ],
};

const COL_SPAN_FULL: Record<Breakpoint, string> = {
    default: 'col-span-full',
    sm: 'sm:col-span-full',
    md: 'md:col-span-full',
    lg: 'lg:col-span-full',
    xl: 'xl:col-span-full',
    '2xl': '2xl:col-span-full',
};

/** Static `grid-cols-N` class (1-12) for a breakpoint, or an empty string. */
export const gridColsClass = (count: unknown, breakpoint: string = 'default'): string =>
    (GRID_COLS as Record<string, string[] | undefined>)[breakpoint]?.[Number(count) - 1] ?? '';

/** Static `col-span-N` / `col-span-full` class for a breakpoint, or an empty string. */
export const colSpanClass = (span: unknown, breakpoint: string = 'default'): string => {
    if (span === 'full') {
        return (COL_SPAN_FULL as Record<string, string | undefined>)[breakpoint] ?? '';
    }

    return (COL_SPANS as Record<string, string[] | undefined>)[breakpoint]?.[Number(span) - 1] ?? '';
};
