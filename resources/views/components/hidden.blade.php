<x-laravilt-component name="hidden" :data="$component->toLaraviltProps()">
    <input
        :id="id"
        :name="name"
        type="hidden"
        v-model="inputValue"
    />
</x-laravilt-component>

<script>
export default {
    props: {
        id: String,
        name: String,
        value: [String, Number],
        defaultValue: [String, Number],
    },

    data() {
        return {
            inputValue: this.value || this.defaultValue || '',
        };
    },

    watch: {
        value(newValue) {
            this.inputValue = newValue;
        }
    },

    methods: {
        updateValue(newValue) {
            this.inputValue = newValue;
            this.$emit('input', this.inputValue);
            this.$emit('update:modelValue', this.inputValue);
        }
    }
}
</script>
