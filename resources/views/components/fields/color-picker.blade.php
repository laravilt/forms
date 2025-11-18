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
        type="color"
        id="{{ $props['name'] }}"
        name="{{ $props['name'] }}"
        value="{{ $props['state'] ?? '#000000' }}"
        @if($props['required']) required @endif
        @if($props['disabled']) disabled @endif
        class="h-10 w-full rounded-md border-gray-300 cursor-pointer"
    />
</x-laravilt-field-wrapper>
