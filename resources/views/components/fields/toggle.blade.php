@props(['component'])

@php
    $props = $component->toLaraviltProps();
    $id = $props['id'] ?? $props['name'];
    $onColor = $props['onColor'] ?? 'primary';
    $offColor = $props['offColor'] ?? 'gray';
@endphp

<x-laravilt-field-wrapper
    :field="$component"
    :id="$id"
    :name="$props['name']"
    :label="$props['label']"
    :helperText="$props['helperText']"
    :required="$props['required']"
    :disabled="$props['disabled']"
    :hidden="$props['hidden']"
    :columnSpan="$props['columnSpan']"
    :extraFieldWrapperAttributes="$props['extraAttributes'] ?? []"
>
    <div
        x-data="{
            enabled: @js($props['isOn']),
            onValue: @js($props['onValue']),
            offValue: @js($props['offValue']),
            toggle() {
                this.enabled = !this.enabled;
                @if($props['reactive'])
                    $wire.call('updateState', '{{ $props['name'] }}', this.enabled ? this.onValue : this.offValue);
                @endif
            }
        }"
        class="flex items-center"
    >
        {{-- Toggle Switch --}}
        <button
            type="button"
            @click="toggle()"
            :disabled="@js($props['disabled'])"
            @class([
                'relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent',
                'transition-colors duration-200 ease-in-out',
                'focus:outline-none focus:ring-2 focus:ring-offset-2',
                'disabled:cursor-not-allowed disabled:opacity-50',
            ])
            :class="{
                'bg-{{ $onColor }}-600 focus:ring-{{ $onColor }}-600': enabled,
                'bg-{{ $offColor }}-200 focus:ring-{{ $offColor }}-600': !enabled
            }"
            role="switch"
            :aria-checked="enabled.toString()"
            :aria-labelledby="'{{ $id }}_label'"
        >
            <span
                :class="{ 'translate-x-5': enabled, 'translate-x-0': !enabled }"
                class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
            >
                {{-- On/Off Icons --}}
                @if($props['onIcon'] || $props['offIcon'])
                    @if($props['onIcon'])
                        <span
                            x-show="enabled"
                            x-transition:enter="ease-in duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-out duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="absolute inset-0 flex h-full w-full items-center justify-center"
                        >
                            <x-dynamic-component :component="$props['onIcon']" class="h-3 w-3 text-{{ $onColor }}-600" />
                        </span>
                    @endif

                    @if($props['offIcon'])
                        <span
                            x-show="!enabled"
                            x-transition:enter="ease-in duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-out duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="absolute inset-0 flex h-full w-full items-center justify-center"
                        >
                            <x-dynamic-component :component="$props['offIcon']" class="h-3 w-3 text-{{ $offColor }}-400" />
                        </span>
                    @endif
                @endif
            </span>
        </button>

        {{-- On/Off Labels --}}
        @if($props['onLabel'] || $props['offLabel'])
            <span id="{{ $id }}_label" class="ml-3 text-sm">
                <span
                    x-show="enabled"
                    class="font-medium text-gray-900 dark:text-white"
                >
                    {{ $props['onLabel'] }}
                </span>
                <span
                    x-show="!enabled"
                    class="font-medium text-gray-900 dark:text-white"
                >
                    {{ $props['offLabel'] }}
                </span>
            </span>
        @endif

        {{-- Hidden Input --}}
        <input
            type="hidden"
            id="{{ $id }}"
            name="{{ $props['name'] }}"
            x-model="enabled ? onValue : offValue"
        />
    </div>
</x-laravilt-field-wrapper>
