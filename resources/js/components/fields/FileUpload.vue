<script setup lang="ts">
import { computed, ref, watch, onMounted, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'
import vueFilePond from 'vue-filepond'
import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css'
import 'filepond-plugin-image-edit/dist/filepond-plugin-image-edit.min.css'
import * as LucideIcons from 'lucide-vue-next'

import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type'
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size'
import FilePondPluginImagePreview from 'filepond-plugin-image-preview'
import FilePondPluginImageCrop from 'filepond-plugin-image-crop'
import FilePondPluginImageResize from 'filepond-plugin-image-resize'
import FilePondPluginImageTransform from 'filepond-plugin-image-transform'
import FilePondPluginImageValidateSize from 'filepond-plugin-image-validate-size'
import FilePondPluginImageEdit from 'filepond-plugin-image-edit'

// Import Cropper.js
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'

const FilePond = vueFilePond(
  FilePondPluginFileValidateType,
  FilePondPluginFileValidateSize,
  FilePondPluginImagePreview,
  FilePondPluginImageCrop,
  FilePondPluginImageResize,
  FilePondPluginImageTransform,
  FilePondPluginImageValidateSize,
  FilePondPluginImageEdit
)

interface FileUploadProps {
  modelValue?: string | string[] | null
  label?: string
  helperText?: string
  required?: boolean
  disk?: string
  directory?: string
  visibility?: 'public' | 'private'
  acceptedFileTypes?: string[]
  maxSize?: number | null
  minSize?: number | null
  maxFiles?: number | null
  minFiles?: number | null
  multiple?: boolean
  image?: boolean
  imagePreview?: boolean
  imageResize?: {
    width?: number | null
    height?: number | null
    mode?: string
  }
  imageCrop?: boolean
  imageCropAspectRatio?: number | string | null
  reorderable?: boolean
  required?: boolean
  disabled?: boolean
  prefix?: string
  suffix?: string
  prefixIcon?: string
  suffixIcon?: string
  prefixIconColor?: string
  suffixIconColor?: string
  // Additional Filament-compatible properties
  preserveFilenames?: boolean
  storeFileNamesIn?: string | null
  avatar?: boolean
  imageEditor?: boolean
  imageEditorAspectRatios?: string[] | null
  imageEditorMode?: number | null
  imageEditorEmptyFillColor?: string | null
  imageEditorViewportWidth?: string | null
  imageEditorViewportHeight?: string | null
  circleCropper?: boolean
  imagePreviewHeight?: string | null
  loadingIndicatorPosition?: string | null
  panelAspectRatio?: string | null
  panelLayout?: string | null
  removeUploadedFileButtonPosition?: string | null
  uploadButtonPosition?: string | null
  uploadProgressIndicatorPosition?: string | null
  appendFiles?: boolean
  prependFiles?: boolean
  moveFiles?: boolean
  storeFiles?: boolean
  orientImagesFromExif?: boolean
  pasteable?: boolean
  fetchFileInformation?: boolean
  uploadingMessage?: string | null
  mimeTypeMap?: Record<string, string> | null
  maxParallelUploads?: number | null
  pannable?: boolean
  downloadable?: boolean
  openable?: boolean
  previewable?: boolean
  deletable?: boolean
  alignCenter?: boolean
}

const props = withDefaults(defineProps<FileUploadProps>(), {
  disk: 'public',
  directory: 'uploads',
  visibility: 'public',
  maxFiles: 1,
  multiple: false,
  image: false,
  imagePreview: true,
  imageCrop: false,
  reorderable: false,
  required: false,
  disabled: false,
  acceptedFileTypes: () => [],
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
})

const emit = defineEmits<{
  'update:modelValue': [value: string | string[] | null]
}>()

const pond = ref<any>(null)
const files = ref<any[]>([])
const previewUrlCache = new Map<string, string>()
const lastSyncedValue = ref<string>('[]')
const suppressNextSync = ref(false)
const suppressNextRemoval = ref(false)
const suppressUpdatesDuringEdit = ref(false)
const pendingSyncRemovals: Record<string, number> = Object.create(null)
const pendingRemovalTtlMs = 2000
const pendingEditReplacements = new Map<string, string>() // Maps old serverId to new serverId after edit

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const log = (_message: string, _payload?: unknown) => {
  // Disabled for production
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

// Debug props on mount
// Component props logged on mount (disabled for production)

const toPathArray = (value: string | string[] | null | undefined): string[] => {
  if (!value) {
    return []
  }

  return (Array.isArray(value) ? value : [value])
    .filter((path): path is string => typeof path === 'string' && path.length > 0)
}

const normalizeValue = (value: string | string[] | null | undefined): string => {
  return JSON.stringify(toPathArray(value))
}

const toFilePondSource = (path: string) => {
  const previewUrl = previewUrlCache.get(path) ?? null
  log('toFilePondSource', { path, previewUrl, cacheSize: previewUrlCache.size })
  return {
    source: path,
    options: {
      type: 'local', // Use 'local' instead of 'limbo' to skip validation for existing files
      metadata: {
        previewUrl,
      },
    },
  }
}

const cleanupPendingRemovals = () => {
  const now = Date.now()

  Object.keys(pendingSyncRemovals).forEach((path) => {
    if (pendingSyncRemovals[path] <= now) {
      delete pendingSyncRemovals[path]
    }
  })
}

const registerPendingRemovals = (paths: string[]) => {
  const candidates = Array.from(new Set(paths.filter((path) => path && path.length > 0)))

  if (candidates.length === 0) {
    return
  }

  cleanupPendingRemovals()
  const expiresAt = Date.now() + pendingRemovalTtlMs

  candidates.forEach((path) => {
    pendingSyncRemovals[path] = expiresAt
  })
}

const getFileItemSource = (fileItem: any): string | null => {
  if (!fileItem) {
    return null
  }

  if (typeof fileItem.source === 'string') {
    return fileItem.source
  }

  if (fileItem.source && typeof fileItem.source.source === 'string') {
    return fileItem.source.source
  }

  return null
}

const getCurrentSources = (): string[] =>
  files.value
    .map(getFileItemSource)
    .filter((path): path is string => typeof path === 'string' && path.length > 0)

const applyFileSources = async (sources: string[], trackRemovals = true) => {
  const sanitizedSources = Array.from(new Set(sources.filter((path) => path && path.length > 0)))

  if (trackRemovals) {
    const guardPaths = Array.from(new Set([...getCurrentSources(), ...sanitizedSources]))
    registerPendingRemovals(guardPaths)
  }

  // Pre-resolve URLs and cache them before adding to FilePond
  await Promise.all(
    sanitizedSources.map(async (path) => {
      try {
        await resolveFileUrl(path)
      } catch (error) {
        log('Failed to pre-resolve URL', { path, error })
      }
    })
  )

  files.value = sanitizedSources.map(toFilePondSource)
}

const syncFilesFromValue = async (value: string | string[] | null | undefined, force = false) => {
  const normalized = normalizeValue(value)

  if (!force && suppressNextSync.value) {
    log('sync skipped (internal update)')
    suppressNextSync.value = false
    lastSyncedValue.value = normalized
    return
  }

  if (!force && normalized === lastSyncedValue.value) {
    log('sync skipped (value unchanged)', normalized)
    return
  }

  lastSyncedValue.value = normalized
  log('syncing files', normalized)
  const paths = toPathArray(value)

  try {
    await applyFileSources(paths, true)
    log('sync completed successfully', { paths })
  } catch {
    // Sync error occurred
  }
}

const removeLocalFile = async (path: string) => {
  const nextSources = getCurrentSources().filter((sourcePath) => sourcePath !== path)
  await applyFileSources(nextSources, false)
}

const page = usePage()
const normalizePath = (path?: string | null) => {
  if (!path) return '/dashboard'
  return path.startsWith('/') ? path : `/${path}`
}

// Upload URL is always at /uploads (global route from forms package)
// Panel-specific upload routes are not used since the forms package
// registers routes at the root level for all panels
const uploadUrl = computed(() => '/uploads')

const temporaryUrlEndpoint = computed(() => `${uploadUrl.value}/temporary-url`)

const csrfToken = computed(() => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')

const currentOrigin = window.location.origin
const signatureParams = ['signature', 'Signature', 'X-Amz-Signature', 'X-Amz-Signature'.toLowerCase()]
const hasSignatureParam = (params: URLSearchParams) =>
  signatureParams.some((key) => params.has(key) || params.has(key.toLowerCase()))

const ensureHttps = (url: string) => {
  if (!url) {
    return url
  }

  try {
    const parsed = new URL(url, currentOrigin)

    if (
      window.location.protocol === 'https:' &&
      parsed.protocol === 'http:' &&
      !hasSignatureParam(parsed.searchParams)
    ) {
      parsed.protocol = 'https:'
    }

    return parsed.toString()
  } catch {
    return url
  }
}

const fetchTemporaryUrl = async (path: string) => {
  const response = await fetch(temporaryUrlEndpoint.value, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken.value,
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'include',
    body: JSON.stringify({
      path,
      disk: props.disk,
    }),
  })

  if (!response.ok) {
    throw new Error('Unable to generate temporary URL')
  }

  const data = await response.json()
  if (typeof data.url !== 'string') {
    throw new Error('Temporary URL missing in response')
  }

  const secureUrl = ensureHttps(data.url)
  previewUrlCache.set(path, secureUrl)
  log('resolveFileUrl: generated temporary url', { path, secureUrl })

  return secureUrl
}

const resolveFileUrl = async (path: string | null) => {
  if (!path) {
    log('resolveFileUrl: missing path')
    return null
  }

  // Check cache first
  if (previewUrlCache.has(path)) {
    const cached = previewUrlCache.get(path) as string
    log('resolveFileUrl: using cached url', { path, cached })
    return cached
  }

  // If already an absolute URL, return as-is
  if (/^https?:\/\//.test(path)) {
    log('resolveFileUrl: value already absolute', { path })
    previewUrlCache.set(path, path)
    return path
  }

  // If path already starts with /storage/, return as-is
  if (path.startsWith('/storage/')) {
    log('resolveFileUrl: path already has /storage/ prefix', { path })
    previewUrlCache.set(path, path)
    return path
  }

  // For private files or local disk, fetch temporary signed URL
  if (preferTemporaryPreview.value) {
    try {
      const temporaryUrl = await fetchTemporaryUrl(path)
      log('resolveFileUrl: fetched temporary url', { path, temporaryUrl })
      previewUrlCache.set(path, temporaryUrl)
      return temporaryUrl
    } catch (error) {
      log('resolveFileUrl: temporary url fetch failed', error)
      throw error
    }
  }

  // For public files, construct direct storage URL
  // Remove leading slash if present to avoid double slashes
  const cleanPath = path.startsWith('/') ? path.substring(1) : path
  const publicUrl = `/storage/${cleanPath}`
  previewUrlCache.set(path, publicUrl)
  log('resolveFileUrl: constructed public url', { path, cleanPath, publicUrl })
  return publicUrl
}

const deleteFile = async (path: string | null, load: () => void, error: (message?: string) => void) => {
  if (!path) {
    error('File reference missing')
    return
  }

  try {
    log('deleteFile: start', { path })
    const response = await fetch(uploadUrl.value, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken.value,
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'include',
      body: JSON.stringify({
        path,
        disk: props.disk,
      }),
    })

    if (!response.ok) {
      throw new Error('Failed to delete file')
    }

    previewUrlCache.delete(path)
    load()
    log('deleteFile: success', { path })
  } catch (err: any) {
    // File delete error
    error(err?.message ?? 'Unable to delete file')
  }
}

// FilePond server configuration
// Only use temporary preview for private files or non-public disks
const preferTemporaryPreview = computed(() => props.visibility === 'private' || props.disk === 'local')

const serverConfig = computed(() => {
  const directoryParam = props.directory ?? ''
  const appendCommonData = (formData: FormData) => {
    formData.append('disk', props.disk)
    formData.append('directory', directoryParam)
    formData.append('visibility', props.visibility)

    return formData
  }

  return {
    process: {
      url: uploadUrl.value,
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken.value,
        'X-Requested-With': 'XMLHttpRequest',
      },
      ondata: (formData: FormData) => appendCommonData(formData),
      onload: (response: string) => {
        // Response is plain text containing the file path (serverId)
        const path = response.trim()
        log('upload complete, received path', { path })

        // Prefetch preview URL in background (don't block)
        if (path) {
          resolveFileUrl(path).catch((error) => {
            log('prefetch preview url failed', { path, error })
          })
        }

        return path || null
      },
    },
    revert: (uniqueFileId: string, load: () => void, error: (message?: string) => void) => {
      log('server.revert: start', { uniqueFileId })
      deleteFile(uniqueFileId, load, error)
    },
    load: (source: any, load: (file: Blob) => void, error: (message?: string) => void, progress: (computable: boolean, current: number, total: number) => void, abort: () => void) => {
      const controller = new AbortController()
      const fetchWithSignal = async (url: string) => {
        const response = await fetch(url, {
          signal: controller.signal,
          credentials: 'include',
        })

        return response
      }

      const loadFile = async () => {
        try {
          const path = typeof source === 'string' ? source : null
          const url = await resolveFileUrl(path)

          if (!url) {
            throw new Error('File path missing')
          }

          progress(true, 0, 1)
          let resolvedUrl = url
          let response = await fetchWithSignal(resolvedUrl)

          if (!response.ok) {
            if (!path) {
              throw new Error('Failed to load file')
            }

            const fallbackUrl = await fetchTemporaryUrl(path)
            response = await fetchWithSignal(fallbackUrl)
            resolvedUrl = fallbackUrl

            if (!response.ok) {
              throw new Error(`Failed to load file (fallback status ${response.status})`)
            }
          }

          const blob = await response.blob()
          progress(true, 1, 1)
          load(blob)
        } catch (err: any) {
          if (err.name === 'AbortError') {
            return
          }

          error(err?.message ?? 'Unable to load file')
        }
      }

      loadFile()

      return {
        abort: () => {
          controller.abort()
          abort()
        },
      }
    },
    fetch: null,
    restore: null,
  }
})

