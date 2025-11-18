<template>
  <div class="laravilt-markdown-editor">
    <!-- Toolbar -->
    <div class="border border-gray-300 border-b-0 rounded-t-lg bg-gray-50 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
      <div class="flex flex-wrap gap-1">
        <button
          v-for="button in toolbarButtons"
          :key="button.name"
          type="button"
          @click="executeCommand(button.command, button.value)"
          :title="button.title"
          :disabled="disabled"
          class="p-2 text-gray-600 hover:bg-gray-200 hover:text-gray-900 rounded disabled:opacity-50 disabled:cursor-not-allowed dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white"
        >
          <component :is="button.icon" class="h-4 w-4" />
        </button>

        <div class="border-l border-gray-300 dark:border-gray-600 mx-1"></div>

        <!-- View Mode Toggle -->
        <button
          type="button"
          @click="viewMode = 'editor'"
          :class="{ 'bg-gray-200 dark:bg-gray-600': viewMode === 'editor' }"
          class="px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-200 rounded dark:text-gray-400 dark:hover:bg-gray-600"
        >
          Editor
        </button>
        <button
          type="button"
          @click="viewMode = 'preview'"
          :class="{ 'bg-gray-200 dark:bg-gray-600': viewMode === 'preview' }"
          class="px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-200 rounded dark:text-gray-400 dark:hover:bg-gray-600"
        >
          Preview
        </button>
        <button
          type="button"
          @click="viewMode = 'split'"
          :class="{ 'bg-gray-200 dark:bg-gray-600': viewMode === 'split' }"
          class="px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-200 rounded dark:text-gray-400 dark:hover:bg-gray-600"
        >
          Split
        </button>
      </div>
    </div>

    <!-- Editor and Preview Area -->
    <div class="border border-gray-300 rounded-b-lg dark:border-gray-600">
      <div :class="viewModeClass">
        <!-- Editor -->
        <div v-show="viewMode === 'editor' || viewMode === 'split'" class="relative">
          <textarea
            ref="textarea"
            :value="modelValue"
            @input="updateValue"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            :rows="rows"
            class="block w-full border-0 focus:ring-0 font-mono text-sm dark:bg-gray-800 dark:text-white"
            :style="{ minHeight: minHeight + 'px', maxHeight: maxHeight ? maxHeight + 'px' : 'none' }"
          ></textarea>
        </div>

        <!-- Preview -->
        <div
          v-show="viewMode === 'preview' || viewMode === 'split'"
          class="prose prose-sm max-w-none p-4 overflow-auto dark:prose-invert"
          :style="{ minHeight: minHeight + 'px', maxHeight: maxHeight ? maxHeight + 'px' : 'none' }"
          v-html="renderedMarkdown"
        ></div>
      </div>
    </div>

    <!-- Footer: Character/Word Count -->
    <div v-if="showCharacterCount || showWordCount" class="mt-1.5 flex justify-end gap-4 text-xs text-gray-500">
      <span v-if="showCharacterCount">
        {{ characterCount }}
        <template v-if="maxLength"> / {{ maxLength }}</template>
        characters
      </span>
      <span v-if="showWordCount">
        {{ wordCount }} words
      </span>
    </div>

    <!-- Hidden Input -->
    <input type="hidden" :name="name" :value="modelValue" />
  </div>
</template>

