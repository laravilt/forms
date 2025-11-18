@props(['component'])

@php
    $props = $component->toLaraviltProps();
@endphp

<x-laravilt-field-wrapper
    :field="$component"
    :name="$props['name']"
    :label="$props['label']"
    :helperText="$props['helperText']"
    :required="$props['required']"
    :disabled="$props['disabled']"
    :hidden="$props['hidden']"
    :columnSpan="$props['columnSpan']"
>
    <input
        type="datetime-local"
        id="{{ $props['name'] }}"
        name="{{ $props['name'] }}"
        value="{{ $props['state'] ?? '' }}"
        @if($props['required']) required @endif
        @if($props['disabled']) disabled @endif
        @if($props['readonly']) readonly @endif
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-50 disabled:text-gray-500"
    />
</x-laravilt-field-wrapper>
