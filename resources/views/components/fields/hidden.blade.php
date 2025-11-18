@props(['component'])

@php
    $props = $component->toLaraviltProps();
@endphp

<input
    type="hidden"
    id="{{ $props['name'] }}"
    name="{{ $props['name'] }}"
    value="{{ $props['state'] ?? '' }}"
/>
