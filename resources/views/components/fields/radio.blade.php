@props(['component'])

@php
    $props = $component->toLaraviltProps();
@endphp

<x-laravilt-vue-component
    component="Radio"
    :props="$props"
/>
