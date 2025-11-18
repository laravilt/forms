<template>
    <div class="laravilt-select" :class="{'rtl': rtl}" :dir="rtl ? 'rtl' : 'ltr'">
        <!-- Label -->
        <label
            v-if="label"
            :for="id"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
        >
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Native Select -->
        <select
            v-if="native"
            :id="id"
            :name="name"
            v-model="selectedValue"
            :required="required"
            :disabled="disabled"
            :multiple="multiple"
            :tabindex="tabindex"
            @change="handleChange"
            @blur="handleBlur"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-100 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-white"
            :class="{'border-red-500': hasError}"
        >
            <option value="" v-if="placeholder && !multiple">{{ placeholder }}</option>
            <option
                v-for="(optionLabel, optionValue) in options"
                :key="optionValue"
                :value="optionValue"
            >
                {{ optionLabel }}
            </option>
        </select>

        <!-- Custom Select -->
        <div v-else class="relative">
            <button
                type="button"
                :id="id"
                @click="toggleDropdown"
                @blur="handleBlur"
                :disabled="disabled"
                class="relative w-full cursor-pointer rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-left shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 disabled:bg-gray-100 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                :class="{'border-red-500': hasError}"
            >
                <span class="block truncate" v-if="selectedLabel">{{ selectedLabel }}</span>
                <span class="block truncate text-gray-400" v-else>{{ placeholder || 'Select...' }}</span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            </button>

            <!-- Dropdown -->
            <Transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div
                    v-show="isOpen"
                    class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800"
                >
                    <!-- Search input -->
                    <div v-if="searchable" class="sticky top-0 bg-white dark:bg-gray-800 p-2">
                        <input
                            type="text"
                            v-model="searchQuery"
                            :placeholder="searchPrompt || 'Search...'"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            @click.stop
                        />
                    </div>

                    <!-- Options -->
                    <div
                        v-for="(optionLabel, optionValue) in filteredOptions"
                        :key="optionValue"
                        @click="selectOption(optionValue)"
                        class="relative cursor-pointer select-none py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700"
                        :class="{
                            'bg-primary-100 dark:bg-primary-900': isSelected(optionValue),
                            'text-gray-900 dark:text-white': !isSelected(optionValue),
                            'text-primary-900 dark:text-primary-100': isSelected(optionValue)
                        }"
                    >
                        <span class="block truncate" :class="{'font-semibold': isSelected(optionValue)}">
                            {{ optionLabel }}
                        </span>
                        <span
                            v-if="isSelected(optionValue)"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-primary-600"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>

                    <!-- No results -->
                    <div v-if="Object.keys(filteredOptions).length === 0" class="py-2 px-3 text-gray-500 text-sm">
                        {{ noSearchResultsMessage || 'No results found.' }}
                    </div>
                </div>
            </Transition>
        </div>

        <!-- Helper text -->
        <p v-if="helperText && !hasError" class="mt-1 text-sm text-gray-500">
            {{ helperText }}
        </p>

        <!-- Error message -->
        <p v-if="hasError" class="mt-1 text-sm text-red-600">
            {{ errorMessage }}
        </p>
    </div>
</template>

<script>
export default {
    name: 'LaraviltSelect',

    props: {
        id: String,
        name: String,
        modelValue: [String, Number, Array],
        options: {
            type: Object,
            default: () => ({})
        },
        placeholder: String,
        label: String,
        helperText: String,
        required: Boolean,
        disabled: Boolean,
        multiple: Boolean,
        native: Boolean,
        searchable: Boolean,
        searchPrompt: String,
        noSearchResultsMessage: String,
        emptyStateMessage: String,
        allowHtml: Boolean,
        maxItems: Number,
        tabindex: Number,
        validation: [String, Array, Object],
        validationMessages: Object,
        rtl: Boolean,
        theme: String,
    },

    emits: ['update:modelValue', 'blur', 'focus', 'change'],

    data() {
        return {
            selectedValue: this.multiple ? (this.modelValue || []) : (this.modelValue || ''),
            isOpen: false,
            searchQuery: '',
            hasError: false,
            errorMessage: '',
        };
    },

    computed: {
        selectedLabel() {
            if (this.multiple) {
                return this.selectedValue.map(val => this.options[val]).join(', ');
            }
            return this.options[this.selectedValue] || '';
        },

        filteredOptions() {
            if (!this.searchable || !this.searchQuery) {
                return this.options;
            }

            const query = this.searchQuery.toLowerCase();
            return Object.fromEntries(
                Object.entries(this.options).filter(([key, label]) =>
                    label.toLowerCase().includes(query)
                )
            );
        }
    },

    watch: {
        modelValue(newValue) {
            this.selectedValue = newValue;
        }
    },

    methods: {
        toggleDropdown() {
            if (!this.disabled) {
                this.isOpen = !this.isOpen;
            }
        },

        selectOption(value) {
            if (this.multiple) {
                const index = this.selectedValue.indexOf(value);
                if (index > -1) {
                    this.selectedValue.splice(index, 1);
                } else {
                    if (!this.maxItems || this.selectedValue.length < this.maxItems) {
                        this.selectedValue.push(value);
                    }
                }
            } else {
                this.selectedValue = value;
                this.isOpen = false;
            }

            this.$emit('update:modelValue', this.selectedValue);
            this.$emit('change', this.selectedValue);
        },

        isSelected(value) {
            if (this.multiple) {
                return this.selectedValue.includes(value);
            }
            return this.selectedValue === value;
        },

        handleChange(event) {
            this.selectedValue = this.multiple
                ? Array.from(event.target.selectedOptions, option => option.value)
                : event.target.value;

            this.$emit('update:modelValue', this.selectedValue);
            this.$emit('change', this.selectedValue);
        },

        handleBlur() {
            setTimeout(() => {
                this.isOpen = false;
                this.$emit('blur');
                this.validateField();
            }, 200);
        },

        validateField() {
            if (this.required && !this.selectedValue) {
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