<script>
export default {
  name: 'LaraviltMarkdownEditor',

  props: {
    modelValue: {
      type: String,
      default: ''
    },
    name: {
      type: String,
      required: true
    },
    placeholder: String,
    required: Boolean,
    disabled: Boolean,
    rows: {
      type: Number,
      default: 10
    },
    minHeight: {
      type: Number,
      default: 200
    },
    maxHeight: Number,
    maxLength: Number,
    showCharacterCount: Boolean,
    showWordCount: Boolean,
    toolbarButtons: {
      type: Array,
      default: () => [
        { name: 'bold', command: 'bold', title: 'Bold (Ctrl+B)', icon: 'BoldIcon' },
        { name: 'italic', command: 'italic', title: 'Italic (Ctrl+I)', icon: 'ItalicIcon' },
        { name: 'heading', command: 'heading', value: 1, title: 'Heading', icon: 'HeadingIcon' },
        { name: 'quote', command: 'quote', title: 'Quote', icon: 'QuoteIcon' },
        { name: 'code', command: 'code', title: 'Code', icon: 'CodeIcon' },
        { name: 'link', command: 'link', title: 'Link (Ctrl+K)', icon: 'LinkIcon' },
        { name: 'image', command: 'image', title: 'Image', icon: 'ImageIcon' },
        { name: 'list', command: 'list', value: 'ul', title: 'Bullet List', icon: 'ListIcon' },
        { name: 'orderedList', command: 'list', value: 'ol', title: 'Numbered List', icon: 'OrderedListIcon' },
        { name: 'table', command: 'table', title: 'Table', icon: 'TableIcon' }
      ]
    }
  },

  emits: ['update:modelValue'],

  data() {
    return {
      viewMode: 'split' // 'editor', 'preview', 'split'
    };
  },

  computed: {
    characterCount() {
      return this.modelValue.length;
    },

    wordCount() {
      return this.modelValue.trim().split(/\s+/).filter(w => w.length > 0).length;
    },

    viewModeClass() {
      if (this.viewMode === 'split') {
        return 'grid grid-cols-2 divide-x divide-gray-300 dark:divide-gray-600';
      }
      return '';
    },

    renderedMarkdown() {
      // Simple markdown to HTML conversion
      // In production, use a library like marked.js or markdown-it
      let html = this.modelValue;

      // Headings
      html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
      html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
      html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');

      // Bold
      html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

      // Italic
      html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');

      // Links
      html = html.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2">$1</a>');

      // Images
      html = html.replace(/!\[(.*?)\]\((.*?)\)/g, '<img src="$2" alt="$1" />');

      // Code blocks
      html = html.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');

      // Inline code
      html = html.replace(/`(.*?)`/g, '<code>$1</code>');

      // Blockquotes
      html = html.replace(/^> (.*$)/gim, '<blockquote>$1</blockquote>');

      // Unordered lists
      html = html.replace(/^\* (.*$)/gim, '<li>$1</li>');
      html = html.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');

      // Ordered lists
      html = html.replace(/^\d+\. (.*$)/gim, '<li>$1</li>');

      // Line breaks
      html = html.replace(/\n/g, '<br>');

      return html;
    }
  },

  methods: {
    updateValue(event) {
      this.$emit('update:modelValue', event.target.value);
    },

    executeCommand(command, value = null) {
      const textarea = this.$refs.textarea;
      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;
      const selectedText = this.modelValue.substring(start, end);
      let replacement = '';

      switch (command) {
        case 'bold':
          replacement = `**${selectedText || 'bold text'}**`;
          break;

        case 'italic':
          replacement = `*${selectedText || 'italic text'}*`;
          break;

        case 'heading':
          const level = value || 1;
          const prefix = '#'.repeat(level);
          replacement = `${prefix} ${selectedText || 'Heading'}`;
          break;

        case 'quote':
          replacement = `> ${selectedText || 'Quote'}`;
          break;

        case 'code':
          if (selectedText.includes('\n')) {
            replacement = `\`\`\`\n${selectedText || 'code'}\n\`\`\``;
          } else {
            replacement = `\`${selectedText || 'code'}\``;
          }
          break;

        case 'link':
          replacement = `[${selectedText || 'link text'}](url)`;
          break;

        case 'image':
          replacement = `![${selectedText || 'alt text'}](image-url)`;
          break;

        case 'list':
          if (value === 'ul') {
            const lines = selectedText.split('\n');
            replacement = lines.map(line => `* ${line}`).join('\n');
          } else if (value === 'ol') {
            const lines = selectedText.split('\n');
            replacement = lines.map((line, i) => `${i + 1}. ${line}`).join('\n');
          }
          break;

        case 'table':
          replacement = `| Header 1 | Header 2 |\n| -------- | -------- |\n| Cell 1   | Cell 2   |`;
          break;

        default:
          return;
      }

      const newValue =
        this.modelValue.substring(0, start) +
        replacement +
        this.modelValue.substring(end);

      this.$emit('update:modelValue', newValue);

      // Restore focus and selection
      this.$nextTick(() => {
        textarea.focus();
        textarea.setSelectionRange(
          start + replacement.length,
          start + replacement.length
        );
      });
    }
  },

  mounted() {
    // Add keyboard shortcuts
    this.$refs.textarea.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
        e.preventDefault();
        this.executeCommand('bold');
      } else if ((e.ctrlKey || e.metaKey) && e.key === 'i') {
        e.preventDefault();
        this.executeCommand('italic');
      } else if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        this.executeCommand('link');
      }
    });
  }
};
</script>

<style scoped>
.laravilt-markdown-editor textarea {
  resize: vertical;
}

.prose {
  color: inherit;
}

.prose h1,
.prose h2,
.prose h3 {
  margin-top: 1em;
  margin-bottom: 0.5em;
}

.prose code {
  background-color: rgba(0, 0, 0, 0.05);
  padding: 0.2em 0.4em;
  border-radius: 3px;
  font-size: 0.9em;
}

.prose pre {
  background-color: rgba(0, 0, 0, 0.05);
  padding: 1em;
  border-radius: 5px;
  overflow-x: auto;
}

.prose blockquote {
  border-left: 4px solid #ddd;
  padding-left: 1em;
  color: #666;
  font-style: italic;
}

.prose ul,
.prose ol {
  padding-left: 2em;
}

.prose table {
  width: 100%;
  border-collapse: collapse;
}

.prose th,
.prose td {
  border: 1px solid #ddd;
  padding: 0.5em;
  text-align: left;
}

.prose th {
  background-color: rgba(0, 0, 0, 0.05);
  font-weight: bold;
}
</style>
