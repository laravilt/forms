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
import LaraviltDateRangePicker from './components/fields/DateRangePicker';
import LaraviltDateTimePicker from './components/fields/DateTimePicker';
import LaraviltFileUpload from './components/fields/FileUpload';
import LaraviltHidden from './components/fields/Hidden';
import LaraviltIconPicker from './components/fields/IconPicker';
import LaraviltKeyValue from './components/fields/KeyValue';
import LaraviltMarkdownEditor from './components/fields/MarkdownEditor';
import LaraviltNumberField from './components/fields/NumberField';
import LaraviltPinInput from './components/fields/PinInput';
import LaraviltRadio from './components/fields/Radio';
import LaraviltRateInput from './components/fields/RateInput';
import LaraviltRepeater from './components/fields/Repeater';
import LaraviltRichEditor from './components/fields/RichEditor';
import LaraviltSelect from './components/fields/SelectWrapper';
import LaraviltSlider from './components/fields/Slider';
import LaraviltTagsInput from './components/fields/TagsInput';
import LaraviltTextInput from './components/fields/TextInput';
import LaraviltTextarea from './components/fields/Textarea';
import LaraviltTranslatableInput from './components/fields/TranslatableInput';
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
            'laravilt-translatable-input': LaraviltTranslatableInput,
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
            'laravilt-date-range-picker': LaraviltDateRangePicker,
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
            'laravilt-icon-picker': LaraviltIconPicker,
            'laravilt-number-field': LaraviltNumberField,
            'laravilt-pin-input': LaraviltPinInput,
            'laravilt-rate-input': LaraviltRateInput,

            // Schema components
            'laravilt-tabs': LaraviltTabs,
            'laravilt-section': LaraviltSection,
            'laravilt-grid': LaraviltGrid,
        });
    },
};
