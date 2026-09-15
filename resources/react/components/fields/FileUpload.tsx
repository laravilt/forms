import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';
import 'filepond-plugin-image-edit/dist/filepond-plugin-image-edit.min.css';

import { create as createFilePond, registerPlugin, supported as filePondSupported, type FilePond } from 'filepond';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginImageCrop from 'filepond-plugin-image-crop';
import FilePondPluginImageEdit from 'filepond-plugin-image-edit';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageResize from 'filepond-plugin-image-resize';
import FilePondPluginImageTransform from 'filepond-plugin-image-transform';
import FilePondPluginImageValidateSize from 'filepond-plugin-image-validate-size';

// Import Cropper.js
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

import { useEffect, useRef, useState } from 'react';

import { cn } from '@/lib/utils';
import { useLatest } from '@laravilt/support/composables/hooks';
import { resolveIcon } from '@laravilt/support/lib/icons';

// Same plugin registration vue-filepond performs at module load
registerPlugin(
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize,
    FilePondPluginImagePreview,
    FilePondPluginImageCrop,
    FilePondPluginImageResize,
    FilePondPluginImageTransform,
    FilePondPluginImageValidateSize,
    FilePondPluginImageEdit,
);

/*
 * Styles of FileUpload.vue.
 * Scoped `:deep(x)` compiled to `[data-v-hash] x`; `.filepond--wrapper x` keeps the same specificity and scope
 * (the wrapper is the component-owned parent of FilePond's root). The cropper rules at the end were scoped in
 * Vue too, so they never reached the editor overlay appended to <body>; they are kept equally inert.
 */
