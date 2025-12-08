<script setup lang="ts">
import { watch, onBeforeUnmount } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { Underline } from '@tiptap/extension-underline'
import { Link } from '@tiptap/extension-link'
import { Image } from '@tiptap/extension-image'
import { TextAlign } from '@tiptap/extension-text-align'
import { Highlight } from '@tiptap/extension-highlight'
import { HorizontalRule } from '@tiptap/extension-horizontal-rule'
import { Table } from '@tiptap/extension-table'
import { TableRow } from '@tiptap/extension-table-row'
import { TableHeader } from '@tiptap/extension-table-header'
import { TableCell } from '@tiptap/extension-table-cell'
import { Placeholder } from '@tiptap/extension-placeholder'
import { Color } from '@tiptap/extension-color'
import { TextStyle } from '@tiptap/extension-text-style'
import { Button } from '@/components/ui/button'
import * as LucideIcons from 'lucide-vue-next'
import {
  Bold,
  Italic,
  Underline as UnderlineIcon,
  List,
  ListOrdered,
  Link as LinkIcon,
  Image as ImageIcon,
  Strikethrough,
  Code,
  Heading1,
  Heading2,
  Heading3,
  AlignLeft,
  AlignCenter,
  AlignRight,
  AlignJustify,
  Undo,
  Redo,
  Highlighter,
  Minus,
  RemoveFormatting,
  Table as TableIcon,
  Columns,
  Rows,
  Trash2,
  Palette,
} from 'lucide-vue-next'

interface Props {
  name?: string
  value?: string
  modelValue?: string
  label?: string
  helperText?: string
  required?: boolean
  toolbarButtons?: string[]
  placeholder?: string
  minHeight?: number
  maxHeight?: number
  disabled?: boolean
  json?: boolean
  floatingToolbars?: Array<{ nodeTypes: string[]; buttons: string[] }>
  textColors?: string[]
  customTextColors?: Array<{ label: string; color: string }>
  fileAttachmentsDisk?: string | null
  fileAttachmentsDirectory?: string | null
  fileAttachmentsVisibility?: string | null
  fileAttachmentsAcceptedFileTypes?: string[]
  fileAttachmentsMaxSize?: number | null
  customBlocks?: Array<{ identifier: string; label: string; icon?: string }>
  mergeTags?: Array<{ tag: string; label: string; content?: string }>
  activePanel?: string | null
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  toolbarButtons: () => [
    'bold',
    'italic',
    'underline',
    'strike',
    'code',
    'heading1',
    'heading2',
    'heading3',
    'bulletList',
    'orderedList',
    'alignLeft',
    'alignCenter',
    'alignRight',
    'alignJustify',
    'link',
    'image',
    'clearFormatting',
    'highlight',
    'horizontalRule',
    'textColor',
    'table',
    'undo',
    'redo',
  ],
  placeholder: 'Start typing...',
  minHeight: 200,
  disabled: false,
  json: false,
  floatingToolbars: () => [],
  textColors: () => [],
  customTextColors: () => [],
  fileAttachmentsAcceptedFileTypes: () => [],
  customBlocks: () => [],
  mergeTags: () => [],
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'update:value': [value: string]
}>()

