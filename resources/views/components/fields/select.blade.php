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
>
    @if($props['searchable'] && !$props['native'])
        {{-- Custom searchable select (use Vue component) --}}
        <div x-data="{ open: false, search: '', selected: @js($props['value'] ?? $props['defaultValue']) }">
            <button
                type="button"
                @click="open = !open"
                @class([
                    'relative w-full cursor-pointer rounded-lg border border-gray-300',
                    'bg-white py-2 pl-3 pr-10 text-left shadow-sm',
                    'focus:border-primary-600 focus:outline-none focus:ring-1 focus:ring-primary-600',
                    'disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed',
                    'dark:border-gray-600 dark:bg-gray-700 dark:text-white',
                ])
                :disabled="$props['disabled']"
            >
                <span class="block truncate" x-text="selected || '{{ $props['placeholder'] ?? 'Select an option' }}'"></span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z" clip-rule="evenodd" />
                    </svg>
                </span>
            </button>

            {{-- Dropdown --}}
            <div
                x-show="open"
                @click.away="open = false"
                x-transition
                class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800"
            >
                {{-- Search input --}}
                <div class="px-2 py-2">
                    <input
                        type="text"
                        x-model="search"
                        placeholder="Search..."
                        class="w-full rounded border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                </div>

                {{-- Options --}}
                @foreach($props['options'] ?? [] as $value => $label)
                    <div
                        @click="selected = '{{ $label }}'; open = false"
                        class="cursor-pointer select-none px-3 py-2 hover:bg-primary-50 dark:hover:bg-primary-900"
                    >
                        {{ $label }}
                    </div>
                @endforeach
            </div>

            {{-- Hidden input --}}
            <input type="hidden" name="{{ $props['name'] }}" x-model="selected" />
        </div>
    @else
        {{-- Native select --}}
        <select
            id="{{ $id }}"
            name="{{ $props['name'] }}{{ $props['multiple'] ? '[]' : '' }}"
            @if($props['required']) required @endif
            @if($props['disabled']) disabled @endif
            @if($props['multiple']) multiple @endif
            @class([
                'block w-full rounded-lg border-gray-300 shadow-sm transition duration-75',
                'focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600',
                'disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed',
                'dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500',
            ])
        >
            @if(!$props['required'] && !$props['multiple'])
                <option value="">{{ $props['placeholder'] ?? 'Select an option' }}</option>
            @endif

            @foreach($props['options'] ?? [] as $value => $label)
                <option
                    value="{{ $value }}"
                    @if($value == ($props['value'] ?? $props['defaultValue'])) selected @endif
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    @endif
</x-laravilt-field-wrapper>
