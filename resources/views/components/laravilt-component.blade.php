@props(['name', 'data'])

<div
    x-data="laraviltComponent(@js($data))"
    x-init="init()"
    {{ $attributes->merge(['class' => 'laravilt-component']) }}
    data-component="{{ $name }}"
>
    {{ $slot }}
</div>

@once
    @push('scripts')
    <script>
        function laraviltComponent(initialData) {
            return {
                ...initialData,

                init() {
                    // Component initialization
                    if (this.autofocus) {
                        this.$nextTick(() => {
                            const input = this.$el.querySelector('input, textarea, select');
                            if (input) {
                                input.focus();
                            }
                        });
                    }
                },

                // Helper to evaluate closures
                evaluate(value, params = {}) {
                    if (typeof value === 'function') {
                        return value(params);
                    }
                    return value;
                }
            }
        }
    </script>
    @endpush
@endonce
