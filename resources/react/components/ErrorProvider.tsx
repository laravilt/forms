import { ErrorsContext } from '@laravilt/support/composables/contexts';
import type { ReactNode } from 'react';

export interface ErrorProviderProps {
    errors: Record<string, string | string[]>;
    children?: ReactNode;
}

// Provide errors to all child components
export default function ErrorProvider({ errors, children }: ErrorProviderProps) {
    return <ErrorsContext.Provider value={errors || {}}>{children}</ErrorsContext.Provider>;
}
