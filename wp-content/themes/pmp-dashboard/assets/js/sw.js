/**
 * PMP Exam Prep - Service Worker
 * Task: T001 - Service Worker Implementation
 */

const CACHE_VERSION = 'pmp-v1.0.0';
const CACHE_PREFIX = 'pmp-prep';

// Cache names
const CACHES = {
  APP_SHELL: `${CACHE_PREFIX}-app-shell-${CACHE_VERSION}`,
  CONTENT: `${CACHE_PREFIX}-content-${CACHE_VERSION}`,
  API: `${CACHE_PREFIX}-api-${CACHE_VERSION}`,
  IMAGES: `${CACHE_PREFIX}-images-${CACHE_VERSION}`,
  FONTS: `${CACHE_PREFIX}-fonts-${CACHE_VERSION}`
};

// Cache strategies
const CACHE_STRATEGIES = {
  APP_SHELL: 'cache-first',
  CONTENT: 'stale-while-revalidate',
  API: 'network-first',
  IMAGES: 'cache-first',
  FONTS: 'cache-first'
};

// App shell resources (critical files)
const APP_SHELL_RESOURCES = [
  '/',
  '/wp-content/themes/pmp-dashboard/style.css',
  '/wp-content/themes/pmp-dashboard/assets/js/main.js',
  '/wp-content/themes/pmp-dashboard/assets/js/pwa-register.js',
  '/wp-content/themes/pmp-dashboard/assets/css/critical.css',
  '/wp-content/themes/pmp-dashboard/manifest.json'
];

// Content patterns
const CONTENT_PATTERNS = [
  /\/lessons\/.*/,
  /\/practice-tests\/.*/,
  /\/resources\/.*/,
  /\/wp-json\/pmp\/v1\/content\/.*/
];

// API patterns
const API_PATTERNS = [
  /\/wp-json\/pmp\/v1\/progress\/.*/,
  /\/wp-json\/pmp\/v1\/test\/.*/,
  /\/wp-json\/pmp\/v1\/bookmark\/.*/
];

// Image patterns
const IMAGE_PATTERNS = [
  /\.(?:png|jpg|jpeg|svg|gif|webp)$/,
  /\/wp-content\/uploads\/.*/
];

// Font patterns
const FONT_PATTERNS = [
  /\.(?:woff|woff2|ttf|eot)$/,
  /fonts\.googleapis\.com/,
  /fonts\.gstatic\.com/
];

/**
 * Service Worker Install Event
 */
self.addEventListener('install', event => {
  console.log('[SW] Installing service worker...');
  
  event.waitUntil(
    caches.open(CACHES.APP_SHELL)
      .then(cache => {
        console.log('[SW] Caching app shell resources');
        return cache.addAll(APP_SHELL_RESOURCES);
      })
      .then(() => {
        console.log('[SW] App shell cached successfully');
        return self.skipWaiting();
      })
      .catch(error => {
        console.error('[SW] Failed to cache app shell:', error);
      })
  );
});

/**
 * Service Worker Activate Event
 */
self.addEventListener('activate', event => {
  console.log('[SW] Activating service worker...');
  
  event.waitUntil(
    Promise.all([
      // Clean up old caches
      cleanupOldCaches(),
      // Claim all clients
      self.clients.claim()
    ]).then(() => {
      console.log('[SW] Service worker activated successfully');
    })
  );
});

/**
 * Service Worker Fetch Event
 */
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip chrome-extension and other protocols
  if (!url.protocol.startsWith('http')) {
    return;
  }
  
  // Determine cache strategy based on request
  const strategy = getCacheStrategy(request);
  
  event.respondWith(
    handleRequest(request, strategy)
      .catch(error => {
        console.error('[SW] Fetch error:', error);
        return getOfflineFallback(request);
      })
  );
});

/**
 * Background Sync Event
 */
self.addEventListener('sync', event => {
  console.log('[SW] Background sync triggered:', event.tag);
  
  switch (event.tag) {
    case 'sync-progress':
      event.waitUntil(syncProgress());
      break;
    case 'sync-test-results':
      event.waitUntil(syncTestResults());
      break;
    case 'sync-bookmarks':
      event.waitUntil(syncBookmarks());
      break;
    default:
      console.log('[SW] Unknown sync tag:', event.tag);
  }
});

