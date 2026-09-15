import { usePage } from '@inertiajs/react';
import { ErrorsContext } from '@laravilt/support/composables/contexts';
import type { ReactNode } from 'react';

export interface ErrorProviderProps {
    errors?: Record<string, string | string[]>;
    children?: ReactNode;
}

// Provide errors to all child components: the given errors, else the Inertia page's validation errors
export default function ErrorProvider({ errors, children }: ErrorProviderProps) {
    const page = usePage();
    const pageErrors = page.props.errors as Record<string, string | string[]> | undefined;

    return <ErrorsContext.Provider value={errors ?? pageErrors ?? {}}>{children}</ErrorsContext.Provider>;
}
