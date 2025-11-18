<template>
    <div class="laravilt-date-picker" :class="{'rtl': rtl}" :dir="rtl ? 'rtl' : 'ltr'">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div class="relative">
            <input
                :id="id"
                :name="name"
                :type="timePicker ? 'datetime-local' : 'date'"
                v-model="selectedDate"
                :required="required"
                :disabled="disabled"
                :readonly="readonly"
                :min="minDate"
                :max="maxDate"
                :tabindex="tabindex"
                @input="handleInput"
                @blur="handleBlur"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-100 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-white pr-10"
                :class="{'border-red-500': hasError}"
            />

            <div class="absolute inset-y-0 flex items-center pointer-events-none" :class="rtl ? 'left-0 pl-3' : 'right-0 pr-3'">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <div v-if="range" class="mt-2">
            <input
                :name="`${name}_end`"
                :type="timePicker ? 'datetime-local' : 'date'"
                v-model="selectedEndDate"
                :required="required"
                :disabled="disabled"
                :readonly="readonly"
                :min="selectedDate || minDate"
                :max="maxDate"
                placeholder="End date"
                @input="handleRangeInput"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-100 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                :class="{'border-red-500': hasError}"
            />
        </div>

        <p v-if="helperText && !hasError" class="mt-1 text-sm text-gray-500">{{ helperText }}</p>
        <p v-if="hasError" class="mt-1 text-sm text-red-600">{{ errorMessage }}</p>
    </div>
</template>

<script>
export default {
    name: 'LaraviltDatePicker',

    props: {
        id: String,
        name: String,
        modelValue: [String, Array],
        label: String,
        helperText: String,
        required: Boolean,
        disabled: Boolean,
        readonly: Boolean,
        tabindex: Number,
        format: { type: String, default: 'Y-m-d' },
        displayFormat: { type: String, default: 'M d, Y' },
        minDate: String,
        maxDate: String,
        disabledDates: { type: Array, default: () => [] },
        range: Boolean,
        timePicker: Boolean,
        firstDayOfWeek: Number,
        validation: [String, Array, Object],
        validationMessages: Object,
        rtl: Boolean,
        theme: String,
    },

    emits: ['update:modelValue', 'blur', 'change'],

    data() {
        return {
            selectedDate: this.range ? (this.modelValue?.[0] || '') : (this.modelValue || ''),
            selectedEndDate: this.range ? (this.modelValue?.[1] || '') : '',
            hasError: false,
            errorMessage: '',
        };
    },

    watch: {
        modelValue(newValue) {
            if (this.range) {
                this.selectedDate = newValue?.[0] || '';
                this.selectedEndDate = newValue?.[1] || '';
            } else {
                this.selectedDate = newValue || '';
            }
        }
    },

    methods: {
        handleInput() {
            const value = this.range ? [this.selectedDate, this.selectedEndDate].filter(Boolean) : this.selectedDate;
            this.$emit('update:modelValue', value);
            this.$emit('change', value);
        },

        handleRangeInput() {
            const value = [this.selectedDate, this.selectedEndDate].filter(Boolean);
            this.$emit('update:modelValue', value);
            this.$emit('change', value);
        },

        handleBlur() {
            this.$emit('blur');
        }
    }
}
</script>
