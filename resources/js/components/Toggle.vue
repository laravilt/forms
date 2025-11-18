<template>
    <div class="laravilt-toggle" :class="{'rtl': rtl}" :dir="rtl ? 'rtl' : 'ltr'">
        <div class="flex items-center justify-between">
            <!-- Label -->
            <label
                v-if="label"
                :for="id"
                class="text-sm font-medium text-gray-700 dark:text-gray-300"
            >
                {{ label }}
                <span v-if="required" class="text-red-500">*</span>
            </label>

            <!-- Toggle Switch -->
            <button
                type="button"
                :id="id"
                role="switch"
                :aria-checked="isOn"
                :disabled="disabled"
                @click="toggle"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2"
                :class="[
                    isOn ? `bg-${onColor}-600 focus:ring-${onColor}-500` : `bg-${offColor}-200 dark:bg-${offColor}-700`,
                    { 'opacity-50 cursor-not-allowed': disabled }
                ]"
            >
                <span class="sr-only">{{ label }}</span>
                <span
                    :aria-hidden="true"
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                    :class="{
                        'translate-x-5': isOn && !rtl,
                        '-translate-x-5': isOn && rtl,
                        'translate-x-0': !isOn
                    }"
                ></span>
            </button>
        </div>

        <!-- State Labels -->
        <div v-if="onLabel || offLabel" class="mt-1 text-xs text-gray-500">
            <span v-if="isOn && onLabel">{{ onLabel }}</span>
            <span v-if="!isOn && offLabel">{{ offLabel }}</span>
        </div>

        <!-- Helper text -->
        <p v-if="helperText && !hasError" class="mt-1 text-sm text-gray-500">
            {{ helperText }}
        </p>

        <!-- Error message -->
        <p v-if="hasError" class="mt-1 text-sm text-red-600">
            {{ errorMessage }}
        </p>

        <!-- Hidden input -->
        <input type="hidden" :name="name" :value="toggleValue" />
    </div>
</template>

<script>
export default {
    name: 'LaraviltToggle',

    props: {
        id: String,
        name: String,
        modelValue: [Boolean, String, Number],
        label: String,
        helperText: String,
        required: Boolean,
        disabled: Boolean,
        onValue: { default: true },
        offValue: { default: false },
        onLabel: String,
        offLabel: String,
        onColor: { type: String, default: 'primary' },
        offColor: { type: String, default: 'gray' },
        validation: [String, Array, Object],
        validationMessages: Object,
        rtl: Boolean,
        theme: String,
    },

    emits: ['update:modelValue', 'change'],

    data() {
        return {
            toggleValue: this.modelValue !== undefined ? this.modelValue : this.offValue,
            hasError: false,
            errorMessage: '',
        };
    },

    computed: {
        isOn() {
            return this.toggleValue === this.onValue;
        }
    },

    watch: {
        modelValue(newValue) {
            this.toggleValue = newValue;
        }
    },

    methods: {
        toggle() {
            if (this.disabled) return;

            this.toggleValue = this.isOn ? this.offValue : this.onValue;
            this.$emit('update:modelValue', this.toggleValue);
            this.$emit('change', this.toggleValue);
        }
    }
}
</script>
