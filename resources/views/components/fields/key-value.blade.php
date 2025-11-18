@props(['component'])

@php
    $props = $component->toLaraviltProps();
    $pairs = is_array($props['state']) ? $props['state'] : [];
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
    <div class="space-y-2">
        @foreach($pairs as $key => $value)
            <div class="flex gap-2">
                <input
                    type="text"
                    value="{{ $key }}"
                    placeholder="Key"
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
                <input
                    type="text"
                    value="{{ $value }}"
                    placeholder="Value"
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
                <button type="button" class="text-red-500 hover:text-red-700">Remove</button>
            </div>
        @endforeach
        <button type="button" class="text-blue-500 hover:text-blue-700 text-sm">+ Add pair</button>
    </div>
</x-laravilt-field-wrapper>
