<x-laravilt-component name="textarea" :data="$component->toLaraviltProps()">
    <div class="laravilt-textarea" :class="{'rtl': rtl}" :dir="rtl ? 'rtl' : 'ltr'">
        <!-- Label -->
        <label
            v-if="label"
            :for="id"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
        >
            @{{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Textarea -->
        <textarea
            :id="id"
            :name="name"
            v-model="inputValue"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            :readonly="readonly"
            :autofocus="autofocus"
            :tabindex="tabindex"
            :rows="rows"
            :maxlength="maxLength"
            v-bind="extraAttributes"
            @input="handleInput"
            @blur="handleBlur"
            ref="textarea"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-100 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-white"
            :class="{
                'border-red-500': hasError,
                'resize-none': autosize
            }"
        ></textarea>

        <!-- Helper text -->
        <p v-if="helperText && !hasError" class="mt-1 text-sm text-gray-500">
            @{{ helperText }}
        </p>

        <!-- Error message -->
        <p v-if="hasError" class="mt-1 text-sm text-red-600">
            @{{ errorMessage }}
        </p>

        <!-- Character/Word count -->
        <div v-if="showCharacterCount || showWordCount" class="mt-1 flex justify-end gap-4 text-xs text-gray-500">
            <span v-if="showCharacterCount && maxLength">
                @{{ characterCount }} / @{{ maxLength }} characters
            </span>
            <span v-if="showWordCount">
                @{{ wordCount }} words
            </span>
        </div>
    </div>
</x-laravilt-component>

<script>
export default {
    props: {
        id: String,
        name: String,
        value: String,
        placeholder: String,
        label: String,
        helperText: String,
        required: Boolean,
        disabled: Boolean,
        readonly: Boolean,
        autofocus: Boolean,
        tabindex: Number,
        rows: {
            type: Number,
            default: 3
        },
        minRows: Number,
        maxRows: Number,
        autosize: Boolean,
        maxLength: Number,
        showCharacterCount: Boolean,
        showWordCount: Boolean,
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
        },

        wordCount() {
            if (!this.inputValue) return 0;
            return this.inputValue.trim().split(/\s+/).filter(word => word.length > 0).length;
        }
    },

    watch: {
        value(newValue) {
            this.inputValue = newValue;
        },

        inputValue() {
            if (this.autosize) {
                this.$nextTick(() => {
                    this.adjustHeight();
                });
            }
        }
    },

    mounted() {
        if (this.autosize) {
            this.adjustHeight();
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
            if (this.required && !this.inputValue) {
                this.hasError = true;
                this.errorMessage = this.validationMessages?.required || 'This field is required.';
                return false;
            }

            this.hasError = false;
            this.errorMessage = '';
            return true;
        },

        adjustHeight() {
            const textarea = this.$refs.textarea;
            if (!textarea) return;

            textarea.style.height = 'auto';
            const scrollHeight = textarea.scrollHeight;

            if (this.minRows) {
                const minHeight = this.minRows * 24; // Approximate line height
                textarea.style.height = Math.max(scrollHeight, minHeight) + 'px';
            }

            if (this.maxRows) {
                const maxHeight = this.maxRows * 24;
                textarea.style.height = Math.min(scrollHeight, maxHeight) + 'px';
            } else {
                textarea.style.height = scrollHeight + 'px';
            }
        }
    }
}
</script>
