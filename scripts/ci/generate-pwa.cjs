const crypto = require('node:crypto');
const fs = require('node:fs');
const path = require('node:path');

const root = path.resolve(__dirname, '../..');
const publicPath = path.join(root, 'public');
const viteManifestPath = path.join(publicPath, 'build', 'manifest.json');

if (!fs.existsSync(viteManifestPath)) {
    throw new Error('PWA generation failed: public/build/manifest.json is missing');
}

const buildId = crypto
    .createHash('sha256')
    .update(fs.readFileSync(viteManifestPath))
    .digest('hex')
    .slice(0, 16);

const manifest = {
    id: '/dashboard',
    name: 'Material Inspection Recording System',
    short_name: 'MIRS',
    description: 'Material inspection records and approvals for production teams.',
    start_url: '/dashboard',
    scope: '/',
    display: 'standalone',
    prefer_related_applications: false,
    orientation: 'any',
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
};

const serviceWorker = `'use strict';

// A changed build ID makes the browser install a waiting worker for each release.
const BUILD_ID = '${buildId}';

self.addEventListener('install', () => {
    void BUILD_ID;
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') {
        void self.skipWaiting();
    }
});
`;

fs.writeFileSync(
    path.join(publicPath, 'manifest.webmanifest'),
    `${JSON.stringify(manifest)}\n`,
);
fs.writeFileSync(path.join(publicPath, 'sw.js'), serviceWorker);

console.log(`Generated root PWA assets for build ${buildId}.`);