const getServerId = (value: unknown): string | null =>
  typeof value === 'string' && value.length > 0 ? value : null

// Handle file process (upload complete)
const handleProcessFile = (error: any, file: any) => {
  if (error) {
    // File upload error
    return
  }

  const serverId = getServerId(file.serverId)

  if (!serverId) {
    log('Upload response missing file path', file.serverId)
    return
  }

  log('processfile received', {
    serverId,
    multiple: props.multiple,
    preview: previewUrlCache.get(serverId) ?? null,
    lastSyncedValue: lastSyncedValue.value,
    fileOrigin: file.origin,
  })

  // Check if this is a replacement for an edited file
  let isEditReplacement = false
  let oldServerId: string | null = null

  for (const [oldId, newId] of pendingEditReplacements.entries()) {
    if (newId === file.id) {
      isEditReplacement = true
      oldServerId = oldId
      pendingEditReplacements.delete(oldId)

      // Re-enable updateFiles handler now that upload is complete
      suppressUpdatesDuringEdit.value = false
      break
    }
  }

  // Update modelValue without triggering sync since file is already in FilePond
  if (props.multiple) {
    const currentValue = Array.isArray(props.modelValue) ? props.modelValue : []
    let nextValue: string[]

    if (isEditReplacement && oldServerId) {
      // Replace the old path with the new path, maintaining order
      nextValue = currentValue.map(path => path === oldServerId ? serverId : path)
    } else {
      // Add new file
      nextValue = [...currentValue, serverId]
    }

    lastSyncedValue.value = JSON.stringify(nextValue)
    emit('update:modelValue', nextValue)
  } else {
    lastSyncedValue.value = JSON.stringify([serverId])
    emit('update:modelValue', serverId)
  }

  // Inject open button if needed
  if (props.openable) {
    setTimeout(injectOpenButtons, 100)
  }
}

