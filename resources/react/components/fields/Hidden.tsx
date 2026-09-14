import type { ReactNode } from 'react';

export interface HiddenSlotScope {
    id: string;
    name: string;
    value?: string;
}

export interface HiddenProps {
    id?: string;
    name: string;
    value?: string;
    /** Scoped default slot (Blade mode). */
    children?: (scope: HiddenSlotScope) => ReactNode;
    [key: string]: any;
}

export default function Hidden({ id, name, value, children }: HiddenProps) {
    // Use slot if provided (Blade mode), otherwise use internal template (Direct usage)
    if (children) {
        return <>{children({ id: id || name, name, value })}</>;
    }

    return <input type="hidden" id={id || name} name={name} value={value ?? ''} />;
}
