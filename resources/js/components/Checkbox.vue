<template>
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
            <div :class="rtl ? 'mr-3' : 'ml-3'" class="text-sm">
                <label :for="id" class="font-medium text-gray-700 dark:text-gray-300">
                    {{ label }}
                    <span v-if="required" class="text-red-500">*</span>
                </label>
                <p v-if="description" class="text-gray-500 dark:text-gray-400">
                    {{ description }}
                </p>
            </div>
        </div>

        <!-- Checkbox List -->
        <div v-else>
            <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {{ label }}
                <span v-if="required" class="text-red-500">*</span>
            </label>

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
                    <div :class="rtl ? 'mr-3' : 'ml-3'" class="text-sm">
                        <label
                            :for="`${id}-${optionValue}`"
                            class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer"
                        >
                            {{ optionLabel }}
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <p v-if="helperText && !hasError" class="mt-2 text-sm text-gray-500">
            {{ helperText }}
        </p>

        <p v-if="hasError" class="mt-2 text-sm text-red-600">
            {{ errorMessage }}
        </p>
    </div>
</template>

<script>
export default {
    name: 'LaraviltCheckbox',

    props: {
        id: String,
        name: String,
        modelValue: [Boolean, Array, String, Number],
        options: { type: Object, default: () => ({}) },
        label: String,
        description: String,
        helperText: String,
        required: Boolean,
        disabled: Boolean,
        inline: Boolean,
        checkedValue: { default: true },
        uncheckedValue: { default: false },
        isCheckboxList: Boolean,
        tabindex: Number,
        validation: [String, Array, Object],
        validationMessages: Object,
        rtl: Boolean,
        theme: String,
    },

    emits: ['update:modelValue', 'change'],

    data() {
        return {
            checked: this.isCheckboxList ? false : (this.modelValue === this.checkedValue),
            selectedValues: this.isCheckboxList ? (Array.isArray(this.modelValue) ? this.modelValue : []) : [],
            hasError: false,
            errorMessage: '',
        };
    },

    watch: {
        modelValue(newValue) {
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
                this.$emit('update:modelValue', this.selectedValues);
                this.$emit('change', this.selectedValues);
            } else {
                const value = this.checked ? this.checkedValue : this.uncheckedValue;
                this.$emit('update:modelValue', value);
                this.$emit('change', value);
            }
        }
    }
}
</script>
