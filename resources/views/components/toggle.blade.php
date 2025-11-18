<x-laravilt-component name="toggle" :data="$component->toLaraviltProps()">
    <div class="laravilt-toggle" :class="{'rtl': rtl}" :dir="rtl ? 'rtl' : 'ltr'">
        <div class="flex items-center justify-between">
            <!-- Label -->
            <label
                v-if="label"
                :for="id"
                class="text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                @{{ label }}
                <span v-if="required" class="text-red-500">*</span>
            </label>

            <!-- Toggle Switch -->
            <button
                type="button"
                :id="id"
                role="switch"
                :aria-checked="isOn"
                :disabled="disabled"
                @click="toggle"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2"
                :class="{
                    [`bg-${onColor}-600`]: isOn,
                    [`bg-${offColor}-200 dark:bg-${offColor}-700`]: !isOn,
                    [`focus:ring-${onColor}-500`]: isOn,
                    'opacity-50 cursor-not-allowed': disabled
                }"
            >
                <span class="sr-only">@{{ label }}</span>
                <span
                    :aria-hidden="true"
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                    :class="{
                        'translate-x-5': isOn && !rtl,
                        '-translate-x-5': isOn && rtl,
                        'translate-x-0': !isOn
                    }"
                >
                    <!-- Icon -->
                    <span
                        v-if="onIcon || offIcon"
                        class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                    >
                        <i
                            v-if="isOn && onIcon"
                            :class="[onIcon, `text-${onColor}-600`]"
                            class="text-xs"
                        ></i>
                        <i
                            v-if="!isOn && offIcon"
                            :class="[offIcon, `text-${offColor}-600`]"
                            class="text-xs"
                        ></i>
                    </span>
                </span>
            </button>
        </div>

        <!-- State Labels -->
        <div v-if="onLabel || offLabel" class="mt-1 text-xs text-gray-500">
            <span v-if="isOn && onLabel">@{{ onLabel }}</span>
            <span v-if="!isOn && offLabel">@{{ offLabel }}</span>
        </div>

        <!-- Helper text -->
        <p v-if="helperText && !hasError" class="mt-1 text-sm text-gray-500">
            @{{ helperText }}
        </p>

        <!-- Error message -->
        <p v-if="hasError" class="mt-1 text-sm text-red-600">
            @{{ errorMessage }}
        </p>

        <!-- Hidden input for form submission -->
        <input
            type="hidden"
            :name="name"
            :value="toggleValue"
        />
    </div>
</x-laravilt-component>

<script>
export default {
    props: {
        id: String,
        name: String,
        value: [Boolean, String, Number],
        label: String,
        helperText: String,
        required: Boolean,
        disabled: Boolean,
        onValue: {
            default: true
        },
        offValue: {
            default: false
        },
        onLabel: String,
        offLabel: String,
        onIcon: String,
        offIcon: String,
        onColor: {
            type: String,
            default: 'primary'
        },
        offColor: {
            type: String,
            default: 'gray'
        },
        isOn: Boolean,
        validation: [String, Array, Object],
        validationMessages: Object,
        rtl: Boolean,
        theme: String,
    },

    data() {
        return {
            toggleValue: this.value !== undefined ? this.value : this.offValue,
            hasError: false,
            errorMessage: '',
        };
    },

    computed: {
        isOn() {
            return this.toggleValue === this.onValue;
        }
    },

    watch: {
        value(newValue) {
            this.toggleValue = newValue;
        }
    },

    methods: {
        toggle() {
            if (this.disabled) return;

            this.toggleValue = this.isOn ? this.offValue : this.onValue;
            this.$emit('input', this.toggleValue);
            this.$emit('update:modelValue', this.toggleValue);
            this.$emit('change', this.toggleValue);
        },

        validateField() {
            if (this.required && this.toggleValue === this.offValue) {
                this.hasError = true;
                this.errorMessage = this.validationMessages?.required || 'This field is required.';
                return false;
            }

            this.hasError = false;
            this.errorMessage = '';
            return true;
        }
    }
}
</script>