// Handle file prepare (after edit, before upload)
// eslint-disable-next-line @typescript-eslint/no-unused-vars
const handlePrepareFile = (_file: any, _output: any) => {
  // File prepared
}

// Handle file add (when edited file is added back)
// eslint-disable-next-line @typescript-eslint/no-unused-vars
const handleAddFile = (error: any, _file: any) => {
  if (error) {
    // File add error
    return
  }

  // File added - inject open button if needed
  if (props.openable) {
    setTimeout(injectOpenButtons, 100)
  }
}

// Handle file remove
const handleRemoveFile = (error: any, file: any) => {
  if (error) {
    console.error('[FileUpload] File remove error:', error)
    return
  }

  // If we're suppressing removals (during edit replacement), skip this
  if (suppressNextRemoval.value) {
    suppressNextRemoval.value = false
    return
  }

  const serverId = getServerId(file.serverId)

  if (!serverId) {
    log('removefile skipped: missing serverId', file.serverId)
    return
  }

  cleanupPendingRemovals()
  const pendingExpiry = pendingSyncRemovals[serverId]

  if (pendingExpiry && pendingExpiry > Date.now()) {
    delete pendingSyncRemovals[serverId]
    log('removefile ignored (sync reconciliation)', { serverId })
    return
  }

  log('removefile received', { serverId })

  if (props.multiple && Array.isArray(props.modelValue)) {
    const filtered = props.modelValue.filter((path: string) => path !== serverId)
    const nextValue = filtered.length > 0 ? filtered : null
    suppressNextSync.value = true
    emit('update:modelValue', nextValue)
    removeLocalFile(serverId)
  } else {
    suppressNextSync.value = true
    emit('update:modelValue', null)
    removeLocalFile(serverId)
  }
}

// Handle file reorder - triggered by updatefiles event
const handleUpdateFiles = (files: any[]) => {
  // Skip updates during edit replacement
  if (suppressUpdatesDuringEdit.value) {
    return
  }

  if (!props.multiple || !props.reorderable) {
    return
  }

  const orderedPaths = files
    .map((file) => {
      // Try serverId first (for newly uploaded files)
      const serverId = getServerId(file.serverId)
      if (serverId) {
        return serverId
      }

      // For existing/loaded files, use the source
      return getFileItemSource(file)
    })
    .filter((path): path is string => path !== null)

  const currentPaths = toPathArray(props.modelValue)
  const hasChanged = JSON.stringify(orderedPaths) !== JSON.stringify(currentPaths)

  if (orderedPaths.length > 0 && hasChanged) {
    suppressNextSync.value = true
    emit('update:modelValue', orderedPaths)
  }
}

// Load existing files
watch(() => props.modelValue, (newValue) => {
  log('modelValue changed', newValue)
  syncFilesFromValue(newValue)
}, { immediate: true })

