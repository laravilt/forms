export interface IconPickerSimpleProps {
    name?: string;
    label?: string;
    icons?: string[];
    [key: string]: any;
}

const EMPTY_ICONS: string[] = [];

export default function IconPickerSimple({ name, label, icons = EMPTY_ICONS }: IconPickerSimpleProps) {
    return (
        <div className="p-4 border border-blue-500 rounded">
            <h3 className="font-bold text-blue-600">IconPicker Simple Test</h3>
            <p>Name: {name}</p>
            <p>Label: {label}</p>
            <p>Icons count: {icons?.length || 0}</p>
            <p>First icons: {icons?.slice(0, 5).join(', ')}</p>
        </div>
    );
}
