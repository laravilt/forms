// Laravilt Forms - Components Only (without CSS)
// This is used by the Blade demo which uses the main app's CSS

// Import all components for registration
import LaraviltFormRenderer from './components/FormRenderer.vue';
import LaraviltFieldWrapper from './components/FieldWrapper.vue';
import LaraviltTextInput from './components/fields/TextInput.vue';
import LaraviltTextarea from './components/fields/Textarea.vue';
import LaraviltSelect from './components/fields/SelectWrapper.vue';
import LaraviltCheckbox from './components/fields/Checkbox.vue';
import LaraviltCheckboxList from './components/fields/CheckboxList.vue';
import LaraviltRadio from './components/fields/Radio.vue';
import LaraviltToggle from './components/fields/Toggle.vue';
import LaraviltToggleButtons from './components/fields/ToggleButtons.vue';
import LaraviltHidden from './components/fields/Hidden.vue';
import LaraviltDatePicker from './components/fields/DatePicker.vue';
import LaraviltTimePicker from './components/fields/TimePicker.vue';
import LaraviltDateTimePicker from './components/fields/DateTimePicker.vue';
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
import LaraviltTabs from './components/schema/Tabs.vue';
import LaraviltSection from './components/schema/Section.vue';
import LaraviltGrid from './components/schema/Grid.vue';

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

        // Schema components
        app.component('laravilt-tabs', LaraviltTabs);
        app.component('laravilt-section', LaraviltSection);
        app.component('laravilt-grid', LaraviltGrid);
    }
};
