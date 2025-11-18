<x-laravilt-component name="text-input" :data="$component->toLaraviltProps()">
    <div class="laravilt-text-input" :class="{'rtl': rtl}" :dir="rtl ? 'rtl' : 'ltr'">
        <!-- Label -->
        <label
            v-if="label"
            :for="id"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
        >
            @{{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Input wrapper with prefix/suffix -->
        <div class="relative">
            <!-- Prefix -->
            <div
                v-if="prefixIcon || prefixText"
                class="absolute inset-y-0 flex items-center pointer-events-none"
                :class="rtl ? 'right-0 pr-3' : 'left-0 pl-3'"
            >
                <i v-if="prefixIcon" :class="prefixIcon" class="text-gray-400"></i>
                <span v-if="prefixText" class="text-gray-500 text-sm">@{{ prefixText }}</span>
            </div>

            <!-- Input -->
            <input
                :id="id"
                :name="name"
                :type="type"
                v-model="inputValue"
                :placeholder="placeholder"
                :required="required"
                :disabled="disabled"
                :readonly="readonly"
                :autofocus="autofocus"
                :autocomplete="autocomplete"
                :tabindex="tabindex"
                :minlength="minLength"
                :maxlength="maxLength"
                :pattern="pattern"
                v-bind="extraAttributes"
                @input="handleInput"
                @blur="handleBlur"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-100 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                :class="{
                    'pl-10': !rtl && (prefixIcon || prefixText),
                    'pr-10': rtl && (prefixIcon || prefixText),
                    'pr-10': !rtl && (suffixIcon || suffixText),
                    'pl-10': rtl && (suffixIcon || suffixText),
                    'border-red-500': hasError
                }"
            />

            <!-- Suffix -->
            <div
                v-if="suffixIcon || suffixText"
                class="absolute inset-y-0 flex items-center pointer-events-none"
                :class="rtl ? 'left-0 pl-3' : 'right-0 pr-3'"
            >
                <i v-if="suffixIcon" :class="suffixIcon" class="text-gray-400"></i>
                <span v-if="suffixText" class="text-gray-500 text-sm">@{{ suffixText }}</span>
            </div>
        </div>

        <!-- Helper text -->
        <p v-if="helperText && !hasError" class="mt-1 text-sm text-gray-500">
            @{{ helperText }}
        </p>

        <!-- Error message -->
        <p v-if="hasError" class="mt-1 text-sm text-red-600">
            @{{ errorMessage }}
        </p>

        <!-- Character count -->
        <div v-if="showCharacterCount && maxLength" class="mt-1 text-xs text-gray-500 text-right">
            @{{ characterCount }} / @{{ maxLength }}
        </div>
    </div>
</x-laravilt-component>

<script>
export default {
    props: {
        id: String,
        name: String,
        type: {
            type: String,
            default: 'text'
        },
        value: [String, Number],
        placeholder: String,
        label: String,
        helperText: String,
        required: Boolean,
        disabled: Boolean,
        readonly: Boolean,
        autofocus: Boolean,
        autocomplete: String,
        tabindex: Number,
        minLength: Number,
        maxLength: Number,
        pattern: String,
        mask: String,
        prefixIcon: String,
        suffixIcon: String,
        prefixText: String,
        suffixText: String,
        showCharacterCount: Boolean,
        validation: [String, Array, Object],
        validationMessages: Object,
        extraAttributes: Object,
        rtl: Boolean,
        theme: String,
    },

    data() {
        return {
            inputValue: this.value || '',
            hasError: false,
            errorMessage: '',
        };
    },

    computed: {
        characterCount() {
            return this.inputValue?.length || 0;
        }
    },

    watch: {
        value(newValue) {
            this.inputValue = newValue;
        }
    },

    mounted() {
        if (this.mask) {
            this.applyMask();
        }
    },

    methods: {
        handleInput(event) {
            this.inputValue = event.target.value;
            this.$emit('input', this.inputValue);
            this.$emit('update:modelValue', this.inputValue);
        },

        handleBlur() {
            this.$emit('blur');
            this.validateField();
        },

        validateField() {
            // Basic validation
            if (this.required && !this.inputValue) {
                this.hasError = true;
                this.errorMessage = this.validationMessages?.required || 'This field is required.';
                return false;
            }

            if (this.type === 'email' && this.inputValue) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(this.inputValue)) {
                    this.hasError = true;
                    this.errorMessage = this.validationMessages?.email || 'Please enter a valid email.';
                    return false;
                }
            }

            this.hasError = false;
            this.errorMessage = '';
            return true;
        },

        applyMask() {
            // TODO: Implement input masking
        }
    }
}
</script>
