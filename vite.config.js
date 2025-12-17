import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'url';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts', 'resources/css/app.css'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
            script: {
                defineModel: true,
                propsDestructure: true,
            },
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            'ziggy-js': 'ziggy-js',
            'ziggy': 'ziggy-js',
        },
        extensions: ['.js', '.ts', '.vue', '.json'],
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: '192.168.2.32',
            protocol: 'ws',
        },
        cors: {
            origin: true,
            credentials: true
        }
    },
});