// Laravilt Forms - Components Only (without CSS)
// This is used by the Blade demo which uses the main app's CSS

import { registerComponents } from '@laravilt/support/composables/registry';
import LaraviltFieldWrapper from './components/FieldWrapper';
import LaraviltForm from './components/Form';
import LaraviltBuilder from './components/fields/Builder';
import LaraviltCheckbox from './components/fields/Checkbox';
import LaraviltCheckboxList from './components/fields/CheckboxList';
import LaraviltCodeEditor from './components/fields/CodeEditor';
import LaraviltColorPicker from './components/fields/ColorPicker';
import LaraviltDatePicker from './components/fields/DatePicker';
import LaraviltDateTimePicker from './components/fields/DateTimePicker';
import LaraviltFileUpload from './components/fields/FileUpload';
import LaraviltHidden from './components/fields/Hidden';
import LaraviltKeyValue from './components/fields/KeyValue';
import LaraviltMarkdownEditor from './components/fields/MarkdownEditor';
import LaraviltRadio from './components/fields/Radio';
import LaraviltRepeater from './components/fields/Repeater';
import LaraviltRichEditor from './components/fields/RichEditor';
import LaraviltSelect from './components/fields/SelectWrapper';
import LaraviltSlider from './components/fields/Slider';
import LaraviltTagsInput from './components/fields/TagsInput';
import LaraviltTextInput from './components/fields/TextInput';
import LaraviltTextarea from './components/fields/Textarea';
import LaraviltTimePicker from './components/fields/TimePicker';
import LaraviltToggle from './components/fields/Toggle';
import LaraviltToggleButtons from './components/fields/ToggleButtons';
import LaraviltGrid from './components/schema/Grid';
import LaraviltSection from './components/schema/Section';
import LaraviltTabs from './components/schema/Tabs';

// Plugin-style default export (Vue: `install(app)`)
export default {
    register() {
        registerComponents({
            // Register all components globally
            'laravilt-form': LaraviltForm,
            'laravilt-field-wrapper': LaraviltFieldWrapper,

            // Field components
            'laravilt-text-input': LaraviltTextInput,
            'laravilt-textarea': LaraviltTextarea,
            'laravilt-select': LaraviltSelect,
            'laravilt-checkbox': LaraviltCheckbox,
            'laravilt-checkbox-list': LaraviltCheckboxList,
            'laravilt-radio': LaraviltRadio,
            'laravilt-toggle': LaraviltToggle,
            'laravilt-toggle-buttons': LaraviltToggleButtons,
            'laravilt-hidden': LaraviltHidden,
            'laravilt-date-picker': LaraviltDatePicker,
            'laravilt-time-picker': LaraviltTimePicker,
            'laravilt-datetime-picker': LaraviltDateTimePicker,
            'laravilt-file-upload': LaraviltFileUpload,
            'laravilt-rich-editor': LaraviltRichEditor,
            'laravilt-markdown-editor': LaraviltMarkdownEditor,
            'laravilt-code-editor': LaraviltCodeEditor,
            'laravilt-color-picker': LaraviltColorPicker,
            'laravilt-tags-input': LaraviltTagsInput,
            'laravilt-key-value': LaraviltKeyValue,
            'laravilt-repeater': LaraviltRepeater,
            'laravilt-builder': LaraviltBuilder,
            'laravilt-slider': LaraviltSlider,

            // Schema components
            'laravilt-tabs': LaraviltTabs,
            'laravilt-section': LaraviltSection,
            'laravilt-grid': LaraviltGrid,
        });
    },
};
