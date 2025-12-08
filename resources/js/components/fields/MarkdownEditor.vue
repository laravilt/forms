<script setup lang="ts">
import { Button } from '@/components/ui/button'
import * as LucideIcons from 'lucide-vue-next'
import {
  Eye,
  EyeOff,
  Bold,
  Italic,
  Strikethrough,
  Link,
  Heading2,
  Quote,
  Code,
  List,
  ListOrdered,
  Table,
  Image,
  Undo,
  Redo
} from 'lucide-vue-next'
import { computed, ref, watch } from 'vue'
import MarkdownIt from 'markdown-it'

interface Props {
  name?: string
  value?: string
  modelValue?: string
  label?: string
  placeholder?: string
  preview?: boolean
  disabled?: boolean
  required?: boolean
  helperText?: string
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
  toolbarButtons?: string[][]
  fileAttachmentsEnabled?: boolean
  fileAttachmentsDisk?: string
  fileAttachmentsDirectory?: string
  fileAttachmentsAcceptedFileTypes?: string[]
  fileAttachmentsMaxSize?: number
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  placeholder: 'Write markdown...',
  preview: true,
  disabled: false,
  toolbarButtons: () => [
    ['bold', 'italic', 'strike', 'link'],
    ['heading'],
    ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
    ['table', 'attachFiles'],
    ['undo', 'redo'],
  ],
  fileAttachmentsEnabled: false,
  fileAttachmentsDisk: 'public',
  fileAttachmentsDirectory: 'attachments',
  fileAttachmentsAcceptedFileTypes: () => ['image/png', 'image/jpeg', 'image/gif', 'image/webp'],
  fileAttachmentsMaxSize: 12288, // 12 MB
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'update:value': [value: string]
}>()

// Internal state for markdown content
const internalValue = ref<string>('')

// Watch for prop changes and update internal state
watch(() => props.value ?? props.modelValue, (newValue) => {
  internalValue.value = newValue ?? ''
}, { immediate: true })

const showPreview = ref(props.preview)
const textareaRef = ref<HTMLTextAreaElement | null>(null)
const fileInputRef = ref<HTMLInputElement | null>(null)
const uploadHistory = ref<Array<{ action: string; value: string }>>([])
const uploadHistoryIndex = ref(-1)

// Initialize markdown-it with sensible defaults
const md = new MarkdownIt({
  html: false, // Disable HTML tags in source for security
  xhtmlOut: false,
  breaks: true, // Convert \n to <br>
  linkify: true, // Auto-convert URL-like text to links
  typographer: true, // Enable smart quotes and other typographic replacements
})

// Computed property for rendered HTML
const renderedHtml = computed(() => {
  if (!internalValue.value) return ''
  return md.render(internalValue.value)
})

// Toolbar button icons mapping
const toolbarIcons: Record<string, any> = {
  bold: Bold,
  italic: Italic,
  strike: Strikethrough,
  link: Link,
  heading: Heading2,
  blockquote: Quote,
  codeBlock: Code,
  bulletList: List,
  orderedList: ListOrdered,
  table: Table,
  attachFiles: Image,
  undo: Undo,
  redo: Redo,
}

// Insert markdown syntax at cursor position
const insertMarkdown = (before: string, after: string = '', placeholder: string = '') => {
  const textarea = textareaRef.value
  if (!textarea) return

  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const selectedText = internalValue.value.substring(start, end)
  const replacement = before + (selectedText || placeholder) + after

  const newValue = internalValue.value.substring(0, start) + replacement + internalValue.value.substring(end)

  // Save to history for undo/redo
  saveToHistory('insert', newValue)

  // Update internal state
  internalValue.value = newValue

  // Emit events
  emit('update:modelValue', newValue)
  emit('update:value', newValue)

  // Set cursor position after insertion
  setTimeout(() => {
    if (selectedText) {
      textarea.setSelectionRange(start + before.length, start + before.length + selectedText.length)
    } else {
      textarea.setSelectionRange(start + before.length, start + before.length + placeholder.length)
    }
    textarea.focus()
  }, 0)
}

// Save action to history
const saveToHistory = (action: string, value: string) => {
  uploadHistory.value = uploadHistory.value.slice(0, uploadHistoryIndex.value + 1)
  uploadHistory.value.push({ action, value })
  uploadHistoryIndex.value = uploadHistory.value.length - 1
}

// Undo last action
const undo = () => {
  if (uploadHistoryIndex.value > 0) {
    uploadHistoryIndex.value--
    const newValue = uploadHistory.value[uploadHistoryIndex.value].value
    internalValue.value = newValue
    emit('update:modelValue', newValue)
    emit('update:value', newValue)
  }
}

// Redo last undone action
const redo = () => {
  if (uploadHistoryIndex.value < uploadHistory.value.length - 1) {
    uploadHistoryIndex.value++
    const newValue = uploadHistory.value[uploadHistoryIndex.value].value
    internalValue.value = newValue
    emit('update:modelValue', newValue)
    emit('update:value', newValue)
  }
}

