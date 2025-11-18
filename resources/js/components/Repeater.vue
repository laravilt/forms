<template>
    <div class="laravilt-repeater">
        <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div class="space-y-3">
            <!-- Repeater Items -->
            <div
                v-for="(item, index) in items"
                :key="item._id"
                class="repeater-item border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden"
                :class="{'bg-gray-50 dark:bg-gray-800': collapsible && isCollapsed(index)}"
            >
                <!-- Item Header -->
                <div
                    v-if="collapsible || orderable || showItemNumbers"
                    class="item-header bg-gray-100 dark:bg-gray-700 px-4 py-2 flex items-center justify-between border-b border-gray-300 dark:border-gray-600"
                >
                    <div class="flex items-center gap-3">
                        <!-- Drag Handle -->
                        <button
                            v-if="orderable && items.length > 1"
                            type="button"
                            class="drag-handle cursor-move text-gray-400 hover:text-gray-600"
                            @mousedown="startDrag(index)"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z" />
                            </svg>
                        </button>

                        <!-- Item Number/Label -->
                        <span v-if="showItemNumbers" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Item {{ index + 1 }}
                        </span>

                        <!-- Collapse Toggle -->
                        <button
                            v-if="collapsible"
                            type="button"
                            @click="toggleCollapse(index)"
                            class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                        >
                            <svg class="w-5 h-5 transition-transform" :class="{'rotate-180': !isCollapsed(index)}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <!-- Remove Button -->
                    <button
                        v-if="canRemoveItem"
                        type="button"
                        @click="removeItem(index)"
                        class="text-red-600 hover:text-red-800"
                        title="Remove item"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Item Content -->
                <div v-show="!isCollapsed(index)" class="item-content p-4">
                    <div
                        class="grid gap-4"
                        :class="`grid-cols-${gridColumns}`"
                    >
                        <slot name="item" :item="item" :index="index">
                            <!-- Render schema fields here -->
                            <div v-for="field in schema" :key="field.name" class="field">
                                <input
                                    :type="field.type || 'text'"
                                    :name="`${name}[${index}][${field.name}]`"
                                    v-model="item[field.name]"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    :placeholder="field.placeholder"
                                />
                            </div>
                        </slot>
                    </div>
                </div>
            </div>

            <!-- Add Button -->
            <button
                v-if="canAddItem"
                type="button"
                @click="addItem"
                class="w-full py-2 px-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-400 hover:border-primary-500 hover:text-primary-600 transition-colors"
            >
                <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                {{ addButtonLabel }}
            </button>
        </div>

        <p v-if="helperText" class="mt-2 text-sm text-gray-500">{{ helperText }}</p>
        <p v-if="hasError && errorMessage" class="mt-2 text-sm text-red-600">{{ errorMessage }}</p>
    </div>
</template>

<script>
export default {
    name: 'LaraviltRepeater',
    props: {
        id: String,
        name: String,
        label: String,
        modelValue: {
            type: Array,
            default: () => []
        },
        helperText: String,
        required: Boolean,
        schema: {
            type: Array,
            default: () => []
        },
        minItems: Number,
        maxItems: Number,
        defaultItems: {
            type: Number,
            default: 0
        },
        orderable: {
            type: Boolean,
            default: true
        },
        collapsible: {
            type: Boolean,
            default: false
        },
        collapsed: {
            type: Boolean,
            default: false
        },
        addButtonLabel: {
            type: String,
            default: 'Add item'
        },
        showItemNumbers: {
            type: Boolean,
            default: true
        },
        gridColumns: {
            type: Number,
            default: 1
        },
    },
    emits: ['update:modelValue', 'change'],
    data() {
        return {
            items: [],
            collapsedItems: new Set(),
            hasError: false,
            errorMessage: '',
            draggedIndex: null,
        };
    },
    computed: {
        canAddItem() {
            return !this.maxItems || this.items.length < this.maxItems;
        },
        canRemoveItem() {
            return !this.minItems || this.items.length > this.minItems;
        }
    },
    watch: {
        modelValue: {
            immediate: true,
            handler(newValue) {
                if (Array.isArray(newValue)) {
                    this.items = newValue.map((item, index) => ({
                        ...item,
                        _id: item._id || `item-${Date.now()}-${index}`
                    }));
                }
            }
        },
        items: {
            deep: true,
            handler(newItems) {
                const cleanItems = newItems.map(item => {
                    const { _id, ...rest } = item;
                    return rest;
                });
                this.$emit('update:modelValue', cleanItems);
                this.$emit('change', cleanItems);
            }
        }
    },
    mounted() {
        // Initialize with default items if needed
        if (this.items.length === 0 && this.defaultItems > 0) {
            for (let i = 0; i < this.defaultItems; i++) {
                this.addItem();
            }
        }

        // Collapse items if needed
        if (this.collapsed) {
            this.items.forEach((_, index) => {
                this.collapsedItems.add(index);
            });
        }
    },
    methods: {
        addItem() {
            if (!this.canAddItem) return;

            const newItem = {
                _id: `item-${Date.now()}-${this.items.length}`
            };

            // Initialize fields from schema
            this.schema.forEach(field => {
                newItem[field.name] = field.defaultValue || '';
            });

            this.items.push(newItem);

            // Collapse if needed
            if (this.collapsed) {
                this.collapsedItems.add(this.items.length - 1);
            }
        },
        removeItem(index) {
            if (!this.canRemoveItem) return;

            this.items.splice(index, 1);
            this.collapsedItems.delete(index);

            // Adjust collapsed items indices
            const newCollapsed = new Set();
            this.collapsedItems.forEach(i => {
                if (i > index) {
                    newCollapsed.add(i - 1);
                } else if (i < index) {
                    newCollapsed.add(i);
                }
            });
            this.collapsedItems = newCollapsed;
        },
        toggleCollapse(index) {
            if (this.collapsedItems.has(index)) {
                this.collapsedItems.delete(index);
            } else {
                this.collapsedItems.add(index);
            }
            this.collapsedItems = new Set(this.collapsedItems);
        },
        isCollapsed(index) {
            return this.collapsedItems.has(index);
        },
        startDrag(index) {
            this.draggedIndex = index;
            // Add drag event listeners
            document.addEventListener('mousemove', this.onDrag);
            document.addEventListener('mouseup', this.endDrag);
        },
        onDrag(event) {
            // Simple reordering logic
            // In production, you'd use a library like Sortable.js
        },
        endDrag() {
            this.draggedIndex = null;
            document.removeEventListener('mousemove', this.onDrag);
            document.removeEventListener('mouseup', this.endDrag);
        }
    }
}
</script>
