import {
    Bold,
    Code,
    Eye,
    EyeOff,
    Heading2,
    Image,
    Italic,
    Link,
    List,
    ListOrdered,
    Quote,
    Redo,
    Strikethrough,
    Table,
    Undo,
    type LucideIcon,
} from 'lucide-react';
import MarkdownIt from 'markdown-it';
import { Fragment, useEffect, useMemo, useRef, useState, type ChangeEvent } from 'react';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { resolveIcon } from '@laravilt/support/lib/icons';

// Scoped <style> block of MarkdownEditor.vue (`.markdown-preview :deep(x)` -> `.markdown-preview x`)
const MARKDOWN_EDITOR_STYLES = `
.markdown-preview {
  color: var(--foreground);
  font-size: 0.875rem;
  line-height: 1.625;
}

.markdown-preview h1 {
  font-size: 1.875rem;
  font-weight: 700;
  margin-top: 0;
  margin-bottom: 1rem;
  line-height: 2.25rem;
  color: var(--foreground);
}

.markdown-preview h2 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
  line-height: 2rem;
  color: var(--foreground);
}

.markdown-preview h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-top: 1.25rem;
  margin-bottom: 0.5rem;
  line-height: 1.75rem;
  color: var(--foreground);
}

.markdown-preview h4 {
  font-size: 1.125rem;
  font-weight: 600;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
  line-height: 1.5rem;
  color: var(--foreground);
}

.markdown-preview h5,
.markdown-preview h6 {
  font-size: 1rem;
  font-weight: 600;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
  line-height: 1.5rem;
  color: var(--foreground);
}

.markdown-preview p {
  margin-top: 0;
  margin-bottom: 1rem;
}

.markdown-preview a {
  color: var(--primary);
  text-decoration: underline;
  text-underline-offset: 2px;
}

.markdown-preview a:hover {
  opacity: 0.8;
}

.markdown-preview strong {
  font-weight: 600;
  color: var(--foreground);
}

.markdown-preview em {
  font-style: italic;
}

.markdown-preview code {
  background-color: var(--muted);
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-size: 0.875em;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
  color: var(--foreground);
}

.markdown-preview pre {
  background-color: var(--muted);
  padding: 1rem;
  border-radius: 0.5rem;
  overflow-x: auto;
  margin-top: 0;
  margin-bottom: 1rem;
}

.markdown-preview pre code {
  background-color: transparent;
  padding: 0;
  border-radius: 0;
  font-size: 0.875rem;
}

.markdown-preview ul,
.markdown-preview ol {
  margin-top: 0;
  margin-bottom: 1rem;
  padding-left: 1.625rem;
}

.markdown-preview ul {
  list-style-type: disc;
}

.markdown-preview ol {
  list-style-type: decimal;
}

.markdown-preview li {
  margin-top: 0.25rem;
  margin-bottom: 0.25rem;
}

.markdown-preview blockquote {
  border-left: 4px solid var(--border);
  padding-left: 1rem;
  margin: 1rem 0;
  font-style: italic;
  color: var(--muted-foreground);
}

.markdown-preview hr {
  border: none;
  border-top: 1px solid var(--border);
  margin: 1.5rem 0;
}

.markdown-preview table {
  width: 100%;
  border-collapse: collapse;
  margin: 1rem 0;
}

.markdown-preview th,
.markdown-preview td {
  border: 1px solid var(--border);
  padding: 0.5rem 0.75rem;
  text-align: left;
}

.markdown-preview th {
  background-color: var(--muted);
  font-weight: 600;
}

.markdown-preview img {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
  margin: 1rem 0;
}
`;

export interface MarkdownEditorProps {
    name?: string;
    value?: string;
    modelValue?: string;
    label?: string;
    placeholder?: string;
    preview?: boolean;
    disabled?: boolean;
    required?: boolean;
    helperText?: string;
    prefixIcon?: string;
    suffixIcon?: string;
    prefixIconColor?: string;
    suffixIconColor?: string;
    toolbarButtons?: string[][];
    fileAttachmentsEnabled?: boolean;
    fileAttachmentsDisk?: string;
    fileAttachmentsDirectory?: string;
    fileAttachmentsAcceptedFileTypes?: string[];
    fileAttachmentsMaxSize?: number;
    onUpdateModelValue?: (value: string) => void;
    onUpdateValue?: (value: string) => void;
    [key: string]: any;
}

const DEFAULT_TOOLBAR_BUTTONS: string[][] = [
    ['bold', 'italic', 'strike', 'link'],
    ['heading'],
    ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
    ['table', 'attachFiles'],
    ['undo', 'redo'],
];

const DEFAULT_ACCEPTED_FILE_TYPES: string[] = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];

