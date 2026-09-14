import { createContext, useContext } from 'react';

/**
 * React twin of the Vue `inject('formData', null)` used by Select for dependent (cascading) options.
 * No Laravilt component provides this key in the Vue code, so the default is `null`, exactly like Vue.
 * A host can wrap fields in `<FormDataContext.Provider value={formData}>` to enable dependent selects.
 */
export const FormDataContext = createContext<Record<string, any> | null>(null);

export function useFormData(): Record<string, any> | null {
    return useContext(FormDataContext);
}
