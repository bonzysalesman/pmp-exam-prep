/**
 * PMP PWA Service Worker
 * Minimal implementation for offline lesson caching
 */

const CACHE_NAME = 'pmp-pwa-v1';
const LESSON_CACHE = 'pmp-lessons-v1';

// Core files to cache immediately
const CORE_FILES = [
    '/',
    '/wp-content/themes/pmp-dashboard/style.css',
    '/wp-content/themes/pmp-dashboard/assets/js/main.js',
    '/offline.html'
];

// Install event - cache core files
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(CORE_FILES))
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME && cacheName !== LESSON_CACHE) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', event => {
    const { request } = event;
    
    // Handle lesson requests
    if (request.url.includes('/lesson/') || request.url.includes('post_type=lesson')) {
        event.respondWith(
            caches.open(LESSON_CACHE).then(cache => {
                return cache.match(request).then(response => {
                    if (response) {
                        // Serve from cache, update in background
                        fetch(request).then(fetchResponse => {
                            cache.put(request, fetchResponse.clone());
                        }).catch(() => {});
                        return response;
                    }
                    
                    // Not in cache, try network
                    return fetch(request).then(fetchResponse => {
                        cache.put(request, fetchResponse.clone());
                        return fetchResponse;
                    }).catch(() => {
                        // Offline fallback
                        return caches.match('/offline.html');
                    });
                });
            })
        );
        return;
    }
    
    // Handle other requests - network first, cache fallback
    event.respondWith(
        fetch(request).catch(() => {
            return caches.match(request).then(response => {
                return response || caches.match('/offline.html');
            });
        })
    );
});