/**
 * Push Event Handler
 */
self.addEventListener('push', event => {
  console.log('[SW] Push notification received');
  
  const options = {
    body: 'Time for your PMP study session!',
    icon: '/wp-content/themes/pmp-dashboard/assets/icons/icon-192.png',
    badge: '/wp-content/themes/pmp-dashboard/assets/icons/badge-72.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'Start Studying',
        icon: '/wp-content/themes/pmp-dashboard/assets/icons/study-icon.png'
      },
      {
        action: 'close',
        title: 'Later',
        icon: '/wp-content/themes/pmp-dashboard/assets/icons/close-icon.png'
      }
    ]
  };
  
  if (event.data) {
    const payload = event.data.json();
    options.body = payload.body || options.body;
    options.data = { ...options.data, ...payload.data };
  }
  
  event.waitUntil(
    self.registration.showNotification('PMP Exam Prep', options)
  );
});

/**
 * Notification Click Event
 */
self.addEventListener('notificationclick', event => {
  console.log('[SW] Notification clicked:', event.action);
  
  event.notification.close();
  
  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('/lessons')
    );
  } else if (event.action === 'close') {
    // Just close the notification
    return;
  } else {
    // Default action - open the app
    event.waitUntil(
      clients.matchAll({ type: 'window' }).then(clientList => {
        for (const client of clientList) {
          if (client.url === '/' && 'focus' in client) {
            return client.focus();
          }
        }
        if (clients.openWindow) {
          return clients.openWindow('/');
        }
      })
    );
  }
});

/**
 * Determine cache strategy for request
 */
function getCacheStrategy(request) {
  const url = new URL(request.url);
  
  // App shell resources
  if (APP_SHELL_RESOURCES.includes(url.pathname)) {
    return CACHE_STRATEGIES.APP_SHELL;
  }
  
  // Content patterns
  if (CONTENT_PATTERNS.some(pattern => pattern.test(url.pathname))) {
    return CACHE_STRATEGIES.CONTENT;
  }
  
  // API patterns
  if (API_PATTERNS.some(pattern => pattern.test(url.pathname))) {
    return CACHE_STRATEGIES.API;
  }
  
  // Image patterns
  if (IMAGE_PATTERNS.some(pattern => pattern.test(url.pathname))) {
    return CACHE_STRATEGIES.IMAGES;
  }
  
  // Font patterns
  if (FONT_PATTERNS.some(pattern => pattern.test(url.href))) {
    return CACHE_STRATEGIES.FONTS;
  }
  
  // Default to network-first
  return 'network-first';
}

/**
 * Handle request based on strategy
 */
async function handleRequest(request, strategy) {
  const cacheName = getCacheName(request);
  
  switch (strategy) {
    case 'cache-first':
      return cacheFirst(request, cacheName);
    case 'network-first':
      return networkFirst(request, cacheName);
    case 'stale-while-revalidate':
      return staleWhileRevalidate(request, cacheName);
    default:
      return fetch(request);
  }
}

/**
 * Cache-first strategy
 */
async function cacheFirst(request, cacheName) {
  const cache = await caches.open(cacheName);
  const cachedResponse = await cache.match(request);
  
  if (cachedResponse) {
    return cachedResponse;
  }
  
  const networkResponse = await fetch(request);
  
  if (networkResponse.ok) {
    cache.put(request, networkResponse.clone());
  }
  
  return networkResponse;
}

/**
 * Network-first strategy
 */
async function networkFirst(request, cacheName) {
  const cache = await caches.open(cacheName);
  
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
      return cachedResponse;
    }
    
    throw error;
  }
}

/**
 * Stale-while-revalidate strategy
 */
