<template>
    <input
        :id="id"
        :name="name"
        type="hidden"
        v-model="inputValue"
    />
</template>

<script>
export default {
    name: 'LaraviltHidden',

    props: {
        id: String,
        name: String,
        modelValue: [String, Number],
        defaultValue: [String, Number],
    },

    emits: ['update:modelValue'],

    data() {
        return {
            inputValue: this.modelValue || this.defaultValue || '',
        };
    },

    watch: {
        modelValue(newValue) {
            this.inputValue = newValue;
        }
    },

    methods: {
        updateValue(newValue) {
            this.inputValue = newValue;
            this.$emit('update:modelValue', this.inputValue);
        }
    }
}
</script>
