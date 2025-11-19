@props(['component'])

@php
    $props = $component->toLaraviltProps();
@endphp

<x-laravilt-vue-component
    component="Textarea"
    :props="$props"
/>
