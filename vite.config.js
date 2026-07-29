import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';
import { fileURLToPath, URL } from 'url';

export default defineConfig({
    build: {
        chunkSizeWarningLimit: 1000,
    },
    plugins: [
        laravel({
            input: ['./resources/js/app.ts', './resources/css/app.css'],
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
        VitePWA({
            buildBase: '/build/',
            injectRegister: false,
            registerType: 'prompt',
            scope: '/',
            manifestFilename: 'manifest.webmanifest',
            manifest: {
                id: '/dashboard',
                name: 'Material Inspection Recording System',
                short_name: 'MIRS',
                description: 'Material inspection records and approvals for production teams.',
                start_url: '/dashboard',
                scope: '/',
                display: 'standalone',
                prefer_related_applications: false,
                orientation: 'portrait',
                background_color: '#f4f7f6',
                theme_color: '#0f4c5c',
                icons: [
                    {
                        src: '/pwa-64x64.png',
                        sizes: '64x64',
                        type: 'image/png',
                    },
                    {
                        src: '/pwa-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: '/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any',
                    },
                    {
                        src: '/maskable-icon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable',
                    },
                ],
            },
            workbox: {
                cleanupOutdatedCaches: true,
                globPatterns: ['**/*.{js,css,woff,woff2}'],
                navigateFallback: null,
                runtimeCaching: [],
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
            host: '192.168.2.243',
            protocol: 'ws',
        },
        cors: {
            origin: true,
            credentials: true
        }
    },
});
