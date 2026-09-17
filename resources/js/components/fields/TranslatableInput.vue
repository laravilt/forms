<template>
    <!-- Use slot if provided (Blade mode), otherwise use internal template (Direct Vue mode) -->
    <slot
        v-if="$slots.default"
        :id="id || name"
        :name="name"
        :translations="localValue"
        :activeLocale="active"
        :locales="availableLocales"
        :multiline="multiline"
        :rows="rows"
        :placeholder="placeholder"
        :readonly="readonly"
        :maxLength="maxLength"
        :hasError="hasError"
        :setLocale="setLocale"
        :extraAttributes="extraAttributes"
    />

    <!-- Internal template for direct Vue usage -->
    <FieldWrapper
        v-else
        :id="id"
        :name="name"
        :label="label"
        :helper-text="helperText"
        :hint="hint"
        :required="required"
        :disabled="disabled"
        :hidden="hidden"
        :column-span="columnSpan"
        :hint-actions="hintActions"
    >
        <div class="relative flex items-center gap-2">
            <!-- Prefix Actions -->
            <div v-if="prefixActions && prefixActions.length" class="flex items-center gap-1">
                <ActionButton
                    v-for="action in prefixActions"
                    :key="action.name"
                    v-bind="action"
                />
            </div>

            <!-- The wrapper takes the active locale's direction so the globe (end) and the input's end padding agree -->
            <div class="relative flex flex-1 items-center" :dir="directionOf(active)">
                <!-- Main input edits the active locale -->
                <Textarea
                    v-if="multiline"
                    :id="id || name"
                    :model-value="localValue[active] ?? ''"
                    @update:model-value="(val) => setLocale(active, String(val ?? ''))"
                    :placeholder="placeholder"
                    :rows="rows"
                    :maxlength="maxLength"
                    :disabled="disabled"
                    :readonly="readonly"
                    :dir="directionOf(active)"
                    v-bind="extraAttributes"
                    class="w-full"
                    :class="[
                        hasError ? 'border-destructive focus-visible:ring-destructive' : '',
                        hasLocaleSwitcher ? 'pe-12' : '',
                    ]"
                    :aria-invalid="hasError ? 'true' : 'false'"
                    :aria-describedby="hasError ? `${name}-error` : undefined"
                />
                <Input
                    v-else
                    :id="id || name"
                    type="text"
                    :model-value="localValue[active] ?? ''"
                    @update:model-value="(val) => setLocale(active, String(val ?? ''))"
                    :placeholder="placeholder"
                    :required="required"
                    :disabled="disabled"
                    :readonly="readonly"
                    :maxlength="maxLength"
                    :dir="directionOf(active)"
                    v-bind="extraAttributes"
                    class="w-full"
                    :class="[
                        hasError ? 'border-destructive focus-visible:ring-destructive' : '',
                        hasLocaleSwitcher ? 'pe-12' : '',
                    ]"
                    :aria-invalid="hasError ? 'true' : 'false'"
                    :aria-describedby="hasError ? `${name}-error` : undefined"
                />

                <!-- Globe button opens the per-locale dialog (hidden with a single locale) -->
                <Dialog v-if="hasLocaleSwitcher" v-model:open="dialogOpen">
                    <DialogTrigger as-child>
                        <button
                            type="button"
                            class="absolute end-1.5 flex items-center gap-0.5 text-muted-foreground transition-colors hover:text-foreground disabled:pointer-events-none disabled:opacity-50"
                            :class="multiline ? 'top-2' : 'top-1/2 -translate-y-1/2'"
                            :disabled="disabled"
                            :aria-label="trans('forms::forms.translatable_input.translations')"
                            tabindex="-1"
                        >
                            <Globe class="h-3.5 w-3.5" />
                            <span class="text-[9px] font-bold uppercase tracking-wide">{{ labelOf(active) }}</span>
                        </button>
                    </DialogTrigger>
                    <DialogContent class="flex max-h-[85vh] flex-col gap-0 p-0 sm:max-w-xl">
                        <!-- Logical alignment + end padding so the title follows the UI direction and clears the close button -->
                        <DialogHeader class="border-b px-6 py-4 pe-12 text-start sm:text-start">
                            <DialogTitle>{{ label || trans('forms::forms.translatable_input.translations') }}</DialogTitle>
                            <DialogDescription>
                                {{ trans('forms::forms.translatable_input.description') }}
                            </DialogDescription>
                        </DialogHeader>
                        <div class="flex-1 space-y-4 overflow-y-auto px-6 py-4">
                            <div v-for="locale in availableLocales" :key="locale.code" class="space-y-1.5">
                                <Label
                                    :for="`${id || name}-${locale.code}`"
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span>{{ locale.name || locale.code }}</span>
                                    <span class="text-[10px] font-bold uppercase text-muted-foreground">
                                        {{ locale.label || locale.code }}
                                        <span v-if="isLocaleRequired(locale.code)" class="text-destructive">*</span>
                                    </span>
                                </Label>
                                <Textarea
                                    v-if="multiline"
                                    :id="`${id || name}-${locale.code}`"
                                    :model-value="localValue[locale.code] ?? ''"
                                    @update:model-value="(val) => setLocale(locale.code, String(val ?? ''))"
                                    :rows="rows"
                                    :maxlength="maxLength"
                                    :disabled="disabled"
                                    :readonly="readonly"
                                    :dir="locale.direction || 'ltr'"
                                    :class="localeHasError(locale.code) ? 'border-destructive focus-visible:ring-destructive' : ''"
                                />
                                <Input
                                    v-else
                                    :id="`${id || name}-${locale.code}`"
                                    type="text"
                                    :model-value="localValue[locale.code] ?? ''"
                                    @update:model-value="(val) => setLocale(locale.code, String(val ?? ''))"
                                    :maxlength="maxLength"
                                    :disabled="disabled"
                                    :readonly="readonly"
                                    :dir="locale.direction || 'ltr'"
                                    :class="localeHasError(locale.code) ? 'border-destructive focus-visible:ring-destructive' : ''"
                                />
                                <p v-if="localeError(locale.code)" class="text-xs text-destructive">
                                    {{ localeError(locale.code) }}
                                </p>
                            </div>
                        </div>
                        <DialogFooter class="border-t px-6 py-4">
                            <Button type="button" @click="dialogOpen = false">
                                {{ trans('forms::forms.translatable_input.done') }}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Suffix Actions -->
            <div v-if="suffixActions && suffixActions.length" class="flex items-center gap-1">
                <ActionButton
                    v-for="action in suffixActions"
                    :key="action.name"
                    v-bind="action"
                    :getFormData="getFormData"
                />
            </div>
        </div>

        <!-- Per-locale errors (name.en, ...) are not shown by FieldWrapper, so surface the first one here -->
        <p v-if="localeErrorMessage" class="mt-1.5 text-xs text-destructive">
            {{ localeErrorMessage }}
        </p>

        <!-- Hidden inputs so native (non-Inertia) form posts submit every locale -->
        <template v-if="name">
            <input
                v-for="locale in availableLocales"
                :key="locale.code"
                type="hidden"
                :name="`${name}[${locale.code}]`"
                :value="localValue[locale.code] ?? ''"
            />
        </template>
    </FieldWrapper>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useLocalization } from '@laravilt/support/composables';