// Add "Open" button functionality when openable is enabled
const injectOpenButtons = () => {
  if (!props.openable || !pond.value) return

  const fileItems = pond.value.$el?.querySelectorAll('.filepond--item')
  if (!fileItems) return

  fileItems.forEach((item: HTMLElement) => {
    // Check if button already exists
    if (item.querySelector('.filepond-open-button')) return

    // Find the file action buttons container
    const actionsContainer = item.querySelector('.filepond--action-remove-item')?.parentElement
    if (!actionsContainer) return

    // Get the file serverId to construct URL
    const fileId = item.getAttribute('data-id')
    if (!fileId) return

    const files = pond.value.getFiles()
    const fileItem = files.find((f: any) => f.id === fileId)
    if (!fileItem) return

    const serverId = getServerId(fileItem.serverId)
    if (!serverId) return

    // Create open button
    const openButton = document.createElement('button')
    openButton.type = 'button'
    openButton.className = 'filepond--file-action-button filepond--action-open-item filepond-open-button'
    openButton.title = 'Open file in new tab'
    openButton.setAttribute('data-align', 'left')
    openButton.innerHTML = `
      <svg width="26" height="26" viewBox="0 0 26 26" xmlns="http://www.w3.org/2000/svg">
        <path d="M14 3h7v7M21 3L10 14M18 13v8H5V8h8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    `

    openButton.addEventListener('click', async (e) => {
      e.preventDefault()
      e.stopPropagation()

      try {
        const url = await resolveFileUrl(serverId)
        if (url) {
          window.open(url, '_blank', 'noopener,noreferrer')
        }
      } catch (error) {
        console.error('[FileUpload] Failed to open file:', error)
      }
    })

    // Insert the button before the remove button
    const removeButton = actionsContainer.querySelector('.filepond--action-remove-item')
    if (removeButton) {
      actionsContainer.insertBefore(openButton, removeButton)
    } else {
      actionsContainer.appendChild(openButton)
    }
  })
}

// Inject download buttons for each file item
const injectDownloadButtons = () => {
  if (!props.downloadable || !pond.value) return

  const fileItems = pond.value.$el?.querySelectorAll('.filepond--item')
  if (!fileItems) return

  fileItems.forEach((item: HTMLElement) => {
    // Check if button already exists
    if (item.querySelector('.filepond-download-button')) return

    // Find the file action buttons container
    const actionsContainer = item.querySelector('.filepond--action-remove-item')?.parentElement
    if (!actionsContainer) return

    // Get the file serverId to construct URL
    const fileId = item.getAttribute('data-id')
    if (!fileId) return

    const files = pond.value.getFiles()
    const fileItem = files.find((f: any) => f.id === fileId)
    if (!fileItem) return

    const serverId = getServerId(fileItem.serverId)
    if (!serverId) return

    // Create download button
    const downloadButton = document.createElement('button')
    downloadButton.type = 'button'
    downloadButton.className = 'filepond--file-action-button filepond--action-download-item filepond-download-button'
    downloadButton.title = 'Download file'
    downloadButton.setAttribute('data-align', 'left')
    downloadButton.innerHTML = `
      <svg width="26" height="26" viewBox="0 0 26 26" xmlns="http://www.w3.org/2000/svg">
        <path d="M13 3v12m0 0l-4-4m4 4l4-4M5 17v4h14v-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    `

    downloadButton.addEventListener('click', async (e) => {
      e.preventDefault()
      e.stopPropagation()

      try {
        const url = await resolveFileUrl(serverId)
        if (url) {
          // Create a temporary anchor element to trigger download
          const a = document.createElement('a')
          a.href = url
          a.download = fileItem.filename || 'download'
          a.style.display = 'none'
          document.body.appendChild(a)
          a.click()
          document.body.removeChild(a)
        }
      } catch (error) {
        console.error('[FileUpload] Failed to download file:', error)
      }
    })

    // Insert the button before the remove button
    const removeButton = actionsContainer.querySelector('.filepond--action-remove-item')
    if (removeButton) {
      actionsContainer.insertBefore(downloadButton, removeButton)
    } else {
      actionsContainer.appendChild(downloadButton)
    }
  })
}

// Apply avatar background image for avatar mode
const applyAvatarBackground = () => {
  if (!props.avatar || !pond.value) return

  setTimeout(() => {
    const fileItem = pond.value.$el?.querySelector('.filepond--item')
    if (!fileItem) return

    const itemPanel = fileItem.querySelector('.filepond--item-panel')
    const img = fileItem.querySelector('img')

    if (itemPanel && img && img.src) {
      itemPanel.style.backgroundImage = `url(${img.src})`
    }
  }, 100)
}

// Watch for file updates to inject open and download buttons
watch(
  () => files.value.length,
  () => {
    if (props.openable) {
      // Use nextTick to ensure FilePond has rendered the items
      setTimeout(injectOpenButtons, 100)
    }
    if (props.downloadable) {
      // Use nextTick to ensure FilePond has rendered the items
      setTimeout(injectDownloadButtons, 100)
    }
    if (props.avatar) {
      // Apply background image for avatar mode
      applyAvatarBackground()
    }
  },
  { flush: 'post' }
)

