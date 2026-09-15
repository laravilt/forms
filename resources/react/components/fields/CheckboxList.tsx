import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { useLocalization } from '@laravilt/support/composables/useLocalization';
import { resolveIcon } from '@laravilt/support/lib/icons';
import { Check, ChevronDown, ChevronRight, Minus, Search, SearchX, Shield } from 'lucide-react';
import { useEffect, useMemo, useState, type CSSProperties } from 'react';

interface Option {
    value: string | number;
    label: string;
    disabled?: boolean;
    group?: string;
    groupLabel?: string;
    action?: string;
    permissionName?: string;
}

type CheckboxListValue = (string | number)[];

export interface CheckboxListProps {
    modelValue?: CheckboxListValue;
    options: Option[];
    disabled?: boolean;
    inline?: boolean;
    columns?: number;
    gridDirection?: 'row' | 'column';
    prefixIcon?: string;
    suffixIcon?: string;
    prefixIconColor?: string;
    suffixIconColor?: string;
    searchable?: boolean;
    bulkToggleable?: boolean;
    groupBy?: string;
    groupByResource?: boolean;
    groupLabels?: Record<string, string>;
    groupSelectAll?: boolean;
    defaultGroup?: string;
    collapsible?: boolean;
    onUpdateModelValue?: (value: CheckboxListValue) => void;
    [key: string]: any;
}

const EMPTY_VALUE: CheckboxListValue = [];
const EMPTY_OPTIONS: Option[] = [];

