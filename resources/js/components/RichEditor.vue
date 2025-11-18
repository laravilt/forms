<template>
    <div class="laravilt-rich-editor" :class="{'has-error': hasError}">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div class="editor-container border rounded-lg overflow-hidden" :class="{'border-red-500': hasError, 'border-gray-300': !hasError}">
            <!-- Toolbar -->
            <div v-if="!disabled" class="toolbar bg-gray-50 dark:bg-gray-800 border-b border-gray-300 dark:border-gray-700 p-2 flex flex-wrap gap-1">
                <button
                    v-for="button in activeToolbarButtons"
                    :key="button"
                    type="button"
                    @click="executeCommand(button)"
                    class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded"
                    :title="button"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path :d="getButtonIcon(button)" />
                    </svg>
                </button>
            </div>

            <!-- Editor -->
            <div
                ref="editor"
                contenteditable="true"
                :id="id"
                class="editor-content p-3 prose prose-sm max-w-none focus:outline-none"
                :class="{
                    'bg-gray-100 dark:bg-gray-900': disabled,
                    'min-h-[200px]': !minHeight,
                    'max-h-[500px]': !maxHeight,
                    'overflow-y-auto': true
                }"
                :style="editorStyle"
                @input="handleInput"
                @blur="handleBlur"
                @focus="handleFocus"
                v-html="editorValue"
            ></div>

            <!-- Character/Word Count -->
            <div v-if="showCharacterCount || showWordCount" class="bg-gray-50 dark:bg-gray-800 border-t border-gray-300 dark:border-gray-700 px-3 py-2 text-xs text-gray-500 flex justify-end gap-4">
                <span v-if="showCharacterCount">{{ characterCount }} characters</span>
                <span v-if="showWordCount">{{ wordCount }} words</span>
            </div>
        </div>

        <p v-if="helperText" class="mt-1 text-sm text-gray-500">{{ helperText }}</p>
        <p v-if="hasError && errorMessage" class="mt-1 text-sm text-red-600">{{ errorMessage }}</p>
    </div>
</template>

<script>
export default {
    name: 'LaraviltRichEditor',
    props: {
        id: String,
        name: String,
        label: String,
        modelValue: [String, null],
        helperText: String,
        placeholder: String,
        required: Boolean,
        disabled: Boolean,
        toolbarButtons: Array,
        disabledFeatures: {
            type: Array,
            default: () => []
        },
        minHeight: Number,
        maxHeight: Number,
        showCharacterCount: Boolean,
        showWordCount: Boolean,
    },
    emits: ['update:modelValue', 'blur', 'focus', 'change'],
    data() {
        return {
            editorValue: this.modelValue || '',
            hasError: false,
            errorMessage: '',
            isFocused: false,
        };
    },
    computed: {
        activeToolbarButtons() {
            if (this.toolbarButtons) {
                return this.toolbarButtons;
            }
            // Default toolbar
            return ['bold', 'italic', 'underline', 'h1', 'h2', 'h3', 'ul', 'ol', 'link', 'image', 'code']
                .filter(btn => !this.disabledFeatures.includes(btn));
        },
        characterCount() {
            const text = this.stripHtml(this.editorValue);
            return text.length;
        },
        wordCount() {
            const text = this.stripHtml(this.editorValue);
            return text.trim().split(/\s+/).filter(word => word.length > 0).length;
        },
        editorStyle() {
            const styles = {};
            if (this.minHeight) {
                styles.minHeight = `${this.minHeight}px`;
            }
            if (this.maxHeight) {
                styles.maxHeight = `${this.maxHeight}px`;
            }
            return styles;
        }
    },
    watch: {
        modelValue(newValue) {
            if (newValue !== this.editorValue) {
                this.editorValue = newValue || '';
                if (this.$refs.editor) {
                    this.$refs.editor.innerHTML = this.editorValue;
                }
            }
        }
    },
    methods: {
        handleInput(event) {
            this.editorValue = event.target.innerHTML;
            this.$emit('update:modelValue', this.editorValue);
            this.$emit('change', this.editorValue);
        },
        handleBlur(event) {
            this.isFocused = false;
            this.$emit('blur', event);
        },
        handleFocus(event) {
            this.isFocused = true;
            this.$emit('focus', event);
        },
        executeCommand(command) {
            this.$refs.editor.focus();

            switch (command) {
                case 'bold':
                    document.execCommand('bold');
                    break;
                case 'italic':
                    document.execCommand('italic');
                    break;
                case 'underline':
                    document.execCommand('underline');
                    break;
                case 'h1':
                case 'h2':
                case 'h3':
                    document.execCommand('formatBlock', false, command);
                    break;
                case 'ul':
                    document.execCommand('insertUnorderedList');
                    break;
                case 'ol':
                    document.execCommand('insertOrderedList');
                    break;
                case 'link':
                    const url = prompt('Enter URL:');
                    if (url) {
                        document.execCommand('createLink', false, url);
                    }
                    break;
                case 'image':
                    const imgUrl = prompt('Enter image URL:');
                    if (imgUrl) {
                        document.execCommand('insertImage', false, imgUrl);
                    }
                    break;
                case 'code':
                    document.execCommand('formatBlock', false, 'pre');
                    break;
            }

            this.handleInput({ target: this.$refs.editor });
        },
        getButtonIcon(button) {
            const icons = {
                bold: 'M13.5 3.5H7.5L5 11h2.5l.5-2h3l.5 2H14l-2.5-7.5z',
                italic: 'M10 3h4l-4 14H6l4-14z',
                underline: 'M6 18h12v2H6v-2zm1-13v6c0 2.21 1.79 4 4 4s4-1.79 4-4V5h-2v6c0 1.1-.9 2-2 2s-2-.9-2-2V5H7z',
                h1: 'M3 4h2v6h4V4h2v16h-2v-7H5v7H3V4zm14 8h-3v8h-2V4h2v6h3V4h2v16h-2v-8z',
                h2: 'M3 4h2v6h4V4h2v16h-2v-7H5v7H3V4zm15 14h-5v-2l4-5h-4V9h7v2l-4 5h4v2z',
                h3: 'M3 4h2v6h4V4h2v16h-2v-7H5v7H3V4zm15 14h-5v-2l4-5h-4V9h7v2l-4 5h4v2z',
                ul: 'M4 10.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-6c-.83 0-1.5.67-1.5 1.5S3.17 7.5 4 7.5 5.5 6.83 5.5 6 4.83 4.5 4 4.5zm0 12c-.83 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.67-1.5-1.5-1.5zM7 19h14v-2H7v2zm0-6h14v-2H7v2zm0-8v2h14V5H7z',
                ol: 'M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-9h1V4H2v1h1v3zm-1 3h1.8L2 13.1v.9h3v-1H3.2L5 10.9V10H2v1zm5-6v2h14V5H7zm0 14h14v-2H7v2zm0-6h14v-2H7v2z',
                link: 'M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z',
                image: 'M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z',
                code: 'M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4z',
            };
            return icons[button] || '';
        },
        stripHtml(html) {
            const tmp = document.createElement('DIV');
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || '';
        }
    }
}
</script>

<style scoped>
.editor-content:empty:before {
    content: attr(placeholder);
    color: #9CA3AF;
}

.prose {
    color: inherit;
}

.dark .prose {
    color: #E5E7EB;
}
</style>
