<x-laravilt-component name="checkbox" :data="$component->toLaraviltProps()">
    <div class="laravilt-checkbox" :class="{'rtl': rtl}" :dir="rtl ? 'rtl' : 'ltr'">
        <!-- Single Checkbox -->
        <div v-if="!isCheckboxList" class="flex items-start">
            <div class="flex h-5 items-center">
                <input
                    :id="id"
                    :name="name"
                    type="checkbox"
                    v-model="checked"
                    :value="checkedValue"
                    :required="required"
                    :disabled="disabled"
                    :tabindex="tabindex"
                    @change="handleChange"
                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="{'border-red-500': hasError}"
                />
            </div>
            <div class="ml-3 text-sm" :class="rtl ? 'mr-3 ml-0' : 'ml-3'">
                <label :for="id" class="font-medium text-gray-700 dark:text-gray-300">
                    @{{ label }}
                    <span v-if="required" class="text-red-500">*</span>
                </label>
                <p v-if="description" class="text-gray-500 dark:text-gray-400">
                    @{{ description }}
                </p>
            </div>
        </div>

        <!-- Checkbox List -->
        <div v-else>
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
                            :name="`${name}[]`"
                            type="checkbox"
                            :value="optionValue"
                            v-model="selectedValues"
                            :disabled="disabled"
                            @change="handleChange"
                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        />
                    </div>
                    <div class="ml-3 text-sm" :class="rtl ? 'mr-3 ml-0' : 'ml-3'">
                        <label
                            :for="`${id}-${optionValue}`"
                            class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer"
                        >
                            @{{ optionLabel }}
                        </label>
                    </div>
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
        value: [Boolean, Array, String, Number],
        options: {
            type: Object,
            default: () => ({})
        },
        label: String,
        description: String,
        helperText: String,
        required: Boolean,
        disabled: Boolean,
        inline: Boolean,
        checkedValue: {
            default: true
        },
        uncheckedValue: {
            default: false
        },
        isCheckboxList: Boolean,
        isChecked: Boolean,
        tabindex: Number,
        validation: [String, Array, Object],
        validationMessages: Object,
        rtl: Boolean,
        theme: String,
    },

    data() {
        return {
            checked: this.isCheckboxList ? false : (this.isChecked || false),
            selectedValues: this.isCheckboxList ? (Array.isArray(this.value) ? this.value : []) : [],
            hasError: false,
            errorMessage: '',
        };
    },

    watch: {
        value(newValue) {
            if (this.isCheckboxList) {
                this.selectedValues = Array.isArray(newValue) ? newValue : [];
            } else {
                this.checked = newValue === this.checkedValue;
            }
        }
    },

    methods: {
        handleChange() {
            if (this.isCheckboxList) {
                this.$emit('input', this.selectedValues);
                this.$emit('update:modelValue', this.selectedValues);
                this.$emit('change', this.selectedValues);
            } else {
                const value = this.checked ? this.checkedValue : this.uncheckedValue;
                this.$emit('input', value);
                this.$emit('update:modelValue', value);
                this.$emit('change', value);
            }
            this.validateField();
        },

        validateField() {
            if (this.required) {
                if (this.isCheckboxList && this.selectedValues.length === 0) {
                    this.hasError = true;
                    this.errorMessage = this.validationMessages?.required || 'Please select at least one option.';
                    return false;
                }
                if (!this.isCheckboxList && !this.checked) {
                    this.hasError = true;
                    this.errorMessage = this.validationMessages?.required || 'This field is required.';
                    return false;
                }
            }

            this.hasError = false;
            this.errorMessage = '';
            return true;
        }
    }
}
</script>
