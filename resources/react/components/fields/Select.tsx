import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import { useSchemaContext } from '@laravilt/support/composables/contexts';
import { useLatest } from '@laravilt/support/composables/hooks';
import { useLocalization } from '@laravilt/support/composables/useLocalization';
import { resolveIcon } from '@laravilt/support/lib/icons';
import { Check, ChevronDown, Edit, LoaderCircle, Plus } from 'lucide-react';
import { Popover as PopoverPrimitive } from 'radix-ui';
import {
    useEffect,
    useReducer,
    useRef,
    useState,
    type ComponentType,
    type KeyboardEvent,
    type ReactNode,
    type UIEvent,
} from 'react';
import { useFormData } from '../../composables/useFormData';
import { useReactiveField } from '../../composables/useReactiveField';
import Textarea from './Textarea';
import TextInput from './TextInput';
import Toggle from './Toggle';

interface Option {
    value: string;
    label: string;
    disabled?: boolean;
}

type SelectModel = string | string[] | number | number[] | null;

export interface SelectProps {
    name?: string;
    value?: SelectModel;
    modelValue?: SelectModel;
    label?: string;
    resourceSlug?: string;
    fieldName?: string;
    placeholder?: string;
    multiple?: boolean;
    disabled?: boolean;
    required?: boolean;
    helperText?: string;
    loadingMessage?: string;
    noSearchResultsMessage?: string;
    searchPrompt?: string;
    searchingMessage?: string;
    searchDebounce?: number;
    searchableColumns?: string[];
    allowHtml?: boolean;
    wrapOptionLabels?: boolean;
    selectablePlaceholder?: boolean;
    prefix?: string;
    suffix?: string;
    prefixIcon?: string;
    suffixIcon?: string;
    prefixIconColor?: string;
    suffixIconColor?: string;
    optionsLimit?: number;
    minItems?: number;
    maxItems?: number;
    hasCreateOptionForm?: boolean;
    hasEditOptionForm?: boolean;
    optionsAreGrouped?: boolean;
    options?: Option[]; // Static options from backend
    createOptionForm?: any[]; // Form schema for creating options
    editOptionForm?: any[]; // Form schema for editing options
    dependsOn?: string[]; // Fields this select depends on for reactive loading
    optionsUrl?: string; // API endpoint for loading dynamic options
    hasDynamicOptions?: boolean; // Whether options are closure-based (evaluated server-side)
    relationshipSearchUrl?: string; // API endpoint for searching relationship options
    relationshipOptionsUrl?: string; // API endpoint for loading specific relationship options by ID
    relationshipModel?: string; // The parent model class for relationship selects
    hasMoreOptions?: boolean; // Whether there are more options available (closure-based options)
    closureOptionsUrl?: string; // API endpoint for loading more closure-based options
    relationManager?: string; // Relation manager class name (for Select inside relation managers)
    live?: boolean | string | number; // Reactive mode
    isLive?: boolean; // Whether field triggers reactive updates
    isLazy?: boolean; // Whether field triggers debounced reactive updates
    liveDebounce?: number; // Debounce delay for reactive updates
    meta?: Record<string, any>; // Additional metadata (e.g., dependentOptions)
    // Text labels for i18n support
    createModalTitle?: string;
    createModalDescription?: string;
    editModalTitle?: string;
    editModalDescription?: string;
    cancelButtonLabel?: string;
    createButtonLabel?: string;
    creatingButtonLabel?: string;
    saveChangesButtonLabel?: string;
    savingButtonLabel?: string;
    scrollForMoreLabel?: string;
    searchPlaceholder?: string;
    tryAdjustingSearchLabel?: string;
    minItemsValidationMessage?: string;
    maxItemsValidationMessage?: string;
    onUpdateModelValue?: (value: string | string[] | null) => void;
    onUpdateValue?: (value: string | string[] | null) => void;
    [key: string]: any;
}

const EMPTY_OPTIONS: Option[] = [];
const EMPTY_FORM: any[] = [];
const EMPTY_STRINGS: string[] = [];
const EMPTY_META: Record<string, any> = {};

// Component mapping for dynamic form rendering
const componentMap: Record<string, ComponentType<any>> = {
    TextInput: TextInput,
    Textarea: Textarea,
    Toggle: Toggle,
};

// Helper to get component from schema
const getComponent = (componentName: string): ComponentType<any> | null => {
    return componentMap[componentName] || null;
};

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

const normalizeIncoming = (newValue: SelectModel | undefined): string[] => {
    if (newValue === null || newValue === undefined || (newValue as any) === '') {
        return [];
    } else if (Array.isArray(newValue)) {
        return (newValue as Array<string | number>).map((v) => String(v)).filter((v) => v !== '');
    }
    return [String(newValue)];
};

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

