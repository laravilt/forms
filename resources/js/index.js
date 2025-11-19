// Laravilt Forms - Main Entry Point

// Import CSS
import '../css/forms.css';

// Export form renderer and field wrapper
export { default as FormRenderer } from './components/FormRenderer.vue';
export { default as FieldWrapper } from './components/FieldWrapper.vue';

// Export field components
export { default as TextInput } from './components/fields/TextInput.vue';
export { default as Textarea } from './components/fields/Textarea.vue';
export { default as Select } from './components/fields/Select.vue';
export { default as Checkbox } from './components/fields/Checkbox.vue';
export { default as CheckboxList } from './components/fields/CheckboxList.vue';
export { default as Radio } from './components/fields/Radio.vue';
export { default as Toggle } from './components/fields/Toggle.vue';
export { default as ToggleButtons } from './components/fields/ToggleButtons.vue';
export { default as Hidden } from './components/fields/Hidden.vue';
export { default as DatePicker } from './components/fields/DatePicker.vue';
export { default as TimePicker } from './components/fields/TimePicker.vue';
export { default as DateTimePicker } from './components/fields/DateTimePicker.vue';
export { default as DateRangePicker } from './components/fields/DateRangePicker.vue';
export { default as FileUpload } from './components/fields/FileUpload.vue';
export { default as RichEditor } from './components/fields/RichEditor.vue';
export { default as MarkdownEditor } from './components/fields/MarkdownEditor.vue';
export { default as CodeEditor } from './components/fields/CodeEditor.vue';
export { default as ColorPicker } from './components/fields/ColorPicker.vue';
export { default as TagsInput } from './components/fields/TagsInput.vue';
export { default as KeyValue } from './components/fields/KeyValue.vue';
export { default as Repeater } from './components/fields/Repeater.vue';
export { default as Builder } from './components/fields/Builder.vue';
export { default as Slider } from './components/fields/Slider.vue';
export { default as IconPicker } from './components/fields/IconPicker.vue';
export { default as NumberField } from './components/fields/NumberField.vue';
export { default as PinInput } from './components/fields/PinInput.vue';
export { default as RateInput } from './components/fields/RateInput.vue';

// Export schema components
export { default as Tabs } from './components/schema/Tabs.vue';
export { default as Section } from './components/schema/Section.vue';
export { default as Grid } from './components/schema/Grid.vue';

// Import all components for registration
import LaraviltFormRenderer from './components/FormRenderer.vue';
import LaraviltFieldWrapper from './components/FieldWrapper.vue';
import LaraviltTextInput from './components/fields/TextInput.vue';
import LaraviltTextarea from './components/fields/Textarea.vue';
import LaraviltSelect from './components/fields/Select.vue';
import LaraviltCheckbox from './components/fields/Checkbox.vue';
import LaraviltCheckboxList from './components/fields/CheckboxList.vue';
import LaraviltRadio from './components/fields/Radio.vue';
import LaraviltToggle from './components/fields/Toggle.vue';
import LaraviltToggleButtons from './components/fields/ToggleButtons.vue';
import LaraviltHidden from './components/fields/Hidden.vue';
import LaraviltDatePicker from './components/fields/DatePicker.vue';
import LaraviltTimePicker from './components/fields/TimePicker.vue';
import LaraviltDateTimePicker from './components/fields/DateTimePicker.vue';
import LaraviltDateRangePicker from './components/fields/DateRangePicker.vue';
import LaraviltFileUpload from './components/fields/FileUpload.vue';
import LaraviltRichEditor from './components/fields/RichEditor.vue';
import LaraviltMarkdownEditor from './components/fields/MarkdownEditor.vue';
import LaraviltCodeEditor from './components/fields/CodeEditor.vue';
import LaraviltColorPicker from './components/fields/ColorPicker.vue';
import LaraviltTagsInput from './components/fields/TagsInput.vue';
import LaraviltKeyValue from './components/fields/KeyValue.vue';
import LaraviltRepeater from './components/fields/Repeater.vue';
import LaraviltBuilder from './components/fields/Builder.vue';
import LaraviltSlider from './components/fields/Slider.vue';
import LaraviltIconPicker from './components/fields/IconPicker.vue';
import LaraviltNumberField from './components/fields/NumberField.vue';
import LaraviltPinInput from './components/fields/PinInput.vue';
import LaraviltRateInput from './components/fields/RateInput.vue';
import LaraviltTabs from './components/schema/Tabs.vue';
import LaraviltSection from './components/schema/Section.vue';
import LaraviltGrid from './components/schema/Grid.vue';

