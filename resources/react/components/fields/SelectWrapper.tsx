import Select from './Select';
import SelectSimple from './SelectSimple';

export interface SelectWrapperProps {
    native?: boolean;
    searchable?: boolean;
    [key: string]: any;
}

export default function SelectWrapper(props: SelectWrapperProps) {
    // Use SelectSimple when native=true OR when not searchable
    // Use full Select when searchable=true (requires the combobox)
    const shouldUseSimple = (() => {
        // If explicitly set to native, use simple
        if (props.native === true) {
            return true;
        }

        // If searchable, must use full Select
        if (props.searchable === true) {
            return false;
        }

        // Default to simple for static options
        return true;
    })();

    const ComponentToUse = shouldUseSimple ? SelectSimple : Select;

    return <ComponentToUse {...(props as any)} />;
}
