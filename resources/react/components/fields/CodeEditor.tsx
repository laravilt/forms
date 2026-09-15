import { cpp } from '@codemirror/lang-cpp';
import { css } from '@codemirror/lang-css';
import { html } from '@codemirror/lang-html';
import { java } from '@codemirror/lang-java';
import { javascript } from '@codemirror/lang-javascript';
import { json } from '@codemirror/lang-json';
import { markdown } from '@codemirror/lang-markdown';
import { php } from '@codemirror/lang-php';
import { python } from '@codemirror/lang-python';
import { sql } from '@codemirror/lang-sql';
import { xml } from '@codemirror/lang-xml';
import { yaml } from '@codemirror/lang-yaml';
import { EditorState, type Extension } from '@codemirror/state';
import { oneDark } from '@codemirror/theme-one-dark';
import { EditorView } from '@codemirror/view';
import { basicSetup } from 'codemirror';
import { Code } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

import { cn } from '@/lib/utils';
import { useLatest } from '@laravilt/support/composables/hooks';
import { resolveIcon } from '@laravilt/support/lib/icons';

// Unscoped <style> block of CodeEditor.vue
const CODE_EDITOR_STYLES = `
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
`;

export interface CodeEditorProps {
    name?: string;
    value?: string | null;
    modelValue?: string | null;
    label?: string;
    helperText?: string;
    required?: boolean;
    language?: string;
    theme?: 'light' | 'dark';
    lineNumbers?: boolean;
    readOnly?: boolean;
    disabled?: boolean;
    placeholder?: string;
    prefixIcon?: string;
    suffixIcon?: string;
    prefixIconColor?: string;
    suffixIconColor?: string;
    onUpdateModelValue?: (value: string) => void;
    onUpdateValue?: (value: string) => void;
    [key: string]: any;
}

// Language extension mapping
const getLanguageExtension = (lang: string): Extension | null => {
    const langMap: Record<string, Extension | null> = {
        cpp: cpp(),
        'c++': cpp(),
        css: css(),
        go: null, // CodeMirror doesn't have built-in Go support in v6
        html: html(),
        java: java(),
        javascript: javascript(),
        js: javascript(),
        jsx: javascript({ jsx: true }),
        typescript: javascript({ typescript: true }),
        ts: javascript({ typescript: true }),
        tsx: javascript({ typescript: true, jsx: true }),
        json: json(),
        markdown: markdown(),
        md: markdown(),
        php: php(),
        python: python(),
        py: python(),
        sql: sql(),
        xml: xml(),
        yaml: yaml(),
        yml: yaml(),
    };

    return langMap[lang.toLowerCase()] || null;
};

const LANGUAGE_LABELS: Record<string, string> = {
    cpp: 'C++',
    'c++': 'C++',
    css: 'CSS',
    go: 'Go',
    html: 'HTML',
    java: 'Java',
    javascript: 'JavaScript',
    js: 'JavaScript',
    jsx: 'JSX',
    typescript: 'TypeScript',
    ts: 'TypeScript',
    tsx: 'TSX',
    json: 'JSON',
    markdown: 'Markdown',
    md: 'Markdown',
    php: 'PHP',
    python: 'Python',
    py: 'Python',
    sql: 'SQL',
    xml: 'XML',
    yaml: 'YAML',
    yml: 'YAML',
};

// Helper to get Tailwind color classes for icons
const getIconColorClass = (color?: string): string => {
    if (!color) return 'text-muted-foreground';

    const colorMap: Record<string, string> = {
        primary: 'text-primary',
        secondary: 'text-secondary',
        success: 'text-green-600',
        danger: 'text-red-600',
        warning: 'text-yellow-600',
        info: 'text-blue-600',
        muted: 'text-muted-foreground',
        destructive: 'text-destructive',
    };

    return colorMap[color] || 'text-muted-foreground';
};