// Helper to get Tailwind color classes for icons
const getIconColorClass = (color?: string) => {
    if (!color) return 'text-muted-foreground';
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

export default function CheckboxList({
    modelValue = EMPTY_VALUE,
    options = EMPTY_OPTIONS,
    disabled = false,
    inline = false,
    columns = 1,
    gridDirection = 'column',
    prefixIcon,
    suffixIcon,
    prefixIconColor,
    suffixIconColor,
    searchable = false,
    bulkToggleable = false,
    groupBy,
    groupByResource = false,
    groupLabels,
    groupSelectAll = true,
    collapsible = true,
    onUpdateModelValue,
}: CheckboxListProps) {
    // Initialize localization
    const { trans } = useLocalization();

    // Use a local state that syncs with props for proper reactivity
    const [localValue, setLocalValue] = useState<CheckboxListValue>(() => [...(modelValue || [])]);

    // Watch for external changes (deep)
    const modelValueKey = JSON.stringify(modelValue);
    useEffect(() => {
        setLocalValue((current) => {
            if (modelValueKey !== JSON.stringify(current)) {
                return [...(modelValue || [])];
            }
            return current;
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [modelValueKey]);

    // Helper to update value and emit
    const updateValue = (newValue: CheckboxListValue) => {
        setLocalValue([...newValue]);
        onUpdateModelValue?.(newValue);
    };

    const [searchQuery, setSearchQuery] = useState('');

    // Track collapsed state for each group
    const [collapsedGroups, setCollapsedGroups] = useState<Record<string, boolean>>({});

    // Filter options by search query
    const filteredOptions = useMemo(() => {
        if (!searchQuery) return options;
        const query = searchQuery.toLowerCase();
        return options.filter(
            (option) =>
                option.label.toLowerCase().includes(query) ||
                option.permissionName?.toLowerCase().includes(query) ||
                option.groupLabel?.toLowerCase().includes(query),
        );
    }, [options, searchQuery]);

    // Group options by the group field
    const groupedOptions = useMemo((): Record<string, Option[]> => {
        if (!groupBy && !groupByResource) {
            return { _default: filteredOptions };
        }

        const groups: Record<string, Option[]> = {};
        for (const option of filteredOptions) {
            const groupKey = option.group || '_default';
            if (!groups[groupKey]) {
                groups[groupKey] = [];
            }
            groups[groupKey].push(option);
        }

        // Sort groups alphabetically, but put _default/_other at the end
        const sortedGroups: Record<string, Option[]> = {};
        const keys = Object.keys(groups).sort((a, b) => {
            if (a === '_default' || a === '_other') return 1;
            if (b === '_default' || b === '_other') return -1;
            return a.localeCompare(b);
        });
        for (const key of keys) {
            sortedGroups[key] = groups[key];
        }

        return sortedGroups;
    }, [filteredOptions, groupBy, groupByResource]);

    // Get display label for a group
    const getGroupLabel = (groupKey: string) => {
        if (groupKey === '_default') return 'Other';
        if (groupKey === '_other') return 'Other';

        // Check if any option in this group has a groupLabel
        const groupOptions = groupedOptions[groupKey] || [];
        if (groupOptions.length > 0 && groupOptions[0].groupLabel) {
            return groupOptions[0].groupLabel;
        }

        // Check custom labels
        if (groupLabels?.[groupKey]) {
            return groupLabels[groupKey];
        }

        // Fallback: format the group key
        return groupKey
            .split('_')
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    };

    // Check if a group is collapsed
    const isGroupCollapsed = (groupKey: string) => {
        return collapsedGroups[groupKey] ?? false;
    };

    // Toggle group collapsed state
    const toggleGroupCollapse = (groupKey: string) => {
        setCollapsedGroups((current) => ({ ...current, [groupKey]: !current[groupKey] }));
    };

    // Set of checked values for reactive tracking
    const checkedSet = useMemo(() => new Set((localValue || []).map((v) => String(v))), [localValue]);

    const isChecked = (value: string | number) => {
        return checkedSet.has(String(value));
    };

    const handleChange = (optionValue: string | number, checked: boolean | 'indeterminate') => {
        const currentValues = [...(localValue || [])];
        let newValues: CheckboxListValue;

        if (checked === true) {
            // Check if value already exists using string comparison
            const alreadyExists = currentValues.some((v) => String(v) === String(optionValue));
            if (!alreadyExists) {
                newValues = [...currentValues, optionValue];
            } else {
                newValues = currentValues;
            }
        } else {
            // Remove using string comparison
            newValues = currentValues.filter((v) => String(v) !== String(optionValue));
        }

        updateValue(newValues);
    };

    // Check if all options in a group are selected
    const isGroupAllSelected = (groupKey: string) => {
        const groupOptions = groupedOptions[groupKey] || [];
        if (groupOptions.length === 0) return false;
        return groupOptions.every((option) => isChecked(option.value));
    };

    // Check if some (but not all) options in a group are selected
    const isGroupPartiallySelected = (groupKey: string) => {
        const groupOptions = groupedOptions[groupKey] || [];
        if (groupOptions.length === 0) return false;
        const selectedCount = groupOptions.filter((option) => isChecked(option.value)).length;
        return selectedCount > 0 && selectedCount < groupOptions.length;
    };

    // Get selected count for a group
    const getGroupSelectedCount = (groupKey: string) => {
        const groupOptions = groupedOptions[groupKey] || [];
        return groupOptions.filter((option) => isChecked(option.value)).length;
    };

    // Check if all options are selected (use localValue for immediate reactivity)
    const isAllSelected =
        filteredOptions.length === 0
            ? false
            : filteredOptions.every((option) => localValue?.some((v) => String(v) === String(option.value)) ?? false);

    // Check if some options are selected (use localValue for immediate reactivity)
    const isPartiallySelected = (() => {
        if (filteredOptions.length === 0) return false;
        const selectedCount = filteredOptions.filter(
            (option) => localValue?.some((v) => String(v) === String(option.value)) ?? false,
        ).length;
        return selectedCount > 0 && selectedCount < filteredOptions.length;
    })();

    // Select all globally using click handler
    const selectAllGlobal = () => {
        if (disabled) return;

        const allValues = filteredOptions.map((o) => o.value);

        if (isAllSelected) {
            updateValue([]);
        } else {
            updateValue(allValues);
        }
    };

    // Select all in a group using click handler
    const selectAllInGroup = (groupKey: string) => {
        if (disabled) return;

        const groupOptions = groupedOptions[groupKey] || [];
        const groupValues = groupOptions.map((o) => o.value);
        const groupValuesStr = groupValues.map((v) => String(v));
        const currentValues = [...(localValue || [])];

        const allSelected = isGroupAllSelected(groupKey);

        let newValues: CheckboxListValue;
        if (allSelected) {
            // Deselect all in this group
            newValues = currentValues.filter((v) => !groupValuesStr.includes(String(v)));
        } else {
            // Select all in this group
            const toAdd = groupValues.filter((v) => !currentValues.some((cv) => String(cv) === String(v)));
            newValues = [...currentValues, ...toAdd];
        }

        updateValue(newValues);
    };

    const gridClass = inline
        ? 'flex flex-wrap gap-4'
        : columns > 1
          ? gridDirection === 'row'
              ? 'grid gap-2 grid-flow-row'
              : 'grid gap-2 grid-flow-col'
          : 'flex flex-col gap-2';

    const gridStyle = ((): CSSProperties => {
        if (inline || columns <= 1) {
            return {};
        }

        if (gridDirection === 'row') {
            return {
                gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))`,
            };
        }

        const optionCount =
            groupBy || groupByResource ? Math.max(...Object.values(groupedOptions).map((g) => g.length)) : options.length;
        const rows = Math.ceil(optionCount / columns);
        return {
            gridTemplateRows: `repeat(${rows}, minmax(0, auto))`,
            gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))`,
        };
    })();

    // Check if we should show grouped layout
    const hasGroups = (groupBy || groupByResource) && Object.keys(groupedOptions).length > 0;

    const PrefixIcon = resolveIcon(prefixIcon);
    const SuffixIcon = resolveIcon(suffixIcon);

    const renderCheckbox = (option: Option) => (
        <button
            type="button"
            id={`checkbox-${option.value}`}
            role="checkbox"
            aria-checked={checkedSet.has(String(option.value))}
            disabled={disabled || option.disabled}
            className={cn(
                'peer size-4 shrink-0 rounded-[4px] border shadow-xs transition-shadow outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 flex items-center justify-center',
                checkedSet.has(String(option.value)) ? 'bg-primary border-primary text-primary-foreground' : 'border-input',
            )}
            onClick={() => handleChange(option.value, !checkedSet.has(String(option.value)))}
        >
            {checkedSet.has(String(option.value)) && <Check className="size-3" />}
        </button>
    );

    return (
        <div className="space-y-3">
            {/* Search input */}
            {searchable && (
                <div className="relative">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        type="text"
                        placeholder={trans('forms::forms.checkbox_list.search_placeholder')}
                        className="pl-9"
                    />
                </div>
            )}

            {/* Global Select All */}
            {bulkToggleable && (
                <div className="flex items-center justify-between pb-3 border-b mb-3">
                    <button
                        type="button"
                        className="flex items-center gap-2 cursor-pointer select-none hover:bg-muted/50 rounded px-2 py-1 -mx-2 transition-colors"
                        disabled={disabled}
                        onClick={selectAllGlobal}
                    >
                        <div
                            className={cn(
                                'flex items-center justify-center size-4 rounded-[4px] border shadow-xs shrink-0',
                                isAllSelected
                                    ? 'bg-primary border-primary text-primary-foreground'
                                    : isPartiallySelected
                                      ? 'bg-primary border-primary text-primary-foreground'
                                      : 'border-input',
                            )}
                        >
                            {isAllSelected ? <Check className="size-3" /> : isPartiallySelected ? <Minus className="size-3" /> : null}
                        </div>
                        <span className="text-sm font-semibold">{trans('forms::forms.checkbox_list.select_all_permissions')}</span>
                    </button>
                    <span className="text-xs text-muted-foreground">
                        {localValue?.length || 0}/{filteredOptions.length} {trans('forms::forms.checkbox_list.selected')}
                    </span>
                </div>
            )}

            {/* Grouped Layout */}
            {hasGroups ? (
                Object.entries(groupedOptions).map(([groupKey, groupOptions]) => (
                    <div key={groupKey} className="rounded-lg border bg-card text-card-foreground shadow-sm">
                        {/* Group Header */}
                        <div
                            className={cn('flex items-center justify-between bg-muted/50 px-4 py-2.5', {
                                'cursor-pointer': collapsible,
                            })}
                            onClick={() => (collapsible ? toggleGroupCollapse(groupKey) : null)}
                        >
                            <div className="flex items-center gap-2">
                                {/* Collapse Toggle */}
                                {collapsible && (
                                    <button
                                        type="button"
                                        className="p-0.5 hover:bg-muted rounded transition-colors"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            toggleGroupCollapse(groupKey);
                                        }}
                                    >
                                        {isGroupCollapsed(groupKey) ? (
                                            <ChevronRight className="h-4 w-4 text-muted-foreground transition-transform" />
                                        ) : (
                                            <ChevronDown className="h-4 w-4 text-muted-foreground transition-transform" />
                                        )}
                                    </button>
                                )}

                                {/* Group Icon & Label */}
                                <Shield className="h-4 w-4 text-primary" />
                                <span className="text-sm font-semibold">{getGroupLabel(groupKey)}</span>
                                <span className="text-xs text-muted-foreground">
                                    ({getGroupSelectedCount(groupKey)}/{groupOptions.length})
                                </span>
                            </div>

                            {/* Select All Toggle */}
                            {groupSelectAll && (
                                <button
                                    type="button"
                                    className="flex items-center gap-2 cursor-pointer select-none hover:bg-muted rounded px-2 py-1 transition-colors"
                                    disabled={disabled}
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        selectAllInGroup(groupKey);
                                    }}
                                >
                                    <div
                                        className={cn(
                                            'flex items-center justify-center size-4 rounded-[4px] border shadow-xs shrink-0',
                                            isGroupAllSelected(groupKey)
                                                ? 'bg-primary border-primary text-primary-foreground'
                                                : isGroupPartiallySelected(groupKey)
                                                  ? 'bg-primary border-primary text-primary-foreground'
                                                  : 'border-input',
                                        )}
                                    >
                                        {isGroupAllSelected(groupKey) ? (
                                            <Check className="size-3" />
                                        ) : isGroupPartiallySelected(groupKey) ? (
                                            <Minus className="size-3" />
                                        ) : null}
                                    </div>
                                    <span className="text-xs font-medium text-muted-foreground">
                                        {trans('forms::forms.checkbox_list.select_all')}
                                    </span>
                                </button>
                            )}
                        </div>

                        {/* Group Content (Collapsible) */}
                        <div className="p-3" style={isGroupCollapsed(groupKey) ? { display: 'none' } : undefined}>
                            <div className={gridClass} style={gridStyle}>
                                {groupOptions.map((option) => (
                                    <label
                                        key={option.value}
                                        htmlFor={`checkbox-${option.value}`}
                                        className={cn(
                                            'flex items-center gap-2 cursor-pointer hover:bg-muted/50 rounded px-2 py-1.5 -mx-2 transition-colors',
                                            { 'opacity-50 cursor-not-allowed': disabled || option.disabled },
                                        )}
                                    >
                                        {/* Checkbox (custom for proper reactivity) */}
                                        {renderCheckbox(option)}

                                        {/* Label */}
                                        <span className="text-sm font-medium leading-none select-none">{option.label}</span>
                                    </label>
                                ))}
                            </div>
                        </div>
                    </div>
                ))
            ) : (
                /* Non-grouped Layout */
                <div className={gridClass} style={gridStyle}>
                    {filteredOptions.map((option) => (
                        <label
                            key={option.value}
                            htmlFor={`checkbox-${option.value}`}
                            className={cn('flex items-center gap-2 cursor-pointer hover:bg-muted/50 rounded px-2 py-1.5 transition-colors', {
                                'opacity-50 cursor-not-allowed': disabled || option.disabled,
                            })}
                        >
                            {/* Prefix icon */}
                            {PrefixIcon && <PrefixIcon className={cn('h-4 w-4', getIconColorClass(prefixIconColor))} />}

                            {/* Checkbox (custom for proper reactivity) */}
                            {renderCheckbox(option)}

                            {/* Label */}
                            <span className="text-sm font-medium leading-none select-none">{option.label}</span>

                            {/* Suffix icon */}
                            {SuffixIcon && <SuffixIcon className={cn('h-4 w-4', getIconColorClass(suffixIconColor))} />}
                        </label>
                    ))}
                </div>
            )}

            {/* Empty state */}
            {filteredOptions.length === 0 && (
                <div className="text-center py-8 text-muted-foreground">
                    <SearchX className="h-10 w-10 mx-auto mb-3 opacity-40" />
                    <p className="text-sm">No permissions found</p>
                </div>
            )}
        </div>
    );
}
