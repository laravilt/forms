@props(['component'])

@php
    $props = $component->toLaraviltProps();
    $id = $props['id'] ?? $props['name'];
    $accept = !empty($props['acceptedFileTypes']) ? implode(',', $props['acceptedFileTypes']) : '';
@endphp

<x-laravilt-field-wrapper
    :field="$component"
    :id="$id"
    :name="$props['name']"
    :label="$props['label']"
    :helperText="$props['helperText']"
    :required="$props['required']"
    :disabled="$props['disabled']"
    :hidden="$props['hidden']"
    :columnSpan="$props['columnSpan']"
    :extraFieldWrapperAttributes="$props['extraAttributes'] ?? []"
>
    <div
        x-data="{
            files: [],
            previews: [],
            isDragging: false,
            maxFiles: @js($props['maxFiles']),
            maxSize: @js($props['maxSize']),
            isImage: @js($props['image']),
            isAvatar: @js($props['avatar']),
            multiple: @js($props['multiple']),

            handleFiles(fileList) {
                const newFiles = Array.from(fileList);

                // Check max files limit
                if (this.maxFiles && (this.files.length + newFiles.length) > this.maxFiles) {
                    alert('Maximum ' + this.maxFiles + ' files allowed');
                    return;
                }

                newFiles.forEach(file => {
                    // Check file size
                    if (this.maxSize && (file.size / 1024) > this.maxSize) {
                        alert('File ' + file.name + ' exceeds maximum size of ' + this.maxSize + 'KB');
                        return;
                    }

                    this.files.push(file);

                    // Generate preview for images
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.previews.push({
                                name: file.name,
                                url: e.target.result,
                                type: 'image'
                            });
                        };
                        reader.readAsDataURL(file);
                    } else {
                        this.previews.push({
                            name: file.name,
                            url: null,
                            type: 'file'
                        });
                    }
                });

                @if($props['reactive'])
                    $wire.call('updateState', '{{ $props['name'] }}', this.files);
                @endif
            },

            removeFile(index) {
                this.files.splice(index, 1);
                this.previews.splice(index, 1);
            },

            formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            }
        }"
        class="space-y-4"
    >
        {{-- Avatar Upload (Circular) --}}
        @if($props['avatar'])
            <div class="flex items-center gap-6">
                {{-- Avatar Preview --}}
                <div class="relative">
                    <div
                        class="h-24 w-24 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
                    >
                        <template x-if="previews.length > 0 && previews[0].url">
                            <img
                                :src="previews[0].url"
                                alt="Avatar"
                                class="h-full w-full object-cover"
                            />
                        </template>
                        <template x-if="previews.length === 0">
                            <svg class="h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </template>
                    </div>

                    {{-- Remove Button --}}
                    <button
                        type="button"
                        x-show="previews.length > 0"
                        @click="removeFile(0)"
                        class="absolute -top-2 -right-2 rounded-full bg-red-500 p-1 text-white shadow-sm hover:bg-red-600"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Upload Button --}}
                <div>
                    <label
                        for="{{ $id }}"
                        class="cursor-pointer inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                    >
                        <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Change Avatar
                    </label>
                    @if($props['maxSize'])
                        <p class="mt-1 text-xs text-gray-500">Max {{ $props['maxSize'] }}KB</p>
                    @endif
                </div>
            </div>
        @else
            {{-- Standard File Upload Area --}}
            <div
                @drop.prevent="isDragging = false; handleFiles($event.dataTransfer.files)"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': isDragging }"
                class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 transition-colors dark:border-gray-600"
            >
                <div class="text-center">
                    {{-- Upload Icon --}}
                    @if($props['image'])
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    @else
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    @endif

                    <div class="mt-4 flex text-sm leading-6 text-gray-600 dark:text-gray-400 justify-center">
                        <label
                            for="{{ $id }}"
                            class="relative cursor-pointer rounded-md font-semibold text-primary-600 focus-within:outline-none hover:text-primary-500"
                        >
                            <span>Upload {{ $props['multiple'] ? 'files' : 'a file' }}</span>
                        </label>
                        <p class="pl-1">or drag and drop</p>
                    </div>

                    <p class="text-xs leading-5 text-gray-600 dark:text-gray-400">
                        @if(!empty($props['acceptedFileTypes']))
                            {{ implode(', ', $props['acceptedFileTypes']) }}
                        @endif
                        @if($props['maxSize'])
                            up to {{ $props['maxSize'] }}KB
                        @endif
                    </p>
                </div>
            </div>

            {{-- File Previews --}}
            <div
                x-show="previews.length > 0"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <template x-for="(preview, index) in previews" :key="index">
                    <div class="relative rounded-lg border border-gray-300 p-4 dark:border-gray-600">
                        {{-- Image Preview --}}
                        <template x-if="preview.type === 'image'">
                            <img
                                :src="preview.url"
                                :alt="preview.name"
                                class="h-32 w-full object-cover rounded-lg mb-2"
                            />
                        </template>

                        {{-- File Icon --}}
                        <template x-if="preview.type === 'file'">
                            <div class="h-32 flex items-center justify-center bg-gray-100 rounded-lg mb-2 dark:bg-gray-800">
                                <svg class="h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </template>

                        {{-- File Name --}}
                        <p class="text-sm font-medium text-gray-900 truncate dark:text-white" x-text="preview.name"></p>
                        <p class="text-xs text-gray-500" x-text="formatFileSize(files[index].size)"></p>

                        {{-- Remove Button --}}
                        <button
                            type="button"
                            @click="removeFile(index)"
                            class="absolute top-2 right-2 rounded-full bg-white p-1 text-red-500 shadow-sm hover:bg-red-50 dark:bg-gray-800 dark:hover:bg-red-900"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        @endif

        {{-- Hidden File Input --}}
        <input
            type="file"
            id="{{ $id }}"
            name="{{ $props['name'] }}{{ $props['multiple'] ? '[]' : '' }}"
            @if($props['multiple'])
                multiple
            @endif
            @if($accept)
                accept="{{ $accept }}"
            @endif
            @if($props['required'])
                required
            @endif
            @if($props['disabled'])
                disabled
            @endif
            @change="handleFiles($event.target.files)"
            class="sr-only"
        />
    </div>
</x-laravilt-field-wrapper>