const FILE_UPLOAD_STYLES = `
/* Override FilePond to match Reka UI design system */

/* Root container - inherit parent styling */
.filepond--wrapper .filepond--root {
  font-family: inherit;
  font-size: 0.875rem; /* text-sm */
  margin-bottom: 0;
}

/* Main panel - no border/background since wrapper has it */
.filepond--wrapper .filepond--panel-root {
  background-color: transparent;
  border: none;
  border-radius: 0;
}

/* Drop label styling */
.filepond--wrapper .filepond--drop-label {
  color: hsl(var(--muted-foreground));
  min-height: 4.5rem; /* h-18 */
}

.filepond--wrapper .filepond--drop-label label {
  cursor: pointer;
}

/* Browse action link */
.filepond--wrapper .filepond--label-action {
  color: hsl(var(--primary));
  text-decoration: underline;
  text-decoration-color: hsl(var(--primary) / 0.4);
  transition: text-decoration-color 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

.filepond--wrapper .filepond--label-action:hover {
  text-decoration-color: hsl(var(--primary));
}

/* Drag & drop animation */
.filepond--wrapper .filepond--drip-blob {
  background-color: hsl(var(--primary) / 0.1);
}

/* Grid layout styling - FilePond official grid approach */
.filepond--wrapper .filepond--list-scroller[data-style-panel-layout="grid"] .filepond--item {
  width: calc(50% - 0.5em);
}

@media (min-width: 30em) {
  .filepond--wrapper .filepond--list-scroller[data-style-panel-layout="grid"] .filepond--item {
    width: calc(50% - 0.5em);
  }
}

@media (min-width: 50em) {
  .filepond--wrapper .filepond--list-scroller[data-style-panel-layout="grid"] .filepond--item {
    width: calc(33.33% - 0.5em);
  }
}

.filepond--wrapper .filepond--item-panel {
  background-color: hsl(var(--muted) / 0.4);
  border-radius: calc(var(--radius) - 2px);
  transition: background-color 0.15s ease;
}

.filepond--wrapper .filepond--item-panel:hover {
  background-color: hsl(var(--muted) / 0.6);
}

/* Action buttons */
.filepond--wrapper .filepond--file-action-button {
  cursor: pointer;
  transition: transform 0.15s ease;
}

.filepond--wrapper .filepond--file-action-button:hover {
  transform: scale(1.05);
}

.filepond--wrapper .filepond--file-action-button:active {
  transform: scale(0.95);
}

/* Open button styling */
.filepond--wrapper .filepond--action-open-item {
  color: hsl(var(--primary));
}

.filepond--wrapper .filepond--action-open-item:hover {
  color: hsl(var(--primary) / 0.8);
}

/* Progress indicator */
.filepond--wrapper .filepond--file-status {
  color: hsl(var(--foreground));
  font-size: 0.75rem;
}

.filepond--wrapper .filepond--file-status-main {
  color: hsl(var(--muted-foreground));
}

.filepond--wrapper .filepond--file-status-sub {
  color: hsl(var(--muted-foreground) / 0.8);
  font-size: 0.7rem;
}

/* Loading indicator */
.filepond--wrapper .filepond--load-indicator {
  color: hsl(var(--primary));
}

/* Process indicator (upload progress) */
.filepond--wrapper .filepond--process-indicator {
  color: hsl(var(--primary));
}

/* Error state */
.filepond--wrapper [data-filepond-item-state*='error'] .filepond--item-panel,
.filepond--wrapper [data-filepond-item-state*='invalid'] .filepond--item-panel {
  background-color: hsl(var(--destructive) / 0.1);
}

.filepond--wrapper [data-filepond-item-state='processing-complete'] .filepond--item-panel {
  background-color: hsl(var(--primary) / 0.1);
}

/* Image preview */
.filepond--wrapper .filepond--image-preview {
  background-color: hsl(var(--background));
}

.filepond--wrapper .filepond--image-preview-overlay {
  background: linear-gradient(
    to bottom,
    rgba(0, 0, 0, 0) 0%,
    rgba(0, 0, 0, 0.3) 100%
  );
}

/* File info */
.filepond--wrapper .filepond--file-info {
  color: hsl(var(--foreground));
}

.filepond--wrapper .filepond--file-info-main {
  font-size: 0.875rem;
  font-weight: 500;
}

.filepond--wrapper .filepond--file-info-sub {
  font-size: 0.75rem;
  color: hsl(var(--muted-foreground));
  opacity: 0.8;
}

/* Disabled state */
.filepond--wrapper .filepond--root[disabled] .filepond--drop-label {
  opacity: 0.5;
  cursor: not-allowed;
}

.filepond--wrapper .filepond--root[disabled] .filepond--drop-label label {
  cursor: not-allowed;
}

/* Avatar mode - circular clipping mask for FilePond */
.filepond-avatar-mode {
  width: 160px;
  height: 160px;
  border-radius: 50%;
  overflow: hidden !important;
  flex: 0 0 auto;
}

.filepond-avatar-mode .filepond--root {
  width: 160px !important;
  height: 160px !important;
}

.filepond-avatar-mode .filepond--panel-root {
  background-color: hsl(var(--muted) / 0.3);
  height: 160px !important;
}

.filepond-avatar-mode .filepond--drop-label {
  height: 160px !important;
}

/* Force all FilePond elements to 160x160 */
.filepond-avatar-mode .filepond--item,
.filepond-avatar-mode .filepond--item-panel,
.filepond-avatar-mode .filepond--file,
.filepond-avatar-mode .filepond--image-preview,
.filepond-avatar-mode .filepond--image-preview-wrapper,
.filepond-avatar-mode .filepond--image-preview-overlay,
.filepond-avatar-mode .filepond--image-canvas,
.filepond-avatar-mode .filepond--image-bitmap {
  width: 160px !important;
  height: 160px !important;
  margin: 0 !important;
}

/* Hide the constrained img element and show background instead */
.filepond-avatar-mode .filepond--image-preview-markup,
.filepond-avatar-mode .filepond--image-preview-wrapper,
.filepond-avatar-mode .filepond--image-preview-overlay,
.filepond-avatar-mode .filepond--image-canvas-wrapper {
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  width: 160px !important;
  height: 160px !important;
}

/* Make the image preview use cover sizing */
.filepond-avatar-mode .filepond--image-preview {
  background-size: cover !important;
  background-position: center !important;
}

/* Make the image clip use cover sizing */
.filepond-avatar-mode .filepond--image-clip {
  background-size: cover !important;
  background-position: center !important;
}

/* Make the image preview overlay transparent */
.filepond-avatar-mode .filepond--image-preview-overlay {
  background-color: transparent !important;
  background: transparent !important;
}

/* Hide FilePond's constrained image */
.filepond-avatar-mode img {
  opacity: 0 !important;
  pointer-events: none !important;
  width: 100% !important;
  height: 100% !important;
  object-fit: cover !important;
}

/* Use the item panel as background container */
.filepond-avatar-mode .filepond--item-panel {
  background-size: cover !important;
  background-position: center !important;
  background-repeat: no-repeat !important;
}

/* Hide file info/details in avatar mode */
.filepond-avatar-mode .filepond--file-info,
.filepond-avatar-mode .filepond--file-status,
.filepond-avatar-mode .filepond--file-info-main,
.filepond-avatar-mode .filepond--file-info-sub {
  display: none !important;
}

/* Position action buttons inside the circle */
.filepond-avatar-mode .filepond--action-remove-item {
  position: absolute;
  top: 8px;
  right: 8px;
  z-index: 10;
  width: 32px;
  height: 32px;
  background-color: transparent !important;
  border-radius: 50%;
  cursor: pointer;
}

.filepond-avatar-mode .filepond--action-remove-item:hover {
  background-color: rgba(0, 0, 0, 0.3) !important;
}

.filepond-avatar-mode .filepond--action-download-item,
.filepond-avatar-mode .filepond--action-open-item {
  position: absolute;
  bottom: 8px;
  z-index: 10;
  width: 32px;
  height: 32px;
  background-color: rgba(0, 0, 0, 0.7) !important;
  border-radius: 50%;
  cursor: pointer;
}

.filepond-avatar-mode .filepond--action-download-item {
  right: 48px;
}

.filepond-avatar-mode .filepond--action-open-item {
  right: 8px;
}

.filepond-avatar-mode .filepond--action-download-item:hover,
.filepond-avatar-mode .filepond--action-open-item:hover {
  background-color: rgba(0, 0, 0, 0.85) !important;
}

/* Center the file list */
.filepond-avatar-mode .filepond--list-scroller {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Image Editor Styles - Reka UI/ShadCN inspired */
.filepond-image-editor-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.filepond-image-editor-window {
  display: flex;
  flex-direction: column;
  width: 100%;
  max-width: 1200px;
  max-height: 90vh;
  background: hsl(var(--background));
  border: 1px solid hsl(var(--border));
  border-radius: calc(var(--radius) + 2px);
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.filepond-image-editor-image-container {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: hsl(var(--muted) / 0.2);
  min-height: 400px;
  max-height: calc(90vh - 100px);
  overflow: hidden;
}

.filepond-image-editor-image {
  max-width: 100%;
  max-height: 100%;
  display: block;
}

.filepond-image-editor-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 16px;
  background: hsl(var(--background));
  border-top: 1px solid hsl(var(--border));
  flex-wrap: wrap;
}

.filepond-image-editor-aspect-ratios {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.filepond-image-editor-aspect-label {
  font-size: 14px;
  font-weight: 500;
  color: hsl(var(--foreground));
  margin-right: 6px;
}

.filepond-image-editor-actions {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.filepond-image-editor-controls {
  display: flex;
  gap: 8px;
  margin-left: auto;
}

/* Button base styles - matching ShadCN/Reka UI */
.filepond-image-editor-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  white-space: nowrap;
  min-width: 36px;
  height: 36px;
  padding: 0 12px;
  border: 1px solid hsl(var(--input));
  border-radius: var(--radius);
  background: hsl(var(--background));
  color: hsl(var(--foreground));
  font-size: 14px;
  font-weight: 500;
  line-height: 1;
  cursor: pointer;
  transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
  user-select: none;
  outline: none;
}

.filepond-image-editor-btn:hover {
  background: hsl(var(--accent));
  color: hsl(var(--accent-foreground));
}

.filepond-image-editor-btn:active {
  transform: scale(0.98);
}

.filepond-image-editor-btn:focus-visible {
  outline: 2px solid hsl(var(--ring));
  outline-offset: 2px;
}

/* Primary variant - matching ShadCN primary button */
.filepond-image-editor-btn-primary {
  background: hsl(var(--primary));
  color: hsl(var(--primary-foreground));
  border-color: transparent;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.filepond-image-editor-btn-primary:hover {
  background: hsl(var(--primary) / 0.9);
  box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.1);
}

/* Secondary variant - matching ShadCN secondary button */
.filepond-image-editor-btn-secondary {
  background: hsl(var(--secondary));
  color: hsl(var(--secondary-foreground));
  border-color: transparent;
}

.filepond-image-editor-btn-secondary:hover {
  background: hsl(var(--secondary) / 0.8);
}

/* Destructive variant - matching ShadCN destructive button */
.filepond-image-editor-btn-destructive {
  background: hsl(var(--destructive));
  color: hsl(var(--destructive-foreground));
  border-color: transparent;
}

.filepond-image-editor-btn-destructive:hover {
  background: hsl(var(--destructive) / 0.9);
}

/* Active variant - for selected aspect ratio */
.filepond-image-editor-btn-active {
  background: hsl(var(--primary));
  color: hsl(var(--primary-foreground));
  border-color: transparent;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.filepond-image-editor-btn-active:hover {
  background: hsl(var(--primary) / 0.9);
}

/* Action buttons (icon buttons) */
.filepond-image-editor-actions .filepond-image-editor-btn {
  font-size: 18px;
  min-width: 36px;
  padding: 0 8px;
}

/* Circular cropping styles */
.filepond-image-editor-image-circle {
  border-radius: 0; /* Keep image square, but show circular crop */
}

/* Circular overlay for circle cropper */
.filepond-circle-crop-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  z-index: 2;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .filepond-image-editor-toolbar {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
    padding: 12px;
  }

  .filepond-image-editor-actions {
    justify-content: center;
    gap: 4px;
  }

  .filepond-image-editor-controls {
    justify-content: stretch;
    margin-left: 0;
  }

  .filepond-image-editor-controls .filepond-image-editor-btn {
    flex: 1;
  }

  .filepond-image-editor-image-container {
    padding: 16px;
  }
}

/* Style the cropper elements for circular cropping */
.filepond--wrapper .cropper-container:has(.filepond-image-editor-image-circle) .cropper-view-box,
.filepond--wrapper .cropper-container:has(.filepond-image-editor-image-circle) .cropper-face {
  border-radius: 50%;
  outline: 0;
}

/* Add circular mask overlay effect */
.filepond--wrapper .cropper-container:has(.filepond-image-editor-image-circle) .cropper-view-box {
  box-shadow: 0 0 0 1px hsl(var(--primary)), 0 0 0 9999em rgba(0, 0, 0, 0.5);
}
`;

export interface FileUploadProps {
    modelValue?: string | string[] | null;
    value?: string | string[] | null;
    label?: string;
    helperText?: string;
    required?: boolean;
    disk?: string;
    directory?: string;
    visibility?: 'public' | 'private';
    acceptedFileTypes?: string[];
    maxSize?: number | null;
    minSize?: number | null;
    maxFiles?: number | null;
    minFiles?: number | null;
    multiple?: boolean;
    image?: boolean;
    imagePreview?: boolean;
    imageResize?: {
        width?: number | null;
        height?: number | null;
        mode?: string;
    };
    imageCrop?: boolean;
    imageCropAspectRatio?: number | string | null;
    reorderable?: boolean;
    disabled?: boolean;
    prefix?: string;
    suffix?: string;
    prefixIcon?: string;
    suffixIcon?: string;
    prefixIconColor?: string;
    suffixIconColor?: string;
    // Additional Filament-compatible properties
    preserveFilenames?: boolean;
    storeFileNamesIn?: string | null;
    avatar?: boolean;
    imageEditor?: boolean;
    imageEditorAspectRatios?: string[] | null;
    imageEditorMode?: number | null;
    imageEditorEmptyFillColor?: string | null;
    imageEditorViewportWidth?: string | null;
    imageEditorViewportHeight?: string | null;
    circleCropper?: boolean;
    imagePreviewHeight?: string | null;
    loadingIndicatorPosition?: string | null;
    panelAspectRatio?: string | null;
    panelLayout?: string | null;
    removeUploadedFileButtonPosition?: string | null;
    uploadButtonPosition?: string | null;
    uploadProgressIndicatorPosition?: string | null;
    appendFiles?: boolean;
    prependFiles?: boolean;
    moveFiles?: boolean;
    storeFiles?: boolean;
    orientImagesFromExif?: boolean;
    pasteable?: boolean;
    fetchFileInformation?: boolean;
    uploadingMessage?: string | null;
    mimeTypeMap?: Record<string, string> | null;
    maxParallelUploads?: number | null;
    pannable?: boolean;
    downloadable?: boolean;
    openable?: boolean;
    previewable?: boolean;
    deletable?: boolean;
    alignCenter?: boolean;
    translations?: {
        dragDrop?: string;
        browse?: string;
        aspectRatio?: string;
        rotateLeft?: string;
        rotateRight?: string;
        flipHorizontal?: string;
        flipVertical?: string;
        zoomIn?: string;
        zoomOut?: string;
        cancel?: string;
        reset?: string;
        save?: string;
        openFile?: string;
        downloadFile?: string;
    };
    onUpdateModelValue?: (value: string | string[] | null) => void;
    [key: string]: any;
}