import ActionButton from '@laravilt/actions/components/ActionButton.vue';
import { Globe } from 'lucide-vue-next';
import type { ComputedRef } from 'vue';
import { computed, inject, ref, watch } from 'vue';
import { useReactiveField } from '../../composables/useReactiveField';
import FieldWrapper from '../FieldWrapper.vue';

/** Locale metadata as serialized by TranslatableInput::toLaraviltProps(). */
interface LocaleMeta {
    code: string;
    name?: string;
    label?: string;
    direction?: 'ltr' | 'rtl' | string;
}

type Translations = Record<string, string>;

const { trans } = useLocalization();

const props = defineProps<{
    id?: string;
    name: string;
    label?: string;
    placeholder?: string;
    hint?: string;
    required?: boolean;
    disabled?: boolean;
    readonly?: boolean;
    helperText?: string;
    value?: Translations | string | null;
    modelValue?: Translations | string | null;
    /** Allowed locales: metadata objects from PHP, or plain locale codes. */
    locales?: (LocaleMeta | string)[];
    /** Locale edited by the main (collapsed) input. */
    activeLocale?: string;
    requiredLocales?: string[];
    multiline?: boolean;
    rows?: number;
    maxLength?: number;
    extraAttributes?: Record<string, any>;
    hidden?: boolean;
    columnSpan?: number | string;
    hintActions?: any[];
    prefixActions?: any[];
    suffixActions?: any[];
    isLive?: boolean;
    isLazy?: boolean;
    liveDebounce?: number;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: Translations];
}>();

