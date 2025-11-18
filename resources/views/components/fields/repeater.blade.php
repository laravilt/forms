@props(['component'])

@php
    $props = $component->toLaraviltProps();
    $items = is_array($props['state']) ? $props['state'] : [];
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
    <div class="space-y-4">
        @foreach($items as $index => $item)
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="font-medium">Item {{ $index + 1 }}</span>
                    <button type="button" class="text-red-500 hover:text-red-700">Remove</button>
                </div>
                {{-- Repeater schema would go here --}}
            </div>
        @endforeach
        <button type="button" class="text-blue-500 hover:text-blue-700 text-sm">+ {{ $props['addButtonLabel'] ?? 'Add item' }}</button>
    </div>
</x-laravilt-field-wrapper>