// Helper to get Lucide icon component by name
const getIconComponent = (iconName?: string) => {
  if (!iconName) return null
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

// Initialize TipTap editor
const editor = useEditor({
  content: props.modelValue,
  editable: !props.disabled,
  extensions: [
    StarterKit.configure({
      // Disable extensions we're configuring separately
      horizontalRule: false,
    }),
    Underline,
    Link.configure({
      openOnClick: false,
      HTMLAttributes: {
        class: 'text-primary underline',
      },
    }),
    Image.configure({
      HTMLAttributes: {
        class: 'max-w-full h-auto rounded-md',
      },
    }),
    TextAlign.configure({
      types: ['heading', 'paragraph'],
    }),
    Highlight.configure({
      multicolor: true,
    }),
    HorizontalRule,
    Table.configure({
      resizable: true,
    }),
    TableRow,
    TableHeader,
    TableCell,
    Placeholder.configure({
      placeholder: props.placeholder,
    }),
    TextStyle,
    Color,
  ],
  editorProps: {
    attributes: {
      class: 'prose prose-sm dark:prose-invert max-w-none focus:outline-none p-4',
      style: `min-height: ${props.minHeight}px; ${props.maxHeight ? `max-height: ${props.maxHeight}px; overflow-y: auto;` : ''}`,
    },
  },
  onUpdate: ({ editor }) => {
    const content = props.json ? editor.getJSON() : editor.getHTML()
    const emittedValue = props.json ? JSON.stringify(content) : content
    emit('update:modelValue', emittedValue)
    emit('update:value', emittedValue)
  },
})

// Watch for external changes to modelValue
watch(() => props.modelValue, (value) => {
  if (!editor.value) return

  if (props.json) {
    const currentContent = JSON.stringify(editor.value.getJSON())
    if (value && value !== currentContent) {
      try {
        editor.value.commands.setContent(JSON.parse(value), false)
      } catch (e) {
        editor.value.commands.setContent(value || '', false)
      }
    }
  } else {
    if (value !== editor.value.getHTML()) {
      editor.value.commands.setContent(value || '', false)
    }
  }
})

// Watch for disabled changes
watch(() => props.disabled, (disabled) => {
  if (editor.value) {
    editor.value.setEditable(!disabled)
  }
})

// Cleanup on unmount
onBeforeUnmount(() => {
  editor.value?.destroy()
})

// Toolbar button icons map
const toolbarIconMap: Record<string, any> = {
  'bold': Bold,
  'italic': Italic,
  'underline': UnderlineIcon,
  'strike': Strikethrough,
  'code': Code,
  'heading1': Heading1,
  'heading2': Heading2,
  'heading3': Heading3,
  'bulletList': List,
  'orderedList': ListOrdered,
  'alignLeft': AlignLeft,
  'alignCenter': AlignCenter,
  'alignRight': AlignRight,
  'alignJustify': AlignJustify,
  'link': LinkIcon,
  'image': ImageIcon,
  'highlight': Highlighter,
  'horizontalRule': Minus,
  'clearFormatting': RemoveFormatting,
  'textColor': Palette,
  'table': TableIcon,
  'tableAddColumnBefore': Columns,
  'tableAddColumnAfter': Columns,
  'tableDeleteColumn': Trash2,
  'tableAddRowBefore': Rows,
  'tableAddRowAfter': Rows,
  'tableDeleteRow': Trash2,
  'tableMergeCells': TableIcon,
  'tableSplitCell': TableIcon,
  'tableToggleHeaderRow': TableIcon,
  'tableDelete': Trash2,
  'undo': Undo,
  'redo': Redo,
}

// Toolbar actions
const handleToolbarAction = (action: string) => {
  if (!editor.value) return

  switch (action) {
    case 'bold':
      editor.value.chain().focus().toggleBold().run()
      break
    case 'italic':
      editor.value.chain().focus().toggleItalic().run()
      break
    case 'underline':
      editor.value.chain().focus().toggleUnderline().run()
      break
    case 'strike':
      editor.value.chain().focus().toggleStrike().run()
      break
    case 'code':
      editor.value.chain().focus().toggleCode().run()
      break
    case 'heading1':
      editor.value.chain().focus().toggleHeading({ level: 1 }).run()
      break
    case 'heading2':
      editor.value.chain().focus().toggleHeading({ level: 2 }).run()
      break
    case 'heading3':
      editor.value.chain().focus().toggleHeading({ level: 3 }).run()
      break
    case 'bulletList':
      editor.value.chain().focus().toggleBulletList().run()
      break
    case 'orderedList':
      editor.value.chain().focus().toggleOrderedList().run()
      break
    case 'alignLeft':
      editor.value.chain().focus().setTextAlign('left').run()
      break
    case 'alignCenter':
      editor.value.chain().focus().setTextAlign('center').run()
      break
    case 'alignRight':
      editor.value.chain().focus().setTextAlign('right').run()
      break
    case 'alignJustify':
      editor.value.chain().focus().setTextAlign('justify').run()
      break
    case 'link':
      addLink()
      break
    case 'image':
      addImage()
      break
    case 'highlight':
      editor.value.chain().focus().toggleHighlight().run()
      break
    case 'horizontalRule':
      editor.value.chain().focus().setHorizontalRule().run()
      break
    case 'clearFormatting':
      editor.value.chain().focus().clearNodes().unsetAllMarks().run()
      break
    case 'textColor':
      setTextColor()
      break
    case 'table':
      editor.value.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()
      break
    case 'tableAddColumnBefore':
      editor.value.chain().focus().addColumnBefore().run()
      break
    case 'tableAddColumnAfter':
      editor.value.chain().focus().addColumnAfter().run()
      break
    case 'tableDeleteColumn':
      editor.value.chain().focus().deleteColumn().run()
      break
    case 'tableAddRowBefore':
      editor.value.chain().focus().addRowBefore().run()
      break
    case 'tableAddRowAfter':
      editor.value.chain().focus().addRowAfter().run()
      break
    case 'tableDeleteRow':
      editor.value.chain().focus().deleteRow().run()
      break
    case 'tableMergeCells':
      editor.value.chain().focus().mergeCells().run()
      break
    case 'tableSplitCell':
      editor.value.chain().focus().splitCell().run()
      break
    case 'tableToggleHeaderRow':
      editor.value.chain().focus().toggleHeaderRow().run()
      break
    case 'tableDelete':
      editor.value.chain().focus().deleteTable().run()
      break
    case 'undo':
      editor.value.chain().focus().undo().run()
      break
    case 'redo':
      editor.value.chain().focus().redo().run()
      break
  }
}

// Check if action is active
const isActive = (action: string): boolean => {
  if (!editor.value) return false

  switch (action) {
    case 'bold':
      return editor.value.isActive('bold')
    case 'italic':
      return editor.value.isActive('italic')
    case 'underline':
      return editor.value.isActive('underline')
    case 'strike':
      return editor.value.isActive('strike')
    case 'code':
      return editor.value.isActive('code')
    case 'heading1':
      return editor.value.isActive('heading', { level: 1 })
    case 'heading2':
      return editor.value.isActive('heading', { level: 2 })
    case 'heading3':
      return editor.value.isActive('heading', { level: 3 })
    case 'bulletList':
      return editor.value.isActive('bulletList')
    case 'orderedList':
      return editor.value.isActive('orderedList')
    case 'alignLeft':
      return editor.value.isActive({ textAlign: 'left' })
    case 'alignCenter':
      return editor.value.isActive({ textAlign: 'center' })
    case 'alignRight':
      return editor.value.isActive({ textAlign: 'right' })
    case 'alignJustify':
      return editor.value.isActive({ textAlign: 'justify' })
    case 'link':
      return editor.value.isActive('link')
    case 'highlight':
      return editor.value.isActive('highlight')
    case 'table':
      return editor.value.isActive('table')
    default:
      return false
  }
}

// Add link
const addLink = () => {
  if (!editor.value) return

  const url = window.prompt('Enter URL:')
  if (url) {
    editor.value.chain().focus().setLink({ href: url }).run()
  }
}

// Add image
const addImage = () => {
  if (!editor.value) return

  const url = window.prompt('Enter image URL:')
  if (url) {
    editor.value.chain().focus().setImage({ src: url }).run()
  }
}

// Set text color
const setTextColor = () => {
  if (!editor.value) return

  const color = window.prompt('Enter color (hex, rgb, or color name):')
  if (color) {
    editor.value.chain().focus().setColor(color).run()
  }
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
      :value="editor?.getHTML() || ''"
    />

    <!-- Header icons -->
    <div v-if="getIconComponent(props.prefixIcon) || getIconComponent(props.suffixIcon)" class="flex items-center justify-between">
      <component
        v-if="getIconComponent(props.prefixIcon)"
        :is="getIconComponent(props.prefixIcon)"
        class="h-4 w-4"
        :class="getIconColorClass(props.prefixIconColor)"
      />
      <component
        v-if="getIconComponent(props.suffixIcon)"
        :is="getIconComponent(props.suffixIcon)"
        class="h-4 w-4"
        :class="getIconColorClass(props.suffixIconColor)"
      />
    </div>

    <!-- Editor container -->
    <div class="border border-input rounded-md overflow-hidden bg-background">
      <!-- Toolbar -->
      <div class="flex items-center flex-wrap gap-1 p-2 border-b border-input bg-muted/50">
        <Button
          v-for="button in toolbarButtons"
          :key="button"
          type="button"
          variant="ghost"
          size="sm"
          class="h-8 w-8 p-0"
          :class="{ 'bg-accent': isActive(button) }"
          :disabled="disabled || !editor"
          @click="handleToolbarAction(button)"
        >
          <component
            :is="toolbarIconMap[button]"
            class="h-4 w-4"
          />
        </Button>
      </div>

      <!-- TipTap Editor -->
      <EditorContent :editor="editor" />
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

<style>
/* TipTap editor styles */
.ProseMirror {
  outline: none;
}

.ProseMirror p.is-editor-empty:first-child::before {
  color: hsl(var(--muted-foreground));
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}

.ProseMirror:focus {
  outline: none;
}

/* Link styles */
.ProseMirror a {
  color: hsl(var(--primary));
  text-decoration: underline;
  cursor: pointer;
}

.ProseMirror a:hover {
  color: hsl(var(--primary) / 0.8);
}

/* Image styles */
.ProseMirror img {
  max-width: 100%;
  height: auto;
  border-radius: 0.375rem;
  display: block;
  margin: 1rem 0;
}

/* Code styles */
.ProseMirror code {
  background-color: hsl(var(--muted));
  padding: 0.125rem 0.25rem;
  border-radius: 0.25rem;
  font-size: 0.875em;
}

.ProseMirror pre {
  background-color: hsl(var(--muted));
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  overflow-x: auto;
}

.ProseMirror pre code {
  background: none;
  padding: 0;
}

/* List styles */
.ProseMirror ul,
.ProseMirror ol {
  padding-left: 1.5rem;
}

.ProseMirror ul {
  list-style-type: disc;
}

.ProseMirror ol {
  list-style-type: decimal;
}

/* Heading styles */
.ProseMirror h1 {
  font-size: 2em;
  font-weight: bold;
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}

.ProseMirror h2 {
  font-size: 1.5em;
  font-weight: bold;
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}

.ProseMirror h3 {
  font-size: 1.25em;
  font-weight: bold;
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}

/* Table styles */
.ProseMirror table {
  border-collapse: collapse;
  table-layout: fixed;
  width: 100%;
  margin: 1rem 0;
  overflow: hidden;
}

.ProseMirror td,
.ProseMirror th {
  min-width: 1em;
  border: 2px solid hsl(var(--border));
  padding: 0.5rem;
  vertical-align: top;
  box-sizing: border-box;
  position: relative;
}

.ProseMirror th {
  font-weight: bold;
  text-align: left;
  background-color: hsl(var(--muted));
}

.ProseMirror .selectedCell:after {
  z-index: 2;
  position: absolute;
  content: "";
  left: 0; right: 0; top: 0; bottom: 0;
  background: hsl(var(--primary) / 0.1);
  pointer-events: none;
}

.ProseMirror .column-resize-handle {
  position: absolute;
  right: -2px;
  top: 0;
  bottom: -2px;
  width: 4px;
  background-color: hsl(var(--primary));
  pointer-events: none;
}

/* Highlight styles */
.ProseMirror mark {
  background-color: hsl(var(--yellow) / 0.3);
  padding: 0.125rem 0;
}

/* Horizontal rule styles */
.ProseMirror hr {
  border: none;
  border-top: 2px solid hsl(var(--border));
  margin: 2rem 0;
}
</style>
