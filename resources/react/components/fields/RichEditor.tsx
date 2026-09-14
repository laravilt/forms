import { Color } from '@tiptap/extension-color';
import { Highlight } from '@tiptap/extension-highlight';
import { HorizontalRule } from '@tiptap/extension-horizontal-rule';
import { Image } from '@tiptap/extension-image';
import { Link } from '@tiptap/extension-link';
import { Placeholder } from '@tiptap/extension-placeholder';
import { Table, TableCell, TableHeader, TableRow } from '@tiptap/extension-table';
import { TextAlign } from '@tiptap/extension-text-align';
import { TextStyle } from '@tiptap/extension-text-style';
import { Underline } from '@tiptap/extension-underline';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import {
    AlignCenter,
    AlignJustify,
    AlignLeft,
    AlignRight,
    Bold,
    Code,
    Columns,
    Heading1,
    Heading2,
    Heading3,
    Highlighter,
    Image as ImageIcon,
    Italic,
    Link as LinkIcon,
    List,
    ListOrdered,
    Minus,
    Palette,
    Redo,
    RemoveFormatting,
    Rows,
    Strikethrough,
    Table as TableIcon,
    Trash2,
    Underline as UnderlineIcon,
    Undo,
    type LucideIcon,
} from 'lucide-react';
import { useEffect, useRef } from 'react';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { useLatest } from '@laravilt/support/composables/hooks';
import { resolveIcon } from '@laravilt/support/lib/icons';

// Unscoped <style> block of RichEditor.vue
const RICH_EDITOR_STYLES = `
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
`;

export interface RichEditorProps {
    name?: string;
    value?: string;
    modelValue?: string;
    label?: string;
    helperText?: string;
    required?: boolean;
    toolbarButtons?: string[];
    placeholder?: string;
    minHeight?: number;
    maxHeight?: number;
    disabled?: boolean;
    json?: boolean;
    floatingToolbars?: Array<{ nodeTypes: string[]; buttons: string[] }>;
    textColors?: string[];
    customTextColors?: Array<{ label: string; color: string }>;
    fileAttachmentsDisk?: string | null;
    fileAttachmentsDirectory?: string | null;
    fileAttachmentsVisibility?: string | null;
    fileAttachmentsAcceptedFileTypes?: string[];
    fileAttachmentsMaxSize?: number | null;
    customBlocks?: Array<{ identifier: string; label: string; icon?: string }>;
    mergeTags?: Array<{ tag: string; label: string; content?: string }>;
    activePanel?: string | null;
    prefixIcon?: string;
    suffixIcon?: string;
    prefixIconColor?: string;
    suffixIconColor?: string;
    onUpdateModelValue?: (value: string) => void;
    onUpdateValue?: (value: string) => void;
    [key: string]: any;
}

const DEFAULT_TOOLBAR_BUTTONS: string[] = [
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
];

// Toolbar button icons map
const toolbarIconMap: Record<string, LucideIcon> = {
    bold: Bold,
    italic: Italic,
    underline: UnderlineIcon,
    strike: Strikethrough,
    code: Code,
    heading1: Heading1,
    heading2: Heading2,
    heading3: Heading3,
    bulletList: List,
    orderedList: ListOrdered,
    alignLeft: AlignLeft,
    alignCenter: AlignCenter,
    alignRight: AlignRight,
    alignJustify: AlignJustify,
    link: LinkIcon,
    image: ImageIcon,
    highlight: Highlighter,
    horizontalRule: Minus,
    clearFormatting: RemoveFormatting,
    textColor: Palette,
    table: TableIcon,
    tableAddColumnBefore: Columns,
    tableAddColumnAfter: Columns,
    tableDeleteColumn: Trash2,
    tableAddRowBefore: Rows,
    tableAddRowAfter: Rows,
    tableDeleteRow: Trash2,
    tableMergeCells: TableIcon,
    tableSplitCell: TableIcon,
    tableToggleHeaderRow: TableIcon,
    tableDelete: Trash2,
    undo: Undo,
    redo: Redo,
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

    return colorMap[color] || `text-${color}`;
};

