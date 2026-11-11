'use strict';

// A changed build ID makes the browser install a waiting worker for each release.
const BUILD_ID = '8f991d0831074c9e';

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
