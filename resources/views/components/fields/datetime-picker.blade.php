@props(['component'])

@php
    $props = $component->toLaraviltProps();
@endphp

<x-laravilt-vue-component
    component="DateTimePicker"
    :props="$props"
/>
