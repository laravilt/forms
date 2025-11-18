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
    @if($props['isCheckboxList'])
        {{-- Checkbox List --}}
        <div @class([
            'space-y-2' => !$props['inline'],
            'flex flex-wrap gap-4' => $props['inline'],
        ])>
            @foreach($props['options'] as $value => $label)
                <label @class([
                    'flex items-center',
                    'cursor-pointer' => !$props['disabled'],
                    'cursor-not-allowed opacity-50' => $props['disabled'],
                ])>
                    <input
                        type="checkbox"
                        name="{{ $props['name'] }}[]"
                        value="{{ $value }}"
                        @if(is_array($props['value']) && in_array($value, $props['value']))
                            checked
                        @endif
                        @if($props['required'])
                            required
                        @endif
                        @if($props['disabled'])
                            disabled
                        @endif
                        @class([
                            'size-4 rounded border-0 ring-1 ring-inset ring-gray-300 dark:ring-gray-700',
                            'text-primary-500 bg-white dark:bg-gray-900',
                            'transition-colors',
                            'focus:outline-none focus:ring-2 focus:ring-primary-500',
                            'disabled:cursor-not-allowed disabled:opacity-75',
                            'checked:bg-primary-500 dark:checked:bg-primary-500',
                        ])
                        @if($props['reactive'])
                            x-on:change="$wire.call('updateState', '{{ $props['name'] }}', Array.from(document.querySelectorAll('input[name=\'{{ $props['name'] }}[]\']:checked')).map(el => el.value))"
                        @endif
                    />
                    <span class="ml-2 text-sm text-gray-900 dark:text-white">
                        {{ $label }}
                    </span>
                </label>
            @endforeach
        </div>
    @else
        {{-- Single Checkbox --}}
        <label @class([
            'flex items-start',
            'cursor-pointer' => !$props['disabled'],
            'cursor-not-allowed opacity-50' => $props['disabled'],
        ])>
            <div class="flex items-center h-5">
                <input
                    type="checkbox"
                    id="{{ $id }}"
                    name="{{ $props['name'] }}"
                    value="{{ $props['checkedValue'] }}"
                    @if($props['isChecked'])
                        checked
                    @endif
                    @if($props['required'])
                        required
                    @endif
                    @if($props['disabled'])
                        disabled
                    @endif
                    @if($props['autofocus'])
                        autofocus
                    @endif
                    @class([
                        'size-4 rounded border-0 ring-1 ring-inset ring-gray-300 dark:ring-gray-700',
                        'text-primary-500 bg-white dark:bg-gray-900',
                        'transition-colors',
                        'focus:outline-none focus:ring-2 focus:ring-primary-500',
                        'disabled:cursor-not-allowed disabled:opacity-75',
                        'checked:bg-primary-500 dark:checked:bg-primary-500',
                    ])
                    @if($props['reactive'])
                        x-on:change="$wire.call('updateState', '{{ $props['name'] }}', $event.target.checked ? '{{ $props['checkedValue'] }}' : '{{ $props['uncheckedValue'] }}')"
                    @endif
                />
            </div>

            {{-- Checkbox Label and Description --}}
            @if($props['label'] || $props['description'])
                <div class="ml-3 text-sm">
                    @if($props['label'])
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $props['label'] }}
                            @if($props['required'])
                                <span class="text-red-500">*</span>
                            @endif
                        </span>
                    @endif

                    @if($props['description'])
                        <p class="text-gray-500 dark:text-gray-400">
                            {{ $props['description'] }}
                        </p>
                    @endif
                </div>
            @endif
        </label>

        {{-- Hidden input for unchecked value --}}
        <input type="hidden" name="{{ $props['name'] }}" value="{{ $props['uncheckedValue'] }}" />
    @endif
</x-laravilt-field-wrapper>