// Build FilePond options
const filePondOptions = computed(() => {
  const options: any = {
    server: serverConfig.value,
    name: 'file',
    allowMultiple: props.avatar ? false : props.multiple,
    maxFiles: props.avatar ? 1 : (props.maxFiles ?? null),
    maxFileSize: props.maxSize ? `${props.maxSize}KB` : null,
    acceptedFileTypes: props.acceptedFileTypes.length > 0 ? props.acceptedFileTypes : null,
    fileValidateTypeDetectType: props.mimeTypeMap ? (source: any, type: string) => {
      // Custom MIME type detection using the provided map
      return new Promise((resolve) => {
        // Try to get extension from filename
        const extension = source.name ? source.name.split('.').pop()?.toLowerCase() : null
        if (extension && props.mimeTypeMap && props.mimeTypeMap[extension]) {
          resolve(props.mimeTypeMap[extension])
        } else {
          resolve(type)
        }
      })
    } : null,
    imagePreviewMaxHeight: props.avatar ? 256 : (props.imagePreviewHeight ? parseInt(props.imagePreviewHeight) : 256),
    imagePreviewMinHeight: props.avatar ? 160 : null,
    styleItemPanelAspectRatio: props.avatar ? '1:1' : null,
    stylePanelLayout: props.avatar ? 'compact' : null,
    styleLoadIndicatorPosition: props.avatar ? 'center' : null,
    styleProgressIndicatorPosition: props.avatar ? 'center' : null,
    styleButtonRemoveItemPosition: props.avatar ? 'center' : null,
    allowReorder: props.reorderable,
    credits: [],
    disabled: props.disabled,
    instantUpload: true,
    allowRevert: true,
    allowFileSizeValidation: true,
    allowFileTypeValidation: true,
    itemInsertLocationFreedom: props.appendFiles || props.prependFiles,
    allowImagePreview: props.imagePreview && props.previewable,
    allowPaste: props.pasteable,
    allowDrop: true,
    allowBrowse: true,
    allowRemove: props.deletable,
    allowProcess: true,
    allowDownloadByUrl: props.downloadable,
    maxParallelUploads: props.maxParallelUploads ?? 2,
  }

  // Apply panel layout and aspect ratio
  if (props.panelLayout) {
    options.stylePanelLayout = props.panelLayout
  }

  if (props.panelAspectRatio) {
    options.stylePanelAspectRatio = props.panelAspectRatio
  }

  // Apply button positions
  if (props.loadingIndicatorPosition) {
    options.styleLoadIndicatorPosition = props.loadingIndicatorPosition
  }

  if (props.uploadButtonPosition) {
    options.styleButtonProcessItemPosition = props.uploadButtonPosition
  }

  if (props.removeUploadedFileButtonPosition) {
    options.styleButtonRemoveItemPosition = props.removeUploadedFileButtonPosition
  }

  if (props.uploadProgressIndicatorPosition) {
    options.styleProgressIndicatorPosition = props.uploadProgressIndicatorPosition
  }

  // Apply uploading message
  if (props.uploadingMessage) {
    options.labelIdle = props.uploadingMessage
  }

  // Image EXIF orientation (auto-orient images based on EXIF data)
  options.allowImageExifOrientation = props.orientImagesFromExif ?? true

  // Check file validity (fetches file information on load)
  options.checkValidity = props.fetchFileInformation ?? true

  // Add image transformation options if needed
  if (props.imageCrop || props.imageResize?.width || props.imageResize?.height) {
    options.allowImageTransform = true
    options.imageTransformOutputQuality = 90
    options.imageTransformOutputMimeType = 'image/jpeg'
  }

  // Add crop options
  if (props.imageCrop) {
    options.allowImageCrop = true
    if (props.imageCropAspectRatio) {
      options.imageCropAspectRatio = props.imageCropAspectRatio
    }
  }

  // Enable image editing with Cropper.js - only if imageEditor is explicitly enabled
  if (props.imageEditor && (props.imageCrop || props.imageResize?.width || props.imageResize?.height)) {
    options.allowImageEdit = true
    options.imageEditInstantEdit = false

    options.imageEditEditor = {
      // Open editor - create Cropper.js instance
      open: (file: File, instructions: any, onconfirm: any, oncancel: any) => {
        // Create editor overlay
        const overlay = document.createElement('div')
        overlay.className = 'filepond-image-editor-overlay'

        // Create editor window
        const editorWindow = document.createElement('div')
        editorWindow.className = 'filepond-image-editor-window'

        // Apply viewport dimensions if provided
        if (props.imageEditorViewportWidth || props.imageEditorViewportHeight) {
          if (props.imageEditorViewportWidth) {
            editorWindow.style.width = props.imageEditorViewportWidth
          }
          if (props.imageEditorViewportHeight) {
            editorWindow.style.height = props.imageEditorViewportHeight
          }
        }

        // Create image container
        const imgContainer = document.createElement('div')
        imgContainer.className = 'filepond-image-editor-image-container'

        // Apply empty fill color if provided
        if (props.imageEditorEmptyFillColor) {
          imgContainer.style.backgroundColor = props.imageEditorEmptyFillColor
        }

        // Create image element
        const img = document.createElement('img')
        img.className = 'filepond-image-editor-image'

        // Add circular class if circleCropper or avatar mode is enabled
        if (props.circleCropper || props.avatar) {
          img.classList.add('filepond-image-editor-image-circle')
        }

        imgContainer.appendChild(img)

        // Create toolbar
        const toolbar = document.createElement('div')
        toolbar.className = 'filepond-image-editor-toolbar'

        // Create aspect ratio selector if aspect ratios are provided
        let aspectRatioGroup: HTMLDivElement | null = null
        if (props.imageEditorAspectRatios && props.imageEditorAspectRatios.length > 0 && !props.circleCropper && !props.avatar) {
          aspectRatioGroup = document.createElement('div')
          aspectRatioGroup.className = 'filepond-image-editor-aspect-ratios'

          const aspectLabel = document.createElement('span')
          aspectLabel.className = 'filepond-image-editor-aspect-label'
          aspectLabel.textContent = 'Aspect Ratio:'
          aspectRatioGroup.appendChild(aspectLabel)
        }

        // Create action buttons
        const actionsGroup = document.createElement('div')
        actionsGroup.className = 'filepond-image-editor-actions'

        // Rotate buttons
        const rotateLeftBtn = createEditorButton('↶', 'Rotate Left')
        const rotateRightBtn = createEditorButton('↷', 'Rotate Right')
        const flipHBtn = createEditorButton('⇄', 'Flip Horizontal')
        const flipVBtn = createEditorButton('⇅', 'Flip Vertical')
        const zoomInBtn = createEditorButton('+', 'Zoom In')
        const zoomOutBtn = createEditorButton('−', 'Zoom Out')

        actionsGroup.appendChild(rotateLeftBtn)
        actionsGroup.appendChild(rotateRightBtn)
        actionsGroup.appendChild(flipHBtn)
        actionsGroup.appendChild(flipVBtn)
        actionsGroup.appendChild(zoomInBtn)
        actionsGroup.appendChild(zoomOutBtn)

        // Create control buttons
        const controlsGroup = document.createElement('div')
        controlsGroup.className = 'filepond-image-editor-controls'

        const cancelBtn = createEditorButton('Cancel', 'Cancel', 'secondary')
        const resetBtn = createEditorButton('Reset', 'Reset', 'destructive')
        const confirmBtn = createEditorButton('Save', 'Save', 'primary')

        controlsGroup.appendChild(cancelBtn)
        controlsGroup.appendChild(resetBtn)
        controlsGroup.appendChild(confirmBtn)

        if (aspectRatioGroup) {
          toolbar.appendChild(aspectRatioGroup)
        }
        toolbar.appendChild(actionsGroup)
        toolbar.appendChild(controlsGroup)

        editorWindow.appendChild(imgContainer)
        editorWindow.appendChild(toolbar)
        overlay.appendChild(editorWindow)
        document.body.appendChild(overlay)

        // Load image
        const reader = new FileReader()
        reader.onload = (e) => {
          img.src = e.target?.result as string

          // Parse aspect ratio from string format (e.g., '16:9', '4:3', '1:1')
          const parseAspectRatio = (ratio: string | null): number => {
            if (!ratio) return NaN
            const parts = ratio.split(':')
            if (parts.length === 2) {
              const width = parseFloat(parts[0])
              const height = parseFloat(parts[1])
              return width / height
            }
            return NaN
          }

          // Determine initial aspect ratio
          let initialAspectRatio = NaN

          // Force 1:1 aspect ratio for circle cropper or avatar mode
          if (props.circleCropper || props.avatar) {
            initialAspectRatio = 1
          } else if (props.imageEditorAspectRatios && props.imageEditorAspectRatios.length > 0) {
            // Use first aspect ratio as default
            initialAspectRatio = parseAspectRatio(props.imageEditorAspectRatios[0])
          } else if (props.imageCropAspectRatio) {
            initialAspectRatio = typeof props.imageCropAspectRatio === 'number'
              ? props.imageCropAspectRatio
              : parseAspectRatio(props.imageCropAspectRatio)
          }

          // Initialize Cropper.js with enhanced options
          const cropperOptions: Cropper.Options = {
            viewMode: props.imageEditorMode ?? 1, // Use imageEditorMode or default to 1
            dragMode: 'move',
            aspectRatio: initialAspectRatio,
            autoCropArea: 1,
            restore: false,
            guides: true,
            center: true,
            highlight: true,
            cropBoxMovable: true,
            cropBoxResizable: !props.circleCropper, // Disable resize for circle cropper
            toggleDragModeOnDblclick: false,
            background: props.imageEditorEmptyFillColor ? true : false,
            responsive: true,
            checkOrientation: true,
            ready() {
              // Add circular overlay for circle cropper
              if (props.circleCropper || props.avatar) {
                const cropperContainer = img.parentElement?.querySelector('.cropper-container')
                if (cropperContainer) {
                  const circleOverlay = document.createElement('div')
                  circleOverlay.className = 'filepond-circle-crop-overlay'
                  cropperContainer.appendChild(circleOverlay)
                }
              }
            }
          }

          const cropper = new Cropper(img, cropperOptions)

          // Create aspect ratio buttons if aspect ratios are provided (not for circle cropper)
          if (aspectRatioGroup && props.imageEditorAspectRatios && !props.circleCropper) {
            let activeAspectBtn: HTMLButtonElement | null = null

            props.imageEditorAspectRatios.forEach((ratio, index) => {
              const ratioValue = parseAspectRatio(ratio)
              const ratioBtn = createEditorButton(
                ratio || 'Free',
                `Set aspect ratio to ${ratio || 'free'}`,
                index === 0 ? 'active' : 'default'
              )

              if (index === 0) {
                activeAspectBtn = ratioBtn
              }

              ratioBtn.onclick = () => {
                cropper.setAspectRatio(ratioValue)

                // Update active state
                if (activeAspectBtn) {
                  activeAspectBtn.classList.remove('filepond-image-editor-btn-active')
                }
                ratioBtn.classList.add('filepond-image-editor-btn-active')
                activeAspectBtn = ratioBtn
              }

              aspectRatioGroup?.appendChild(ratioBtn)
            })
          }

          // Store original file item to replace after edit
          let originalFileItem: any = null
          if (pond.value && pond.value.getFiles) {
            const allFiles = pond.value.getFiles()
            originalFileItem = allFiles.find((item: any) => {
              return item.file === file || item.filename === file.name
            })
          }

          // Action button handlers
          rotateLeftBtn.onclick = () => cropper.rotate(-90)
          rotateRightBtn.onclick = () => cropper.rotate(90)
          flipHBtn.onclick = () => {
            const data = cropper.getData()
            cropper.scaleX((data.scaleX || 1) * -1)
          }
          flipVBtn.onclick = () => {
            const data = cropper.getData()
            cropper.scaleY((data.scaleY || 1) * -1)
          }
          zoomInBtn.onclick = () => cropper.zoom(0.1)
          zoomOutBtn.onclick = () => cropper.zoom(-0.1)
          resetBtn.onclick = () => cropper.reset()

          // Handle confirm
          confirmBtn.onclick = () => {
            const canvas = cropper.getCroppedCanvas({
              maxWidth: props.imageResize?.width || 4096,
              maxHeight: props.imageResize?.height || 4096,
              imageSmoothingEnabled: true,
              imageSmoothingQuality: 'high',
              fillColor: props.imageEditorEmptyFillColor || '#fff',
            })

            canvas.toBlob((blob) => {
              if (!blob) {
                console.error('[FileUpload] Failed to create blob from cropped image')
                if (typeof oncancel === 'function') {
                  oncancel()
                }
                cleanup()
                return
              }

              const editedFile = new File([blob], file.name, {
                type: file.type,
                lastModified: Date.now(),
              })

              // Cropper edit completed

              // For already-loaded files (origin: 3), manually replace in FilePond
              if (originalFileItem && originalFileItem.origin === 3 && pond.value) {
                const allFiles = pond.value.getFiles()
                const originalIndex = allFiles.indexOf(originalFileItem)
                const originalServerId = getServerId(originalFileItem.serverId)

                // Mark that we're doing an edit replacement to prevent removal from modelValue
                suppressNextRemoval.value = true
                suppressUpdatesDuringEdit.value = true

                // Remove the old file
                pond.value.removeFile(originalFileItem.id, { revert: false })

                // Wait a tick for removal to complete
                setTimeout(() => {
                  if (!pond.value) {
                    cleanup()
                    return
                  }

                  // Add the edited file back
                  const addResult = pond.value.addFile(editedFile, {
                    index: originalIndex >= 0 ? originalIndex : undefined,
                  })

                  // Track the replacement
                  if (originalServerId) {
                    if (addResult && typeof addResult.then === 'function') {
                      addResult.then((fileItem: any) => {
                        if (fileItem && fileItem.id) {
                          pendingEditReplacements.set(originalServerId, fileItem.id)
                        }
                      }).catch((err: any) => {
                        console.error('[FileUpload] Error adding edited file:', err)
                      })
                    } else if (addResult && addResult.id) {
                      pendingEditReplacements.set(originalServerId, addResult.id)
                    }
                  }
                }, 50)
              } else {
                // For new uploads, use the standard onconfirm callback
                onconfirm(editedFile)
              }

              cleanup()
            }, file.type, 0.92)
          }

          // Handle cancel
          cancelBtn.onclick = () => {
            if (typeof oncancel === 'function') {
              oncancel()
            }
            cleanup()
          }

          // Cleanup function
          const cleanup = () => {
            if (cropper) {
              cropper.destroy()
            }
            if (overlay.parentNode) {
              document.body.removeChild(overlay)
            }
            document.removeEventListener('keydown', handleEscape)
          }

          // Close on overlay click
          overlay.onclick = (e) => {
            if (e.target === overlay) {
              if (typeof oncancel === 'function') {
                oncancel()
              }
              cleanup()
            }
          }

          // Close on Escape key
          const handleEscape = (e: KeyboardEvent) => {
            if (e.key === 'Escape') {
              if (typeof oncancel === 'function') {
                oncancel()
              }
              cleanup()
            }
          }
          document.addEventListener('keydown', handleEscape)
        }

        reader.readAsDataURL(file)

        // Helper function to create editor buttons
        function createEditorButton(text: string, title: string, variant: 'primary' | 'secondary' | 'default' | 'destructive' | 'active' = 'default') {
          const btn = document.createElement('button')
          btn.textContent = text
          btn.title = title
          btn.type = 'button'
          btn.className = `filepond-image-editor-btn filepond-image-editor-btn-${variant}`
          return btn
        }

        return {
          onclose: () => {
            if (overlay.parentNode) {
              document.body.removeChild(overlay)
            }
          },
        }
      },
    }
  }

  // Add resize options
  if (props.imageResize?.width || props.imageResize?.height) {
    options.allowImageResize = true
    if (props.imageResize.width) {
      options.imageResizeTargetWidth = props.imageResize.width
    }
    if (props.imageResize.height) {
      options.imageResizeTargetHeight = props.imageResize.height
    }
    options.imageResizeMode = props.imageResize.mode || 'contain'
  }

  return options
})