// Handle toolbar button clicks
const handleToolbarAction = (action: string) => {
  switch (action) {
    case 'bold':
      insertMarkdown('**', '**', 'bold text')
      break
    case 'italic':
      insertMarkdown('*', '*', 'italic text')
      break
    case 'strike':
      insertMarkdown('~~', '~~', 'strikethrough text')
      break
    case 'link':
      insertMarkdown('[', '](url)', 'link text')
      break
    case 'heading':
      insertMarkdown('## ', '', 'Heading')
      break
    case 'blockquote':
      insertMarkdown('> ', '', 'Quote')
      break
    case 'codeBlock':
      insertMarkdown('```\n', '\n```', 'code')
      break
    case 'bulletList':
      insertMarkdown('- ', '', 'List item')
      break
    case 'orderedList':
      insertMarkdown('1. ', '', 'List item')
      break
    case 'table':
      insertMarkdown(
        '| Column 1 | Column 2 |\n|----------|----------|\n| ',
        ' | Cell 2 |',
        'Cell 1'
      )
      break
    case 'attachFiles':
      fileInputRef.value?.click()
      break
    case 'undo':
      undo()
      break
    case 'redo':
      redo()
      break
  }
}

// Handle file upload
const handleFileUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (!file) return

  // Validate file type
  if (!props.fileAttachmentsAcceptedFileTypes.includes(file.type)) {
    alert(`File type not accepted. Allowed types: ${props.fileAttachmentsAcceptedFileTypes.join(', ')}`)
    return
  }

  // Validate file size (convert to KB)
  const fileSizeKB = file.size / 1024
  if (fileSizeKB > props.fileAttachmentsMaxSize) {
    alert(`File size exceeds maximum allowed size of ${props.fileAttachmentsMaxSize} KB`)
    return
  }

  // TODO: Implement actual file upload to Laravel backend
  // For now, we'll create a data URL for preview
  const reader = new FileReader()
  reader.onload = (e) => {
    const imageUrl = e.target?.result as string
    insertMarkdown('![', `](${imageUrl})`, file.name)
  }
  reader.readAsDataURL(file)

  // Reset file input
  target.value = ''
}

// Helper to get Lucide icon component by name
const getIconComponent = (iconName?: string) => {
  if (!iconName) return null
  // Convert kebab-case or lowercase to PascalCase for Lucide icons
  const pascalCase = iconName
    .split(/[-_\s]/)
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join('')
  return (LucideIcons as any)[pascalCase] || null
}

// Helper to get Tailwind color classes for icons
const getIconColorClass = (color?: string) => {
  if (!color) return 'text-muted-foreground'

  const colorMap: Record<string, string> = {
    'primary': 'text-primary',
    'secondary': 'text-secondary',
    'success': 'text-green-600',
    'danger': 'text-red-600',
    'warning': 'text-yellow-600',
    'info': 'text-blue-600',
    'muted': 'text-muted-foreground',
    'destructive': 'text-destructive',
  }

  return colorMap[color] || `text-${color}`
}

const handleInput = (event: Event) => {
  const target = event.target as HTMLTextAreaElement
  internalValue.value = target.value
  emit('update:modelValue', target.value)
  emit('update:value', target.value)
}

const togglePreview = () => {
  showPreview.value = !showPreview.value
}
</script>

<template>
  <div class="w-full space-y-2">
    <!-- Label -->
    <label
      v-if="label"
      :for="name"
      class="text-sm font-medium block text-foreground"
    >
      {{ label }}
      <span v-if="required" class="text-destructive ms-0.5">*</span>
    </label>

    <!-- Hidden input for form submission -->
    <input
      v-if="name"
      type="hidden"
      :name="name"
      :value="internalValue"
    />

    <!-- Header with icons and preview toggle -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <component
          v-if="getIconComponent(props.prefixIcon)"
          :is="getIconComponent(props.prefixIcon)"
          class="h-4 w-4"
          :class="getIconColorClass(props.prefixIconColor)"
        />
      </div>
      <div class="flex items-center gap-2">
        <Button
          type="button"
          variant="ghost"
          size="sm"
          @click="togglePreview"
        >
          <component :is="showPreview ? EyeOff : Eye" class="h-4 w-4 mr-2" />
          {{ showPreview ? 'Hide' : 'Show' }} Preview
        </Button>
        <component
          v-if="getIconComponent(props.suffixIcon)"
          :is="getIconComponent(props.suffixIcon)"
          class="h-4 w-4"
          :class="getIconColorClass(props.suffixIconColor)"
        />
      </div>
    </div>

    <!-- Toolbar -->
    <div
      v-if="toolbarButtons && toolbarButtons.length > 0"
      class="flex flex-wrap items-center gap-1 p-2 border border-input rounded-md bg-muted/50"
    >
      <template v-for="(group, groupIndex) in toolbarButtons" :key="groupIndex">
        <div class="flex items-center gap-1">
          <Button
            v-for="button in group"
            :key="button"
            type="button"
            variant="ghost"
            size="sm"
            class="h-8 w-8 p-0"
            :disabled="disabled || (button === 'attachFiles' && !fileAttachmentsEnabled)"
            :title="button"
            @click="handleToolbarAction(button)"
          >
            <component :is="toolbarIcons[button]" class="h-4 w-4" />
          </Button>
        </div>
        <div
          v-if="groupIndex < toolbarButtons.length - 1"
          class="h-6 w-px bg-border"
        />
      </template>
    </div>

    <!-- Hidden file input for image uploads -->
    <input
      v-if="fileAttachmentsEnabled"
      ref="fileInputRef"
      type="file"
      :accept="fileAttachmentsAcceptedFileTypes.join(',')"
      class="hidden"
      @change="handleFileUpload"
    />

    <!-- Editor container -->
    <div class="grid gap-4" :class="showPreview ? 'md:grid-cols-2' : 'grid-cols-1'">
      <!-- Editor -->
      <div>
        <textarea
          ref="textareaRef"
          :value="internalValue"
          :placeholder="placeholder"
          :disabled="disabled"
          :rows="12"
          class="flex min-h-[240px] w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm font-mono"
          @input="handleInput"
        />
      </div>

      <!-- Preview -->
      <div
        v-if="showPreview"
        class="border border-input rounded-md p-4 bg-background min-h-[200px] overflow-auto"
      >
        <div
          v-if="renderedHtml"
          class="markdown-preview"
          v-html="renderedHtml"
        />
        <p v-else class="text-muted-foreground text-sm">
          Start typing to see a preview...
        </p>
      </div>
    </div>

    <!-- Helper text -->
    <p
      v-if="helperText"
      class="text-xs text-muted-foreground mt-1"
    >
      {{ helperText }}
    </p>
  </div>