// Toolbar button icons mapping
const toolbarIcons: Record<string, LucideIcon> = {
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

export default function MarkdownEditor({
    name,
    value,
    modelValue = '',
    label,
    placeholder = 'Write markdown...',
    preview = true,
    disabled = false,
    required,
    helperText,
    prefixIcon,
    suffixIcon,
    prefixIconColor,
    suffixIconColor,
    toolbarButtons = DEFAULT_TOOLBAR_BUTTONS,
    fileAttachmentsEnabled = false,
    fileAttachmentsAcceptedFileTypes = DEFAULT_ACCEPTED_FILE_TYPES,
    fileAttachmentsMaxSize = 12288, // 12 MB
    onUpdateModelValue,
    onUpdateValue,
}: MarkdownEditorProps) {
    const externalValue = value ?? modelValue;

    // Internal state for markdown content (a ref mirror keeps async callbacks on the latest value)
    const [internalValue, setInternalValueState] = useState<string>(() => externalValue ?? '');
    const internalValueRef = useRef<string>(internalValue);
    const setInternalValue = (next: string) => {
        internalValueRef.current = next;
        setInternalValueState(next);
    };

    // Watch for prop changes and update internal state
    useEffect(() => {
        setInternalValue(externalValue ?? '');
    }, [externalValue]);

    const [showPreview, setShowPreview] = useState<boolean>(preview);
    const textareaRef = useRef<HTMLTextAreaElement>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);
    const uploadHistory = useRef<Array<{ action: string; value: string }>>([]);
    const uploadHistoryIndex = useRef(-1);

    // Initialize markdown-it with sensible defaults
    const md = useMemo(
        () =>
            new MarkdownIt({
                html: false, // Disable HTML tags in source for security
                xhtmlOut: false,
                breaks: true, // Convert \n to <br>
                linkify: true, // Auto-convert URL-like text to links
                typographer: true, // Enable smart quotes and other typographic replacements
            }),
        [],
    );

    // Rendered HTML
    const renderedHtml = useMemo(() => {
        if (!internalValue) return '';
        return md.render(internalValue);
    }, [md, internalValue]);

    const emitValue = (newValue: string) => {
        onUpdateModelValue?.(newValue);
        onUpdateValue?.(newValue);
    };

    // Save action to history
    const saveToHistory = (action: string, historyValue: string) => {
        uploadHistory.current = uploadHistory.current.slice(0, uploadHistoryIndex.current + 1);
        uploadHistory.current.push({ action, value: historyValue });
        uploadHistoryIndex.current = uploadHistory.current.length - 1;
    };

    // Insert markdown syntax at cursor position
    const insertMarkdown = (before: string, after: string = '', insertPlaceholder: string = '') => {
        const textarea = textareaRef.current;
        if (!textarea) return;

        const current = internalValueRef.current;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = current.substring(start, end);
        const replacement = before + (selectedText || insertPlaceholder) + after;

        const newValue = current.substring(0, start) + replacement + current.substring(end);

        // Save to history for undo/redo
        saveToHistory('insert', newValue);

        // Update internal state
        setInternalValue(newValue);

        // Emit events
        emitValue(newValue);

        // Set cursor position after insertion
        setTimeout(() => {
            if (selectedText) {
                textarea.setSelectionRange(start + before.length, start + before.length + selectedText.length);
            } else {
                textarea.setSelectionRange(start + before.length, start + before.length + insertPlaceholder.length);
            }
            textarea.focus();
        }, 0);
    };

    // Undo last action
    const undo = () => {
        if (uploadHistoryIndex.current > 0) {
            uploadHistoryIndex.current--;
            const newValue = uploadHistory.current[uploadHistoryIndex.current].value;
            setInternalValue(newValue);
            emitValue(newValue);
        }
    };

    // Redo last undone action
    const redo = () => {
        if (uploadHistoryIndex.current < uploadHistory.current.length - 1) {
            uploadHistoryIndex.current++;
            const newValue = uploadHistory.current[uploadHistoryIndex.current].value;
            setInternalValue(newValue);
            emitValue(newValue);
        }
    };

    // Handle toolbar button clicks
    const handleToolbarAction = (action: string) => {
        switch (action) {
            case 'bold':
                insertMarkdown('**', '**', 'bold text');
                break;
            case 'italic':
                insertMarkdown('*', '*', 'italic text');
                break;
            case 'strike':
                insertMarkdown('~~', '~~', 'strikethrough text');
                break;
            case 'link':
                insertMarkdown('[', '](url)', 'link text');
                break;
            case 'heading':
                insertMarkdown('## ', '', 'Heading');
                break;
            case 'blockquote':
                insertMarkdown('> ', '', 'Quote');
                break;
            case 'codeBlock':
                insertMarkdown('```\n', '\n```', 'code');
                break;
            case 'bulletList':
                insertMarkdown('- ', '', 'List item');
                break;
            case 'orderedList':
                insertMarkdown('1. ', '', 'List item');
                break;
            case 'table':
                insertMarkdown('| Column 1 | Column 2 |\n|----------|----------|\n| ', ' | Cell 2 |', 'Cell 1');
                break;
            case 'attachFiles':
                fileInputRef.current?.click();
                break;
            case 'undo':
                undo();
                break;
            case 'redo':
                redo();
                break;
        }
    };

    // Handle file upload
    const handleFileUpload = async (event: ChangeEvent<HTMLInputElement>) => {
        const target = event.target;
        const file = target.files?.[0];

        if (!file) return;

        // Validate file type
        if (!fileAttachmentsAcceptedFileTypes.includes(file.type)) {
            alert(`File type not accepted. Allowed types: ${fileAttachmentsAcceptedFileTypes.join(', ')}`);
            return;
        }

        // Validate file size (convert to KB)
        const fileSizeKB = file.size / 1024;
        if (fileSizeKB > fileAttachmentsMaxSize) {
            alert(`File size exceeds maximum allowed size of ${fileAttachmentsMaxSize} KB`);
            return;
        }

        // TODO: Implement actual file upload to Laravel backend
        // For now, we'll create a data URL for preview
        const reader = new FileReader();
        reader.onload = (e) => {
            const imageUrl = e.target?.result as string;
            insertMarkdown('![', `](${imageUrl})`, file.name);
        };
        reader.readAsDataURL(file);

        // Reset file input
        target.value = '';
    };

    const handleInput = (event: ChangeEvent<HTMLTextAreaElement>) => {
        const newValue = event.target.value;
        setInternalValue(newValue);
        emitValue(newValue);
    };

    const togglePreview = () => {
        setShowPreview((previous) => !previous);
    };

    const PrefixIcon = resolveIcon(prefixIcon);
    const SuffixIcon = resolveIcon(suffixIcon);
    const PreviewIcon = showPreview ? EyeOff : Eye;

    return (
        <div className="w-full space-y-2">
            <style href="laravilt-forms-markdown-editor" precedence="default">
                {MARKDOWN_EDITOR_STYLES}
            </style>

            {/* Label */}
            {label && (
                <label htmlFor={name} className="text-sm font-medium block text-foreground">
                    {label}{' '}
                    {required && <span className="text-destructive ms-0.5">*</span>}
                </label>
            )}

            {/* Hidden input for form submission */}
            {name && <input type="hidden" name={name} value={internalValue} />}

            {/* Header with icons and preview toggle */}
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                    {PrefixIcon && <PrefixIcon className={cn('h-4 w-4', getIconColorClass(prefixIconColor))} />}
                </div>
                <div className="flex items-center gap-2">
                    <Button type="button" variant="ghost" size="sm" onClick={togglePreview}>
                        <PreviewIcon className="h-4 w-4 mr-2" />
                        {showPreview ? 'Hide' : 'Show'} Preview
                    </Button>
                    {SuffixIcon && <SuffixIcon className={cn('h-4 w-4', getIconColorClass(suffixIconColor))} />}
                </div>
            </div>

            {/* Toolbar */}
            {toolbarButtons && toolbarButtons.length > 0 && (
                <div className="flex flex-wrap items-center gap-1 p-2 border border-input rounded-md bg-muted/50">
                    {toolbarButtons.map((group, groupIndex) => (
                        <Fragment key={groupIndex}>
                            <div className="flex items-center gap-1">
                                {group.map((button) => {
                                    const ToolbarIcon = toolbarIcons[button];

                                    return (
                                        <Button
                                            key={button}
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            className="h-8 w-8 p-0"
                                            disabled={disabled || (button === 'attachFiles' && !fileAttachmentsEnabled)}
                                            title={button}
                                            onClick={() => handleToolbarAction(button)}
                                        >
                                            {ToolbarIcon && <ToolbarIcon className="h-4 w-4" />}
                                        </Button>
                                    );
                                })}
                            </div>
                            {groupIndex < toolbarButtons.length - 1 && <div className="h-6 w-px bg-border" />}
                        </Fragment>
                    ))}
                </div>
            )}

            {/* Hidden file input for image uploads */}
            {fileAttachmentsEnabled && (
                <input
                    ref={fileInputRef}
                    type="file"
                    accept={fileAttachmentsAcceptedFileTypes.join(',')}
                    className="hidden"
                    onChange={handleFileUpload}
                />
            )}

            {/* Editor container */}
            <div className={cn('grid gap-4', showPreview ? 'md:grid-cols-2' : 'grid-cols-1')}>
                {/* Editor */}
                <div>
                    <textarea
                        ref={textareaRef}
                        value={internalValue}
                        placeholder={placeholder}
                        disabled={disabled}
                        rows={12}
                        className="flex min-h-[240px] w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm font-mono"
                        onChange={handleInput}
                    />
                </div>

                {/* Preview */}
                {showPreview && (
                    <div className="border border-input rounded-md p-4 bg-background min-h-[200px] overflow-auto">
                        {renderedHtml ? (
                            <div className="markdown-preview" dangerouslySetInnerHTML={{ __html: renderedHtml }} />
                        ) : (
                            <p className="text-muted-foreground text-sm">Start typing to see a preview...</p>
                        )}
                    </div>
                )}
            </div>

            {/* Helper text */}
            {helperText && <p className="text-xs text-muted-foreground mt-1">{helperText}</p>}
        </div>
    );
}
