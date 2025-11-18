@props([
    'schema' => null,
    'data' => [],
    'model' => null,
    'statePath' => null,
    'columns' => 1,
    'disabled' => false,
    'extraAttributes' => [],
])

@php
    $gridClass = match($columns) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
        6 => 'grid-cols-1 md:grid-cols-3 lg:grid-cols-6',
        12 => 'grid-cols-12',
        default => 'grid-cols-1',
    };
@endphp

<div
    @class([
        'laravilt-form',
        'space-y-6',
    ])
    {{
        $attributes->merge($extraAttributes)->merge([
            'data-statepath' => $statePath,
        ])
    }}
>
    @if($schema && is_array($schema))
        <div @class([
            'grid gap-6',
            $gridClass,
        ])>
            @foreach($schema as $component)
                @if($component && method_exists($component, 'render'))
                    {!! $component->render() !!}
                @elseif(is_array($component))
                    {{-- Handle array-based component data --}}
                    <x-dynamic-component
                        :component="'laravilt-forms::' . ($component['type'] ?? 'text-input')"
                        :data="$component"
                    />
                @endif
            @endforeach
        </div>
    @else
        {{-- Custom slot content --}}
        {{ $slot }}
    @endif
</div>