const EMPTY_FILE_TYPES: string[] = [];

// Vue `withDefaults` values (applied only when a prop is undefined)
const DEFAULTS = {
    disk: 'public',
    directory: 'uploads',
    visibility: 'public' as 'public' | 'private',
    maxFiles: 1 as number | null,
    multiple: false,
    image: false,
    imagePreview: true,
    imageCrop: false,
    reorderable: false,
    required: false,
    disabled: false,
    acceptedFileTypes: EMPTY_FILE_TYPES,
    preserveFilenames: false,
    avatar: false,
    imageEditor: false,
    circleCropper: false,
    appendFiles: false,
    prependFiles: false,
    moveFiles: true,
    storeFiles: true,
    orientImagesFromExif: true,
    pasteable: true,
    fetchFileInformation: true,
    pannable: true,
    downloadable: false,
    openable: false,
    previewable: true,
    deletable: true,
};

type ResolvedFileUploadProps = FileUploadProps & typeof DEFAULTS;

function applyDefaults<P extends object, D extends object>(props: P, defaults: D): P & D {
    const resolved: Record<string, any> = { ...defaults };

    Object.entries(props).forEach(([key, propValue]) => {
        if (propValue !== undefined) {
            resolved[key] = propValue;
        }
    });

    return resolved as P & D;
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const log = (message: string, payload?: unknown) => {
    // console.log(`[FileUpload] ${message}`, payload)
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

const toPathArray = (value: string | string[] | null | undefined): string[] => {
    if (!value) {
        return [];
    }

    return (Array.isArray(value) ? value : [value]).filter((path): path is string => typeof path === 'string' && path.length > 0);
};

const normalizeValue = (value: string | string[] | null | undefined): string => {
    return JSON.stringify(toPathArray(value));
};

const getFileItemSource = (fileItem: any): string | null => {
    if (!fileItem) {
        return null;
    }

    if (typeof fileItem.source === 'string') {
        return fileItem.source;
    }

    if (fileItem.source && typeof fileItem.source.source === 'string') {
        return fileItem.source.source;
    }

    return null;
};

const getServerId = (value: unknown): string | null => (typeof value === 'string' && value.length > 0 ? value : null);

// Upload URL is always at /uploads (global route from forms package)
// Panel-specific upload routes are not used since the forms package
// registers routes at the root level for all panels
const UPLOAD_URL = '/uploads';
const TEMPORARY_URL_ENDPOINT = `${UPLOAD_URL}/temporary-url`;
const PENDING_REMOVAL_TTL_MS = 2000;

const getCsrfToken = (): string =>
    typeof document !== 'undefined' ? document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' : '';

const signatureParams = ['signature', 'Signature', 'X-Amz-Signature', 'X-Amz-Signature'.toLowerCase()];
const hasSignatureParam = (params: URLSearchParams) => signatureParams.some((key) => params.has(key) || params.has(key.toLowerCase()));

const ensureHttps = (url: string) => {
    if (!url) {
        return url;
    }

    try {
        const parsed = new URL(url, window.location.origin);

        if (window.location.protocol === 'https:' && parsed.protocol === 'http:' && !hasSignatureParam(parsed.searchParams)) {
            parsed.protocol = 'https:';
        }

        return parsed.toString();
    } catch {
        return url;
    }
};

// Compare FilePond option values the way vue-filepond's per-prop watchers would notice a change
const sameOption = (previous: any, next: any): boolean => {
    if (previous === next) return true;
    if (typeof previous === 'function' || typeof next === 'function') return false;

    if (previous && next && typeof previous === 'object' && typeof next === 'object') {
        try {
            return JSON.stringify(previous) === JSON.stringify(next);
        } catch {
            return false;
        }
    }

    return false;
};

export default function FileUpload(rawProps: FileUploadProps) {
    const props: ResolvedFileUploadProps = applyDefaults(rawProps, DEFAULTS);

    // FilePond callbacks are registered once, so every helper reads props through this ref (Vue's live props).
    const propsRef = useLatest(props);

    const wrapperRef = useRef<HTMLDivElement>(null); // vue-filepond's `$el` (div.filepond--wrapper)
    const inputRef = useRef<HTMLInputElement>(null);
    const pond = useRef<FilePond | null>(null);

    const [files, setFilesState] = useState<any[]>([]);
    const filesRef = useRef<any[]>(files);
    const setFiles = (next: any[]) => {
        filesRef.current = next;
        setFilesState(next);
    };

    const [previewUrlCache] = useState(() => new Map<string, string>());
    const lastSyncedValue = useRef<string>('[]');
    const suppressNextSync = useRef(false);
    const suppressNextRemoval = useRef(false);
    const suppressUpdatesDuringEdit = useRef(false);
    const [pendingSyncRemovals] = useState<Record<string, number>>(() => Object.create(null));
    const [pendingEditReplacements] = useState(() => new Map<string, string>()); // Maps old serverId to new serverId after edit

    const emitUpdate = (next: string | string[] | null) => {
        propsRef.current.onUpdateModelValue?.(next);
    };

    // Only use temporary preview for private files or non-public disks
    const preferTemporaryPreview = () => propsRef.current.visibility === 'private' || propsRef.current.disk === 'local';

    const toFilePondSource = (path: string) => {
        const previewUrl = previewUrlCache.get(path) ?? null;
        log('toFilePondSource', { path, previewUrl, cacheSize: previewUrlCache.size });
        return {
            source: path,
            options: {
                type: 'local', // Use 'local' instead of 'limbo' to skip validation for existing files
                metadata: {
                    previewUrl,
                },
            },
        };
    };

    const cleanupPendingRemovals = () => {
        const now = Date.now();

        Object.keys(pendingSyncRemovals).forEach((path) => {
            if (pendingSyncRemovals[path] <= now) {
                delete pendingSyncRemovals[path];
            }
        });
    };

    const registerPendingRemovals = (paths: string[]) => {
        const candidates = Array.from(new Set(paths.filter((path) => path && path.length > 0)));

        if (candidates.length === 0) {
            return;
        }

        cleanupPendingRemovals();
        const expiresAt = Date.now() + PENDING_REMOVAL_TTL_MS;

        candidates.forEach((path) => {
            pendingSyncRemovals[path] = expiresAt;
        });
    };

    const getCurrentSources = (): string[] =>
        filesRef.current.map(getFileItemSource).filter((path): path is string => typeof path === 'string' && path.length > 0);

    const fetchTemporaryUrl = async (path: string) => {
        const response = await fetch(TEMPORARY_URL_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'include',
            body: JSON.stringify({
                path,
                disk: propsRef.current.disk,
            }),
        });

        if (!response.ok) {
            throw new Error('Unable to generate temporary URL');
        }

        const data = await response.json();
        if (typeof data.url !== 'string') {
            throw new Error('Temporary URL missing in response');
        }

        const secureUrl = ensureHttps(data.url);
        previewUrlCache.set(path, secureUrl);
        log('resolveFileUrl: generated temporary url', { path, secureUrl });

        return secureUrl;
    };

    const resolveFileUrl = async (path: string | null): Promise<string | null> => {
        if (!path) {
            log('resolveFileUrl: missing path');
            return null;
        }

        // Check cache first
        if (previewUrlCache.has(path)) {
            const cached = previewUrlCache.get(path) as string;
            log('resolveFileUrl: using cached url', { path, cached });
            return cached;
        }

        // If already an absolute URL, return as-is
        if (/^https?:\/\//.test(path)) {
            log('resolveFileUrl: value already absolute', { path });
            previewUrlCache.set(path, path);
            return path;
        }

        // If path already starts with /storage/, return as-is
        if (path.startsWith('/storage/')) {
            log('resolveFileUrl: path already has /storage/ prefix', { path });
            previewUrlCache.set(path, path);
            return path;
        }

        // For private files or local disk, fetch temporary signed URL
        if (preferTemporaryPreview()) {
            try {
                const temporaryUrl = await fetchTemporaryUrl(path);
                log('resolveFileUrl: fetched temporary url', { path, temporaryUrl });
                previewUrlCache.set(path, temporaryUrl);
                return temporaryUrl;
            } catch (error) {
                log('resolveFileUrl: temporary url fetch failed', error);
                throw error;
            }
        }

        // For public files, construct direct storage URL
        // Remove leading slash if present to avoid double slashes
        const cleanPath = path.startsWith('/') ? path.substring(1) : path;
        const publicUrl = `/storage/${cleanPath}`;
        previewUrlCache.set(path, publicUrl);
        log('resolveFileUrl: constructed public url', { path, cleanPath, publicUrl });
        return publicUrl;
    };

    const applyFileSources = async (sources: string[], trackRemovals = true) => {
        const sanitizedSources = Array.from(new Set(sources.filter((path) => path && path.length > 0)));

        if (trackRemovals) {
            const guardPaths = Array.from(new Set([...getCurrentSources(), ...sanitizedSources]));
            registerPendingRemovals(guardPaths);
        }

        // Pre-resolve URLs and cache them before adding to FilePond
        await Promise.all(
            sanitizedSources.map(async (path) => {
                try {
                    await resolveFileUrl(path);
                } catch (error) {
                    log('Failed to pre-resolve URL', { path, error });
                }
            }),
        );

        setFiles(sanitizedSources.map(toFilePondSource));
    };

    const syncFilesFromValue = async (syncValue: string | string[] | null | undefined, force = false) => {
        const normalized = normalizeValue(syncValue);

        if (!force && suppressNextSync.current) {
            log('sync skipped (internal update)');
            suppressNextSync.current = false;
            lastSyncedValue.current = normalized;
            return;
        }

        if (!force && normalized === lastSyncedValue.current) {
            log('sync skipped (value unchanged)', normalized);
            return;
        }

        lastSyncedValue.current = normalized;
        log('syncing files', normalized);
        const paths = toPathArray(syncValue);

        try {
            await applyFileSources(paths, true);
            log('sync completed successfully', { paths });
        } catch {
            // Sync error occurred
        }
    };

    const removeLocalFile = async (path: string) => {
        const nextSources = getCurrentSources().filter((sourcePath) => sourcePath !== path);
        await applyFileSources(nextSources, false);
    };

    const deleteFile = async (path: string | null, load: () => void, error: (message?: string) => void) => {
        if (!path) {
            error('File reference missing');
            return;
        }

        try {
            log('deleteFile: start', { path });
            const response = await fetch(UPLOAD_URL, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include',
                body: JSON.stringify({
                    path,
                    disk: propsRef.current.disk,
                }),
            });

            if (!response.ok) {
                throw new Error('Failed to delete file');
            }

            previewUrlCache.delete(path);
            load();
            log('deleteFile: success', { path });
        } catch (err: any) {
            // File delete error
            error(err?.message ?? 'Unable to delete file');
        }
    };

    // Add "Open" button functionality when openable is enabled
    const injectOpenButtons = () => {
        if (!propsRef.current.openable || !pond.current) return;

        const fileItems = wrapperRef.current?.querySelectorAll<HTMLElement>('.filepond--item');
        if (!fileItems) return;

        fileItems.forEach((item) => {
            // Check if button already exists
            if (item.querySelector('.filepond-open-button')) return;

            // Find the file action buttons container
            const actionsContainer = item.querySelector('.filepond--action-remove-item')?.parentElement;
            if (!actionsContainer) return;

            // Get the file serverId to construct URL
            const fileId = item.getAttribute('data-id');
            if (!fileId) return;

            const pondFiles = pond.current?.getFiles() ?? [];
            const fileItem: any = pondFiles.find((f: any) => f.id === fileId);
            if (!fileItem) return;

            const serverId = getServerId(fileItem.serverId);
            if (!serverId) return;

            // Create open button
            const openButton = document.createElement('button');
            openButton.type = 'button';
            openButton.className = 'filepond--file-action-button filepond--action-open-item filepond-open-button';
            openButton.title = propsRef.current.translations?.openFile || 'Open file in new tab';
            openButton.setAttribute('data-align', 'left');
            openButton.innerHTML = `
      <svg width="26" height="26" viewBox="0 0 26 26" xmlns="http://www.w3.org/2000/svg">
        <path d="M14 3h7v7M21 3L10 14M18 13v8H5V8h8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    `;

            openButton.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();

                try {
                    const url = await resolveFileUrl(serverId);
                    if (url) {
                        window.open(url, '_blank', 'noopener,noreferrer');
                    }
                } catch (error) {
                    console.error('[FileUpload] Failed to open file:', error);
                }
            });

            // Insert the button before the remove button
            const removeButton = actionsContainer.querySelector('.filepond--action-remove-item');
            if (removeButton) {
                actionsContainer.insertBefore(openButton, removeButton);
            } else {
                actionsContainer.appendChild(openButton);
            }
        });
    };

    // Inject download buttons for each file item
    const injectDownloadButtons = () => {
        if (!propsRef.current.downloadable || !pond.current) return;

        const fileItems = wrapperRef.current?.querySelectorAll<HTMLElement>('.filepond--item');
        if (!fileItems) return;

        fileItems.forEach((item) => {
            // Check if button already exists
            if (item.querySelector('.filepond-download-button')) return;

            // Find the file action buttons container
            const actionsContainer = item.querySelector('.filepond--action-remove-item')?.parentElement;
            if (!actionsContainer) return;

            // Get the file serverId to construct URL
            const fileId = item.getAttribute('data-id');
            if (!fileId) return;

            const pondFiles = pond.current?.getFiles() ?? [];
            const fileItem: any = pondFiles.find((f: any) => f.id === fileId);
            if (!fileItem) return;

            const serverId = getServerId(fileItem.serverId);
            if (!serverId) return;

            // Create download button
            const downloadButton = document.createElement('button');
            downloadButton.type = 'button';
            downloadButton.className = 'filepond--file-action-button filepond--action-download-item filepond-download-button';
            downloadButton.title = propsRef.current.translations?.downloadFile || 'Download file';
            downloadButton.setAttribute('data-align', 'left');
            downloadButton.innerHTML = `
      <svg width="26" height="26" viewBox="0 0 26 26" xmlns="http://www.w3.org/2000/svg">
        <path d="M13 3v12m0 0l-4-4m4 4l4-4M5 17v4h14v-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    `;

            downloadButton.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();

                try {
                    const url = await resolveFileUrl(serverId);
                    if (url) {
                        // Create a temporary anchor element to trigger download
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = fileItem.filename || 'download';
                        a.style.display = 'none';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                    }
                } catch (error) {
                    console.error('[FileUpload] Failed to download file:', error);
                }
            });

            // Insert the button before the remove button
            const removeButton = actionsContainer.querySelector('.filepond--action-remove-item');
            if (removeButton) {
                actionsContainer.insertBefore(downloadButton, removeButton);
            } else {
                actionsContainer.appendChild(downloadButton);
            }
        });
    };

    // Apply avatar background image for avatar mode
    const applyAvatarBackground = () => {
        if (!propsRef.current.avatar || !pond.current) return;

        setTimeout(() => {
            const fileItem = wrapperRef.current?.querySelector('.filepond--item');
            if (!fileItem) return;

            const itemPanel = fileItem.querySelector<HTMLElement>('.filepond--item-panel');
            const img = fileItem.querySelector('img');

            if (itemPanel && img && img.src) {
                itemPanel.style.backgroundImage = `url(${img.src})`;
            }
        }, 100);
    };

    // Handle file process (upload complete)
    const handleProcessFile = (error: any, file: any) => {
        if (error) {
            // File upload error
            return;
        }

        const p = propsRef.current;
        const serverId = getServerId(file.serverId);

        if (!serverId) {
            log('Upload response missing file path', file.serverId);
            return;
        }

        log('processfile received', {
            serverId,
            multiple: p.multiple,
            preview: previewUrlCache.get(serverId) ?? null,
            lastSyncedValue: lastSyncedValue.current,
            fileOrigin: file.origin,
        });

        // Check if this is a replacement for an edited file
        let isEditReplacement = false;
        let oldServerId: string | null = null;

        for (const [oldId, newId] of pendingEditReplacements.entries()) {
            if (newId === file.id) {
                isEditReplacement = true;
                oldServerId = oldId;
                pendingEditReplacements.delete(oldId);

                // Re-enable updateFiles handler now that upload is complete
                suppressUpdatesDuringEdit.current = false;
                break;
            }
        }

        // Update modelValue without triggering sync since file is already in FilePond
        if (p.multiple) {
            const currentValue = Array.isArray(p.modelValue) ? p.modelValue : [];
            let nextValue: string[];

            if (isEditReplacement && oldServerId) {
                // Replace the old path with the new path, maintaining order
                nextValue = currentValue.map((path) => (path === oldServerId ? serverId : path));
            } else {
                // Add new file
                nextValue = [...currentValue, serverId];
            }

            lastSyncedValue.current = JSON.stringify(nextValue);
            emitUpdate(nextValue);
        } else {
            lastSyncedValue.current = JSON.stringify([serverId]);
            emitUpdate(serverId);
        }

        // Inject open button if needed
        if (p.openable) {
            setTimeout(injectOpenButtons, 100);
        }
    };

    // Handle file prepare (after edit, before upload)
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const handlePrepareFile = (_file: any, _output: any) => {
        // File prepared
    };

    // Handle file add (when edited file is added back)
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const handleAddFile = (error: any, _file: any) => {
        if (error) {
            // File add error
            return;
        }

        // File added - inject open button if needed
        if (propsRef.current.openable) {
            setTimeout(injectOpenButtons, 100);
        }
    };

    // Handle file remove
    const handleRemoveFile = (error: any, file: any) => {
        if (error) {
            console.error('[FileUpload] File remove error:', error);
            return;
        }

        // If we're suppressing removals (during edit replacement), skip this
        if (suppressNextRemoval.current) {
            suppressNextRemoval.current = false;
            return;
        }

        const serverId = getServerId(file.serverId);

        if (!serverId) {
            log('removefile skipped: missing serverId', file.serverId);
            return;
        }

        cleanupPendingRemovals();
        const pendingExpiry = pendingSyncRemovals[serverId];

        if (pendingExpiry && pendingExpiry > Date.now()) {
            delete pendingSyncRemovals[serverId];
            log('removefile ignored (sync reconciliation)', { serverId });
            return;
        }

        log('removefile received', { serverId });

        const p = propsRef.current;

        if (p.multiple && Array.isArray(p.modelValue)) {
            const filtered = p.modelValue.filter((path: string) => path !== serverId);
            const nextValue = filtered.length > 0 ? filtered : null;
            suppressNextSync.current = true;
            emitUpdate(nextValue);
            void removeLocalFile(serverId);
        } else {
            suppressNextSync.current = true;
            emitUpdate(null);
            void removeLocalFile(serverId);
        }
    };

    // Handle file reorder - triggered by updatefiles event
    const handleUpdateFiles = (pondFiles: any[]) => {
        // Skip updates during edit replacement
        if (suppressUpdatesDuringEdit.current) {
            return;
        }

        const p = propsRef.current;

        if (!p.multiple || !p.reorderable) {
            return;
        }

        const orderedPaths = pondFiles
            .map((file) => {
                // Try serverId first (for newly uploaded files)
                const serverId = getServerId(file.serverId);
                if (serverId) {
                    return serverId;
                }

                // For existing/loaded files, use the source
                return getFileItemSource(file);
            })
            .filter((path): path is string => path !== null);

        const currentPaths = toPathArray(p.modelValue);
        const hasChanged = JSON.stringify(orderedPaths) !== JSON.stringify(currentPaths);

        if (orderedPaths.length > 0 && hasChanged) {
            suppressNextSync.current = true;
            emitUpdate(orderedPaths);
        }
    };

    // Image editor (Cropper.js) opened by filepond-plugin-image-edit
    const openImageEditor = (file: File, _instructions: any, onconfirm: any, oncancel: any) => {
        const p = propsRef.current;

        // Create editor overlay
        const overlay = document.createElement('div');
        overlay.className = 'filepond-image-editor-overlay';

        // Create editor window
        const editorWindow = document.createElement('div');
        editorWindow.className = 'filepond-image-editor-window';

        // Apply viewport dimensions if provided
        if (p.imageEditorViewportWidth || p.imageEditorViewportHeight) {
            if (p.imageEditorViewportWidth) {
                editorWindow.style.width = p.imageEditorViewportWidth;
            }
            if (p.imageEditorViewportHeight) {
                editorWindow.style.height = p.imageEditorViewportHeight;
            }
        }

        // Create image container
        const imgContainer = document.createElement('div');
        imgContainer.className = 'filepond-image-editor-image-container';

        // Apply empty fill color if provided
        if (p.imageEditorEmptyFillColor) {
            imgContainer.style.backgroundColor = p.imageEditorEmptyFillColor;
        }

        // Create image element
        const img = document.createElement('img');
        img.className = 'filepond-image-editor-image';

        // Add circular class if circleCropper or avatar mode is enabled
        if (p.circleCropper || p.avatar) {
            img.classList.add('filepond-image-editor-image-circle');
        }

        imgContainer.appendChild(img);

        // Helper function to create editor buttons
        const createEditorButton = (
            text: string,
            title: string,
            variant: 'primary' | 'secondary' | 'default' | 'destructive' | 'active' = 'default',
        ) => {
            const btn = document.createElement('button');
            btn.textContent = text;
            btn.title = title;
            btn.type = 'button';
            btn.className = `filepond-image-editor-btn filepond-image-editor-btn-${variant}`;
            return btn;
        };

        // Create toolbar
        const toolbar = document.createElement('div');
        toolbar.className = 'filepond-image-editor-toolbar';

        // Create aspect ratio selector if aspect ratios are provided
        let aspectRatioGroup: HTMLDivElement | null = null;
        if (p.imageEditorAspectRatios && p.imageEditorAspectRatios.length > 0 && !p.circleCropper && !p.avatar) {
            aspectRatioGroup = document.createElement('div');
            aspectRatioGroup.className = 'filepond-image-editor-aspect-ratios';

            const aspectLabel = document.createElement('span');
            aspectLabel.className = 'filepond-image-editor-aspect-label';
            aspectLabel.textContent = p.translations?.aspectRatio || 'Aspect Ratio:';
            aspectRatioGroup.appendChild(aspectLabel);
        }

        // Create action buttons
        const actionsGroup = document.createElement('div');
        actionsGroup.className = 'filepond-image-editor-actions';

        // Rotate buttons
        const rotateLeftBtn = createEditorButton('↶', p.translations?.rotateLeft || 'Rotate Left');
        const rotateRightBtn = createEditorButton('↷', p.translations?.rotateRight || 'Rotate Right');
        const flipHBtn = createEditorButton('⇄', p.translations?.flipHorizontal || 'Flip Horizontal');
        const flipVBtn = createEditorButton('⇅', p.translations?.flipVertical || 'Flip Vertical');
        const zoomInBtn = createEditorButton('+', p.translations?.zoomIn || 'Zoom In');
        const zoomOutBtn = createEditorButton('−', p.translations?.zoomOut || 'Zoom Out');

        actionsGroup.appendChild(rotateLeftBtn);
        actionsGroup.appendChild(rotateRightBtn);
        actionsGroup.appendChild(flipHBtn);
        actionsGroup.appendChild(flipVBtn);
        actionsGroup.appendChild(zoomInBtn);
        actionsGroup.appendChild(zoomOutBtn);

        // Create control buttons
        const controlsGroup = document.createElement('div');
        controlsGroup.className = 'filepond-image-editor-controls';

        const cancelBtnText = p.translations?.cancel || 'Cancel';
        const resetBtnText = p.translations?.reset || 'Reset';
        const saveBtnText = p.translations?.save || 'Save';
        const cancelBtn = createEditorButton(cancelBtnText, cancelBtnText, 'secondary');
        const resetBtn = createEditorButton(resetBtnText, resetBtnText, 'destructive');
        const confirmBtn = createEditorButton(saveBtnText, saveBtnText, 'primary');

        controlsGroup.appendChild(cancelBtn);
        controlsGroup.appendChild(resetBtn);
        controlsGroup.appendChild(confirmBtn);

        if (aspectRatioGroup) {
            toolbar.appendChild(aspectRatioGroup);
        }
        toolbar.appendChild(actionsGroup);
        toolbar.appendChild(controlsGroup);

        editorWindow.appendChild(imgContainer);
        editorWindow.appendChild(toolbar);
        overlay.appendChild(editorWindow);
        document.body.appendChild(overlay);

        // Load image
        const reader = new FileReader();
        reader.onload = (e) => {
            const current = propsRef.current;
            img.src = e.target?.result as string;

            // Parse aspect ratio from string format (e.g., '16:9', '4:3', '1:1')
            const parseAspectRatio = (ratio: string | null): number => {
                if (!ratio) return NaN;
                const parts = ratio.split(':');
                if (parts.length === 2) {
                    const width = parseFloat(parts[0]);
                    const height = parseFloat(parts[1]);
                    return width / height;
                }
                return NaN;
            };

            // Determine initial aspect ratio
            let initialAspectRatio = NaN;

            // Force 1:1 aspect ratio for circle cropper or avatar mode
            if (current.circleCropper || current.avatar) {
                initialAspectRatio = 1;
            } else if (current.imageEditorAspectRatios && current.imageEditorAspectRatios.length > 0) {
                // Use first aspect ratio as default
                initialAspectRatio = parseAspectRatio(current.imageEditorAspectRatios[0]);
            } else if (current.imageCropAspectRatio) {
                initialAspectRatio =
                    typeof current.imageCropAspectRatio === 'number'
                        ? current.imageCropAspectRatio
                        : parseAspectRatio(current.imageCropAspectRatio);
            }

            // Initialize Cropper.js with enhanced options
            const cropperOptions: Cropper.Options = {
                viewMode: (current.imageEditorMode ?? 1) as Cropper.ViewMode, // Use imageEditorMode or default to 1
                dragMode: 'move',
                aspectRatio: initialAspectRatio,
                autoCropArea: 1,
                restore: false,
                guides: true,
                center: true,
                highlight: true,
                cropBoxMovable: true,
                cropBoxResizable: !current.circleCropper, // Disable resize for circle cropper
                toggleDragModeOnDblclick: false,
                background: current.imageEditorEmptyFillColor ? true : false,
                responsive: true,
                checkOrientation: true,
                ready() {
                    // Add circular overlay for circle cropper
                    if (propsRef.current.circleCropper || propsRef.current.avatar) {
                        const cropperContainer = img.parentElement?.querySelector('.cropper-container');
                        if (cropperContainer) {
                            const circleOverlay = document.createElement('div');
                            circleOverlay.className = 'filepond-circle-crop-overlay';
                            cropperContainer.appendChild(circleOverlay);
                        }
                    }
                },
            };

            const cropper = new Cropper(img, cropperOptions);

            // Create aspect ratio buttons if aspect ratios are provided (not for circle cropper)
            if (aspectRatioGroup && current.imageEditorAspectRatios && !current.circleCropper) {
                let activeAspectBtn: HTMLButtonElement | null = null;

                current.imageEditorAspectRatios.forEach((ratio, index) => {
                    const ratioValue = parseAspectRatio(ratio);
                    const ratioBtn = createEditorButton(ratio || 'Free', `Set aspect ratio to ${ratio || 'free'}`, index === 0 ? 'active' : 'default');

                    if (index === 0) {
                        activeAspectBtn = ratioBtn;
                    }

                    ratioBtn.onclick = () => {
                        cropper.setAspectRatio(ratioValue);

                        // Update active state
                        if (activeAspectBtn) {
                            activeAspectBtn.classList.remove('filepond-image-editor-btn-active');
                        }
                        ratioBtn.classList.add('filepond-image-editor-btn-active');
                        activeAspectBtn = ratioBtn;
                    };

                    aspectRatioGroup?.appendChild(ratioBtn);
                });
            }

            // Store original file item to replace after edit
            let originalFileItem: any = null;
            if (pond.current && pond.current.getFiles) {
                const allFiles = pond.current.getFiles();
                originalFileItem = allFiles.find((item: any) => {
                    return item.file === file || item.filename === file.name;
                });
            }

            // Cleanup function
            const cleanup = () => {
                if (cropper) {
                    cropper.destroy();
                }
                if (overlay.parentNode) {
                    document.body.removeChild(overlay);
                }
                document.removeEventListener('keydown', handleEscape);
            };

            // Close on Escape key
            const handleEscape = (event: KeyboardEvent) => {
                if (event.key === 'Escape') {
                    if (typeof oncancel === 'function') {
                        oncancel();
                    }
                    cleanup();
                }
            };

            // Action button handlers
            rotateLeftBtn.onclick = () => cropper.rotate(-90);
            rotateRightBtn.onclick = () => cropper.rotate(90);
            flipHBtn.onclick = () => {
                const data = cropper.getData();
                cropper.scaleX((data.scaleX || 1) * -1);
            };
            flipVBtn.onclick = () => {
                const data = cropper.getData();
                cropper.scaleY((data.scaleY || 1) * -1);
            };
            zoomInBtn.onclick = () => cropper.zoom(0.1);
            zoomOutBtn.onclick = () => cropper.zoom(-0.1);
            resetBtn.onclick = () => cropper.reset();

            // Handle confirm
            confirmBtn.onclick = () => {
                const latestProps = propsRef.current;
                const canvas = cropper.getCroppedCanvas({
                    maxWidth: latestProps.imageResize?.width || 4096,
                    maxHeight: latestProps.imageResize?.height || 4096,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                    fillColor: latestProps.imageEditorEmptyFillColor || '#fff',
                });

                canvas.toBlob(
                    (blob) => {
                        if (!blob) {
                            console.error('[FileUpload] Failed to create blob from cropped image');
                            if (typeof oncancel === 'function') {
                                oncancel();
                            }
                            cleanup();
                            return;
                        }

                        const editedFile = new File([blob], file.name, {
                            type: file.type,
                            lastModified: Date.now(),
                        });

                        // Cropper edit completed

                        // For already-loaded files (origin: 3), manually replace in FilePond
                        if (originalFileItem && originalFileItem.origin === 3 && pond.current) {
                            const allFiles = pond.current.getFiles();
                            const originalIndex = allFiles.indexOf(originalFileItem);
                            const originalServerId = getServerId(originalFileItem.serverId);

                            // Mark that we're doing an edit replacement to prevent removal from modelValue
                            suppressNextRemoval.current = true;
                            suppressUpdatesDuringEdit.current = true;

                            // Remove the old file
                            pond.current.removeFile(originalFileItem.id, { revert: false });

                            // Wait a tick for removal to complete
                            setTimeout(() => {
                                if (!pond.current) {
                                    cleanup();
                                    return;
                                }

                                // Add the edited file back
                                const addResult: any = pond.current.addFile(editedFile, {
                                    index: originalIndex >= 0 ? originalIndex : undefined,
                                });

                                // Track the replacement
                                if (originalServerId) {
                                    if (addResult && typeof addResult.then === 'function') {
                                        addResult
                                            .then((fileItem: any) => {
                                                if (fileItem && fileItem.id) {
                                                    pendingEditReplacements.set(originalServerId, fileItem.id);
                                                }
                                            })
                                            .catch((err: any) => {
                                                console.error('[FileUpload] Error adding edited file:', err);
                                            });
                                    } else if (addResult && addResult.id) {
                                        pendingEditReplacements.set(originalServerId, addResult.id);
                                    }
                                }
                            }, 50);
                        } else {
                            // For new uploads, use the standard onconfirm callback
                            onconfirm(editedFile);
                        }

                        cleanup();
                    },
                    file.type,
                    0.92,
                );
            };

            // Handle cancel
            cancelBtn.onclick = () => {
                if (typeof oncancel === 'function') {
                    oncancel();
                }
                cleanup();
            };

            // Close on overlay click
            overlay.onclick = (event) => {
                if (event.target === overlay) {
                    if (typeof oncancel === 'function') {
                        oncancel();
                    }
                    cleanup();
                }
            };

            document.addEventListener('keydown', handleEscape);
        };

        reader.readAsDataURL(file);

        return {
            onclose: () => {
                if (overlay.parentNode) {
                    document.body.removeChild(overlay);
                }
            },
        };
    };

    // Latest helper functions for the long-lived FilePond callbacks below
    const fns = useLatest({
        resolveFileUrl,
        fetchTemporaryUrl,
        deleteFile,
        openImageEditor,
        handleAddFile,
        handleProcessFile,
        handleRemoveFile,
        handleUpdateFiles,
        handlePrepareFile,
    });

    // FilePond server configuration (stable; values are read from the latest props at call time)
    const [serverConfig] = useState(() => ({
        process: {
            url: UPLOAD_URL,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            ondata: (formData: FormData) => {
                const p = propsRef.current;
                formData.append('disk', p.disk);
                formData.append('directory', p.directory ?? '');
                formData.append('visibility', p.visibility);

                return formData;
            },
            onload: (response: string) => {
                // Response is plain text containing the file path (serverId)
                const path = response.trim();
                log('upload complete, received path', { path });

                // Prefetch preview URL in background (don't block)
                if (path) {
                    fns.current.resolveFileUrl(path).catch((error: unknown) => {
                        log('prefetch preview url failed', { path, error });
                    });
                }

                return path || null;
            },
        },
        revert: (uniqueFileId: string, load: () => void, error: (message?: string) => void) => {
            log('server.revert: start', { uniqueFileId });
            void fns.current.deleteFile(uniqueFileId, load, error);
        },
        load: (
            source: any,
            load: (file: Blob) => void,
            error: (message?: string) => void,
            progress: (computable: boolean, current: number, total: number) => void,
            abort: () => void,
        ) => {
            const controller = new AbortController();
            const fetchWithSignal = async (url: string) => {
                const response = await fetch(url, {
                    signal: controller.signal,
                    credentials: 'include',
                });

                return response;
            };

            const loadFile = async () => {
                try {
                    const path = typeof source === 'string' ? source : null;
                    const url = await fns.current.resolveFileUrl(path);

                    if (!url) {
                        throw new Error('File path missing');
                    }

                    progress(true, 0, 1);
                    let resolvedUrl = url;
                    let response = await fetchWithSignal(resolvedUrl);

                    if (!response.ok) {
                        if (!path) {
                            throw new Error('Failed to load file');
                        }

                        const fallbackUrl = await fns.current.fetchTemporaryUrl(path);
                        response = await fetchWithSignal(fallbackUrl);
                        resolvedUrl = fallbackUrl;

                        if (!response.ok) {
                            throw new Error(`Failed to load file (fallback status ${response.status})`);
                        }
                    }

                    const blob = await response.blob();
                    progress(true, 1, 1);
                    load(blob);
                } catch (err: any) {
                    if (err.name === 'AbortError') {
                        return;
                    }

                    error(err?.message ?? 'Unable to load file');
                }
            };

            void loadFile();

            return {
                abort: () => {
                    controller.abort();
                    abort();
                },
            };
        },
        fetch: null,
        restore: null,
    }));

    // Custom MIME type detection using the provided map (stable function)
    const [detectType] = useState(
        () => (source: any, type: string) =>
            new Promise<string>((resolve) => {
                const mimeTypeMap = propsRef.current.mimeTypeMap;
                // Try to get extension from filename
                const extension = source.name ? source.name.split('.').pop()?.toLowerCase() : null;
                if (extension && mimeTypeMap && mimeTypeMap[extension]) {
                    resolve(mimeTypeMap[extension]);
                } else {
                    resolve(type);
                }
            }),
    );

    // Image editor bridge for filepond-plugin-image-edit (stable object)
    const [imageEditEditor] = useState(() => ({
        // Open editor - create Cropper.js instance
        open: (file: File, instructions: any, onconfirm: any, oncancel: any) =>
            fns.current.openImageEditor(file, instructions, onconfirm, oncancel),
    }));

    // Build FilePond options
    const buildFilePondOptions = (p: ResolvedFileUploadProps): Record<string, any> => {
        const options: Record<string, any> = {
            server: serverConfig,
            name: 'file',
            allowMultiple: p.avatar ? false : p.multiple,
            maxFiles: p.avatar ? 1 : (p.maxFiles ?? null),
            maxFileSize: p.maxSize ? `${p.maxSize}KB` : null,
            acceptedFileTypes: p.acceptedFileTypes.length > 0 ? p.acceptedFileTypes : null,
            fileValidateTypeDetectType: p.mimeTypeMap ? detectType : null,
            imagePreviewMaxHeight: p.avatar ? 256 : p.imagePreviewHeight ? parseInt(p.imagePreviewHeight) : 256,
            imagePreviewMinHeight: p.avatar ? 160 : null,
            styleItemPanelAspectRatio: p.avatar ? '1:1' : null,
            stylePanelLayout: p.avatar ? 'compact' : null,
            styleLoadIndicatorPosition: p.avatar ? 'center' : null,
            styleProgressIndicatorPosition: p.avatar ? 'center' : null,
            styleButtonRemoveItemPosition: p.avatar ? 'center' : null,
            allowReorder: p.reorderable,
            credits: [],
            disabled: p.disabled,
            instantUpload: true,
            allowRevert: true,
            allowFileSizeValidation: true,
            allowFileTypeValidation: true,
            itemInsertLocationFreedom: p.appendFiles || p.prependFiles,
            allowImagePreview: p.imagePreview && p.previewable,
            allowPaste: p.pasteable,
            allowDrop: true,
            allowBrowse: true,
            allowRemove: p.deletable,
            allowProcess: true,
            allowDownloadByUrl: p.downloadable,
            maxParallelUploads: p.maxParallelUploads ?? 2,
        };

        // Apply panel layout and aspect ratio
        if (p.panelLayout) {
            options.stylePanelLayout = p.panelLayout;
        }

        if (p.panelAspectRatio) {
            options.stylePanelAspectRatio = p.panelAspectRatio;
        }

        // Apply button positions
        if (p.loadingIndicatorPosition) {
            options.styleLoadIndicatorPosition = p.loadingIndicatorPosition;
        }

        if (p.uploadButtonPosition) {
            options.styleButtonProcessItemPosition = p.uploadButtonPosition;
        }

        if (p.removeUploadedFileButtonPosition) {
            options.styleButtonRemoveItemPosition = p.removeUploadedFileButtonPosition;
        }

        if (p.uploadProgressIndicatorPosition) {
            options.styleProgressIndicatorPosition = p.uploadProgressIndicatorPosition;
        }

        // Set labelIdle (drag & drop text) - use translations or uploading message
        const dragDropText = p.translations?.dragDrop || 'Drag & Drop your files or';
        const browseText = p.translations?.browse || 'Browse';
        options.labelIdle = p.uploadingMessage || `${dragDropText} <span class="filepond--label-action">${browseText}</span>`;

        // Image EXIF orientation (auto-orient images based on EXIF data)
        options.allowImageExifOrientation = p.orientImagesFromExif ?? true;

        // Check file validity (fetches file information on load)
        options.checkValidity = p.fetchFileInformation ?? true;

        // Add image transformation options if needed
        if (p.imageCrop || p.imageResize?.width || p.imageResize?.height) {
            options.allowImageTransform = true;
            options.imageTransformOutputQuality = 90;
            options.imageTransformOutputMimeType = 'image/jpeg';
        }

        // Add crop options
        if (p.imageCrop) {
            options.allowImageCrop = true;
            if (p.imageCropAspectRatio) {
                options.imageCropAspectRatio = p.imageCropAspectRatio;
            }
        }

        // Enable image editing with Cropper.js - only if imageEditor is explicitly enabled
        if (p.imageEditor && (p.imageCrop || p.imageResize?.width || p.imageResize?.height)) {
            options.allowImageEdit = true;
            options.imageEditInstantEdit = false;
            options.imageEditEditor = imageEditEditor;
        }

        // Add resize options
        if (p.imageResize?.width || p.imageResize?.height) {
            options.allowImageResize = true;
            if (p.imageResize.width) {
                options.imageResizeTargetWidth = p.imageResize.width;
            }
            if (p.imageResize.height) {
                options.imageResizeTargetHeight = p.imageResize.height;
            }
            options.imageResizeMode = p.imageResize.mode || 'contain';
        }

        return options;
    };

    const filePondOptions = buildFilePondOptions(props);
    const appliedOptions = useRef<Record<string, any>>({});

    // Create FilePond on the ref'd <input type="file"> (vue-filepond `mounted`), destroy on unmount
    useEffect(() => {
        const input = inputRef.current;
        const wrapper = wrapperRef.current;

        if (!filePondSupported() || !input || !wrapper) {
            return;
        }

        // pond.destroy() restores the input; make sure it is attached before (re)creating.
        if (!input.isConnected) {
            wrapper.appendChild(input);
        }

        const initialOptions: Record<string, any> = { ...buildFilePondOptions(propsRef.current), files: filesRef.current };
        const passedOptions: Record<string, any> = {};
        Object.keys(initialOptions).forEach((key) => {
            if (initialOptions[key] !== undefined) {
                passedOptions[key] = initialOptions[key];
            }
        });

        const instance = createFilePond(input, {
            ...passedOptions,
            onaddfile: (error: any, file: any) => fns.current.handleAddFile(error, file),
            onprocessfile: (error: any, file: any) => fns.current.handleProcessFile(error, file),
            onremovefile: (error: any, file: any) => fns.current.handleRemoveFile(error, file),
            onupdatefiles: (pondFiles: any[]) => fns.current.handleUpdateFiles(pondFiles),
            onreorderfiles: (pondFiles: any[]) => fns.current.handleUpdateFiles(pondFiles),
            onpreparefile: (file: any, output: any) => fns.current.handlePrepareFile(file, output),
        } as any);

        pond.current = instance;
        appliedOptions.current = initialOptions;

        return () => {
            instance.destroy();
            pond.current = null;
        };
    }, []);

    // Push changed options (including `files`) to FilePond, like vue-filepond's per-prop watchers
    useEffect(() => {
        const instance = pond.current;
        if (!instance) return;

        const nextOptions: Record<string, any> = { ...filePondOptions, files };
        const previousOptions = appliedOptions.current;
        const changed: Record<string, any> = {};

        new Set([...Object.keys(previousOptions), ...Object.keys(nextOptions)]).forEach((key) => {
            const unchanged = key === 'files' ? previousOptions[key] === nextOptions[key] : sameOption(previousOptions[key], nextOptions[key]);

            if (!unchanged) {
                changed[key] = nextOptions[key];
            }
        });

        appliedOptions.current = nextOptions;

        if (Object.keys(changed).length > 0) {
            instance.setOptions(changed as any);
        }
    });

    // Load existing files from modelValue or value prop
    const externalValue = props.modelValue ?? props.value;
    useEffect(() => {
        log('modelValue/value changed', externalValue);
        void syncFilesFromValue(externalValue);
    }, [externalValue]);

    // Also watch value prop separately for initial load from PHP
    useEffect(() => {
        if (props.value && !propsRef.current.modelValue) {
            log('value prop changed (no modelValue)', props.value);
            void syncFilesFromValue(props.value);
        }
    }, [props.value]);

    // Watch for file updates to inject open and download buttons (not on mount, like a non-immediate watch)
    const previousFilesLength = useRef(files.length);
    useEffect(() => {
        if (previousFilesLength.current === files.length) return;
        previousFilesLength.current = files.length;

        const p = propsRef.current;
        if (p.openable) {
            setTimeout(injectOpenButtons, 100);
        }
        if (p.downloadable) {
            setTimeout(injectDownloadButtons, 100);
        }
        if (p.avatar) {
            // Apply background image for avatar mode
            applyAvatarBackground();
        }
    }, [files.length]);

    // Apply panel layout attribute after FilePond mounts
    useEffect(() => {
        const timeout = setTimeout(() => {
            const panelLayout = propsRef.current.panelLayout;
            if (pond.current && wrapperRef.current && panelLayout) {
                const scroller = wrapperRef.current.querySelector('.filepond--list-scroller');
                if (scroller) {
                    scroller.setAttribute('data-style-panel-layout', panelLayout);
                }
            }
        }, 100);

        return () => clearTimeout(timeout);
    }, []);

    // Watch for panelLayout changes
    const previousPanelLayout = useRef(props.panelLayout);
    useEffect(() => {
        if (previousPanelLayout.current === props.panelLayout) return;
        previousPanelLayout.current = props.panelLayout;

        if (pond.current && wrapperRef.current) {
            const scroller = wrapperRef.current.querySelector('.filepond--list-scroller');
            if (scroller && props.panelLayout) {
                scroller.setAttribute('data-style-panel-layout', props.panelLayout);
            }
        }
    }, [props.panelLayout]);

    const PrefixIcon = resolveIcon(props.prefixIcon);
    const SuffixIcon = resolveIcon(props.suffixIcon);
    const acceptAttribute: string[] | null = filePondOptions.acceptedFileTypes;

    return (
        <div className="w-full space-y-2">
            <style href="laravilt-forms-file-upload" precedence="default">
                {FILE_UPLOAD_STYLES}
            </style>

            {/* Label */}
            {props.label && (
                <label className="text-sm font-medium block text-foreground">
                    {props.label}{' '}
                    {props.required && <span className="text-destructive ms-0.5">*</span>}
                </label>
            )}

            <div className="flex items-center gap-2">
                {/* Prefix icon or text */}
                {(PrefixIcon || props.prefix) && (
                    <span className="shrink-0 flex items-center gap-1">
                        {PrefixIcon && <PrefixIcon className={cn('h-4 w-4', getIconColorClass(props.prefixIconColor))} />}
                        {props.prefix && <span className="text-sm text-muted-foreground">{props.prefix}</span>}
                    </span>
                )}

                {/* FilePond container with Reka UI styling */}
                <div
                    className={cn('border border-input bg-background shadow-xs transition-all', {
                        'flex-1 rounded-md': !props.avatar,
                        'focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]': !props.disabled,
                        'opacity-50 cursor-not-allowed': props.disabled,
                        'filepond-avatar-mode': props.avatar,
                    })}
                >
                    {/* Same markup vue-filepond renders; FilePond replaces the input with its root */}
                    <div ref={wrapperRef} className="filepond--wrapper">
                        <input
                            ref={inputRef}
                            type="file"
                            name="file"
                            multiple={filePondOptions.allowMultiple}
                            accept={acceptAttribute ? acceptAttribute.join(',') : undefined}
                        />
                    </div>
                </div>

                {/* Suffix icon or text */}
                {(SuffixIcon || props.suffix) && (
                    <span className="shrink-0 flex items-center gap-1">
                        {props.suffix && <span className="text-sm text-muted-foreground">{props.suffix}</span>}
                        {SuffixIcon && <SuffixIcon className={cn('h-4 w-4', getIconColorClass(props.suffixIconColor))} />}
                    </span>
                )}
            </div>

            {/* Helper text */}
            {props.helperText && <p className="text-xs text-muted-foreground mt-1">{props.helperText}</p>}
        </div>
    );
}
