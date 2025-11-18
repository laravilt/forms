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
    <div class="border border-gray-300 rounded-md">
        <div class="flex gap-1 p-2 border-b border-gray-200 bg-gray-50">
            <button type="button" class="p-2 rounded hover:bg-gray-200" title="Bold">**B**</button>
            <button type="button" class="p-2 rounded hover:bg-gray-200" title="Italic">*I*</button>
            <button type="button" class="p-2 rounded hover:bg-gray-200" title="Heading">#</button>
        </div>
        <textarea
            id="{{ $props['name'] }}"
            name="{{ $props['name'] }}"
            rows="{{ $props['rows'] ?? 15 }}"
            class="block w-full border-0 focus:ring-0 p-4 font-mono"
            @if($props['required']) required @endif
            @if($props['disabled']) disabled @endif
        >{{ $props['state'] ?? '' }}</textarea>
    </div>
</x-laravilt-field-wrapper>
