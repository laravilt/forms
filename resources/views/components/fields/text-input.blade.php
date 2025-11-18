@props(['component'])

@php
    $props = $component->toLaraviltProps();
    $id = $props['id'] ?? $props['name'];
    $type = $props['inputType'] ?? $props['type'] ?? 'text';
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
    <div class="relative">
        {{-- Prefix --}}
        @if(!empty($props['prefix']))
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <span class="text-gray-500 sm:text-sm">{{ $props['prefix'] }}</span>
            </div>
        @endif

        {{-- Input --}}
        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $props['name'] }}"
            value="{{ $props['value'] ?? $props['defaultValue'] ?? '' }}"
            @if($props['placeholder'])
                placeholder="{{ $props['placeholder'] }}"
            @endif
            @if($props['required'])
                required
            @endif
            @if($props['disabled'])
                disabled
            @endif
            @if($props['readonly'])
                readonly
            @endif
            @if($props['autofocus'])
                autofocus
            @endif
            @if($props['autocomplete'])
                autocomplete="{{ $props['autocomplete'] }}"
            @endif
            @if($props['tabindex'])
                tabindex="{{ $props['tabindex'] }}"
            @endif
            @if(!empty($props['maxLength']))
                maxlength="{{ $props['maxLength'] }}"
            @endif
            @class([
                'w-full rounded-md border-0 appearance-none transition-colors',
                'px-2.5 py-1.5 text-sm gap-1.5',
                'placeholder:text-gray-500 dark:placeholder:text-gray-400',
                'text-gray-900 dark:text-white',
                'bg-white dark:bg-gray-900',
                'ring-1 ring-inset ring-gray-300 dark:ring-gray-700',
                'hover:bg-gray-50 dark:hover:bg-gray-800/50',
                'focus:outline-none focus:bg-white dark:focus:bg-gray-900',
                'focus:ring-2 focus:ring-inset focus:ring-primary-500',
                'disabled:cursor-not-allowed disabled:opacity-75',
                'pl-10' => !empty($props['prefix']),
                'pr-10' => !empty($props['suffix']),
            ])
            @if($props['reactive'])
                x-on:input="$wire.call('updateState', '{{ $props['name'] }}', $event.target.value)"
            @endif
        />

        {{-- Suffix --}}
        @if(!empty($props['suffix']))
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <span class="text-gray-500 sm:text-sm">{{ $props['suffix'] }}</span>
            </div>
        @endif
    </div>

    {{-- Character count --}}
    @if(!empty($props['maxLength']))
        <p class="mt-1 text-xs text-gray-500 text-right" x-data="{ count: 0 }">
            <span x-text="count"></span> / {{ $props['maxLength'] }}
        </p>
    @endif
</x-laravilt-field-wrapper>
