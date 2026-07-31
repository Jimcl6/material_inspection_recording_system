'use strict';

// A changed build ID makes the browser install a waiting worker for each release.
const BUILD_ID = '17ed61c6422a8652';

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