</template>

<style scoped>
.markdown-preview {
  color: var(--foreground);
  font-size: 0.875rem;
  line-height: 1.625;
}

.markdown-preview :deep(h1) {
  font-size: 1.875rem;
  font-weight: 700;
  margin-top: 0;
  margin-bottom: 1rem;
  line-height: 2.25rem;
  color: var(--foreground);
}

.markdown-preview :deep(h2) {
  font-size: 1.5rem;
  font-weight: 600;
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
  line-height: 2rem;
  color: var(--foreground);
}

.markdown-preview :deep(h3) {
  font-size: 1.25rem;
  font-weight: 600;
  margin-top: 1.25rem;
  margin-bottom: 0.5rem;
  line-height: 1.75rem;
  color: var(--foreground);
}

.markdown-preview :deep(h4) {
  font-size: 1.125rem;
  font-weight: 600;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
  line-height: 1.5rem;
  color: var(--foreground);
}

.markdown-preview :deep(h5),
.markdown-preview :deep(h6) {
  font-size: 1rem;
  font-weight: 600;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
  line-height: 1.5rem;
  color: var(--foreground);
}

.markdown-preview :deep(p) {
  margin-top: 0;
  margin-bottom: 1rem;
}

.markdown-preview :deep(a) {
  color: var(--primary);
  text-decoration: underline;
  text-underline-offset: 2px;
}

.markdown-preview :deep(a:hover) {
  opacity: 0.8;
}

.markdown-preview :deep(strong) {
  font-weight: 600;
  color: var(--foreground);
}

.markdown-preview :deep(em) {
  font-style: italic;
}

.markdown-preview :deep(code) {
  background-color: var(--muted);
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-size: 0.875em;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
  color: var(--foreground);
}

.markdown-preview :deep(pre) {
  background-color: var(--muted);
  padding: 1rem;
  border-radius: 0.5rem;
  overflow-x: auto;
  margin-top: 0;
  margin-bottom: 1rem;
}

.markdown-preview :deep(pre code) {
  background-color: transparent;
  padding: 0;
  border-radius: 0;
  font-size: 0.875rem;
}

.markdown-preview :deep(ul),
.markdown-preview :deep(ol) {
  margin-top: 0;
  margin-bottom: 1rem;
  padding-left: 1.625rem;
}

.markdown-preview :deep(ul) {
  list-style-type: disc;
}

.markdown-preview :deep(ol) {
  list-style-type: decimal;
}

.markdown-preview :deep(li) {
  margin-top: 0.25rem;
  margin-bottom: 0.25rem;
}

.markdown-preview :deep(blockquote) {
  border-left: 4px solid var(--border);
  padding-left: 1rem;
  margin: 1rem 0;
  font-style: italic;
  color: var(--muted-foreground);
}

.markdown-preview :deep(hr) {
  border: none;
  border-top: 1px solid var(--border);
  margin: 1.5rem 0;
}

.markdown-preview :deep(table) {
  width: 100%;
  border-collapse: collapse;
  margin: 1rem 0;
}

.markdown-preview :deep(th),
.markdown-preview :deep(td) {
  border: 1px solid var(--border);
  padding: 0.5rem 0.75rem;
  text-align: left;
}

.markdown-preview :deep(th) {
  background-color: var(--muted);
  font-weight: 600;
}

.markdown-preview :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
  margin: 1rem 0;
}
</style>