async function staleWhileRevalidate(request, cacheName) {
  const cache = await caches.open(cacheName);
  const cachedResponse = await cache.match(request);
  
  const fetchPromise = fetch(request).then(networkResponse => {
    if (networkResponse.ok) {
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  });
  
  return cachedResponse || fetchPromise;
}

/**
 * Get appropriate cache name for request
 */
function getCacheName(request) {
  const url = new URL(request.url);
  
  if (APP_SHELL_RESOURCES.includes(url.pathname)) {
    return CACHES.APP_SHELL;
  }
  
  if (CONTENT_PATTERNS.some(pattern => pattern.test(url.pathname))) {
    return CACHES.CONTENT;
  }
  
  if (API_PATTERNS.some(pattern => pattern.test(url.pathname))) {
    return CACHES.API;
  }
  
  if (IMAGE_PATTERNS.some(pattern => pattern.test(url.pathname))) {
    return CACHES.IMAGES;
  }
  
  if (FONT_PATTERNS.some(pattern => pattern.test(url.href))) {
    return CACHES.FONTS;
  }
  
  return CACHES.CONTENT;
}

/**
 * Clean up old caches
 */
async function cleanupOldCaches() {
  const cacheNames = await caches.keys();
  const currentCaches = Object.values(CACHES);
  
  const deletePromises = cacheNames
    .filter(cacheName => 
      cacheName.startsWith(CACHE_PREFIX) && 
      !currentCaches.includes(cacheName)
    )
    .map(cacheName => {
      console.log('[SW] Deleting old cache:', cacheName);
      return caches.delete(cacheName);
    });
  
  return Promise.all(deletePromises);
}

/**
 * Get offline fallback response
 */
function getOfflineFallback(request) {
  const url = new URL(request.url);
  
  // For HTML pages, return offline page
  if (request.headers.get('accept').includes('text/html')) {
    return caches.match('/offline.html') || 
           new Response('You are offline. Please check your connection.', {
             status: 200,
             headers: { 'Content-Type': 'text/html' }
           });
  }
  
  // For images, return placeholder
  if (IMAGE_PATTERNS.some(pattern => pattern.test(url.pathname))) {
    return caches.match('/wp-content/themes/pmp-dashboard/assets/images/offline-placeholder.png');
  }
  
  // For API calls, return offline indicator
  if (API_PATTERNS.some(pattern => pattern.test(url.pathname))) {
    return new Response(JSON.stringify({
      error: 'offline',
      message: 'You are currently offline. This action will be synced when you reconnect.'
    }), {
      status: 503,
      headers: { 'Content-Type': 'application/json' }
    });
  }
  
  // Default fallback
  return new Response('Network error occurred', {
    status: 408,
    statusText: 'Request Timeout'
  });
}

/**
 * Background sync functions
 */
async function syncProgress() {
  try {
    const syncData = await getStoredSyncData('progress');
    
    for (const item of syncData) {
      await fetch('/wp-json/pmp/v1/progress/sync', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(item)
      });
    }
    
    await clearStoredSyncData('progress');
    console.log('[SW] Progress sync completed');
  } catch (error) {
    console.error('[SW] Progress sync failed:', error);
  }
}

async function syncTestResults() {
  try {
    const syncData = await getStoredSyncData('test-results');
    
    for (const item of syncData) {
      await fetch('/wp-json/pmp/v1/test/sync-results', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(item)
      });
    }
    
    await clearStoredSyncData('test-results');
    console.log('[SW] Test results sync completed');
  } catch (error) {
    console.error('[SW] Test results sync failed:', error);
  }
}

async function syncBookmarks() {
  try {
    const syncData = await getStoredSyncData('bookmarks');
    
    for (const item of syncData) {
      await fetch('/wp-json/pmp/v1/bookmark/sync', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(item)
      });
    }
    
    await clearStoredSyncData('bookmarks');
    console.log('[SW] Bookmarks sync completed');
  } catch (error) {
    console.error('[SW] Bookmarks sync failed:', error);
  }
}

/**
 * IndexedDB helpers for sync data
 */
async function getStoredSyncData(type) {
  // This would integrate with IndexedDB implementation
  // For now, return empty array
  return [];
}

async function clearStoredSyncData(type) {
  // This would clear IndexedDB sync data
  // Implementation will be added in T009
  console.log(`[SW] Cleared sync data for: ${type}`);
}

console.log('[SW] Service worker script loaded');
