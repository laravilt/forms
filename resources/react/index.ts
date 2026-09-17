// Laravilt Forms - Main Entry Point (React)

// CSS is handled by the main app, no need to import here

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
import LaraviltSelect from './components/fields/Select';
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

// Export form renderer and field wrapper
export { default as FieldWrapper } from './components/FieldWrapper';
export { default as Form } from './components/Form';

// Export field components
export { default as Builder } from './components/fields/Builder';
export { default as Checkbox } from './components/fields/Checkbox';
export { default as CheckboxList } from './components/fields/CheckboxList';
export { default as CodeEditor } from './components/fields/CodeEditor';
export { default as ColorPicker } from './components/fields/ColorPicker';
export { default as DatePicker } from './components/fields/DatePicker';
export { default as DateRangePicker } from './components/fields/DateRangePicker';
export { default as DateTimePicker } from './components/fields/DateTimePicker';
export { default as FileUpload } from './components/fields/FileUpload';
export { default as Hidden } from './components/fields/Hidden';
export { default as IconPicker } from './components/fields/IconPicker';
export { default as KeyValue } from './components/fields/KeyValue';
export { default as MarkdownEditor } from './components/fields/MarkdownEditor';
export { default as NumberField } from './components/fields/NumberField';
export { default as PinInput } from './components/fields/PinInput';
export { default as Radio } from './components/fields/Radio';
export { default as RateInput } from './components/fields/RateInput';
export { default as Repeater } from './components/fields/Repeater';
export { default as RichEditor } from './components/fields/RichEditor';
export { default as Select } from './components/fields/Select';
export { default as Slider } from './components/fields/Slider';
export { default as TagsInput } from './components/fields/TagsInput';
export { default as TextInput } from './components/fields/TextInput';
export { default as Textarea } from './components/fields/Textarea';
export { default as TranslatableInput } from './components/fields/TranslatableInput';
export { default as TimePicker } from './components/fields/TimePicker';
export { default as Toggle } from './components/fields/Toggle';
export { default as ToggleButtons } from './components/fields/ToggleButtons';

// Export schema components
export { default as Grid } from './components/schema/Grid';
export { default as Section } from './components/schema/Section';
export { default as Tabs } from './components/schema/Tabs';

// Note: the Vue file also auto-registers into `window.Vue` when present. That global has no React
// equivalent, so registration happens only through `register()` below.

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
