// Laravilt Forms Package Entry Point (React)
import { registerComponents } from '@laravilt/support/composables/registry';
import FieldWrapper from './components/FieldWrapper';
import Form from './components/Form';
import Builder from './components/fields/Builder';
import Checkbox from './components/fields/Checkbox';
import CheckboxList from './components/fields/CheckboxList';
import CodeEditor from './components/fields/CodeEditor';
import ColorPicker from './components/fields/ColorPicker';
import DatePicker from './components/fields/DatePicker';
import DateRangePicker from './components/fields/DateRangePicker';
import DateTimePicker from './components/fields/DateTimePicker';
import FileUpload from './components/fields/FileUpload';
import Hidden from './components/fields/Hidden';
import IconPicker from './components/fields/IconPicker';
import KeyValue from './components/fields/KeyValue';
import MarkdownEditor from './components/fields/MarkdownEditor';
import NumberField from './components/fields/NumberField';
import PinInput from './components/fields/PinInput';
import Radio from './components/fields/Radio';
import RateInput from './components/fields/RateInput';
import Repeater from './components/fields/Repeater';
import RichEditor from './components/fields/RichEditor';
import Select from './components/fields/Select';
import Slider from './components/fields/Slider';
import TagsInput from './components/fields/TagsInput';
import TextInput from './components/fields/TextInput';
import Textarea from './components/fields/Textarea';
import TimePicker from './components/fields/TimePicker';
import Toggle from './components/fields/Toggle';
import ToggleButtons from './components/fields/ToggleButtons';
import Grid from './components/schema/Grid';
import Section from './components/schema/Section';
import Tabs from './components/schema/Tabs';

export { Form, Select, TextInput };

export default {
    register(_options: Record<string, any> = {}) {
        registerComponents({
            // Register global components with laravilt- prefix for LaraviltComponentRenderer
            'laravilt-form': Form,
            'laravilt-field-wrapper': FieldWrapper,

            // Field components
            'laravilt-text-input': TextInput,
            'laravilt-textarea': Textarea,
            'laravilt-select': Select,
            'laravilt-checkbox': Checkbox,
            'laravilt-checkbox-list': CheckboxList,
            'laravilt-radio': Radio,
            'laravilt-toggle': Toggle,
            'laravilt-toggle-buttons': ToggleButtons,
            'laravilt-hidden': Hidden,
            'laravilt-date-picker': DatePicker,
            'laravilt-time-picker': TimePicker,
            'laravilt-datetime-picker': DateTimePicker,
            'laravilt-date-range-picker': DateRangePicker,
            'laravilt-file-upload': FileUpload,
            'laravilt-rich-editor': RichEditor,
            'laravilt-markdown-editor': MarkdownEditor,
            'laravilt-code-editor': CodeEditor,
            'laravilt-color-picker': ColorPicker,
            'laravilt-tags-input': TagsInput,
            'laravilt-key-value': KeyValue,
            'laravilt-repeater': Repeater,
            'laravilt-builder': Builder,
            'laravilt-slider': Slider,
            'laravilt-icon-picker': IconPicker,
            'laravilt-number-field': NumberField,
            'laravilt-pin-input': PinInput,
            'laravilt-rate-input': RateInput,

            // Schema components
            'laravilt-tabs': Tabs,
            'laravilt-section': Section,
            'laravilt-grid': Grid,

            // Legacy name support
            Form: Form,
        });
    },
};
