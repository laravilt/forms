@props(['component'])

@php
    $props = $component->toLaraviltProps();
    $id = $props['id'] ?? $props['name'];
@endphp

<x-laravilt-field-wrapper
    :field="$component"
    :id="$id"
    :name="$props['name']"
    :label="$props['label']"
    :helperText="$props['helperText']"
    :required="$props['required']"
    :disabled="$props['disabled']"
    :hidden="$props['hidden']"
    :columnSpan="$props['columnSpan']"
    :extraFieldWrapperAttributes="$props['extraAttributes'] ?? []"
>
    <div
        x-data="{
            value: @js($props['value'] ?? $props['defaultValue'] ?? ''),
            displayValue: '',
            showPicker: false,
            format: @js($props['format']),
            displayFormat: @js($props['displayFormat']),
            minDate: @js($props['minDate']),
            maxDate: @js($props['maxDate']),
            disabledDates: @js($props['disabledDates']),

            init() {
                if (this.value) {
                    this.displayValue = this.formatDate(this.value);
                }
            },

            formatDate(date) {
                if (!date) return '';
                // Simple date formatting - in production use a library like day.js
                const d = new Date(date);
                return d.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            },

            selectDate(date) {
                this.value = date;
                this.displayValue = this.formatDate(date);
                this.showPicker = false;
                @if($props['reactive'])
                    $wire.call('updateState', '{{ $props['name'] }}', date);
                @endif
            },

            isDateDisabled(date) {
                return this.disabledDates.includes(date);
            }
        }"
        class="relative"
    >
        {{-- Input Field --}}
        <div class="relative">
            <input
                type="text"
                id="{{ $id }}"
                x-model="displayValue"
                @click="showPicker = !showPicker"
                @if($props['placeholder'])
                    placeholder="{{ $props['placeholder'] }}"
                @endif
                @if($props['required'])
                    required
                @endif
                @if($props['disabled'])
                    disabled
                @endif
                readonly
                @class([
                    'block w-full rounded-lg border-gray-300 shadow-sm transition duration-75',
                    'focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600',
                    'disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed',
                    'dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500',
                    'pr-10',
                ])
            />

            {{-- Calendar Icon --}}
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        {{-- Date Picker Dropdown --}}
        <div
            x-show="showPicker"
            @click.away="showPicker = false"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute z-50 mt-2 w-72 origin-top-left rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-800"
            style="display: none;"
        >
            <div class="p-4">
                {{-- Month/Year Header --}}
                <div class="flex items-center justify-between mb-4">
                    <button
                        type="button"
                        class="p-1 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ date('F Y') }}
                    </span>

                    <button
                        type="button"
                        class="p-1 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                {{-- Day Names --}}
                <div class="grid grid-cols-7 gap-1 mb-2">
                    @foreach(['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $day)
                        <div class="text-center text-xs font-semibold text-gray-600 dark:text-gray-400">
                            {{ $day }}
                        </div>
                    @endforeach
                </div>

                {{-- Calendar Days Grid --}}
                <div class="grid grid-cols-7 gap-1">
                    {{-- This is a simplified calendar - in production use a JS library like flatpickr --}}
                    @for($i = 1; $i <= 31; $i++)
                        <button
                            type="button"
                            @click="selectDate('{{ date('Y-m') }}-{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}')"
                            @class([
                                'h-8 rounded text-sm hover:bg-primary-50 dark:hover:bg-primary-900',
                                'text-gray-900 dark:text-white',
                            ])
                        >
                            {{ $i }}
                        </button>
                    @endfor
                </div>

                {{-- Today Button --}}
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        @click="selectDate('{{ date('Y-m-d') }}')"
                        class="w-full px-3 py-2 text-sm font-medium text-primary-600 hover:bg-primary-50 rounded dark:text-primary-400 dark:hover:bg-primary-900"
                    >
                        Today
                    </button>
                </div>
            </div>
        </div>

        {{-- Hidden Input --}}
        <input
            type="hidden"
            name="{{ $props['name'] }}"
            x-model="value"
        />
    </div>

    {{-- Note about using a proper date picker library --}}
    @if(config('app.debug'))
        <p class="mt-1 text-xs text-gray-500">
            Note: This is a simplified date picker. For production, consider using a library like flatpickr or similar.
        </p>
    @endif
</x-laravilt-field-wrapper>