// Apply panel layout attribute after FilePond mounts
onMounted(() => {
  nextTick(() => {
    setTimeout(() => {
      if (pond.value?.$el && props.panelLayout) {
        const scroller = pond.value.$el.querySelector('.filepond--list-scroller')
        if (scroller) {
          scroller.setAttribute('data-style-panel-layout', props.panelLayout)
        }
      }
    }, 100)
  })
})

// Watch for panelLayout changes
watch(() => props.panelLayout, (newLayout) => {
  if (pond.value?.$el) {
    const scroller = pond.value.$el.querySelector('.filepond--list-scroller')
    if (scroller && newLayout) {
      scroller.setAttribute('data-style-panel-layout', newLayout)
    }
  }
})
</script>

<template>
  <div class="w-full space-y-2">
    <!-- Label -->
    <label
      v-if="label"
      class="text-sm font-medium block text-foreground"
    >
      {{ label }}
      <span v-if="required" class="text-destructive ms-0.5">*</span>
    </label>

    <div class="flex items-center gap-2">
      <!-- Prefix icon or text -->
      <span v-if="getIconComponent(props.prefixIcon) || props.prefix" class="shrink-0 flex items-center gap-1">
        <component
          v-if="getIconComponent(props.prefixIcon)"
          :is="getIconComponent(props.prefixIcon)"
          class="h-4 w-4"
          :class="getIconColorClass(props.prefixIconColor)"
        />
        <span v-if="props.prefix" class="text-sm text-muted-foreground">{{ props.prefix }}</span>
      </span>

      <!-- FilePond container with Reka UI styling -->
      <div
        class="border border-input bg-background shadow-xs transition-all"
        :class="{
          'flex-1 rounded-md': !props.avatar,
          'focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]': !disabled,
          'opacity-50 cursor-not-allowed': disabled,
          'filepond-avatar-mode': props.avatar,
        }"
      >
        <FilePond
          ref="pond"
          :files="files"
          v-bind="filePondOptions"
          @addfile="handleAddFile"
          @processfile="handleProcessFile"
          @removefile="handleRemoveFile"
          @updatefiles="handleUpdateFiles"
          @reorderfiles="handleUpdateFiles"
          @preparefile="handlePrepareFile"
        />
      </div>

      <!-- Suffix icon or text -->
      <span v-if="getIconComponent(props.suffixIcon) || props.suffix" class="shrink-0 flex items-center gap-1">
        <span v-if="props.suffix" class="text-sm text-muted-foreground">{{ props.suffix }}</span>
        <component
          v-if="getIconComponent(props.suffixIcon)"
          :is="getIconComponent(props.suffixIcon)"
          class="h-4 w-4"
          :class="getIconColorClass(props.suffixIconColor)"
        />
      </span>
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
/* Override FilePond to match Reka UI design system */

