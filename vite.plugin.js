import { resolve } from 'path';

export default function FormsPlugin() {
    const pluginPath = resolve(__dirname);

    return {
        name: 'forms-plugin',
        config: () => ({
            build: {
                rollupOptions: {
                    input: {
                        'forms': resolve(pluginPath, 'resources/js/app.js'),
                    },
                    output: {
                        entryFileNames: 'js/[name].js',
                        chunkFileNames: 'js/[name].js',
                        assetFileNames: (assetInfo) => {
                            if (assetInfo.name.endsWith('.css')) {
                                return 'css/[name][extname]';
                            }
                            return 'assets/[name][extname]';
                        },
                    },
                },
                outDir: resolve(pluginPath, 'dist'),
                emptyOutDir: true,
            },
        }),
    };
}
