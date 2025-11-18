<template>
    <div class="laravilt-color-picker">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div class="flex items-center gap-3">
            <!-- Color Preview -->
            <div
                class="color-preview w-12 h-12 rounded-lg border-2 border-gray-300 dark:border-gray-600 cursor-pointer"
                :style="{ backgroundColor: displayColor }"
                @click="togglePicker"
            ></div>

            <!-- Color Input -->
            <input
                :id="id"
                :name="name"
                type="text"
                v-model="colorValue"
                @input="handleInput"
                @blur="handleBlur"
                @focus="handleFocus"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-white"
                :class="{'border-red-500': hasError}"
            />

            <!-- Native Color Picker -->
            <input
                type="color"
                v-model="nativeColor"
                @input="handleNativeInput"
                :disabled="disabled"
                class="w-12 h-12 cursor-pointer"
            />
        </div>

        <!-- Color Picker Panel -->
        <div
            v-if="showPicker && !disabled"
            class="color-picker-panel mt-2 p-4 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg"
        >
            <!-- Swatches -->
            <div v-if="showSwatches && swatches.length > 0" class="swatches mb-4">
                <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Presets</p>
                <div class="grid grid-cols-8 gap-2">
                    <button
                        v-for="(swatch, index) in swatches"
                        :key="index"
                        type="button"
                        class="w-8 h-8 rounded border-2 border-gray-300 dark:border-gray-600 hover:scale-110 transition-transform"
                        :class="{'ring-2 ring-primary-500': colorValue === swatch}"
                        :style="{ backgroundColor: swatch }"
                        @click="selectSwatch(swatch)"
                    ></button>
                </div>
            </div>

            <!-- Alpha Slider -->
            <div v-if="alpha" class="alpha-slider mt-4">
                <label class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 block">
                    Opacity: {{ alphaValue }}%
                </label>
                <input
                    type="range"
                    min="0"
                    max="100"
                    v-model="alphaValue"
                    @input="updateAlpha"
                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                />
            </div>

            <!-- Format Selector -->
            <div class="format-selector mt-4 flex gap-2">
                <button
                    v-for="fmt in ['hex', 'rgb', 'hsl']"
                    :key="fmt"
                    type="button"
                    @click="changeFormat(fmt)"
                    class="px-3 py-1 text-xs rounded"
                    :class="{
                        'bg-primary-600 text-white': format === fmt,
                        'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300': format !== fmt
                    }"
                >
                    {{ fmt.toUpperCase() }}
                </button>
            </div>
        </div>

        <p v-if="helperText" class="mt-1 text-sm text-gray-500">{{ helperText }}</p>
        <p v-if="hasError && errorMessage" class="mt-1 text-sm text-red-600">{{ errorMessage }}</p>
    </div>
</template>

<script>
export default {
    name: 'LaraviltColorPicker',
    props: {
        id: String,
        name: String,
        label: String,
        modelValue: String,
        helperText: String,
        placeholder: {
            type: String,
            default: '#000000'
        },
        required: Boolean,
        disabled: Boolean,
        readonly: Boolean,
        alpha: {
            type: Boolean,
            default: false
        },
        format: {
            type: String,
            default: 'hex',
            validator: (value) => ['hex', 'rgb', 'hsl'].includes(value)
        },
        swatches: {
            type: Array,
            default: () => []
        },
        showSwatches: {
            type: Boolean,
            default: false
        },
    },
    emits: ['update:modelValue', 'blur', 'focus', 'change'],
    data() {
        return {
            colorValue: this.modelValue || this.placeholder,
            nativeColor: this.modelValue || this.placeholder,
            alphaValue: 100,
            showPicker: false,
            hasError: false,
            errorMessage: '',
        };
    },
    computed: {
        displayColor() {
            return this.colorValue || this.placeholder;
        }
    },
    watch: {
        modelValue(newValue) {
            if (newValue !== this.colorValue) {
                this.colorValue = newValue || this.placeholder;
                this.nativeColor = this.extractHexColor(newValue);
            }
        }
    },
    methods: {
        handleInput(event) {
            this.colorValue = event.target.value;
            this.$emit('update:modelValue', this.colorValue);
            this.$emit('change', this.colorValue);
        },
        handleNativeInput(event) {
            this.nativeColor = event.target.value;
            this.colorValue = this.formatColor(event.target.value);
            this.$emit('update:modelValue', this.colorValue);
            this.$emit('change', this.colorValue);
        },
        handleBlur(event) {
            this.$emit('blur', event);
        },
        handleFocus(event) {
            this.$emit('focus', event);
        },
        togglePicker() {
            if (!this.disabled) {
                this.showPicker = !this.showPicker;
            }
        },
        selectSwatch(color) {
            this.colorValue = color;
            this.nativeColor = this.extractHexColor(color);
            this.$emit('update:modelValue', this.colorValue);
            this.$emit('change', this.colorValue);
        },
        updateAlpha() {
            this.colorValue = this.formatColor(this.nativeColor);
            this.$emit('update:modelValue', this.colorValue);
            this.$emit('change', this.colorValue);
        },
        changeFormat(newFormat) {
            this.format = newFormat;
            this.colorValue = this.formatColor(this.nativeColor);
            this.$emit('update:modelValue', this.colorValue);
        },
        formatColor(hexColor) {
            switch (this.format) {
                case 'rgb':
                    return this.hexToRgb(hexColor);
                case 'hsl':
                    return this.hexToHsl(hexColor);
                case 'hex':
                default:
                    return hexColor;
            }
        },
        extractHexColor(color) {
            // Extract hex from rgb, rgba, hsl, etc.
            if (color.startsWith('#')) {
                return color;
            }
            // Simple conversion for demonstration
            return color;
        },
        hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            if (!result) return hex;

            const r = parseInt(result[1], 16);
            const g = parseInt(result[2], 16);
            const b = parseInt(result[3], 16);

            if (this.alpha) {
                const a = this.alphaValue / 100;
                return `rgba(${r}, ${g}, ${b}, ${a})`;
            }

            return `rgb(${r}, ${g}, ${b})`;
        },
        hexToHsl(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            if (!result) return hex;

            let r = parseInt(result[1], 16) / 255;
            let g = parseInt(result[2], 16) / 255;
            let b = parseInt(result[3], 16) / 255;

            const max = Math.max(r, g, b);
            const min = Math.min(r, g, b);
            let h, s, l = (max + min) / 2;

            if (max === min) {
                h = s = 0;
            } else {
                const d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                switch (max) {
                    case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
                    case g: h = ((b - r) / d + 2) / 6; break;
                    case b: h = ((r - g) / d + 4) / 6; break;
                }
            }

            h = Math.round(h * 360);
            s = Math.round(s * 100);
            l = Math.round(l * 100);

            if (this.alpha) {
                const a = this.alphaValue / 100;
                return `hsla(${h}, ${s}%, ${l}%, ${a})`;
            }

            return `hsl(${h}, ${s}%, ${l}%)`;
        }
    }
}
</script>