/* Root container - inherit parent styling */
:deep(.filepond--root) {
  font-family: inherit;
  font-size: 0.875rem; /* text-sm */
  margin-bottom: 0;
}

/* Main panel - no border/background since wrapper has it */
:deep(.filepond--panel-root) {
  background-color: transparent;
  border: none;
  border-radius: 0;
}

/* Drop label styling */
:deep(.filepond--drop-label) {
  color: hsl(var(--muted-foreground));
  min-height: 4.5rem; /* h-18 */
}

:deep(.filepond--drop-label label) {
  cursor: pointer;
}

/* Browse action link */
:deep(.filepond--label-action) {
  color: hsl(var(--primary));
  text-decoration: underline;
  text-decoration-color: hsl(var(--primary) / 0.4);
  transition: text-decoration-color 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep(.filepond--label-action:hover) {
  text-decoration-color: hsl(var(--primary));
}

/* Drag & drop animation */
:deep(.filepond--drip-blob) {
  background-color: hsl(var(--primary) / 0.1);
}

/* Grid layout styling - FilePond official grid approach */
:deep(.filepond--list-scroller[data-style-panel-layout="grid"] .filepond--item) {
  width: calc(50% - 0.5em);
}

@media (min-width: 30em) {
  :deep(.filepond--list-scroller[data-style-panel-layout="grid"] .filepond--item) {
    width: calc(50% - 0.5em);
  }
}

@media (min-width: 50em) {
  :deep(.filepond--list-scroller[data-style-panel-layout="grid"] .filepond--item) {
    width: calc(33.33% - 0.5em);
  }
}

:deep(.filepond--item-panel) {
  background-color: hsl(var(--muted) / 0.4);
  border-radius: calc(var(--radius) - 2px);
  transition: background-color 0.15s ease;
}

:deep(.filepond--item-panel:hover) {
  background-color: hsl(var(--muted) / 0.6);
}

/* Action buttons */
:deep(.filepond--file-action-button) {
  cursor: pointer;
  transition: transform 0.15s ease;
}

:deep(.filepond--file-action-button:hover) {
  transform: scale(1.05);
}

:deep(.filepond--file-action-button:active) {
  transform: scale(0.95);
}

/* Open button styling */
:deep(.filepond--action-open-item) {
  color: hsl(var(--primary));
}

:deep(.filepond--action-open-item:hover) {
  color: hsl(var(--primary) / 0.8);
}

/* Progress indicator */
:deep(.filepond--file-status) {
  color: hsl(var(--foreground));
  font-size: 0.75rem;
}

:deep(.filepond--file-status-main) {
  color: hsl(var(--muted-foreground));
}

:deep(.filepond--file-status-sub) {
  color: hsl(var(--muted-foreground) / 0.8);
  font-size: 0.7rem;
}

/* Loading indicator */
:deep(.filepond--load-indicator) {
  color: hsl(var(--primary));
}

/* Process indicator (upload progress) */
:deep(.filepond--process-indicator) {
  color: hsl(var(--primary));
}

/* Error state */
:deep([data-filepond-item-state*='error'] .filepond--item-panel),
:deep([data-filepond-item-state*='invalid'] .filepond--item-panel) {
  background-color: hsl(var(--destructive) / 0.1);
}

:deep([data-filepond-item-state='processing-complete'] .filepond--item-panel) {
  background-color: hsl(var(--primary) / 0.1);
}

/* Image preview */
:deep(.filepond--image-preview) {
  background-color: hsl(var(--background));
}

:deep(.filepond--image-preview-overlay) {
  background: linear-gradient(
    to bottom,
    rgba(0, 0, 0, 0) 0%,
    rgba(0, 0, 0, 0.3) 100%
  );
}

/* File info */
:deep(.filepond--file-info) {
  color: hsl(var(--foreground));
}

:deep(.filepond--file-info-main) {
  font-size: 0.875rem;
  font-weight: 500;
}

:deep(.filepond--file-info-sub) {
  font-size: 0.75rem;
  color: hsl(var(--muted-foreground));
  opacity: 0.8;
}

/* Disabled state */
:deep(.filepond--root[disabled]) .filepond--drop-label {
  opacity: 0.5;
  cursor: not-allowed;
}

:deep(.filepond--root[disabled]) .filepond--drop-label label {
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

.filepond-avatar-mode :deep(.filepond--root) {
  width: 160px !important;
  height: 160px !important;
}

.filepond-avatar-mode :deep(.filepond--panel-root) {
  background-color: hsl(var(--muted) / 0.3);
  height: 160px !important;
}

.filepond-avatar-mode :deep(.filepond--drop-label) {
  height: 160px !important;
}

/* Force all FilePond elements to 160x160 */
.filepond-avatar-mode :deep(.filepond--item),
.filepond-avatar-mode :deep(.filepond--item-panel),
.filepond-avatar-mode :deep(.filepond--file),
.filepond-avatar-mode :deep(.filepond--image-preview),
.filepond-avatar-mode :deep(.filepond--image-preview-wrapper),
.filepond-avatar-mode :deep(.filepond--image-preview-overlay),
.filepond-avatar-mode :deep(.filepond--image-canvas),
.filepond-avatar-mode :deep(.filepond--image-bitmap) {
  width: 160px !important;
  height: 160px !important;
  margin: 0 !important;
}

/* Hide the constrained img element and show background instead */
.filepond-avatar-mode :deep(.filepond--image-preview-markup),
.filepond-avatar-mode :deep(.filepond--image-preview-wrapper),
.filepond-avatar-mode :deep(.filepond--image-preview-overlay),
.filepond-avatar-mode :deep(.filepond--image-canvas-wrapper) {
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  width: 160px !important;
  height: 160px !important;
}

/* Make the image preview use cover sizing */
.filepond-avatar-mode :deep(.filepond--image-preview) {
  background-size: cover !important;
  background-position: center !important;
}

/* Make the image clip use cover sizing */
.filepond-avatar-mode :deep(.filepond--image-clip) {
  background-size: cover !important;
  background-position: center !important;
}

/* Make the image preview overlay transparent */
.filepond-avatar-mode :deep(.filepond--image-preview-overlay) {
  background-color: transparent !important;
  background: transparent !important;
}

/* Hide FilePond's constrained image */
.filepond-avatar-mode :deep(img) {
  opacity: 0 !important;
  pointer-events: none !important;
  width: 100% !important;
  height: 100% !important;
  object-fit: cover !important;
}

/* Use the item panel as background container */
.filepond-avatar-mode :deep(.filepond--item-panel) {
  background-size: cover !important;
  background-position: center !important;
  background-repeat: no-repeat !important;
}

/* Hide file info/details in avatar mode */
.filepond-avatar-mode :deep(.filepond--file-info),
.filepond-avatar-mode :deep(.filepond--file-status),
.filepond-avatar-mode :deep(.filepond--file-info-main),
.filepond-avatar-mode :deep(.filepond--file-info-sub) {
  display: none !important;
}

/* Position action buttons inside the circle */
.filepond-avatar-mode :deep(.filepond--action-remove-item) {
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

.filepond-avatar-mode :deep(.filepond--action-remove-item:hover) {
  background-color: rgba(0, 0, 0, 0.3) !important;
}

.filepond-avatar-mode :deep(.filepond--action-download-item),
.filepond-avatar-mode :deep(.filepond--action-open-item) {
  position: absolute;
  bottom: 8px;
  z-index: 10;
  width: 32px;
  height: 32px;
  background-color: rgba(0, 0, 0, 0.7) !important;
  border-radius: 50%;
  cursor: pointer;
}

.filepond-avatar-mode :deep(.filepond--action-download-item) {
  right: 48px;
}

.filepond-avatar-mode :deep(.filepond--action-open-item) {
  right: 8px;
}

.filepond-avatar-mode :deep(.filepond--action-download-item:hover),
.filepond-avatar-mode :deep(.filepond--action-open-item:hover) {
  background-color: rgba(0, 0, 0, 0.85) !important;
}

/* Center the file list */
.filepond-avatar-mode :deep(.filepond--list-scroller) {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>

<!-- Unscoped styles for dynamically created image editor overlay -->
<style>
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
</style>

<!-- Scoped styles for circular cropping (need :deep() to penetrate Cropper.js) -->
<style scoped>
/* Style the cropper elements for circular cropping */
:deep(.cropper-container:has(.filepond-image-editor-image-circle) .cropper-view-box),
:deep(.cropper-container:has(.filepond-image-editor-image-circle) .cropper-face) {
  border-radius: 50%;
  outline: 0;
}

/* Add circular mask overlay effect */
:deep(.cropper-container:has(.filepond-image-editor-image-circle) .cropper-view-box) {
  box-shadow: 0 0 0 1px hsl(var(--primary)), 0 0 0 9999em rgba(0, 0, 0, 0.5);
}
</style>
