<x-laravilt-component name="file-upload" :data="$component->toLaraviltProps()">
    <div class="laravilt-file-upload" :class="{'rtl': rtl}" :dir="rtl ? 'rtl' : 'ltr'">
        <!-- Label -->
        <label
            v-if="label"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
        >
            @{{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Dropzone -->
        <div
            @click="openFilePicker"
            @drop.prevent="handleDrop"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            class="dropzone border-2 border-dashed rounded-lg p-6 text-center transition-all duration-200 cursor-pointer"
            :class="{
                'border-primary-500 bg-primary-50 dark:bg-primary-900': isDragging,
                'border-gray-300 hover:border-primary-400': !isDragging && !hasError,
                'border-red-500': hasError,
                'opacity-50 cursor-not-allowed': disabled
            }"
        >
            <!-- Upload Icon -->
            <div v-if="!avatar" class="flex justify-center mb-3">
                <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
            </div>

            <!-- Avatar Preview -->
            <div v-if="avatar && previewUrl" class="flex justify-center mb-3">
                <img
                    :src="previewUrl"
                    alt="Avatar preview"
                    class="h-24 w-24 rounded-full object-cover border-2 border-gray-200"
                />
            </div>

            <!-- Upload Text -->
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-medium text-primary-600 dark:text-primary-400">
                    {{ __('forms::fields.file_upload.drop_files') }}
                </span>
            </p>

            <!-- File Info -->
            <p class="text-xs text-gray-500 mt-2">
                <span v-if="maxSize">
                    {{ __('forms::fields.file_upload.max_size', ['size' => formatFileSize(maxSize)]) }}
                </span>
                <span v-if="maxFiles && multiple">
                    · {{ __('forms::fields.file_upload.max_files', ['max' => maxFiles]) }}
                </span>
            </p>

            <!-- Accepted file types -->
            <p v-if="acceptedFileTypes.length" class="text-xs text-gray-500 mt-1">
                @{{ acceptedFileTypes.join(', ') }}
            </p>
        </div>

        <!-- Hidden File Input -->
        <input
            ref="fileInput"
            type="file"
            :id="id"
            :name="name"
            :multiple="multiple"
            :accept="acceptedFileTypes.join(',')"
            :required="required"
            :disabled="disabled"
            @change="handleFileSelect"
            class="hidden"
        />

        <!-- File List -->
        <div v-if="files.length > 0" class="mt-4 space-y-2">
            <div
                v-for="(file, index) in files"
                :key="index"
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
            >
                <!-- File Info -->
                <div class="flex items-center flex-1 min-w-0">
                    <!-- Preview for images -->
                    <div v-if="file.preview" class="flex-shrink-0 mr-3">
                        <img
                            :src="file.preview"
                            :alt="file.name"
                            class="h-10 w-10 rounded object-cover"
                        />
                    </div>

                    <!-- File icon for non-images -->
                    <div v-else class="flex-shrink-0 mr-3">
                        <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            @{{ file.name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            @{{ formatFileSize(file.size) }}
                        </p>
                    </div>
                </div>

                <!-- Upload Progress -->
                <div v-if="file.uploading" class="ml-3">
                    <div class="flex items-center">
                        <div class="animate-spin h-5 w-5 border-2 border-primary-500 border-t-transparent rounded-full"></div>
                        <span class="ml-2 text-xs text-gray-500">@{{ file.progress }}%</span>
                    </div>
                </div>

                <!-- Remove Button -->
                <button
                    v-else
                    type="button"
                    @click="removeFile(index)"
                    class="ml-3 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Helper text -->
        <p v-if="helperText && !hasError" class="mt-2 text-sm text-gray-500">
            @{{ helperText }}
        </p>

        <!-- Error message -->
        <p v-if="hasError" class="mt-2 text-sm text-red-600">
            @{{ errorMessage }}
        </p>
    </div>
</x-laravilt-component>

<script>
export default {
    props: {
        id: String,
        name: String,
        value: [String, Array, File, FileList],
        label: String,
        helperText: String,
        required: Boolean,
        disabled: Boolean,
        multiple: Boolean,
        maxFiles: Number,
        maxSize: Number, // in KB
        acceptedFileTypes: {
            type: Array,
            default: () => []
        },
        image: Boolean,
        avatar: Boolean,
        directory: String,
        disk: {
            type: String,
            default: 'public'
        },
        validation: [String, Array, Object],
        validationMessages: Object,
        rtl: Boolean,
        theme: String,
    },

    data() {
        return {
            files: [],
            isDragging: false,
            hasError: false,
            errorMessage: '',
            previewUrl: null,
        };
    },

    methods: {
        openFilePicker() {
            if (!this.disabled) {
                this.$refs.fileInput.click();
            }
        },

        handleFileSelect(event) {
            const selectedFiles = Array.from(event.target.files);
            this.processFiles(selectedFiles);
        },

        handleDrop(event) {
            this.isDragging = false;
            if (this.disabled) return;

            const droppedFiles = Array.from(event.dataTransfer.files);
            this.processFiles(droppedFiles);
        },

        processFiles(newFiles) {
            // Check max files
            if (this.maxFiles && (this.files.length + newFiles.length) > this.maxFiles) {
                this.hasError = true;
                this.errorMessage = `Maximum ${this.maxFiles} files allowed.`;
                return;
            }

            // Process each file
            newFiles.forEach(file => {
                // Check file size
                if (this.maxSize && file.size > this.maxSize * 1024) {
                    this.hasError = true;
                    this.errorMessage = `File ${file.name} exceeds maximum size of ${this.formatFileSize(this.maxSize * 1024)}.`;
                    return;
                }

                // Check file type
                if (this.acceptedFileTypes.length > 0) {
                    const fileType = file.type;
                    const isAccepted = this.acceptedFileTypes.some(type => {
                        if (type.endsWith('/*')) {
                            return fileType.startsWith(type.replace('/*', ''));
                        }
                        return fileType === type || file.name.endsWith(type);
                    });

                    if (!isAccepted) {
                        this.hasError = true;
                        this.errorMessage = `File type ${file.type} is not accepted.`;
                        return;
                    }
                }

                // Create preview for images
                let preview = null;
                if (this.image || this.avatar) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const fileObj = this.files.find(f => f.name === file.name);
                        if (fileObj) {
                            fileObj.preview = e.target.result;
                            if (this.avatar) {
                                this.previewUrl = e.target.result;
                            }
                        }
                    };
                    reader.readAsDataURL(file);
                }

                // Add file to list
                this.files.push({
                    file: file,
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    preview: preview,
                    uploading: false,
                    progress: 0,
                });

                // Clear error
                this.hasError = false;
                this.errorMessage = '';
            });

            // Emit files
            this.emitFiles();
        },

        removeFile(index) {
            this.files.splice(index, 1);
            if (this.avatar && this.files.length === 0) {
                this.previewUrl = null;
            }
            this.emitFiles();
        },

        emitFiles() {
            const fileData = this.multiple
                ? this.files.map(f => f.file)
                : (this.files[0]?.file || null);

            this.$emit('input', fileData);
            this.$emit('update:modelValue', fileData);
            this.$emit('change', fileData);
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        },

        validateField() {
            if (this.required && this.files.length === 0) {
                this.hasError = true;
                this.errorMessage = this.validationMessages?.required || 'Please upload a file.';
                return false;
            }

            this.hasError = false;
            this.errorMessage = '';
            return true;
        }
    }
}
</script>
