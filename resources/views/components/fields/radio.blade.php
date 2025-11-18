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
    <div @class([
        'space-y-3' => !$props['inline'],
        'flex flex-wrap gap-4' => $props['inline'],
    ])>
        @foreach($props['options'] as $value => $label)
            @php
                $optionId = $id . '_' . $value;
                $description = $props['descriptions'][$value] ?? null;
            @endphp

            <label
                for="{{ $optionId }}"
                @class([
                    'flex items-start',
                    'cursor-pointer' => !$props['disabled'],
                    'cursor-not-allowed opacity-50' => $props['disabled'],
                ])
            >
                <div class="flex items-center h-5">
                    <input
                        type="radio"
                        id="{{ $optionId }}"
                        name="{{ $props['name'] }}"
                        value="{{ $value }}"
                        @if($value == ($props['value'] ?? $props['defaultValue']))
                            checked
                        @endif
                        @if($props['required'])
                            required
                        @endif
                        @if($props['disabled'])
                            disabled
                        @endif
                        @if($props['autofocus'] && $loop->first)
                            autofocus
                        @endif
                        @class([
                            'size-4 rounded border-0 ring-1 ring-inset ring-gray-300 dark:ring-gray-700 text-primary-500 bg-white dark:bg-gray-900 transition-colors',
                            'focus:outline-none focus:ring-2 focus:ring-primary-500',
                            'disabled:bg-gray-50 disabled:cursor-not-allowed',
                            'dark:border-gray-600 dark:bg-gray-700 dark:checked:bg-primary-600',
                        ])
                        @if($props['reactive'])
                            x-on:change="$wire.call('updateState', '{{ $props['name'] }}', $event.target.value)"
                        @endif
                    />
                </div>

                <div class="ml-3 text-sm">
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ $label }}
                    </span>

                    @if($description)
                        <p class="text-gray-500 dark:text-gray-400">
                            {{ $description }}
                        </p>
                    @endif
                </div>
            </label>
        @endforeach
    </div>
</x-laravilt-field-wrapper>
