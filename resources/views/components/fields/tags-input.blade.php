@props(['component'])

@php
    $props = $component->toLaraviltProps();
    $tags = is_array($props['state']) ? $props['state'] : [];
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
    <div class="flex flex-wrap gap-2 p-2 border border-gray-300 rounded-md min-h-[42px]">
        @foreach($tags as $tag)
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-blue-100 text-blue-800 text-sm">
                {{ $tag }}
                <button type="button" class="ml-1 hover:text-blue-900">×</button>
            </span>
        @endforeach
        <input
            type="text"
            placeholder="{{ $props['placeholder'] ?? 'Add tag...' }}"
            class="flex-1 min-w-[120px] border-0 focus:ring-0 p-0"
        />
    </div>
</x-laravilt-field-wrapper>
