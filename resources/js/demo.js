import { createApp, h, Teleport, resolveComponent } from 'vue';
import { Laravilt } from '../../../support/resources/js/core/Laravilt.js';
// Import components WITHOUT the forms.css (we'll use the main app's CSS instead)
import LaraviltForms from './components-only.js';

// Initialize Laravilt with empty initial data for the Blade demo
// This ensures event buses and other internals are set up
if (typeof window !== 'undefined' && !window.__laravilt_initialized) {
    const initialLaraviltData = {
        shared: {},
        flash: {},
        errors: {},
        head: {},
        toasts: [],
        persistentLayout: null
    };

    Laravilt.init('', {}, initialLaraviltData);
    window.__laravilt_initialized = true;
}

// Convert PascalCase to kebab-case
const toKebabCase = (str) => {
    return str.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
};

function init() {
    const appContainer = document.getElementById('app');
    if (!appContainer) {
        console.error('#app container not found');
        return;
    }

    // Find all component markers before creating the app
    const markers = Array.from(document.querySelectorAll('[data-laravilt-vue]'));

    // Parse all markers into component definitions
    const componentDefinitions = markers.map((marker, index) => {
        const componentName = marker.getAttribute('data-component');
        const propsJson = marker.getAttribute('data-props');
        const props = propsJson ? JSON.parse(propsJson) : {};
        const markerId = `laravilt-marker-${index}`;

        // Add an ID to the marker so we can teleport to it
        marker.setAttribute('id', markerId);

        return {
            componentName,
            props,
            markerId,
            marker
        };
    });

    // Create ONE single Vue app with a render function that returns all components
    const app = createApp({
        name: 'LaraviltBladeApp',
        setup() {
            return () => {
                // Return an array of Teleport components, each rendering to its marker
                return componentDefinitions.map((def, index) => {
                    const kebabName = toKebabCase(def.componentName);
                    const fullComponentName = `laravilt-${kebabName}`;

                    // Resolve the actual component object from the registry
                    const component = resolveComponent(fullComponentName);

                    // Use Teleport to render each component into its marker
                    return h(Teleport, {
                        key: `teleport-${index}`,
                        to: `#${def.markerId}`
                    }, [
                        h(component, {
                            key: `component-${index}`,
                            ...def.props
                        })
                    ]);
                });
            };
        },
        mounted() {
            // Debug: Check if markers still exist and if they have content
            this.$nextTick(() => {
                componentDefinitions.forEach((def, index) => {
                    const marker = document.getElementById(def.markerId);
                    if (!marker) {
                        console.error(
                            `Marker #${index} NOT FOUND: #${def.markerId}`,
                        );
                    }
                });
            });
        }
    });

    // Install LaraviltForms plugin
    app.use(LaraviltForms);

    // Create a hidden mount point for the app (DON'T mount to #app!)
    const mountPoint = document.createElement('div');
    mountPoint.id = 'laravilt-root';
    mountPoint.style.display = 'none';
    document.body.appendChild(mountPoint);

    // Mount the app to the hidden container, not #app
    app.mount(mountPoint);
    // Setup form submission with Inertia
    setupFormSubmission();
}

function setupFormSubmission() {
    const form = document.getElementById('demo-form');
    if (!form) {
        console.warn('Demo form not found');
        return;
    }

    // Listen for Laravilt response events to update the page
    Laravilt.on('internal:request-response', ({ response }) => {
        if (response?.data?.html) {
            // Replace the #app content with the new HTML
            const appContainer = document.getElementById('app');
            if (appContainer) {
                appContainer.innerHTML = response.data.html;

                // Re-initialize the Vue components after HTML update
                init();
            }
        }
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        const action = form.getAttribute('action');
        const method = form.getAttribute('method')?.toLowerCase() || 'post';

        // Use Laravilt request function (Splade-like)
        Laravilt.request(action, method, data, {}, false)
            .then(() => {
                console.log('✓ Form submitted successfully via Laravilt');
            })
            .catch((error) => {
                console.error('Form submission error:', error);
            });
    });

}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
