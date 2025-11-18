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
    <textarea
        id="{{ $id }}"
        name="{{ $props['name'] }}"
        @if($props['rows'])
            rows="{{ $props['rows'] }}"
        @endif
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
        @if(!empty($props['maxLength']))
            maxlength="{{ $props['maxLength'] }}"
        @endif
        @class([
            'block w-full rounded-lg border-gray-300 shadow-sm transition duration-75',
            'focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600',
            'disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed',
            'dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500',
        ])
        @if($props['autosize'])
            x-data="{ resize: () => { $el.style.height = '0px'; $el.style.height = $el.scrollHeight + 'px' } }"
            x-init="resize()"
            x-on:input="resize()"
        @endif
        @if($props['reactive'])
            x-on:input="$wire.call('updateState', '{{ $props['name'] }}', $event.target.value)"
        @endif
    >{{ $props['value'] ?? $props['defaultValue'] ?? '' }}</textarea>

    {{-- Character/Word Count --}}
    @if($props['showCharacterCount'] || $props['showWordCount'])
        <div
            class="mt-1.5 flex justify-end gap-4 text-xs text-gray-500"
            x-data="{
                text: '{{ $props['value'] ?? $props['defaultValue'] ?? '' }}',
                get charCount() { return this.text.length },
                get wordCount() { return this.text.trim().split(/\s+/).filter(w => w.length > 0).length }
            }"
        >
            @if($props['showCharacterCount'])
                <span>
                    <span x-text="charCount"></span>
                    @if(!empty($props['maxLength']))
                        / {{ $props['maxLength'] }}
                    @endif
                    characters
                </span>
            @endif

            @if($props['showWordCount'])
                <span><span x-text="wordCount"></span> words</span>
            @endif
        </div>
    @endif
</x-laravilt-field-wrapper>
