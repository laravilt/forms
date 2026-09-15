<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { EditorView } from '@codemirror/view'
import { EditorState } from '@codemirror/state'
import { basicSetup } from 'codemirror'
import { oneDark } from '@codemirror/theme-one-dark'
import { cpp } from '@codemirror/lang-cpp'
import { css } from '@codemirror/lang-css'
import { html } from '@codemirror/lang-html'
import { java } from '@codemirror/lang-java'
import { javascript } from '@codemirror/lang-javascript'
import { json } from '@codemirror/lang-json'
import { markdown } from '@codemirror/lang-markdown'
import { php } from '@codemirror/lang-php'
import { python } from '@codemirror/lang-python'
import { sql } from '@codemirror/lang-sql'
import { xml } from '@codemirror/lang-xml'
import { yaml } from '@codemirror/lang-yaml'
import * as LucideIcons from 'lucide-vue-next'
import { Code } from 'lucide-vue-next'

interface Props {
  name?: string
  value?: string | null
  modelValue?: string | null
  label?: string
  helperText?: string
  required?: boolean
  language?: string
  theme?: 'light' | 'dark'
  lineNumbers?: boolean
  readOnly?: boolean
  disabled?: boolean
  placeholder?: string
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: null,
  value: null,
  language: 'javascript',
  theme: 'light',
  lineNumbers: true,
  readOnly: false,
  disabled: false,
  placeholder: '',
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'update:value': [value: string]
}>()

// Internal value tracking
const internalValue = ref<string>(props.modelValue ?? props.value ?? '')

// The CodeMirror container is not labelable, so the label is referenced via aria-labelledby
const labelId = computed(() => (props.label && props.name ? `${props.name}-label` : undefined))

const editorContainer = ref<HTMLDivElement>()
let editorView: EditorView | null = null

// Language extension mapping
const getLanguageExtension = (lang: string) => {
  const langMap: Record<string, any> = {
    'cpp': cpp(),
    'c++': cpp(),
    'css': css(),
    'go': null, // CodeMirror doesn't have built-in Go support in v6
    'html': html(),
    'java': java(),
    'javascript': javascript(),
    'js': javascript(),
    'jsx': javascript({ jsx: true }),
    'typescript': javascript({ typescript: true }),
    'ts': javascript({ typescript: true }),
    'tsx': javascript({ typescript: true, jsx: true }),
    'json': json(),
    'markdown': markdown(),
    'md': markdown(),
    'php': php(),
    'python': python(),
    'py': python(),
    'sql': sql(),
    'xml': xml(),
    'yaml': yaml(),
    'yml': yaml(),
  }

  return langMap[lang.toLowerCase()] || null
}

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

  return colorMap[color] || 'text-muted-foreground'
}

// Computed language label
const languageLabel = computed(() => {
  const labels: Record<string, string> = {
    'cpp': 'C++',
    'c++': 'C++',
    'css': 'CSS',
    'go': 'Go',
    'html': 'HTML',
    'java': 'Java',
    'javascript': 'JavaScript',
    'js': 'JavaScript',
    'jsx': 'JSX',
    'typescript': 'TypeScript',
    'ts': 'TypeScript',
    'tsx': 'TSX',
    'json': 'JSON',
    'markdown': 'Markdown',
    'md': 'Markdown',
    'php': 'PHP',
    'python': 'Python',
    'py': 'Python',
    'sql': 'SQL',
    'xml': 'XML',
    'yaml': 'YAML',
    'yml': 'YAML',
  }

  return labels[props.language.toLowerCase()] || props.language.toUpperCase()
})

const createEditorState = (doc: string) => {
  const extensions: any[] = [basicSetup]

  // Add language support
  const langExtension = getLanguageExtension(props.language)
  if (langExtension) {
    extensions.push(langExtension)
  }

  // Add theme
  if (props.theme === 'dark') {
    extensions.push(oneDark)
  }

  // Add read-only mode
  if (props.readOnly || props.disabled) {
    extensions.push(EditorState.readOnly.of(true))
  }

  // Add update listener to emit changes
  extensions.push(
    EditorView.updateListener.of((update) => {
      if (update.docChanged) {
        const newValue = update.state.doc.toString()
        internalValue.value = newValue
        emit('update:modelValue', newValue)
        emit('update:value', newValue)
      }
    })
  )

  // Add placeholder support
  if (props.placeholder) {
    extensions.push(
      EditorView.contentAttributes.of({ 'aria-placeholder': props.placeholder })
    )
  }

  // Associate the label with the editable content
  if (labelId.value) {
    extensions.push(EditorView.contentAttributes.of({ 'aria-labelledby': labelId.value }))
  }

  // basicSetup always includes the line-number gutter; hide it when disabled
  if (props.lineNumbers === false) {
    extensions.push(EditorView.theme({ '.cm-gutters': { display: 'none' } }))
  }

  return EditorState.create({
    doc,
    extensions,
  })
}

