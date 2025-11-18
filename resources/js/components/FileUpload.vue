<template>
    <div class="laravilt-file-upload" :class="{'rtl': rtl}" :dir="rtl ? 'rtl' : 'ltr'">
        <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

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
            <div v-if="!avatar" class="flex justify-center mb-3">
                <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
            </div>

            <div v-if="avatar && previewUrl" class="flex justify-center mb-3">
                <img :src="previewUrl" alt="Avatar preview" class="h-24 w-24 rounded-full object-cover border-2 border-gray-200" />
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-medium text-primary-600 dark:text-primary-400">
                    Drop files here or click to browse
                </span>
            </p>

            <p class="text-xs text-gray-500 mt-2">
                <span v-if="maxSize">Max size: {{ formatFileSize(maxSize * 1024) }}</span>
                <span v-if="maxFiles && multiple"> · Max {{ maxFiles }} files</span>
            </p>

            <p v-if="acceptedFileTypes.length" class="text-xs text-gray-500 mt-1">
                {{ acceptedFileTypes.join(', ') }}
            </p>
        </div>

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

        <div v-if="files.length > 0" class="mt-4 space-y-2">
            <div
                v-for="(file, index) in files"
                :key="index"
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
            >
                <div class="flex items-center flex-1 min-w-0">
                    <div v-if="file.preview" class="flex-shrink-0 mr-3">
                        <img :src="file.preview" :alt="file.name" class="h-10 w-10 rounded object-cover" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ file.name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ formatFileSize(file.size) }}
                        </p>
                    </div>
                </div>

                <button
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

        <p v-if="helperText && !hasError" class="mt-2 text-sm text-gray-500">{{ helperText }}</p>
        <p v-if="hasError" class="mt-2 text-sm text-red-600">{{ errorMessage }}</p>
    </div>
</template>

<script>
export default {
    name: 'LaraviltFileUpload',

    props: {
        id: String,
        name: String,
        modelValue: [String, Array, File, FileList],
        label: String,
        helperText: String,
        required: Boolean,
        disabled: Boolean,
        multiple: Boolean,
        maxFiles: Number,
        maxSize: Number,
        acceptedFileTypes: { type: Array, default: () => [] },
        image: Boolean,
        avatar: Boolean,
        directory: String,
        disk: { type: String, default: 'public' },
        validation: [String, Array, Object],
        validationMessages: Object,
        rtl: Boolean,
        theme: String,
    },

    emits: ['update:modelValue', 'change'],

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
            if (this.maxFiles && (this.files.length + newFiles.length) > this.maxFiles) {
                this.hasError = true;
                this.errorMessage = `Maximum ${this.maxFiles} files allowed.`;
                return;
            }

            newFiles.forEach(file => {
                if (this.maxSize && file.size > this.maxSize * 1024) {
                    this.hasError = true;
                    this.errorMessage = `File ${file.name} exceeds maximum size.`;
                    return;
                }

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

                this.files.push({
                    file: file,
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    preview: preview,
                });

                this.hasError = false;
                this.errorMessage = '';
            });

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

            this.$emit('update:modelValue', fileData);
            this.$emit('change', fileData);
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    }
}
</script>
