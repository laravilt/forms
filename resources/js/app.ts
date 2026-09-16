// Laravilt Forms Package Entry Point
import Form from './components/Form.vue'
import FieldWrapper from './components/FieldWrapper.vue'
import TextInput from './components/fields/TextInput.vue'
import Textarea from './components/fields/Textarea.vue'
import TranslatableInput from './components/fields/TranslatableInput.vue'
import Select from './components/fields/Select.vue'
import Checkbox from './components/fields/Checkbox.vue'
import CheckboxList from './components/fields/CheckboxList.vue'
import Radio from './components/fields/Radio.vue'
import Toggle from './components/fields/Toggle.vue'
import ToggleButtons from './components/fields/ToggleButtons.vue'
import Hidden from './components/fields/Hidden.vue'
import DatePicker from './components/fields/DatePicker.vue'
import TimePicker from './components/fields/TimePicker.vue'
import DateTimePicker from './components/fields/DateTimePicker.vue'
import DateRangePicker from './components/fields/DateRangePicker.vue'
import FileUpload from './components/fields/FileUpload.vue'
import RichEditor from './components/fields/RichEditor.vue'
import MarkdownEditor from './components/fields/MarkdownEditor.vue'
import CodeEditor from './components/fields/CodeEditor.vue'
import ColorPicker from './components/fields/ColorPicker.vue'
import TagsInput from './components/fields/TagsInput.vue'
import KeyValue from './components/fields/KeyValue.vue'
import Repeater from './components/fields/Repeater.vue'
import Builder from './components/fields/Builder.vue'
import Slider from './components/fields/Slider.vue'
import IconPicker from './components/fields/IconPicker.vue'
import NumberField from './components/fields/NumberField.vue'
import PinInput from './components/fields/PinInput.vue'
import RateInput from './components/fields/RateInput.vue'
import Tabs from './components/schema/Tabs.vue'
import Section from './components/schema/Section.vue'
import Grid from './components/schema/Grid.vue'

export { Form, Select, TextInput }

export default {
    install(app, options = {}) {
        // Register global components with laravilt- prefix for LaraviltComponentRenderer
        app.component('laravilt-form', Form)
        app.component('laravilt-field-wrapper', FieldWrapper)

        // Field components
        app.component('laravilt-text-input', TextInput)
        app.component('laravilt-textarea', Textarea)
        app.component('laravilt-translatable-input', TranslatableInput)
        app.component('laravilt-select', Select)
        app.component('laravilt-checkbox', Checkbox)
        app.component('laravilt-checkbox-list', CheckboxList)
        app.component('laravilt-radio', Radio)
        app.component('laravilt-toggle', Toggle)
        app.component('laravilt-toggle-buttons', ToggleButtons)
        app.component('laravilt-hidden', Hidden)
        app.component('laravilt-date-picker', DatePicker)
        app.component('laravilt-time-picker', TimePicker)
        app.component('laravilt-datetime-picker', DateTimePicker)
        app.component('laravilt-date-range-picker', DateRangePicker)
        app.component('laravilt-file-upload', FileUpload)
        app.component('laravilt-rich-editor', RichEditor)
        app.component('laravilt-markdown-editor', MarkdownEditor)
        app.component('laravilt-code-editor', CodeEditor)
        app.component('laravilt-color-picker', ColorPicker)
        app.component('laravilt-tags-input', TagsInput)
        app.component('laravilt-key-value', KeyValue)
        app.component('laravilt-repeater', Repeater)
        app.component('laravilt-builder', Builder)
        app.component('laravilt-slider', Slider)
        app.component('laravilt-icon-picker', IconPicker)
        app.component('laravilt-number-field', NumberField)
        app.component('laravilt-pin-input', PinInput)
        app.component('laravilt-rate-input', RateInput)

        // Schema components
        app.component('laravilt-tabs', Tabs)
        app.component('laravilt-section', Section)
        app.component('laravilt-grid', Grid)

        // Legacy name support
        app.component('Form', Form)
    }
}
