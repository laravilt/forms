<x-laravilt-component name="date-picker" :data="$component->toLaraviltProps()">
    <div class="laravilt-date-picker" :class="{'rtl': rtl}" :dir="rtl ? 'rtl' : 'ltr'">
        <!-- Label -->
        <label
            v-if="label"
            :for="id"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
        >
            @{{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Date Input -->
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
                @focus="showCalendar = !native"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-100 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-white pr-10"
                :class="{
                    'border-red-500': hasError,
                    'pl-10': rtl,
                    'pr-10': !rtl
                }"
            />

            <!-- Calendar Icon -->
            <div
                class="absolute inset-y-0 flex items-center pointer-events-none"
                :class="rtl ? 'left-0 pl-3' : 'right-0 pr-3'"
            >
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Range Mode - Second Date -->
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

        <!-- Helper text -->
        <p v-if="helperText && !hasError" class="mt-1 text-sm text-gray-500">
            @{{ helperText }}
        </p>

        <!-- Error message -->
        <p v-if="hasError" class="mt-1 text-sm text-red-600">
            @{{ errorMessage }}
        </p>

        <!-- Display formatted date -->
        <p v-if="selectedDate && displayFormat !== format" class="mt-1 text-xs text-gray-500">
            @{{ formattedDate }}
        </p>
    </div>
</x-laravilt-component>

<script>
export default {
    props: {
        id: String,
        name: String,
        value: [String, Array],
        label: String,
        helperText: String,
        required: Boolean,
        disabled: Boolean,
        readonly: Boolean,
        tabindex: Number,
        format: {
            type: String,
            default: 'Y-m-d'
        },
        displayFormat: {
            type: String,
            default: 'M d, Y'
        },
        minDate: String,
        maxDate: String,
        disabledDates: {
            type: Array,
            default: () => []
        },
        range: Boolean,
        timePicker: Boolean,
        firstDayOfWeek: Number,
        validation: [String, Array, Object],
        validationMessages: Object,
        rtl: Boolean,
        theme: String,
    },

    data() {
        return {
            selectedDate: this.range ? (this.value?.[0] || '') : (this.value || ''),
            selectedEndDate: this.range ? (this.value?.[1] || '') : '',
            showCalendar: false,
            hasError: false,
            errorMessage: '',
        };
    },

    computed: {
        formattedDate() {
            if (!this.selectedDate) return '';

            try {
                const date = new Date(this.selectedDate);
                return this.formatDate(date);
            } catch (e) {
                return this.selectedDate;
            }
        }
    },

    watch: {
        value(newValue) {
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
            if (this.range) {
                const value = [this.selectedDate, this.selectedEndDate].filter(Boolean);
                this.$emit('input', value);
                this.$emit('update:modelValue', value);
                this.$emit('change', value);
            } else {
                this.$emit('input', this.selectedDate);
                this.$emit('update:modelValue', this.selectedDate);
                this.$emit('change', this.selectedDate);
            }
        },

        handleRangeInput() {
            const value = [this.selectedDate, this.selectedEndDate].filter(Boolean);
            this.$emit('input', value);
            this.$emit('update:modelValue', value);
            this.$emit('change', value);
        },

        handleBlur() {
            this.showCalendar = false;
            this.$emit('blur');
            this.validateField();
        },

        validateField() {
            if (this.required && !this.selectedDate) {
                this.hasError = true;
                this.errorMessage = this.validationMessages?.required || 'This field is required.';
                return false;
            }

            if (this.range && this.required && !this.selectedEndDate) {
                this.hasError = true;
                this.errorMessage = 'Please select an end date.';
                return false;
            }

            this.hasError = false;
            this.errorMessage = '';
            return true;
        },

        formatDate(date) {
            // Simple date formatting
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return date.toLocaleDateString(undefined, options);
        },

        isDateDisabled(date) {
            return this.disabledDates.includes(date);
        }
    }
}
</script>