export default function Select(rawProps: SelectProps) {
    const props = {
        ...rawProps,
        placeholder: rawProps.placeholder ?? 'Select an option',
        multiple: rawProps.multiple ?? false,
        disabled: rawProps.disabled ?? false,
        required: rawProps.required ?? false,
        loadingMessage: rawProps.loadingMessage ?? 'Loading options...',
        noSearchResultsMessage: rawProps.noSearchResultsMessage ?? 'No results found',
        searchPrompt: rawProps.searchPrompt ?? 'Start typing to search...',
        searchingMessage: rawProps.searchingMessage ?? 'Searching...',
        searchDebounce: rawProps.searchDebounce ?? 1000,
        searchableColumns: rawProps.searchableColumns ?? EMPTY_STRINGS,
        allowHtml: rawProps.allowHtml ?? false,
        wrapOptionLabels: rawProps.wrapOptionLabels ?? true,
        selectablePlaceholder: rawProps.selectablePlaceholder ?? true,
        optionsLimit: rawProps.optionsLimit ?? 50,
        hasCreateOptionForm: rawProps.hasCreateOptionForm ?? false,
        hasEditOptionForm: rawProps.hasEditOptionForm ?? false,
        optionsAreGrouped: rawProps.optionsAreGrouped ?? false,
        options: rawProps.options ?? EMPTY_OPTIONS,
        createOptionForm: rawProps.createOptionForm ?? EMPTY_FORM,
        editOptionForm: rawProps.editOptionForm ?? EMPTY_FORM,
        dependsOn: rawProps.dependsOn ?? EMPTY_STRINGS,
        live: rawProps.live ?? false,
        isLive: rawProps.isLive ?? false,
        isLazy: rawProps.isLazy ?? false,
        liveDebounce: rawProps.liveDebounce ?? 500,
        meta: rawProps.meta ?? EMPTY_META,
        createModalTitle: rawProps.createModalTitle ?? 'Create New Option',
        createModalDescription:
            rawProps.createModalDescription ?? 'Create a new option for this field. It will be automatically selected after creation.',
        editModalTitle: rawProps.editModalTitle ?? 'Edit Option',
        editModalDescription: rawProps.editModalDescription ?? 'Update the option details. Changes will be reflected immediately.',
        cancelButtonLabel: rawProps.cancelButtonLabel ?? 'Cancel',
        createButtonLabel: rawProps.createButtonLabel ?? 'Create',
        creatingButtonLabel: rawProps.creatingButtonLabel ?? 'Creating...',
        saveChangesButtonLabel: rawProps.saveChangesButtonLabel ?? 'Save Changes',
        savingButtonLabel: rawProps.savingButtonLabel ?? 'Saving...',
        scrollForMoreLabel: rawProps.scrollForMoreLabel ?? 'Scroll for more',
        searchPlaceholder: rawProps.searchPlaceholder ?? 'Search...',
        tryAdjustingSearchLabel: rawProps.tryAdjustingSearchLabel ?? 'Try adjusting your search',
    };
    const latestProps = useLatest(props);

    // Initialize localization
    const { trans } = useLocalization();

    // Modal state for create/edit
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [showEditModal, setShowEditModal] = useState(false);
    const [editingOptionId, setEditingOptionId] = useState<string | null>(null);

    // Translated labels
    const translatedPlaceholder = props.placeholder !== 'Select an option' ? props.placeholder : trans('forms::forms.select.placeholder');
    const translatedLoadingMessage =
        props.loadingMessage !== 'Loading options...' ? props.loadingMessage : trans('forms::forms.select.loading_message');
    const translatedNoSearchResultsMessage =
        props.noSearchResultsMessage !== 'No results found' ? props.noSearchResultsMessage : trans('forms::forms.select.no_search_results_message');
    const translatedSearchPrompt =
        props.searchPrompt !== 'Start typing to search...' ? props.searchPrompt : trans('forms::forms.select.search_prompt');
    const translatedSearchingMessage =
        props.searchingMessage !== 'Searching...' ? props.searchingMessage : trans('forms::forms.select.searching_message');
    const translatedCreateModalTitle =
        props.createModalTitle !== 'Create New Option' ? props.createModalTitle : trans('forms::forms.select.create_modal_title');
    const translatedCreateModalDescription =
        props.createModalDescription !== 'Create a new option for this field. It will be automatically selected after creation.'
            ? props.createModalDescription
            : trans('forms::forms.select.create_modal_description');
    const translatedEditModalTitle = props.editModalTitle !== 'Edit Option' ? props.editModalTitle : trans('forms::forms.select.edit_modal_title');
    const translatedEditModalDescription =
        props.editModalDescription !== 'Update the option details. Changes will be reflected immediately.'
            ? props.editModalDescription
            : trans('forms::forms.select.edit_modal_description');
    const translatedCancelButtonLabel = props.cancelButtonLabel !== 'Cancel' ? props.cancelButtonLabel : trans('forms::forms.select.cancel_button_label');
    const translatedCreateButtonLabel = props.createButtonLabel !== 'Create' ? props.createButtonLabel : trans('forms::forms.select.create_button_label');
    const translatedCreatingButtonLabel =
        props.creatingButtonLabel !== 'Creating...' ? props.creatingButtonLabel : trans('forms::forms.select.creating_button_label');
    const translatedSaveChangesButtonLabel =
        props.saveChangesButtonLabel !== 'Save Changes' ? props.saveChangesButtonLabel : trans('forms::forms.select.save_changes_button_label');
    const translatedSavingButtonLabel = props.savingButtonLabel !== 'Saving...' ? props.savingButtonLabel : trans('forms::forms.select.saving_button_label');
    const translatedScrollForMoreLabel =
        props.scrollForMoreLabel !== 'Scroll for more' ? props.scrollForMoreLabel : trans('forms::forms.select.scroll_for_more_label');
    const translatedSearchPlaceholder = props.searchPlaceholder !== 'Search...' ? props.searchPlaceholder : trans('forms::forms.select.search_placeholder');
    const translatedTryAdjustingSearchLabel =
        props.tryAdjustingSearchLabel !== 'Try adjusting your search'
            ? props.tryAdjustingSearchLabel
            : trans('forms::forms.select.try_adjusting_search_label');

    const [open, setOpenState] = useState(false);
    const openRef = useRef(false);
    const setOpen = (next: boolean) => {
        openRef.current = next;
        setOpenState(next);
    };

    const [searchTerm, setSearchTermState] = useState('');
    const setSearchTerm = (next: string) => setSearchTermState(next);

    const [options, setOptionsState] = useState<Option[]>([]);
    const optionsRef = useRef<Option[]>([]);
    const setOptions = (next: Option[]) => {
        optionsRef.current = next;
        setOptionsState(next);
    };

    const selectedOptionsCache = useRef<Map<string, string>>(new Map()); // Cache selected option labels
    const [, bumpCache] = useReducer((x: number) => x + 1, 0);

    const [loading, setLoadingState] = useState(false);
    const loadingRef = useRef(false);
    const setLoading = (next: boolean) => {
        loadingRef.current = next;
        setLoadingState(next);
    };

    const [hasMore, setHasMoreState] = useState(false);
    const hasMoreRef = useRef(false);
    const setHasMore = (next: boolean) => {
        hasMoreRef.current = next;
        setHasMoreState(next);
    };

    const page = useRef(1);
    const [, setInitialLoadComplete] = useState(false);
    const scrollContainer = useRef<HTMLDivElement | null>(null);
    const anchorRef = useRef<HTMLDivElement | null>(null);
    const [highlightedIndex, setHighlightedIndex] = useState(-1);

    // Internal state for selected values (+ watch for prop changes, immediate)
    const incoming = props.value ?? props.modelValue;
    const incomingKey = JSON.stringify(incoming ?? null);
    const [internalSelectedValues, setInternalSelectedValuesState] = useState<string[]>(() => normalizeIncoming(incoming));
    const internalSelectedValuesRef = useRef<string[]>(internalSelectedValues);
    const setInternalSelectedValues = (next: string[]) => {
        internalSelectedValuesRef.current = next;
        setInternalSelectedValuesState(next);
    };

    const previousIncomingKey = useRef(incomingKey);
    useEffect(() => {
        if (previousIncomingKey.current === incomingKey) return;
        previousIncomingKey.current = incomingKey;
        setInternalSelectedValues(normalizeIncoming(incoming));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [incomingKey]);

    const selectedValues = internalSelectedValues;

    // Selected values setter - writes to internal state and emits
    const setSelectedValues = (value: string | string[] | null | undefined) => {
        // Normalize to array
        let values: string[];
        if (Array.isArray(value)) {
            values = value;
        } else if (value !== null && value !== undefined) {
            values = [String(value)];
        } else {
            values = [];
        }

        // Filter out empty strings and invalid values
        const validValues = values.filter((v) => v && String(v).trim() !== '');

        // Update internal state
        setInternalSelectedValues(validValues);

        // Emit to parent
        const { multiple, onUpdateModelValue, onUpdateValue } = latestProps.current;
        if (multiple) {
            onUpdateModelValue?.(validValues.length > 0 ? validValues : null);
            onUpdateValue?.(validValues.length > 0 ? validValues : null);
        } else {
            onUpdateModelValue?.(validValues.length > 0 ? validValues[0] : null);
            onUpdateValue?.(validValues.length > 0 ? validValues[0] : null);
        }
    };

    // Form dependencies (Vue: inject('getFormData') / inject('formController') / inject('formMethod') / inject('updateSchema'))
    const schemaContext = useSchemaContext();
    const latestSchemaContext = useLatest(schemaContext);

    // Vue calls an undefined `getFilteredOptions`; it is taken from useReactiveField here (see report)
    const { getFilteredOptions } = useReactiveField(props.name, null, {
        dependsOn: props.dependsOn,
        dependentOptions: props.meta?.dependentOptions,
    });

    // Check if we have static options (non-searchable/non-relationship)
    // Returns false if relationshipSearchUrl is set, even if initial options are provided
    const computeHasStaticOptions = (p: typeof props) => {
        // If we have a relationship search URL, options are NOT static (they're searchable)
        if (p.relationshipSearchUrl) {
            return false;
        }
        // If hasDynamicOptions is true but no dependsOn, options were pre-evaluated and passed from backend
        if (p.hasDynamicOptions && (!p.dependsOn || p.dependsOn.length === 0)) {
            return !!(p.options && p.options.length > 0);
        }
        return !!(p.options && p.options.length > 0);
    };
    const hasStaticOptions = computeHasStaticOptions(props);

    // Reactive field logic: Watch for changes and POST to reactive endpoint
    const reloadTimeout = useRef<ReturnType<typeof setTimeout> | null>(null);
    const isUpdating = useRef(false); // Flag to prevent duplicate updates
    const previousSelectedForLive = useRef(internalSelectedValues);
    useEffect(() => {
        if (previousSelectedForLive.current === internalSelectedValues) return;
        previousSelectedForLive.current = internalSelectedValues;

        const p = latestProps.current;
        if (!((p.isLive || p.isLazy) && p.name)) return;

        // Determine debounce delay
        const debounceMs = p.isLazy ? p.liveDebounce : p.isLive && p.liveDebounce > 0 ? p.liveDebounce : 0;

        // Skip if already updating
        if (isUpdating.current) {
            return;
        }

        // Clear existing timeout
        if (reloadTimeout.current) {
            clearTimeout(reloadTimeout.current);
        }

        // Function to trigger reactive field update
        const triggerUpdate = async () => {
            const { formController, formMethod, getFormData, updateSchema } = latestSchemaContext.current;

            // Skip if no formController is configured
            if (!formController) {
                console.warn('[Select] No formController configured, skipping reactive field update');
                return;
            }

            isUpdating.current = true;
            const formData = getFormData ? getFormData() : {};

            try {
                // POST to reactive field endpoint
                const response = await fetch('/reactive-fields/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify({
                        controller: formController,
                        method: formMethod || 'getSchema',
                        data: formData,
                        changed_field: latestProps.current.name,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Failed to update reactive fields');
                }

                const result = await response.json();

                // Update the schema with the new data
                if (updateSchema && result.schema) {
                    updateSchema(result.schema);
                }
            } catch (error) {
                console.error('Error updating reactive fields:', error);
            } finally {
                // Reset the flag after a short delay to allow schema updates to complete
                setTimeout(() => {
                    isUpdating.current = false;
                }, 100);
            }
        };

        // Apply debounce if needed
        if (debounceMs > 0) {
            reloadTimeout.current = setTimeout(triggerUpdate, debounceMs);
        } else {
            void triggerUpdate();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [internalSelectedValues]);

    // Watch for options changes and validate current selection
    // This ensures dependent fields reset when their parent field changes
    const previousOptionsProp = useRef(props.options);
    useEffect(() => {
        if (previousOptionsProp.current === props.options) return;
        previousOptionsProp.current = props.options;

        const newOptions = props.options;

        // Update the internal options with new options from props
        if (computeHasStaticOptions(latestProps.current)) {
            setOptions(newOptions || []);

            // Update cache with new option labels
            selectedOptionsCache.current.clear();
            (newOptions || []).forEach((option) => {
                selectedOptionsCache.current.set(option.value, option.label);
            });
            bumpCache();
        }

        if (!newOptions || newOptions.length === 0) {
            // No options available, clear selection
            if (internalSelectedValuesRef.current.length > 0) {
                setSelectedValues([]);
            }
            return;
        }

        // Check if current selected values are still valid in new options
        const current = internalSelectedValuesRef.current;
        const validValues = current.filter((val) => newOptions.some((opt) => opt.value === val));

        // If any values became invalid, update selection to only keep valid ones
        if (validValues.length !== current.length) {
            setSelectedValues(validValues);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [props.options]);

    // Get label for a value with HTML support
    const getLabelForValue = (val: string): string => {
        // First try to find in current options
        const found = options.find((opt) => opt.value === val);
        if (found) {
            return found.label;
        }
        // Fallback to cache
        const cached = selectedOptionsCache.current.get(val);
        if (cached) {
            return cached;
        }
        return `ID: ${val}`;
    };

    // Display value for trigger (actual selected labels, empty if none selected)
    const displayValue = (() => {
        if (selectedValues.length === 0) {
            return '';
        }

        const labels = selectedValues.map((val) => getLabelForValue(val)).filter(Boolean);

        if (labels.length === 0) {
            return '';
        }

        if (props.multiple) {
            return labels.join(', ');
        }

        return labels[0];
    })();

    // Current search query (separate from the combobox's internal state)
    const currentSearch = useRef('');

    // Fetch options from API
    const fetchOptions = async (reset: boolean = false, searchQuery?: string) => {
        if (loadingRef.current) return;

        const p = latestProps.current;

        // Use provided search query, otherwise use current search
        const search = searchQuery !== undefined ? searchQuery : currentSearch.current;

        if (reset) {
            page.current = 1;
            setOptions([]);
        }

        setLoading(true);

        try {
            let url: string;
            let responseData: any;

            // Check if we have a relationship search URL (for relationship selects)
            if (p.relationshipSearchUrl) {
                // Get current panel path from window location
                const panelPath = window.location.pathname.split('/')[1] || 'admin';
                const params = new URLSearchParams();
                if (search.trim()) {
                    params.append('search', search.trim());
                }
                params.append('limit', '50');

                url = `/${panelPath}/${p.relationshipSearchUrl}&${params.toString()}`;

                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error('Failed to fetch relationship options');
                }

                const data = await response.json();
                responseData = {
                    options: data.options || [],
                    has_more: data.hasMore || false,
                };
            } else if (p.closureOptionsUrl && p.fieldName) {
                // Closure-based options with pagination
                const panelPath = window.location.pathname.split('/')[1] || 'admin';
                const params = new URLSearchParams();
                // resourceSlug is optional - backend can use field name convention as fallback
                if (p.resourceSlug) {
                    params.append('resource', p.resourceSlug);
                }
                params.append('field', p.fieldName);
                params.append('limit', '50');
                // Calculate offset based on current options count (for pagination)
                params.append('offset', reset ? '0' : optionsRef.current.length.toString());
                if (search.trim()) {
                    params.append('search', search.trim());
                }
                // Pass relation manager class if this is inside a relation manager
                if (p.relationManager) {
                    params.append('relationManager', p.relationManager);
                }

                url = `/${panelPath}/${p.closureOptionsUrl}?${params.toString()}`;

                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error('Failed to fetch closure options');
                }

                const data = await response.json();
                responseData = {
                    options: data.options || [],
                    has_more: data.hasMore || false,
                };
            } else if (p.resourceSlug && p.fieldName) {
                // Legacy: Use dashboard select-options endpoint
                const params = new URLSearchParams({
                    page: page.current.toString(),
                });

                if (search.trim()) {
                    params.append('search', search.trim());
                }

                url = `/dashboard/${p.resourceSlug}/select-options/${p.fieldName}?${params}`;

                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error('Failed to fetch options');
                }

                responseData = await response.json();
            } else {
                // No way to fetch options
                setLoading(false);
                return;
            }

            // Cache all option labels for selected values
            responseData.options.forEach((option: Option) => {
                selectedOptionsCache.current.set(option.value, option.label);
            });

            if (reset) {
                setOptions(responseData.options);
            } else {
                setOptions([...optionsRef.current, ...responseData.options]);
            }

            setHasMore(responseData.has_more || false);
        } catch (error) {
            console.error('[Select] Error fetching options:', error);
        } finally {
            setLoading(false);
        }
    };
    const fetchOptionsRef = useLatest(fetchOptions);

    // Load more options
    const loadMore = () => {
        if (hasMoreRef.current && !loadingRef.current) {
            page.current++;
            void fetchOptions();
        }
    };

    // Handle infinite scroll
    const handleScroll = (event: UIEvent<HTMLDivElement>) => {
        const target = event.target as HTMLElement;
        const scrollTop = target.scrollTop;
        const scrollHeight = target.scrollHeight;
        const clientHeight = target.clientHeight;

        // Load more when scrolled to bottom (with 50px threshold)
        const isNearBottom = scrollTop + clientHeight >= scrollHeight - 50;

        if (isNearBottom && hasMoreRef.current && !loadingRef.current) {
            loadMore();
        }
    };

    // Search fetching is debounced in handleSearchChange only; a second searchTerm watcher here
    // would issue a duplicate request per query.

    // Watch open state to fetch initial data (preload)
    const previousOpen = useRef(open);
    useEffect(() => {
        if (previousOpen.current === open) return;
        previousOpen.current = open;

        if (open) {
            const p = latestProps.current;

            // Clear search term when opening for fresh search
            setSearchTerm('');
            currentSearch.current = '';
            page.current = 1;
            setHasMore(false);
            setHighlightedIndex(-1);

            // Fetch options when opening if:
            // 1. We have a relationship search URL (searchable relationship select)
            // 2. We have a closure options URL (closure-based searchable select)
            // 3. OR we don't have static options AND not using dependent options
            if (p.relationshipSearchUrl) {
                void fetchOptionsRef.current(true, '');
            } else if (p.closureOptionsUrl && p.fieldName) {
                void fetchOptionsRef.current(true, '');
            } else if (!computeHasStaticOptions(p) && !p.optionsUrl && !p.hasDynamicOptions) {
                // Legacy: Fetch fresh data when opening
                void fetchOptionsRef.current(true, '');
            }
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    // Form data for dependent field watching (Vue: inject('formData', null) — not provided anywhere by default)
    const formData = useFormData();

    // Check if select should be disabled due to missing dependencies
    const isDisabledByDependency = (() => {
        if (!props.dependsOn || props.dependsOn.length === 0 || !formData) {
            return false;
        }

        // Check if all dependencies have values
        return props.dependsOn.some((fieldName) => {
            const value = formData[fieldName];
            return value === null || value === undefined || value === '';
        });
    })();

    // Combined disabled state
    const disabled = props.disabled || isDisabledByDependency;

    // Fetch options based on dependent field values
    const fetchDependentOptions = async () => {
        const p = latestProps.current;

        if (!p.dependsOn || p.dependsOn.length === 0) {
            return;
        }

        if (!formData) {
            console.warn('Form data not provided via inject. Dependent selects will not work.');
            return;
        }

        // Check if all dependencies have values
        let hasRequiredValues = true;
        p.dependsOn.forEach((fieldName) => {
            const value = formData[fieldName];
            if (value === null || value === undefined || value === '') {
                hasRequiredValues = false;
            }
        });

        // If no dependent values, clear options and don't fetch
        if (!hasRequiredValues) {
            setOptions([]);
            return;
        }

        setLoading(true);

        try {
            let url: string;
            let requestOptions: RequestInit;

            // Choose fetch method based on optionsUrl vs hasDynamicOptions
            if (p.optionsUrl) {
                // Direct API endpoint (e.g., /api/locations/states)
                const params = new URLSearchParams();
                p.dependsOn.forEach((fieldName) => {
                    const value = formData[fieldName];
                    if (value !== null && value !== undefined && value !== '') {
                        params.append(fieldName, String(value));
                    }
                });

                url = `${p.optionsUrl}?${params.toString()}`;
                requestOptions = {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                };
            } else if (p.hasDynamicOptions) {
                // Closure-based options - use evaluation API
                url = '/api/form/evaluate-field';

                requestOptions = {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        resource: p.resourceSlug,
                        field_name: p.fieldName,
                        form_state: formData,
                        property: 'options',
                    }),
                };
            } else {
                // No way to fetch options
                return;
            }

            const response = await fetch(url, requestOptions);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            // Handle different response formats
            let optionsArray: any[];

            if (p.hasDynamicOptions && data.options) {
                // Evaluation API returns {options: [...]}
                optionsArray = data.options;
            } else if (Array.isArray(data)) {
                // Direct API returns [...]
                optionsArray = data;
            } else {
                setOptions([]);
                return;
            }

            // Transform to options format
            if (Array.isArray(optionsArray)) {
                setOptions(
                    optionsArray.map((item: any) => ({
                        value: String(item.id || item.value),
                        label: item.name || item.label || item.title,
                        disabled: item.disabled || false,
                    })),
                );
            }
        } catch (error) {
            console.error('[Select] Error fetching dependent options:', error);
            setOptions([]);
        } finally {
            setLoading(false);
        }
    };
    const fetchDependentOptionsRef = useLatest(fetchDependentOptions);

    // Watch dependent fields for cascading select behavior (immediate)
    const dependentWatchState = useRef<Record<string, { isFirstRun: boolean; previous: any }>>({});
    const dependencyValuesKey = JSON.stringify((props.dependsOn || []).map((fieldName) => formData?.[fieldName] ?? null));
    useEffect(() => {
        const p = latestProps.current;
        if (!(p.dependsOn && p.dependsOn.length > 0 && (p.optionsUrl || p.hasDynamicOptions))) {
            return;
        }

        // Watch each dependent field
        p.dependsOn.forEach(async (fieldName) => {
            const newValue = formData?.[fieldName];
            const state = (dependentWatchState.current[fieldName] ??= { isFirstRun: true, previous: undefined });

            // On first run (mount), just fetch options without clearing
            if (state.isFirstRun) {
                state.isFirstRun = false;
                state.previous = newValue;

                // Fetch options if dependency has a value
                if (newValue !== null && newValue !== undefined && newValue !== '') {
                    await fetchDependentOptionsRef.current();
                }
                return;
            }

            const oldValue = state.previous;
            state.previous = newValue;

            // When a dependent field changes (not first run), clear and fetch new options
            if (newValue !== oldValue) {
                // Clear current selection when dependency changes
                setSelectedValues([]);

                // Emit the cleared value to update the form
                p.onUpdateModelValue?.(p.multiple ? [] : null);

                // Clear current options
                setOptions([]);

                // Fetch new options if the dependent field has a value
                if (newValue !== null && newValue !== undefined && newValue !== '') {
                    await fetchDependentOptionsRef.current();
                }
            }
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [dependencyValuesKey]);

    // Fetch selected options by IDs (for edit mode)
    const fetchSelectedOptions = async (ids: string[]) => {
        if (ids.length === 0) return;

        const p = latestProps.current;

        setLoading(true);
        try {
            let url: string;
            let responseOptions: Option[] = [];

            // Check if we have a relationship options URL
            if (p.relationshipOptionsUrl) {
                // Get current panel path from window location
                const panelPath = window.location.pathname.split('/')[1] || 'admin';
                const params = new URLSearchParams();
                ids.forEach((id) => params.append('ids[]', id));

                url = `/${panelPath}/${p.relationshipOptionsUrl}&${params.toString()}`;

                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error('Failed to fetch selected relationship options');
                }

                const data = await response.json();
                responseOptions = data.options || [];
            } else if (p.resourceSlug && p.fieldName) {
                // Legacy endpoint
                const params = new URLSearchParams();
                ids.forEach((id) => params.append('ids[]', id));

                url = `/dashboard/${p.resourceSlug}/select-options/${p.fieldName}?${params}`;

                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error('Failed to fetch selected options');
                }

                const data = await response.json();
                responseOptions = data.options || [];
            }

            // Cache all option labels
            responseOptions.forEach((option: Option) => {
                selectedOptionsCache.current.set(option.value, option.label);
            });

            // Add these options to our options array
            setOptions(responseOptions);
        } catch (error) {
            console.error('[Select] Error fetching selected options:', error);
        } finally {
            setLoading(false);
        }
    };

    // Fetch selected options by IDs for closure-based selects
    const fetchClosureSelectedOptions = async (ids: string[]) => {
        const p = latestProps.current;

        // Only need closureOptionsUrl and fieldName - resourceSlug is optional for ID-based lookups
        if (ids.length === 0 || !p.closureOptionsUrl || !p.fieldName) {
            return;
        }

        try {
            const panelPath = window.location.pathname.split('/')[1] || 'admin';
            const params = new URLSearchParams();
            params.append('field', p.fieldName);
            ids.forEach((id) => params.append('ids[]', id));

            // Pass resource context if available (for closure evaluation)
            if (p.resourceSlug) {
                params.append('resource', p.resourceSlug);
            }
            // Pass relation manager class if this is inside a relation manager
            if (p.relationManager) {
                params.append('relationManager', p.relationManager);
            }

            const url = `/${panelPath}/${p.closureOptionsUrl}?${params.toString()}`;

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('Failed to fetch closure selected options');
            }

            const data = await response.json();
            const responseOptions: Option[] = data.options || [];

            // Cache and add these options to our options array
            const nextOptions = [...optionsRef.current];
            responseOptions.forEach((option: Option) => {
                selectedOptionsCache.current.set(option.value, option.label);
                // Add to options if not already there
                if (!nextOptions.find((o) => o.value === option.value)) {
                    nextOptions.push(option);
                }
            });
            setOptions(nextOptions);
        } catch (error) {
            console.error('[Select] Error fetching closure selected options:', error);
        }
    };

    // Watch for selected value changes to fetch labels (deep, immediate)
    // This is needed when the value is set after mount (e.g., in edit modals)
    const selectedKey = JSON.stringify(internalSelectedValues);
    useEffect(() => {
        const newValues = internalSelectedValuesRef.current;
        if (newValues.length === 0) return;

        const p = latestProps.current;

        // Handle closure-based selects
        if (p.closureOptionsUrl && p.fieldName) {
            void fetchClosureSelectedOptions(newValues);
            return;
        }

        // Handle relationship selects
        if (p.relationshipOptionsUrl) {
            const missingIds = newValues.filter((id) => !selectedOptionsCache.current.has(id));
            if (missingIds.length > 0) {
                void fetchSelectedOptions(missingIds);
            }
            return;
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [selectedKey]);

    // Preload initial options on mount for better UX
    useEffect(() => {
        const p = latestProps.current;

        const run = async () => {
            if (p.relationshipSearchUrl) {
                // Relationship select with search - use initial options from backend if provided
                if (p.options && p.options.length > 0) {
                    setOptions(p.options);
                    // Cache all option labels
                    p.options.forEach((option) => {
                        selectedOptionsCache.current.set(option.value, option.label);
                    });
                }
                // Note: Selected value label fetching is handled by the watcher
                setInitialLoadComplete(true);
            } else if (computeHasStaticOptions(p)) {
                // Use static options provided from backend
                setOptions(p.options || []);

                // Cache all static option labels
                (p.options || []).forEach((option) => {
                    selectedOptionsCache.current.set(option.value, option.label);
                });

                setInitialLoadComplete(true);
            } else if (p.meta?.dependentOptions) {
                // This select has dependent options passed via meta
                const filtered = getFilteredOptions(p.meta.dependentOptions);
                setOptions(filtered);

                // Cache the option labels
                filtered.forEach((option: Option) => {
                    selectedOptionsCache.current.set(option.value, option.label);
                });

                setInitialLoadComplete(true);
            } else {
                // For closure-based selects with NO dependencies, the options are already in props
                if (p.hasDynamicOptions && (!p.dependsOn || p.dependsOn.length === 0)) {
                    // Use the pre-evaluated options from props
                    if (p.options && p.options.length > 0) {
                        setOptions(p.options);
                        p.options.forEach((option) => {
                            selectedOptionsCache.current.set(option.value, option.label);
                        });
                    }
                    // Set hasMore if backend indicates more options are available
                    setHasMore(p.hasMoreOptions || false);

                    setInitialLoadComplete(true);
                } else if (p.dependsOn && p.dependsOn.length > 0) {
                    // Dependent selects - wait for dependencies to trigger fetch
                    setInitialLoadComplete(true);
                } else if (internalSelectedValuesRef.current.length > 0) {
                    // If there's a pre-selected value (edit mode), fetch the selected options by ID to display labels
                    await fetchSelectedOptions(internalSelectedValuesRef.current);
                    setInitialLoadComplete(true);
                } else {
                    setInitialLoadComplete(true);
                }
            }
        };

        void run();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Listen for field-changed events from dependent fields (for reactive selects)
    useEffect(() => {
        const initial = latestProps.current;
        if (!(initial.dependsOn && initial.dependsOn.length > 0 && initial.meta?.dependentOptions)) {
            return;
        }

        const handleFieldChange = (event: Event) => {
            const { fieldName } = (event as CustomEvent).detail;
            const p = latestProps.current;

            // Check if the changed field is one we depend on
            if (p.dependsOn.includes(fieldName)) {
                // Re-filter options based on new form data
                const filtered = getFilteredOptions(p.meta.dependentOptions);
                setOptions(filtered);

                // Clear current selection when dependency changes
                if (internalSelectedValuesRef.current.length > 0) {
                    setSelectedValues([]);
                    p.onUpdateModelValue?.(p.multiple ? [] : null);
                }

                // Update cache
                selectedOptionsCache.current.clear();
                filtered.forEach((option: Option) => {
                    selectedOptionsCache.current.set(option.value, option.label);
                });
                bumpCache();
            }
        };

        window.addEventListener('field-changed', handleFieldChange);

        return () => {
            window.removeEventListener('field-changed', handleFieldChange);
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // Handle search term change from the combobox input
    const searchTimeout = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);
    const handleSearchChange = (value: string) => {
        // Update both
        setSearchTerm(value);
        currentSearch.current = value;

        // Only search if dropdown is open
        if (!openRef.current) return;

        clearTimeout(searchTimeout.current);
        searchTimeout.current = setTimeout(() => {
            // Backend search - reset to page 1 with new search query (clears options and fetches fresh)
            void fetchOptionsRef.current(true, value);
        }, props.searchDebounce);
    };

    // Remove a value from selection (for badges)
    const removeValue = (valueToRemove: string) => {
        const newValues = internalSelectedValuesRef.current.filter((v) => v !== valueToRemove);
        setSelectedValues(newValues);
    };

    // Validation errors
    const [validationError, setValidationError] = useState<string | null>(null);

    // Watch selected values for validation (min/max items)
    const previousSelectedForValidation = useRef(internalSelectedValues);
    useEffect(() => {
        if (previousSelectedForValidation.current === internalSelectedValues) return;
        previousSelectedForValidation.current = internalSelectedValues;

        const p = latestProps.current;

        if (!p.multiple) {
            setValidationError(null);
            return;
        }

        const count = internalSelectedValues.length;

        if (p.minItems && count < p.minItems) {
            setValidationError(
                p.minItemsValidationMessage ?? `Please select at least ${p.minItems} item${p.minItems > 1 ? 's' : ''}`,
            );
            return;
        }

        if (p.maxItems && count > p.maxItems) {
            setValidationError(
                p.maxItemsValidationMessage ?? `You can only select up to ${p.maxItems} item${p.maxItems > 1 ? 's' : ''}`,
            );
            return;
        }

        setValidationError(null);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [internalSelectedValues]);

    // Handle selection change from the combobox
    const handleValueChange = (value: string | string[]) => {
        // Convert to array and filter valid values
        let newValues: string[];
        if (Array.isArray(value)) {
            newValues = value.filter((v) => v && String(v).trim() !== '');
        } else if (value !== null && value !== undefined && String(value).trim() !== '') {
            newValues = [String(value)];
        } else {
            newValues = [];
        }

        // Check if we're exceeding maxItems
        if (props.multiple && props.maxItems && newValues.length > props.maxItems) {
            newValues = newValues.slice(0, props.maxItems);
        }

        // Update selectedValues (which will trigger the emit)
        setSelectedValues(newValues);

        if (!props.multiple) {
            setOpen(false);
            // Clear search term when closing after selection
            setSearchTerm('');
            currentSearch.current = '';
        }
        // For multiple select, keep dropdown open and don't clear search
    };

    // Toggle an item the way reka's ComboboxItem does
    const selectOption = (option: Option) => {
        if (option.disabled) return;
        if (props.multiple) {
            const current = internalSelectedValuesRef.current;
            handleValueChange(current.includes(option.value) ? current.filter((v) => v !== option.value) : [...current, option.value]);
        } else {
            handleValueChange(option.value);
        }
    };

    // Create/Edit option functionality
    const [createFormData, setCreateFormData] = useState<Record<string, any>>({});
    const [editFormData, setEditFormData] = useState<Record<string, any>>({});
    const [savingOption, setSavingOptionState] = useState(false);
    const savingOptionRef = useRef(false);
    const setSavingOption = (next: boolean) => {
        savingOptionRef.current = next;
        setSavingOptionState(next);
    };

    // Open create modal
    const openCreateModal = () => {
        setCreateFormData({});
        setShowCreateModal(true);
    };

    // Open edit modal
    const openEditModal = async (optionId: string) => {
        setEditingOptionId(optionId);
        setEditFormData({});
        setShowEditModal(true);

        // Fetch existing option data
        try {
            const response = await fetch(`/dashboard/${props.resourceSlug}/select-options/${props.fieldName}/${optionId}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch option data');
            }

            const data = await response.json();
            setEditFormData(data);
        } catch (error) {
            console.error('[Select] Error fetching option data:', error);
            // Continue with empty form if fetch fails
        }
    };

    // Save new option
    const saveNewOption = async () => {
        if (savingOptionRef.current) return;

        setSavingOption(true);
        try {
            const response = await fetch(`/dashboard/${props.resourceSlug}/select-options/${props.fieldName}/create`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(createFormData),
            });

            if (!response.ok) {
                throw new Error('Failed to create option');
            }

            const data = await response.json();

            // Add new option to list and cache
            const newOption: Option = {
                value: String(data.id),
                label: data.label,
            };
            setOptions([newOption, ...optionsRef.current]);
            selectedOptionsCache.current.set(newOption.value, newOption.label);

            // Auto-select the new option
            if (latestProps.current.multiple) {
                setSelectedValues([...internalSelectedValuesRef.current, newOption.value]);
            } else {
                setSelectedValues([newOption.value]);
            }

            setShowCreateModal(false);
            setCreateFormData({});
        } catch (error) {
            console.error('[Select] Error creating option:', error);
            window.alert('Failed to create option. Please try again.');
        } finally {
            setSavingOption(false);
        }
    };

    // Update existing option
    const updateOption = async () => {
        if (savingOptionRef.current || !editingOptionId) return;

        setSavingOption(true);
        try {
            const response = await fetch(`/dashboard/${props.resourceSlug}/select-options/${props.fieldName}/${editingOptionId}/edit`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(editFormData),
            });

            if (!response.ok) {
                throw new Error('Failed to update option');
            }

            const data = await response.json();

            // Update option in list and cache
            setOptions(optionsRef.current.map((opt) => (opt.value === editingOptionId ? { ...opt, label: data.label } : opt)));
            selectedOptionsCache.current.set(String(data.id), data.label);

            setShowEditModal(false);
            setEditFormData({});
            setEditingOptionId(null);
        } catch (error) {
            console.error('[Select] Error updating option:', error);
            window.alert('Failed to update option. Please try again.');
        } finally {
            setSavingOption(false);
        }
    };

    // Keyboard navigation for the combobox input (reka Combobox behaviour)
    const handleInputKeyDown = (event: KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (!openRef.current) {
                setOpen(true);
                return;
            }
            if (options.length === 0) return;
            const delta = event.key === 'ArrowDown' ? 1 : -1;
            setHighlightedIndex((current) => {
                let next = current;
                for (let i = 0; i < options.length; i++) {
                    next = (next + delta + options.length) % options.length;
                    if (!options[next].disabled) break;
                }
                return next;
            });
        } else if (event.key === 'Enter') {
            event.preventDefault();
            if (openRef.current && highlightedIndex >= 0 && options[highlightedIndex]) {
                selectOption(options[highlightedIndex]);
            }
        } else if (event.key === 'Escape' && openRef.current) {
            event.preventDefault();
            setOpen(false);
        }
    };

    const PrefixIcon = resolveIcon(props.prefixIcon);
    const SuffixIcon = resolveIcon(props.suffixIcon);

    const prefixNode: ReactNode =
        PrefixIcon || props.prefix ? (
            <span className="shrink-0 flex items-center gap-1">
                {PrefixIcon && <PrefixIcon className={cn('h-4 w-4', getIconColorClass(props.prefixIconColor))} />}
                {props.prefix && <span className="text-sm text-muted-foreground">{props.prefix}</span>}
            </span>
        ) : null;

    const suffixNode: ReactNode =
        SuffixIcon || props.suffix ? (
            <span className="shrink-0 flex items-center gap-1">
                {props.suffix && <span className="text-sm text-muted-foreground">{props.suffix}</span>}
                {SuffixIcon && <SuffixIcon className={cn('h-4 w-4', getIconColorClass(props.suffixIconColor))} />}
            </span>
        ) : null;

    const triggerNode = (
        <button
            type="button"
            className="shrink-0"
            disabled={disabled}
            aria-label="Toggle options"
            onClick={(e) => {
                e.stopPropagation();
                setOpen(!openRef.current);
            }}
        >
            <ChevronDown className="h-4 w-4 opacity-50 transition-transform duration-200 group-data-[state=open]:rotate-180" />
        </button>
    );

    const renderOptionForm = (
        fields: any[],
        prefix: 'create' | 'edit',
        data: Record<string, any>,
        setData: (updater: (current: Record<string, any>) => Record<string, any>) => void,
    ) =>
        fields.map((field: any, index: number) => {
            const FieldComponent = getComponent(field.component);
            return (
                <div key={index} className="space-y-2">
                    {field.label && (
                        <label htmlFor={`${prefix}-${field.name}`} className="text-sm font-medium block">
                            {field.label}
                            {field.required && <span className="text-destructive ms-0.5">*</span>}
                        </label>
                    )}
                    {FieldComponent && (
                        <FieldComponent
                            id={`${prefix}-${field.name}`}
                            modelValue={data[field.name]}
                            onUpdateModelValue={(value: any) => setData((current) => ({ ...current, [field.name]: value }))}
                            {...field}
                        />
                    )}
                    {field.helperText && <p className="text-xs text-muted-foreground">{field.helperText}</p>}
                </div>
            );
        });

    return (
        <div className="w-full space-y-2">
            {/* Label */}
            {props.label && (
                <label htmlFor={props.name} className="text-sm font-medium block text-foreground">
                    {props.label}
                    {props.required && <span className="text-destructive ms-0.5">*</span>}
                </label>
            )}

            {/* Hidden input for form submission */}
            {props.name && (
                <input
                    type="hidden"
                    name={props.name}
                    value={
                        props.multiple
                            ? selectedValues.length > 0
                                ? selectedValues.join(',')
                                : ''
                            : selectedValues.length > 0
                              ? selectedValues[0]
                              : ''
                    }
                />
            )}

            <div className="flex items-center gap-2">
                <PopoverPrimitive.Root open={open} onOpenChange={(next) => setOpen(next)}>
                    <div className="relative w-full flex-1">
                        <PopoverPrimitive.Anchor asChild>
                            <div
                                ref={anchorRef}
                                role="combobox"
                                aria-expanded={open}
                                aria-disabled={disabled || undefined}
                                data-state={open ? 'open' : 'closed'}
                                data-disabled={disabled ? '' : undefined}
                                className={cn(
                                    'group w-full rounded-md border border-input bg-background text-sm shadow-sm ring-offset-background transition-all hover:border-ring focus-within:outline-none focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 data-[state=open]:border-ring data-[state=open]:ring-2 data-[state=open]:ring-ring cursor-pointer',
                                    cn(
                                        disabled && 'cursor-not-allowed opacity-50',
                                        props.multiple ? 'flex flex-col items-start' : 'inline-flex items-center gap-2',
                                        props.multiple && selectedValues.length > 0 ? 'px-2 py-2' : 'px-3 py-2',
                                    ),
                                )}
                                onClick={() => {
                                    if (!disabled && !openRef.current) setOpen(true);
                                }}
                            >
                                {props.multiple ? (
                                    /* Multiple select: Show badges + input */
                                    <div className="w-full flex flex-col gap-2">
                                        {/* Selected badges */}
                                        {selectedValues.length > 0 && (
                                            <div className="flex flex-wrap gap-1">
                                                {selectedValues.map((value) => (
                                                    <span
                                                        key={value}
                                                        className="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-primary/10 text-primary text-xs font-medium"
                                                    >
                                                        {props.allowHtml ? (
                                                            <span dangerouslySetInnerHTML={{ __html: getLabelForValue(value) }} />
                                                        ) : (
                                                            <span>{getLabelForValue(value)}</span>
                                                        )}

                                                        {/* Edit button for multi-select badges */}
                                                        {props.hasEditOptionForm && (
                                                            <button
                                                                type="button"
                                                                className="hover:bg-primary/20 rounded-sm p-0.5 transition-colors"
                                                                onClick={(e) => {
                                                                    e.stopPropagation();
                                                                    void openEditModal(value);
                                                                }}
                                                            >
                                                                <Edit className="h-3 w-3" />
                                                            </button>
                                                        )}

                                                        {/* Remove button */}
                                                        <button
                                                            type="button"
                                                            className="hover:bg-primary/20 rounded-sm p-0.5 transition-colors"
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                removeValue(value);
                                                            }}
                                                        >
                                                            <svg className="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </span>
                                                ))}
                                            </div>
                                        )}
                                        {/* Search input (always visible for multiple) */}
                                        <div className="flex items-center gap-2 w-full">
                                            {prefixNode}
                                            <input
                                                type="text"
                                                value={searchTerm}
                                                onChange={(e) => {
                                                    handleSearchChange(e.target.value);
                                                    if (!openRef.current) setOpen(true);
                                                }}
                                                onKeyDown={handleInputKeyDown}
                                                placeholder={selectedValues.length > 0 ? translatedSearchPlaceholder : translatedPlaceholder}
                                                className="flex-1 min-w-0 bg-transparent outline-none placeholder:text-muted-foreground text-sm"
                                                disabled={disabled}
                                                role="searchbox"
                                                aria-autocomplete="list"
                                            />
                                            {suffixNode}
                                            {triggerNode}
                                        </div>
                                    </div>
                                ) : (
                                    /* Single select: Show value or input */
                                    <>
                                        {prefixNode}
                                        {!open && displayValue && props.allowHtml ? (
                                            <span className="flex-1 min-w-0 truncate text-sm" dangerouslySetInnerHTML={{ __html: displayValue }} />
                                        ) : !open && displayValue ? (
                                            <span className="flex-1 min-w-0 truncate text-sm">{displayValue}</span>
                                        ) : !open ? (
                                            <span className="flex-1 min-w-0 text-sm text-muted-foreground">{translatedPlaceholder}</span>
                                        ) : (
                                            <input
                                                type="text"
                                                value={searchTerm}
                                                onChange={(e) => handleSearchChange(e.target.value)}
                                                onKeyDown={handleInputKeyDown}
                                                placeholder={translatedPlaceholder}
                                                className="flex-1 min-w-0 bg-transparent outline-none placeholder:text-muted-foreground text-sm"
                                                disabled={disabled}
                                                autoFocus
                                                role="searchbox"
                                                aria-autocomplete="list"
                                            />
                                        )}
                                        {suffixNode}
                                        {triggerNode}
                                    </>
                                )}
                            </div>
                        </PopoverPrimitive.Anchor>

                        <PopoverPrimitive.Portal>
                            <PopoverPrimitive.Content
                                className="w-full min-w-[var(--radix-popover-trigger-width)] rounded-md border border-border bg-popover text-popover-foreground shadow-lg outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[state=closed]:slide-out-to-top-2 data-[state=open]:slide-in-from-top-2 z-50"
                                side="bottom"
                                sideOffset={4}
                                align="start"
                                collisionPadding={8}
                                avoidCollisions={true}
                                role="listbox"
                                aria-multiselectable={props.multiple || undefined}
                                onOpenAutoFocus={(e) => e.preventDefault()}
                                onCloseAutoFocus={(e) => e.preventDefault()}
                                onInteractOutside={(e) => {
                                    if (anchorRef.current && anchorRef.current.contains(e.target as Node)) {
                                        e.preventDefault();
                                    }
                                }}
                            >
                                {loading && options.length === 0 ? (
                                    /* Initial loading state */
                                    <div className="flex flex-col items-center justify-center gap-2 py-8">
                                        <LoaderCircle className="h-5 w-5 animate-spin text-muted-foreground" />
                                        <span className="text-sm text-muted-foreground">{translatedLoadingMessage}</span>
                                    </div>
                                ) : options.length > 0 ? (
                                    /* Search results / Options list */
                                    <div ref={scrollContainer} className="max-h-[300px] overflow-y-auto overscroll-contain" onScroll={handleScroll}>
                                        <div className="p-1">
                                            {options.map((option, index) => (
                                                <div
                                                    key={option.value}
                                                    role="option"
                                                    aria-selected={selectedValues.includes(option.value)}
                                                    aria-disabled={option.disabled || undefined}
                                                    data-state={selectedValues.includes(option.value) ? 'checked' : 'unchecked'}
                                                    data-highlighted={highlightedIndex === index ? '' : undefined}
                                                    data-disabled={option.disabled ? '' : undefined}
                                                    className="relative flex cursor-pointer select-none items-center gap-2 rounded-sm px-2 py-2 text-sm outline-none transition-colors data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                                                    onPointerMove={() => {
                                                        if (!option.disabled && highlightedIndex !== index) setHighlightedIndex(index);
                                                    }}
                                                    onPointerLeave={() => setHighlightedIndex(-1)}
                                                    onMouseDown={(e) => e.preventDefault()}
                                                    onClick={() => selectOption(option)}
                                                >
                                                    <Check
                                                        className={cn(
                                                            'h-4 w-4 shrink-0 transition-opacity',
                                                            selectedValues.includes(option.value) ? 'opacity-100' : 'opacity-0',
                                                        )}
                                                    />
                                                    {props.allowHtml ? (
                                                        <span
                                                            className={cn('flex-1', props.wrapOptionLabels ? '' : 'truncate')}
                                                            dangerouslySetInnerHTML={{ __html: option.label }}
                                                        />
                                                    ) : (
                                                        <span className={cn('flex-1', props.wrapOptionLabels ? '' : 'truncate')}>{option.label}</span>
                                                    )}
                                                </div>
                                            ))}

                                            {/* Loading more indicator (infinite scroll) */}
                                            {(loading || hasMore) && (
                                                <div className="flex items-center justify-center gap-2 px-2 py-3 text-sm text-muted-foreground">
                                                    {loading && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                                    {loading ? (
                                                        <span>{searchTerm ? translatedSearchingMessage : translatedLoadingMessage}</span>
                                                    ) : hasMore ? (
                                                        <span className="text-xs">{translatedScrollForMoreLabel}</span>
                                                    ) : null}
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                ) : (
                                    /* Empty state */
                                    <div className="flex flex-col items-center justify-center gap-3 py-8 px-4">
                                        <div className="text-muted-foreground text-sm text-center">
                                            {searchTerm ? (
                                                <p className="font-medium">{translatedNoSearchResultsMessage}</p>
                                            ) : (
                                                <p className="font-medium">{translatedSearchPrompt}</p>
                                            )}
                                            {searchTerm && <p className="text-xs mt-1">{translatedTryAdjustingSearchLabel}</p>}
                                        </div>

                                        {/* Create new option button */}
                                        {props.hasCreateOptionForm && (
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                className="gap-2"
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    openCreateModal();
                                                }}
                                            >
                                                <Plus className="h-4 w-4" />
                                                {translatedCreateButtonLabel}
                                            </Button>
                                        )}
                                    </div>
                                )}
                            </PopoverPrimitive.Content>
                        </PopoverPrimitive.Portal>
                    </div>
                </PopoverPrimitive.Root>

                {/* Create button (suffix) */}
                {props.hasCreateOptionForm && (
                    <Button type="button" variant="outline" size="icon" className="shrink-0" onClick={openCreateModal}>
                        <Plus className="h-4 w-4" />
                    </Button>
                )}

                {/* Edit button (suffix) - only for single select with a selected value */}
                {props.hasEditOptionForm && !props.multiple && selectedValues.length > 0 && (
                    <Button type="button" variant="outline" size="icon" className="shrink-0" onClick={() => void openEditModal(selectedValues[0])}>
                        <Edit className="h-3.5 w-3.5" />
                    </Button>
                )}
            </div>

            {/* Validation error message */}
            {validationError && <p className="mt-1 text-sm text-destructive">{validationError}</p>}

            {/* Create Option Modal */}
            <Dialog open={showCreateModal} onOpenChange={setShowCreateModal}>
                <DialogContent className="sm:max-w-[525px]">
                    <DialogHeader>
                        <DialogTitle>{translatedCreateModalTitle}</DialogTitle>
                        <DialogDescription>{translatedCreateModalDescription}</DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">{renderOptionForm(props.createOptionForm, 'create', createFormData, setCreateFormData)}</div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setShowCreateModal(false)}>
                            {translatedCancelButtonLabel}
                        </Button>
                        <Button type="button" disabled={savingOption} onClick={() => void saveNewOption()}>
                            {savingOption && <LoaderCircle className="h-4 w-4 animate-spin mr-2" />}
                            {savingOption ? translatedCreatingButtonLabel : translatedCreateButtonLabel}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Edit Option Modal */}
            <Dialog open={showEditModal} onOpenChange={setShowEditModal}>
                <DialogContent className="sm:max-w-[525px]">
                    <DialogHeader>
                        <DialogTitle>{translatedEditModalTitle}</DialogTitle>
                        <DialogDescription>{translatedEditModalDescription}</DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">{renderOptionForm(props.editOptionForm, 'edit', editFormData, setEditFormData)}</div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setShowEditModal(false)}>
                            {translatedCancelButtonLabel}
                        </Button>
                        <Button type="button" disabled={savingOption} onClick={() => void updateOption()}>
                            {savingOption && <LoaderCircle className="h-4 w-4 animate-spin mr-2" />}
                            {savingOption ? translatedSavingButtonLabel : translatedSaveChangesButtonLabel}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Helper text */}
            {props.helperText && <p className="text-xs text-muted-foreground mt-1">{props.helperText}</p>}
        </div>
    );
}
