self.addEventListener('push', function(event) {
    if (event.data) {
        let data = {};
        try {
            data = event.data.json();
        } catch (e) {
            data = { title: 'New Message', body: event.data.text() };
        }

        const options = {
            body: data.body || 'New message received',
            icon: data.icon || '/pwa-icon.png',
            badge: '/pwa-icon.png',
            vibrate: [100, 50, 100],
            tag: 'chat-notification',
            renotify: true,
            data: {
                url: data.url || '/team-chat'
            },
            // sound disabled
        };

        event.waitUntil(
            clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(windowClients) {
                let isFocused = false;
                for (let i = 0; i < windowClients.length; i++) {
                    if (windowClients[i].focused) {
                        isFocused = true;
                        break;
                    }
                }
                
                // If the app is focused, don't show the background push notification
                if (isFocused) {
                    return;
                }

                return self.registration.showNotification(data.title || 'Team Update', options);
            })
        );
    }
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(windowClients => {
            // If tab already open, focus it
            for (let i = 0; i < windowClients.length; i++) {
                let client = windowClients[i];
                if (client.url.includes(event.notification.data.url) && 'focus' in client) {
                    return client.focus();
                }
            }
            // Otherwise open new
            if (clients.openWindow) {
                return clients.openWindow(event.notification.data.url);
            }
        })
    );
});

// Sync data (Optional)
self.addEventListener('sync', function(event) {
    if (event.tag === 'sync-chat') {
        // Logic to sync messages in background
    }
});

// ==========================================
// PWA CACHING & OFFLINE LOGIC
// ==========================================
const CACHE_NAME = 'asa-portal-v7';
const urlsToCache = [
    '/install.html',
    '/offline.html',
    '/manifest.json',
    '/pwa-icon.png'
];

// Install SW
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                return cache.addAll(urlsToCache);
            })
    );
});

// Listen for requests
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') return;

    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                return response || fetch(event.request).catch(() => {
                    // Fallback to offline page if fetch fails (e.g. no network)
                    if (event.request.mode === 'navigate') {
                        return caches.match('/offline.html');
                    }
                });
            })
    );
});

// Activate & Clean old caches
self.addEventListener('activate', (event) => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then((cacheNames) => Promise.all(
            cacheNames.map((cacheName) => {
                if (!cacheWhitelist.includes(cacheName)) {
                    return caches.delete(cacheName);
                }
            })
        ))
    );
});
