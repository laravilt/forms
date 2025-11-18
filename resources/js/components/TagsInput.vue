<template>
    <div class="laravilt-tags-input">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div
            class="tags-container border border-gray-300 dark:border-gray-600 rounded-lg p-2 min-h-[42px] flex flex-wrap gap-2 focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500"
            :class="{'border-red-500': hasError, 'bg-gray-100 dark:bg-gray-900': disabled}"
            @click="focusInput"
        >
            <!-- Tags -->
            <span
                v-for="(tag, index) in tags"
                :key="index"
                class="tag inline-flex items-center gap-1 px-2 py-1 bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded text-sm"
            >
                {{ tag }}
                <button
                    v-if="!disabled"
                    type="button"
                    @click.stop="removeTag(index)"
                    class="hover:bg-primary-200 dark:hover:bg-primary-800 rounded-full p-0.5"
                >
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </span>

            <!-- Input -->
            <input
                ref="input"
                :id="id"
                type="text"
                v-model="inputValue"
                @keydown="handleKeydown"
                @blur="handleBlur"
                @focus="handleFocus"
                :placeholder="tags.length === 0 ? placeholder : ''"
                :disabled="disabled"
                class="flex-1 min-w-[120px] outline-none bg-transparent"
            />
        </div>

        <!-- Suggestions -->
        <div
            v-if="showSuggestions && filteredSuggestions.length > 0"
            class="suggestions mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto"
        >
            <button
                v-for="(suggestion, index) in filteredSuggestions"
                :key="index"
                type="button"
                @click="addTag(suggestion)"
                class="w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-100"
                :class="{'bg-gray-100 dark:bg-gray-700': index === selectedSuggestionIndex}"
            >
                {{ suggestion }}
            </button>
        </div>

        <p v-if="helperText" class="mt-1 text-sm text-gray-500">{{ helperText }}</p>
        <p v-if="hasError && errorMessage" class="mt-1 text-sm text-red-600">{{ errorMessage }}</p>

        <!-- Hidden input for form submission -->
        <input
            v-for="(tag, index) in tags"
            :key="`hidden-${index}`"
            type="hidden"
            :name="`${name}[]`"
            :value="tag"
        />
    </div>
</template>

<script>
export default {
    name: 'LaraviltTagsInput',
    props: {
        id: String,
        name: String,
        label: String,
        modelValue: {
            type: Array,
            default: () => []
        },
        helperText: String,
        placeholder: {
            type: String,
            default: 'Add tags...'
        },
        required: Boolean,
        disabled: Boolean,
        suggestions: {
            type: Array,
            default: () => []
        },
        minTags: Number,
        maxTags: Number,
        separators: {
            type: Array,
            default: () => [',', 'Enter']
        },
        caseSensitive: {
            type: Boolean,
            default: false
        },
        allowDuplicates: {
            type: Boolean,
            default: false
        },
        tagPattern: String,
    },
    emits: ['update:modelValue', 'blur', 'focus', 'change'],
    data() {
        return {
            tags: [...(this.modelValue || [])],
            inputValue: '',
            showSuggestions: false,
            selectedSuggestionIndex: -1,
            hasError: false,
            errorMessage: '',
        };
    },
    computed: {
        filteredSuggestions() {
            if (!this.inputValue || !this.suggestions.length) {
                return [];
            }

            const searchValue = this.caseSensitive ? this.inputValue : this.inputValue.toLowerCase();

            return this.suggestions.filter(suggestion => {
                const suggestionValue = this.caseSensitive ? suggestion : suggestion.toLowerCase();
                return suggestionValue.includes(searchValue) && !this.tags.includes(suggestion);
            });
        },
        canAddMoreTags() {
            return !this.maxTags || this.tags.length < this.maxTags;
        }
    },
    watch: {
        modelValue(newValue) {
            if (JSON.stringify(newValue) !== JSON.stringify(this.tags)) {
                this.tags = [...(newValue || [])];
            }
        },
        tags(newTags) {
            this.$emit('update:modelValue', newTags);
            this.$emit('change', newTags);
        },
        inputValue(newValue) {
            this.showSuggestions = newValue.length > 0 && this.filteredSuggestions.length > 0;
        }
    },
    methods: {
        handleKeydown(event) {
            const key = event.key;

            // Handle separators
            if (this.separators.includes(key)) {
                event.preventDefault();
                this.addTagFromInput();
                return;
            }

            // Navigate suggestions
            if (this.showSuggestions) {
                if (key === 'ArrowDown') {
                    event.preventDefault();
                    this.selectedSuggestionIndex = Math.min(
                        this.selectedSuggestionIndex + 1,
                        this.filteredSuggestions.length - 1
                    );
                } else if (key === 'ArrowUp') {
                    event.preventDefault();
                    this.selectedSuggestionIndex = Math.max(this.selectedSuggestionIndex - 1, -1);
                } else if (key === 'Enter' && this.selectedSuggestionIndex >= 0) {
                    event.preventDefault();
                    this.addTag(this.filteredSuggestions[this.selectedSuggestionIndex]);
                    return;
                }
            }

            // Remove last tag on backspace
            if (key === 'Backspace' && !this.inputValue && this.tags.length > 0) {
                this.removeTag(this.tags.length - 1);
            }
        },
        addTagFromInput() {
            if (this.inputValue.trim()) {
                this.addTag(this.inputValue.trim());
            }
        },
        addTag(tag) {
            if (!this.canAddMoreTags) {
                this.errorMessage = `Maximum ${this.maxTags} tags allowed`;
                this.hasError = true;
                return;
            }

            // Validate pattern
            if (this.tagPattern && !new RegExp(this.tagPattern).test(tag)) {
                this.errorMessage = 'Invalid tag format';
                this.hasError = true;
                return;
            }

            // Check duplicates
            const existingTag = this.caseSensitive
                ? this.tags.includes(tag)
                : this.tags.some(t => t.toLowerCase() === tag.toLowerCase());

            if (!this.allowDuplicates && existingTag) {
                this.errorMessage = 'Tag already exists';
                this.hasError = true;
                return;
            }

            this.tags.push(tag);
            this.inputValue = '';
            this.showSuggestions = false;
            this.selectedSuggestionIndex = -1;
            this.hasError = false;
            this.errorMessage = '';
        },
        removeTag(index) {
            if (this.minTags && this.tags.length <= this.minTags) {
                this.errorMessage = `Minimum ${this.minTags} tags required`;
                this.hasError = true;
                return;
            }

            this.tags.splice(index, 1);
            this.hasError = false;
            this.errorMessage = '';
        },
        focusInput() {
            if (!this.disabled) {
                this.$refs.input.focus();
            }
        },
        handleBlur(event) {
            setTimeout(() => {
                this.showSuggestions = false;
                this.addTagFromInput();
            }, 200);
            this.$emit('blur', event);
        },
        handleFocus(event) {
            this.$emit('focus', event);
        }
    }
}
</script>