// Initialize CodeMirror
onMounted(() => {
  if (!editorContainer.value) return

  const initialValue = props.modelValue ?? props.value ?? ''
  internalValue.value = initialValue
  const state = createEditorState(initialValue)

  editorView = new EditorView({
    state,
    parent: editorContainer.value,
  })
})

// Clean up on unmount
onUnmounted(() => {
  editorView?.destroy()
  editorView = null
})

// Watch for external value changes
watch(() => props.modelValue ?? props.value, (newValue) => {
  if (!editorView) return

  const currentValue = editorView.state.doc.toString()
  if (newValue !== null && newValue !== undefined && newValue !== currentValue) {
    internalValue.value = newValue
    editorView.dispatch({
      changes: {
        from: 0,
        to: currentValue.length,
        insert: newValue || '',
      },
    })
  }
}, { immediate: true })

// Watch for language changes
watch(() => props.language, () => {
  if (!editorView) return

  // Recreate editor with new language
  const currentValue = editorView.state.doc.toString()
  editorView.destroy()

  const state = createEditorState(currentValue)
  editorView = new EditorView({
    state,
    parent: editorContainer.value!,
  })
})

// Watch for theme changes
watch(() => props.theme, () => {
  if (!editorView) return

  // Recreate editor with new theme
  const currentValue = editorView.state.doc.toString()
  editorView.destroy()

  const state = createEditorState(currentValue)
  editorView = new EditorView({
    state,
    parent: editorContainer.value!,
  })
})

// Watch for readOnly/disabled/lineNumbers changes
watch(() => [props.readOnly, props.disabled, props.lineNumbers], () => {
  if (!editorView) return

  // Recreate editor with new read-only state
  const currentValue = editorView.state.doc.toString()
  editorView.destroy()

  const state = createEditorState(currentValue)
  editorView = new EditorView({
    state,
    parent: editorContainer.value!,
  })
})
</script>

<template>
  <div class="w-full space-y-2">
    <!-- Label -->
    <label
      v-if="label"
      :id="labelId"
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

    <!-- Header with language and icons -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <component
          v-if="getIconComponent(props.prefixIcon)"
          :is="getIconComponent(props.prefixIcon)"
          class="h-4 w-4"
          :class="getIconColorClass(props.prefixIconColor)"
        />
        <Code class="h-4 w-4 text-muted-foreground" />
        <span class="text-sm text-muted-foreground">{{ languageLabel }}</span>
      </div>
      <component
        v-if="getIconComponent(props.suffixIcon)"
        :is="getIconComponent(props.suffixIcon)"
        class="h-4 w-4"
        :class="getIconColorClass(props.suffixIconColor)"
      />
    </div>

    <!-- CodeMirror editor -->
    <div
      ref="editorContainer"
      class="border border-input rounded-md overflow-hidden bg-background"
      :class="{
        'opacity-50 cursor-not-allowed': disabled,
      }"
    />

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
/* CodeMirror custom styling */
.cm-editor {
  height: auto;
  min-height: 300px;
  font-size: 14px;
}

.cm-scroller {
  overflow: auto;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
}

.cm-content {
  padding: 12px 0;
}

.cm-line {
  padding: 0 12px;
}

/* Light theme adjustments */
.cm-editor:not(.cm-focused) .cm-activeLine {
  background-color: transparent;
}

.cm-editor .cm-gutters {
  background-color: hsl(var(--muted));
  border-right: 1px solid hsl(var(--border));
}

.cm-editor .cm-activeLineGutter {
  background-color: hsl(var(--muted) / 0.5);
}

/* Dark theme handled by oneDark extension */
</style>