export default function CodeEditor({
    name,
    value = null,
    modelValue = null,
    label,
    helperText,
    required,
    language = 'javascript',
    theme = 'light',
    readOnly = false,
    disabled = false,
    placeholder = '',
    lineNumbers = true,
    prefixIcon,
    suffixIcon,
    prefixIconColor,
    suffixIconColor,
    onUpdateModelValue,
    onUpdateValue,
}: CodeEditorProps) {
    const externalValue = modelValue ?? value;

    // The CodeMirror container is not labelable, so the label is referenced via aria-labelledby
    const labelId = label && name ? `${name}-label` : undefined;

    // Internal value tracking
    const [internalValue, setInternalValue] = useState<string>(modelValue ?? value ?? '');

    const editorContainer = useRef<HTMLDivElement>(null);
    const editorView = useRef<EditorView | null>(null);

    // CodeMirror callbacks are created once per editor, so they read the latest props through this ref.
    const latest = useLatest({
        language,
        theme,
        readOnly,
        disabled,
        placeholder,
        lineNumbers,
        labelId,
        externalValue,
        onUpdateModelValue,
        onUpdateValue,
    });

    // Computed language label
    const languageLabel = LANGUAGE_LABELS[language.toLowerCase()] || language.toUpperCase();

    const createEditorState = (doc: string): EditorState => {
        const current = latest.current;
        const extensions: Extension[] = [basicSetup];

        // Add language support
        const langExtension = getLanguageExtension(current.language);
        if (langExtension) {
            extensions.push(langExtension);
        }

        // Add theme
        if (current.theme === 'dark') {
            extensions.push(oneDark);
        }

        // Add read-only mode
        if (current.readOnly || current.disabled) {
            extensions.push(EditorState.readOnly.of(true));
        }

        // Add update listener to emit changes
        extensions.push(
            EditorView.updateListener.of((update) => {
                if (update.docChanged) {
                    const newValue = update.state.doc.toString();
                    setInternalValue(newValue);
                    latest.current.onUpdateModelValue?.(newValue);
                    latest.current.onUpdateValue?.(newValue);
                }
            }),
        );

        // Add placeholder support
        if (current.placeholder) {
            extensions.push(EditorView.contentAttributes.of({ 'aria-placeholder': current.placeholder }));
        }

        // Associate the label with the editable content
        if (current.labelId) {
            extensions.push(EditorView.contentAttributes.of({ 'aria-labelledby': current.labelId }));
        }

        // basicSetup always includes the line-number gutter; hide it when disabled
        if (current.lineNumbers === false) {
            extensions.push(EditorView.theme({ '.cm-gutters': { display: 'none' } }));
        }

        return EditorState.create({
            doc,
            extensions,
        });
    };

    // Initialize CodeMirror (onMounted) and clean up on unmount (onUnmounted)
    useEffect(() => {
        if (!editorContainer.current) return;

        const initialValue = latest.current.externalValue ?? '';
        setInternalValue(initialValue);
        const state = createEditorState(initialValue);

        editorView.current = new EditorView({
            state,
            parent: editorContainer.current,
        });

        return () => {
            editorView.current?.destroy();
            editorView.current = null;
        };
    }, []);

    // Watch for external value changes
    useEffect(() => {
        const view = editorView.current;
        if (!view) return;

        const currentValue = view.state.doc.toString();
        if (externalValue !== null && externalValue !== undefined && externalValue !== currentValue) {
            setInternalValue(externalValue);
            view.dispatch({
                changes: {
                    from: 0,
                    to: currentValue.length,
                    insert: externalValue || '',
                },
            });
        }
    }, [externalValue]);

    // Watch for language / theme / readOnly / disabled / lineNumbers changes: recreate the editor
    const previousEditorConfig = useRef({ language, theme, readOnly, disabled, lineNumbers });
    useEffect(() => {
        const previous = previousEditorConfig.current;
        previousEditorConfig.current = { language, theme, readOnly, disabled, lineNumbers };

        if (
            previous.language === language &&
            previous.theme === theme &&
            previous.readOnly === readOnly &&
            previous.disabled === disabled &&
            previous.lineNumbers === lineNumbers
        ) {
            return;
        }

        const view = editorView.current;
        if (!view || !editorContainer.current) return;

        const currentValue = view.state.doc.toString();
        view.destroy();

        const state = createEditorState(currentValue);
        editorView.current = new EditorView({
            state,
            parent: editorContainer.current,
        });
    }, [language, theme, readOnly, disabled, lineNumbers]);

    const PrefixIcon = resolveIcon(prefixIcon);
    const SuffixIcon = resolveIcon(suffixIcon);

    return (
        <div className="w-full space-y-2">
            <style href="laravilt-forms-code-editor" precedence="default">
                {CODE_EDITOR_STYLES}
            </style>

            {/* Label */}
            {label && (
                <label id={labelId} className="text-sm font-medium block text-foreground">
                    {label}{' '}
                    {required && <span className="text-destructive ms-0.5">*</span>}
                </label>
            )}

            {/* Hidden input for form submission */}
            {name && <input type="hidden" name={name} value={internalValue} />}

            {/* Header with language and icons */}
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                    {PrefixIcon && <PrefixIcon className={cn('h-4 w-4', getIconColorClass(prefixIconColor))} />}
                    <Code className="h-4 w-4 text-muted-foreground" />
                    <span className="text-sm text-muted-foreground">{languageLabel}</span>
                </div>
                {SuffixIcon && <SuffixIcon className={cn('h-4 w-4', getIconColorClass(suffixIconColor))} />}
            </div>

            {/* CodeMirror editor */}
            <div
                ref={editorContainer}
                className={cn('border border-input rounded-md overflow-hidden bg-background', {
                    'opacity-50 cursor-not-allowed': disabled,
                })}
            />

            {/* Helper text */}
            {helperText && <p className="text-xs text-muted-foreground mt-1">{helperText}</p>}
        </div>
    );
}
