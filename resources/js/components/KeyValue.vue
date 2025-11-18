<template>
    <div class="laravilt-key-value">
        <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div class="space-y-2">
            <!-- Key-Value Pairs -->
            <div
                v-for="(pair, index) in pairs"
                :key="pair._id"
                class="flex items-center gap-2"
            >
                <!-- Reorder Handle -->
                <button
                    v-if="reorderable && pairs.length > 1"
                    type="button"
                    class="drag-handle cursor-move text-gray-400 hover:text-gray-600 p-1"
                    @mousedown="startDrag(index)"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z" />
                    </svg>
                </button>

                <!-- Key Input -->
                <div class="flex-1">
                    <input
                        type="text"
                        v-model="pair.key"
                        @input="handleKeyInput(index)"
                        @blur="validateKey(index)"
                        :placeholder="keyPlaceholder || keyLabel"
                        :disabled="disabled"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-white"
                        :class="{'border-red-500': pair.keyError}"
                    />
                    <p v-if="pair.keyError" class="mt-1 text-xs text-red-600">{{ pair.keyError }}</p>
                </div>

                <!-- Value Input -->
                <div class="flex-1">
                    <input
                        type="text"
                        v-model="pair.value"
                        @input="handleValueInput(index)"
                        :placeholder="valuePlaceholder || valueLabel"
                        :disabled="disabled"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <!-- Remove Button -->
                <button
                    v-if="canRemovePair"
                    type="button"
                    @click="removePair(index)"
                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded"
                    title="Remove pair"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <!-- Add Button -->
            <button
                v-if="canAddPair"
                type="button"
                @click="addPair"
                class="w-full py-2 px-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-400 hover:border-primary-500 hover:text-primary-600 transition-colors"
            >
                <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add Pair
            </button>
        </div>

        <p v-if="helperText" class="mt-2 text-sm text-gray-500">{{ helperText }}</p>

        <!-- Hidden inputs for form submission -->
        <template v-for="(pair, index) in pairs" :key="`hidden-${index}`">
            <input
                type="hidden"
                :name="`${name}[${pair.key}]`"
                :value="pair.value"
            />
        </template>
    </div>
</template>

<script>
export default {
    name: 'LaraviltKeyValue',
    props: {
        id: String,
        name: String,
        label: String,
        modelValue: {
            type: Object,
            default: () => ({})
        },
        helperText: String,
        required: Boolean,
        disabled: Boolean,
        keyLabel: {
            type: String,
            default: 'Key'
        },
        valueLabel: {
            type: String,
            default: 'Value'
        },
        keyPlaceholder: String,
        valuePlaceholder: String,
        uniqueKeys: {
            type: Boolean,
            default: true
        },
        reorderable: {
            type: Boolean,
            default: false
        },
        minPairs: Number,
        maxPairs: Number,
    },
    emits: ['update:modelValue', 'change'],
    data() {
        return {
            pairs: [],
            draggedIndex: null,
        };
    },
    computed: {
        canAddPair() {
            return !this.maxPairs || this.pairs.length < this.maxPairs;
        },
        canRemovePair() {
            return !this.minPairs || this.pairs.length > this.minPairs;
        }
    },
    watch: {
        modelValue: {
            immediate: true,
            handler(newValue) {
                if (typeof newValue === 'object' && newValue !== null) {
                    this.pairs = Object.entries(newValue).map(([key, value], index) => ({
                        _id: `pair-${Date.now()}-${index}`,
                        key,
                        value,
                        keyError: null
                    }));
                }
            }
        },
        pairs: {
            deep: true,
            handler(newPairs) {
                const obj = {};
                newPairs.forEach(pair => {
                    if (pair.key) {
                        obj[pair.key] = pair.value;
                    }
                });
                this.$emit('update:modelValue', obj);
                this.$emit('change', obj);
            }
        }
    },
    methods: {
        addPair() {
            if (!this.canAddPair) return;

            this.pairs.push({
                _id: `pair-${Date.now()}-${this.pairs.length}`,
                key: '',
                value: '',
                keyError: null
            });
        },
        removePair(index) {
            if (!this.canRemovePair) return;

            this.pairs.splice(index, 1);
        },
        handleKeyInput(index) {
            this.pairs[index].keyError = null;
        },
        handleValueInput(index) {
            // Update value
        },
        validateKey(index) {
            if (!this.uniqueKeys) return;

            const currentKey = this.pairs[index].key;
            if (!currentKey) return;

            const duplicateIndex = this.pairs.findIndex(
                (pair, i) => i !== index && pair.key === currentKey
            );

            if (duplicateIndex !== -1) {
                this.pairs[index].keyError = 'Key must be unique';
            } else {
                this.pairs[index].keyError = null;
            }
        },
        startDrag(index) {
            this.draggedIndex = index;
            document.addEventListener('mousemove', this.onDrag);
            document.addEventListener('mouseup', this.endDrag);
        },
        onDrag(event) {
            // Implement drag logic
        },
        endDrag() {
            this.draggedIndex = null;
            document.removeEventListener('mousemove', this.onDrag);
            document.removeEventListener('mouseup', this.endDrag);
        }
    },
    mounted() {
        // Initialize with at least one pair if empty
        if (this.pairs.length === 0) {
            this.addPair();
        }
    }
}
</script>
