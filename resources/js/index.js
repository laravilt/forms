// Laravilt Forms - Main Entry Point

// Import CSS
import '../css/forms.css';

// Export basic components
export { default as TextInput } from './components/TextInput.vue';
export { default as Textarea } from './components/Textarea.vue';
export { default as Select } from './components/Select.vue';
export { default as Checkbox } from './components/Checkbox.vue';
export { default as Radio } from './components/Radio.vue';
export { default as Toggle } from './components/Toggle.vue';
export { default as DatePicker } from './components/DatePicker.vue';
export { default as FileUpload } from './components/FileUpload.vue';
export { default as Hidden } from './components/Hidden.vue';

// Export advanced components
export { default as RichEditor } from './components/RichEditor.vue';
export { default as Repeater } from './components/Repeater.vue';
export { default as ColorPicker } from './components/ColorPicker.vue';
export { default as TagsInput } from './components/TagsInput.vue';
export { default as KeyValue } from './components/KeyValue.vue';
export { default as TimePicker } from './components/TimePicker.vue';
export { default as DateTimePicker } from './components/DateTimePicker.vue';
export { default as MarkdownEditor } from './components/MarkdownEditor.vue';

// Import all components
import LaraviltTextInput from './components/TextInput.vue';
import LaraviltTextarea from './components/Textarea.vue';
import LaraviltSelect from './components/Select.vue';
import LaraviltCheckbox from './components/Checkbox.vue';
import LaraviltRadio from './components/Radio.vue';
import LaraviltToggle from './components/Toggle.vue';
import LaraviltDatePicker from './components/DatePicker.vue';
import LaraviltFileUpload from './components/FileUpload.vue';
import LaraviltHidden from './components/Hidden.vue';
import LaraviltRichEditor from './components/RichEditor.vue';
import LaraviltRepeater from './components/Repeater.vue';
import LaraviltColorPicker from './components/ColorPicker.vue';
import LaraviltTagsInput from './components/TagsInput.vue';
import LaraviltKeyValue from './components/KeyValue.vue';
import LaraviltTimePicker from './components/TimePicker.vue';
import LaraviltDateTimePicker from './components/DateTimePicker.vue';
import LaraviltMarkdownEditor from './components/MarkdownEditor.vue';

// Auto-register components if Vue instance is available
if (typeof window !== 'undefined' && window.Vue) {
    const components = {
        TextInput: LaraviltTextInput,
        Textarea: LaraviltTextarea,
        Select: LaraviltSelect,
        Checkbox: LaraviltCheckbox,
        Radio: LaraviltRadio,
        Toggle: LaraviltToggle,
        DatePicker: LaraviltDatePicker,
        FileUpload: LaraviltFileUpload,
        Hidden: LaraviltHidden,
        RichEditor: LaraviltRichEditor,
        Repeater: LaraviltRepeater,
        ColorPicker: LaraviltColorPicker,
        TagsInput: LaraviltTagsInput,
        KeyValue: LaraviltKeyValue,
        TimePicker: LaraviltTimePicker,
        DateTimePicker: LaraviltDateTimePicker,
        MarkdownEditor: LaraviltMarkdownEditor,
    };

    Object.keys(components).forEach(name => {
        const kebabName = name.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
        window.Vue.component(`laravilt-${kebabName}`, components[name]);
    });
}

// Export a plugin for Vue 3
export default {
    install(app) {
        // Register all components globally
        app.component('laravilt-text-input', LaraviltTextInput);
        app.component('laravilt-textarea', LaraviltTextarea);
        app.component('laravilt-select', LaraviltSelect);
        app.component('laravilt-checkbox', LaraviltCheckbox);
        app.component('laravilt-radio', LaraviltRadio);
        app.component('laravilt-toggle', LaraviltToggle);
        app.component('laravilt-date-picker', LaraviltDatePicker);
        app.component('laravilt-file-upload', LaraviltFileUpload);
        app.component('laravilt-hidden', LaraviltHidden);
        app.component('laravilt-rich-editor', LaraviltRichEditor);
        app.component('laravilt-repeater', LaraviltRepeater);
        app.component('laravilt-color-picker', LaraviltColorPicker);
        app.component('laravilt-tags-input', LaraviltTagsInput);
        app.component('laravilt-key-value', LaraviltKeyValue);
        app.component('laravilt-time-picker', LaraviltTimePicker);
        app.component('laravilt-datetime-picker', LaraviltDateTimePicker);
        app.component('laravilt-markdown-editor', LaraviltMarkdownEditor);
    }
};