// Normalize the locale list: plain codes become minimal metadata objects
const availableLocales = computed<LocaleMeta[]>(() => {
    const list = (props.locales || [])
        .map((locale) => (typeof locale === 'string' ? { code: locale } : locale))
        .filter((locale): locale is LocaleMeta => !!locale && !!locale.code);

    if (list.length) {
        return list;
    }

    // No metadata from the server: fall back to the active locale so the field still works
    return [{ code: props.activeLocale || 'en' }];
});

const localeCodes = computed(() => availableLocales.value.map((locale) => locale.code));

const hasLocaleSwitcher = computed(() => availableLocales.value.length > 1);

const findLocale = (code: string): LocaleMeta | undefined =>
    availableLocales.value.find((locale) => locale.code === code);

const labelOf = (code: string): string => findLocale(code)?.label || code.split(/[-_]/)[0].toUpperCase();

const directionOf = (code: string): string => findLocale(code)?.direction || 'ltr';

const isLocaleRequired = (code: string): boolean =>
    props.requiredLocales ? props.requiredLocales.includes(code) : !!props.required;

// Parse whatever the server/parent hands us into a dict with every allowed locale present
const parseTranslations = (value: unknown): Translations => {
    let dict: Record<string, unknown> = {};

    if (value && typeof value === 'object' && !Array.isArray(value)) {
        dict = value as Record<string, unknown>;
    } else if (typeof value === 'string' && value !== '') {
        try {
            const parsed = value.trim().startsWith('{') ? JSON.parse(value) : null;
            dict = parsed && typeof parsed === 'object' ? parsed : { [preferredActive()]: value };
        } catch {
            dict = { [preferredActive()]: value };
        }
    }

    return Object.fromEntries(
        localeCodes.value.map((code) => [code, dict[code] == null ? '' : String(dict[code])]),
    );
};

// Pick the main input's locale: the requested one if allowed, else the first allowed locale
function preferredActive(): string {
    const codes = localeCodes.value;
    if (props.activeLocale && codes.includes(props.activeLocale)) return props.activeLocale;
    return codes[0] ?? props.activeLocale ?? 'en';
}

const localValue = ref<Translations>(parseTranslations(props.modelValue ?? props.value));
const active = ref<string>(preferredActive());
const dialogOpen = ref(false);

// Sync local value when the prop or the allowed locales change (React: effect on incoming + localeCodes)
watch(
    [() => props.modelValue ?? props.value, localeCodes],
    ([value]) => {
        const next = parseTranslations(value);
        if (JSON.stringify(next) !== JSON.stringify(localValue.value)) {
            localValue.value = next;
        }
    },
);

// Keep the active locale valid when the allowed set or preference changes
watch([localeCodes, () => props.activeLocale], () => {
    if (!localeCodes.value.includes(active.value)) {
        active.value = preferredActive();
    }
});

const setLocale = (code: string, value: string) => {
    localValue.value = { ...localValue.value, [code]: value };
    emit('update:modelValue', { ...localValue.value });
};

// Reactive field support (live/lazy): re-evaluate the schema when the value changes
useReactiveField(props.name, localValue, {
    isLive: props.isLive,
    isLazy: props.isLazy,
    liveDebounce: props.liveDebounce,
});

// Inject errors from parent
const errors = inject<ComputedRef<Record<string, string | string[]>>>(
    'errors',
    computed(() => ({})),
);

// Inject dependencies for suffix actions
const getFormData = inject<(() => Record<string, any>) | undefined>('getFormData', undefined);

const firstError = (key: string): string | null => {
    const error = errors.value[key];
    if (!error) return null;
    return Array.isArray(error) ? error[0] : error;
};

const localeError = (code: string): string | null => firstError(`${props.name}.${code}`);

const localeHasError = (code: string): boolean => !!localeError(code);

const localeErrorMessage = computed(() => {
    for (const code of localeCodes.value) {
        const error = localeError(code);
        if (error) return `${labelOf(code)}: ${error}`;
    }
    return null;
});

const errorMessage = computed(() => firstError(props.name) || localeErrorMessage.value);

const hasError = computed(() => !!errorMessage.value);
</script>