// Auto-register components if Vue instance is available
if (typeof window !== 'undefined' && window.Vue) {
    const components = {
        FormRenderer: LaraviltFormRenderer,
        FieldWrapper: LaraviltFieldWrapper,
        TextInput: LaraviltTextInput,
        Textarea: LaraviltTextarea,
        Select: LaraviltSelect,
        Checkbox: LaraviltCheckbox,
        CheckboxList: LaraviltCheckboxList,
        Radio: LaraviltRadio,
        Toggle: LaraviltToggle,
        ToggleButtons: LaraviltToggleButtons,
        Hidden: LaraviltHidden,
        DatePicker: LaraviltDatePicker,
        TimePicker: LaraviltTimePicker,
        DateTimePicker: LaraviltDateTimePicker,
        DateRangePicker: LaraviltDateRangePicker,
        FileUpload: LaraviltFileUpload,
        RichEditor: LaraviltRichEditor,
        MarkdownEditor: LaraviltMarkdownEditor,
        CodeEditor: LaraviltCodeEditor,
        ColorPicker: LaraviltColorPicker,
        TagsInput: LaraviltTagsInput,
        KeyValue: LaraviltKeyValue,
        Repeater: LaraviltRepeater,
        Builder: LaraviltBuilder,
        Slider: LaraviltSlider,
        IconPicker: LaraviltIconPicker,
        NumberField: LaraviltNumberField,
        PinInput: LaraviltPinInput,
        RateInput: LaraviltRateInput,
        Tabs: LaraviltTabs,
        Section: LaraviltSection,
        Grid: LaraviltGrid,
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
        app.component('laravilt-form-renderer', LaraviltFormRenderer);
        app.component('laravilt-field-wrapper', LaraviltFieldWrapper);

        // Field components
        app.component('laravilt-text-input', LaraviltTextInput);
        app.component('laravilt-textarea', LaraviltTextarea);
        app.component('laravilt-select', LaraviltSelect);
        app.component('laravilt-checkbox', LaraviltCheckbox);
        app.component('laravilt-checkbox-list', LaraviltCheckboxList);
        app.component('laravilt-radio', LaraviltRadio);
        app.component('laravilt-toggle', LaraviltToggle);
        app.component('laravilt-toggle-buttons', LaraviltToggleButtons);
        app.component('laravilt-hidden', LaraviltHidden);
        app.component('laravilt-date-picker', LaraviltDatePicker);
        app.component('laravilt-time-picker', LaraviltTimePicker);
        app.component('laravilt-datetime-picker', LaraviltDateTimePicker);
        app.component('laravilt-date-range-picker', LaraviltDateRangePicker);
        app.component('laravilt-file-upload', LaraviltFileUpload);
        app.component('laravilt-rich-editor', LaraviltRichEditor);
        app.component('laravilt-markdown-editor', LaraviltMarkdownEditor);
        app.component('laravilt-code-editor', LaraviltCodeEditor);
        app.component('laravilt-color-picker', LaraviltColorPicker);
        app.component('laravilt-tags-input', LaraviltTagsInput);
        app.component('laravilt-key-value', LaraviltKeyValue);
        app.component('laravilt-repeater', LaraviltRepeater);
        app.component('laravilt-builder', LaraviltBuilder);
        app.component('laravilt-slider', LaraviltSlider);
        app.component('laravilt-icon-picker', LaraviltIconPicker);
        app.component('laravilt-number-field', LaraviltNumberField);
        app.component('laravilt-pin-input', LaraviltPinInput);
        app.component('laravilt-rate-input', LaraviltRateInput);

        // Schema components
        app.component('laravilt-tabs', LaraviltTabs);
        app.component('laravilt-section', LaraviltSection);
        app.component('laravilt-grid', LaraviltGrid);
    }
};
