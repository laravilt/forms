<x-laravilt-component name="radio" :data="$component->toLaraviltProps()">
    <div class="laravilt-radio" :class="{'rtl': rtl}" :dir="rtl ? 'rtl' : 'ltr'">
        <!-- Label -->
        <label
            v-if="label"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
        >
            @{{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Options -->
        <div :class="inline ? 'flex flex-wrap gap-4' : 'space-y-2'">
            <div
                v-for="(optionLabel, optionValue) in options"
                :key="optionValue"
                class="flex items-start"
            >
                <div class="flex h-5 items-center">
                    <input
                        :id="`${id}-${optionValue}`"
                        :name="name"
                        type="radio"
                        :value="optionValue"
                        v-model="selectedValue"
                        :required="required"
                        :disabled="disabled"
                        @change="handleChange"
                        class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="{'border-red-500': hasError}"
                    />
                </div>
                <div class="ml-3" :class="rtl ? 'mr-3 ml-0' : 'ml-3'">
                    <label
                        :for="`${id}-${optionValue}`"
                        class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer"
                    >
                        @{{ optionLabel }}
                    </label>
                    <p
                        v-if="descriptions[optionValue]"
                        class="text-sm text-gray-500 dark:text-gray-400"
                    >
                        @{{ descriptions[optionValue] }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Helper text -->
        <p v-if="helperText && !hasError" class="mt-2 text-sm text-gray-500">
            @{{ helperText }}
        </p>

        <!-- Error message -->
        <p v-if="hasError" class="mt-2 text-sm text-red-600">
            @{{ errorMessage }}
        </p>
    </div>
</x-laravilt-component>

<script>
export default {
    props: {
        id: String,
        name: String,
        value: [String, Number, Boolean],
        options: {
            type: Object,
            default: () => ({})
        },
        label: String,
        helperText: String,
        required: Boolean,
        disabled: Boolean,
        inline: Boolean,
        boolean: Boolean,
        descriptions: {
            type: Object,
            default: () => ({})
        },
        validation: [String, Array, Object],
        validationMessages: Object,
        rtl: Boolean,
        theme: String,
    },

    data() {
        return {
            selectedValue: this.value,
            hasError: false,
            errorMessage: '',
        };
    },

    watch: {
        value(newValue) {
            this.selectedValue = newValue;
        }
    },

    methods: {
        handleChange() {
            this.$emit('input', this.selectedValue);
            this.$emit('update:modelValue', this.selectedValue);
            this.$emit('change', this.selectedValue);
            this.validateField();
        },

        validateField() {
            if (this.required && !this.selectedValue && this.selectedValue !== 0) {
                this.hasError = true;
                this.errorMessage = this.validationMessages?.required || 'Please select an option.';
                return false;
            }

            this.hasError = false;
            this.errorMessage = '';
            return true;
        }
    }
}
</script>