export default function RichEditor({
    name,
    value,
    modelValue = '',
    label,
    helperText,
    required,
    toolbarButtons = DEFAULT_TOOLBAR_BUTTONS,
    placeholder = 'Start typing...',
    minHeight = 200,
    maxHeight,
    disabled = false,
    json = false,
    prefixIcon,
    suffixIcon,
    prefixIconColor,
    suffixIconColor,
    onUpdateModelValue,
    onUpdateValue,
}: RichEditorProps) {
    const latest = useLatest({ json, onUpdateModelValue, onUpdateValue });

    // Initialize TipTap editor
    const editor = useEditor({
        // SSR-safe: the editor is created after mount (like @tiptap/vue-3's onMounted creation)
        immediatelyRender: false,
        // Re-render on every transaction so toolbar active states stay in sync (Vue's editor ref is reactive)
        shouldRerenderOnTransaction: true,
        content: modelValue || value || '',
        editable: !disabled,
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
                placeholder,
            }),
            TextStyle,
            Color,
        ],
        editorProps: {
            attributes: {
                class: 'prose prose-sm dark:prose-invert max-w-none focus:outline-none p-4',
                style: `min-height: ${minHeight}px; ${maxHeight ? `max-height: ${maxHeight}px; overflow-y: auto;` : ''}`,
            },
        },
        onUpdate: ({ editor: updatedEditor }) => {
            const content = latest.current.json ? updatedEditor.getJSON() : updatedEditor.getHTML();
            const emittedValue = latest.current.json ? JSON.stringify(content) : (content as string);
            latest.current.onUpdateModelValue?.(emittedValue);
            latest.current.onUpdateValue?.(emittedValue);
        },
    });

    // Watch for external changes to modelValue or value. Like the Vue watcher, this reacts to value
    // changes only; the immediate run happens before the editor exists and is therefore a no-op.
    const externalValue = modelValue ?? value;
    useEffect(() => {
        if (!editor) return;

        if (json) {
            const currentContent = JSON.stringify(editor.getJSON());
            if (externalValue && externalValue !== currentContent) {
                try {
                    editor.commands.setContent(JSON.parse(externalValue), { emitUpdate: false });
                } catch {
                    editor.commands.setContent(externalValue || '', { emitUpdate: false });
                }
            }
        } else {
            if (externalValue !== editor.getHTML()) {
                editor.commands.setContent(externalValue || '', { emitUpdate: false });
            }
        }
    }, [externalValue]);

    // Watch for disabled changes (not on mount: setEditable emits an update)
    const previousDisabled = useRef(disabled);
    useEffect(() => {
        if (previousDisabled.current === disabled) return;
        previousDisabled.current = disabled;

        if (editor) {
            editor.setEditable(!disabled);
        }
    }, [disabled]);

    // Cleanup on unmount: useEditor destroys the editor itself.

    // Add link
    const addLink = () => {
        if (!editor) return;

        const url = window.prompt('Enter URL:');
        if (url) {
            editor.chain().focus().setLink({ href: url }).run();
        }
    };

    // Add image
    const addImage = () => {
        if (!editor) return;

        const url = window.prompt('Enter image URL:');
        if (url) {
            editor.chain().focus().setImage({ src: url }).run();
        }
    };

    // Set text color
    const setTextColor = () => {
        if (!editor) return;

        const color = window.prompt('Enter color (hex, rgb, or color name):');
        if (color) {
            editor.chain().focus().setColor(color).run();
        }
    };

    // Toolbar actions
    const handleToolbarAction = (action: string) => {
        if (!editor) return;

        switch (action) {
            case 'bold':
                editor.chain().focus().toggleBold().run();
                break;
            case 'italic':
                editor.chain().focus().toggleItalic().run();
                break;
            case 'underline':
                editor.chain().focus().toggleUnderline().run();
                break;
            case 'strike':
                editor.chain().focus().toggleStrike().run();
                break;
            case 'code':
                editor.chain().focus().toggleCode().run();
                break;
            case 'heading1':
                editor.chain().focus().toggleHeading({ level: 1 }).run();
                break;
            case 'heading2':
                editor.chain().focus().toggleHeading({ level: 2 }).run();
                break;
            case 'heading3':
                editor.chain().focus().toggleHeading({ level: 3 }).run();
                break;
            case 'bulletList':
                editor.chain().focus().toggleBulletList().run();
                break;
            case 'orderedList':
                editor.chain().focus().toggleOrderedList().run();
                break;
            case 'alignLeft':
                editor.chain().focus().setTextAlign('left').run();
                break;
            case 'alignCenter':
                editor.chain().focus().setTextAlign('center').run();
                break;
            case 'alignRight':
                editor.chain().focus().setTextAlign('right').run();
                break;
            case 'alignJustify':
                editor.chain().focus().setTextAlign('justify').run();
                break;
            case 'link':
                addLink();
                break;
            case 'image':
                addImage();
                break;
            case 'highlight':
                editor.chain().focus().toggleHighlight().run();
                break;
            case 'horizontalRule':
                editor.chain().focus().setHorizontalRule().run();
                break;
            case 'clearFormatting':
                editor.chain().focus().clearNodes().unsetAllMarks().run();
                break;
            case 'textColor':
                setTextColor();
                break;
            case 'table':
                editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
                break;
            case 'tableAddColumnBefore':
                editor.chain().focus().addColumnBefore().run();
                break;
            case 'tableAddColumnAfter':
                editor.chain().focus().addColumnAfter().run();
                break;
            case 'tableDeleteColumn':
                editor.chain().focus().deleteColumn().run();
                break;
            case 'tableAddRowBefore':
                editor.chain().focus().addRowBefore().run();
                break;
            case 'tableAddRowAfter':
                editor.chain().focus().addRowAfter().run();
                break;
            case 'tableDeleteRow':
                editor.chain().focus().deleteRow().run();
                break;
            case 'tableMergeCells':
                editor.chain().focus().mergeCells().run();
                break;
            case 'tableSplitCell':
                editor.chain().focus().splitCell().run();
                break;
            case 'tableToggleHeaderRow':
                editor.chain().focus().toggleHeaderRow().run();
                break;
            case 'tableDelete':
                editor.chain().focus().deleteTable().run();
                break;
            case 'undo':
                editor.chain().focus().undo().run();
                break;
            case 'redo':
                editor.chain().focus().redo().run();
                break;
        }
    };

    // Check if action is active
    const isActive = (action: string): boolean => {
        if (!editor) return false;

        switch (action) {
            case 'bold':
                return editor.isActive('bold');
            case 'italic':
                return editor.isActive('italic');
            case 'underline':
                return editor.isActive('underline');
            case 'strike':
                return editor.isActive('strike');
            case 'code':
                return editor.isActive('code');
            case 'heading1':
                return editor.isActive('heading', { level: 1 });
            case 'heading2':
                return editor.isActive('heading', { level: 2 });
            case 'heading3':
                return editor.isActive('heading', { level: 3 });
            case 'bulletList':
                return editor.isActive('bulletList');
            case 'orderedList':
                return editor.isActive('orderedList');
            case 'alignLeft':
                return editor.isActive({ textAlign: 'left' });
            case 'alignCenter':
                return editor.isActive({ textAlign: 'center' });
            case 'alignRight':
                return editor.isActive({ textAlign: 'right' });
            case 'alignJustify':
                return editor.isActive({ textAlign: 'justify' });
            case 'link':
                return editor.isActive('link');
            case 'highlight':
                return editor.isActive('highlight');
            case 'table':
                return editor.isActive('table');
            default:
                return false;
        }
    };

    const PrefixIcon = resolveIcon(prefixIcon);
    const SuffixIcon = resolveIcon(suffixIcon);

    return (
        <div className="w-full space-y-2">
            <style href="laravilt-forms-rich-editor" precedence="default">
                {RICH_EDITOR_STYLES}
            </style>

            {/* Label */}
            {label && (
                <label htmlFor={name} className="text-sm font-medium block text-foreground">
                    {label}{' '}
                    {required && <span className="text-destructive ms-0.5">*</span>}
                </label>
            )}

            {/* Hidden input for form submission */}
            {name && <input type="hidden" name={name} value={editor?.getHTML() || ''} />}

            {/* Header icons */}
            {(PrefixIcon || SuffixIcon) && (
                <div className="flex items-center justify-between">
                    {PrefixIcon && <PrefixIcon className={cn('h-4 w-4', getIconColorClass(prefixIconColor))} />}
                    {SuffixIcon && <SuffixIcon className={cn('h-4 w-4', getIconColorClass(suffixIconColor))} />}
                </div>
            )}

            {/* Editor container */}
            <div className="border border-input rounded-md overflow-hidden bg-background">
                {/* Toolbar */}
                <div className="flex items-center flex-wrap gap-1 p-2 border-b border-input bg-muted/50">
                    {toolbarButtons.map((button) => {
                        const ToolbarIcon = toolbarIconMap[button];

                        return (
                            <Button
                                key={button}
                                type="button"
                                variant="ghost"
                                size="sm"
                                className={cn('h-8 w-8 p-0', { 'bg-accent': isActive(button) })}
                                disabled={disabled || !editor}
                                onClick={() => handleToolbarAction(button)}
                            >
                                {ToolbarIcon && <ToolbarIcon className="h-4 w-4" />}
                            </Button>
                        );
                    })}
                </div>

                {/* TipTap Editor */}
                <EditorContent editor={editor} />
            </div>

            {/* Helper text */}
            {helperText && <p className="text-xs text-muted-foreground mt-1">{helperText}</p>}
        </div>
    );
}
