@props([
    'field' => null,
    'id' => null,
    'name' => null,
    'label' => null,
    'helperText' => null,
    'hint' => null,
    'required' => false,
    'disabled' => false,
    'hidden' => false,
    'errors' => null,
    'columnSpan' => 1,
    'extraFieldWrapperAttributes' => [],
    'extraLabelAttributes' => [],
    'extraContentAttributes' => [],
    'hiddenLabel' => false,
    'labelSrOnly' => false,
])

@php
    $fieldId = $id ?? ($name ? \Illuminate\Support\Str::slug($name) : null);
    // Get errors from passed prop, view shared data, or create empty MessageBag
    if ($errors !== null) {
        $errorBag = $errors;
    } elseif (isset($__env) && method_exists($__env, 'getShared')) {
        $shared = $__env->getShared();
        $errorBag = $shared['errors'] ?? new \Illuminate\Support\MessageBag();
    } else {
        $errorBag = new \Illuminate\Support\MessageBag();
    }
    $hasError = $errorBag && method_exists($errorBag, 'has') && $errorBag->has($name);
    $errorMessage = $hasError ? $errorBag->first($name) : null;

    // Column span classes
    $columnSpanClass = match($columnSpan) {
        1 => 'col-span-1',
        2 => 'col-span-2',
        3 => 'col-span-3',
        4 => 'col-span-4',
        6 => 'col-span-6',
        12, 'full' => 'col-span-12',
        default => 'col-span-1',
    };
@endphp

<div
    @class([
        'laravilt-field-wrapper',
        $columnSpanClass,
        'hidden' => $hidden,
    ])
    {{
        $attributes->merge($extraFieldWrapperAttributes)->merge([
            'data-field-name' => $name,
            'data-field-type' => $field?->getType() ?? 'field',
        ])
    }}
>
    {{-- Above Label Slot --}}
    @if(isset($aboveLabel))
        <div class="field-above-label mb-1">
            {{ $aboveLabel }}
        </div>
    @endif

    {{-- Label Section --}}
    @if($label && !$hiddenLabel)
        <div class="field-label-section mb-2">
            {{-- Before Label Slot --}}
            @if(isset($beforeLabel))
                <div class="field-before-label inline-block mr-2">
                    {{ $beforeLabel }}
                </div>
            @endif

            <label
                for="{{ $fieldId }}"
                @class([
                    'block text-sm font-medium leading-none select-none',
                    'sr-only' => $labelSrOnly,
                ])
                {{ (new \Illuminate\View\ComponentAttributeBag($extraLabelAttributes)) }}
            >
                {{ $label }}

                @if($required)
                    <span class="text-destructive ml-0.5" aria-label="required">*</span>
                @endif

                @if($hint)
                    <span class="text-xs text-muted-foreground ml-2 font-normal">{{ $hint }}</span>
                @endif
            </label>

            {{-- After Label Slot --}}
            @if(isset($afterLabel))
                <div class="field-after-label inline-block ml-2">
                    {{ $afterLabel }}
                </div>
            @endif
        </div>
    @endif

    {{-- Below Label Slot --}}
    @if(isset($belowLabel))
        <div class="field-below-label mb-2">
            {{ $belowLabel }}
        </div>
    @endif

    {{-- Above Content Slot --}}
    @if(isset($aboveContent))
        <div class="field-above-content mb-2">
            {{ $aboveContent }}
        </div>
    @endif

    {{-- Content Section --}}
    <div
        @class([
            'field-content',
            'opacity-60 cursor-not-allowed' => $disabled,
        ])
        {{ (new \Illuminate\View\ComponentAttributeBag($extraContentAttributes)) }}
    >
        {{-- Before Content Slot --}}
        @if(isset($beforeContent))
            <div class="field-before-content mb-2">
                {{ $beforeContent }}
            </div>
        @endif

        {{-- Main Content (Input Field) --}}
        {{ $slot }}

        {{-- After Content Slot --}}
        @if(isset($afterContent))
            <div class="field-after-content mt-2">
                {{ $afterContent }}
            </div>
        @endif
    </div>

    {{-- Below Content Slot --}}
    @if(isset($belowContent))
        <div class="field-below-content mt-2">
            {{ $belowContent }}
        </div>
    @endif

    {{-- Helper Text --}}
    @if($helperText && !$hasError)
        <p class="mt-1.5 text-xs text-muted-foreground">
            {{ $helperText }}
        </p>
    @endif

    {{-- Error Message --}}
    @if($hasError && $errorMessage)
        <p class="mt-1.5 text-sm text-destructive" role="alert">
            {{ $errorMessage }}
        </p>
    @endif
</div>
