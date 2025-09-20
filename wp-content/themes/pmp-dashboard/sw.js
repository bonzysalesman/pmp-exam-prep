const CACHE_NAME = 'pmp-prep-v1.0.0';
const urlsToCache = [
  '/',
  '/dashboard/',
  '/wp-content/themes/pmp-dashboard/style.css',
  '/wp-content/themes/pmp-dashboard/assets/js/dashboard.js',
  '/wp-content/themes/pmp-dashboard/assets/images/pmp-exam-prep-logo.png',
  '/wp-content/themes/pmp-dashboard/assets/images/mohlomi_institute_logo_black_to_white.png',
  'https://cdn.tailwindcss.com',
  'https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;900&display=swap',
  'https://use.fontawesome.com/releases/v6.0.0/css/all.css'
];

// Install event - cache resources
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
      .catch(error => {
        console.log('Cache install failed:', error);
      })
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Return cached version or fetch from network
        if (response) {
          return response;
        }
        
        return fetch(event.request).then(response => {
          // Don't cache non-successful responses
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }
          
          // Clone the response
          const responseToCache = response.clone();
          
          // Cache dynamic content selectively
          if (event.request.url.includes('/lesson/') || 
              event.request.url.includes('/work-group/') ||
              event.request.url.includes('/dashboard/')) {
            caches.open(CACHE_NAME)
              .then(cache => {
                cache.put(event.request, responseToCache);
              });
          }
          
          return response;
        }).catch(() => {
          // Return offline page for navigation requests
          if (event.request.mode === 'navigate') {
            return caches.match('/offline.html');
          }
        });
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Background sync for progress updates
self.addEventListener('sync', event => {
  if (event.tag === 'progress-sync') {
    event.waitUntil(syncProgressData());
  }
});

// Sync progress data when online
async function syncProgressData() {
  try {
    const progressData = await getStoredProgressData();
    if (progressData.length > 0) {
      for (const data of progressData) {
        await fetch('/wp-admin/admin-ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            action: 'update_lesson_progress',
            ...data
          })
        });
      }
      // Clear stored data after successful sync
      await clearStoredProgressData();
    }
  } catch (error) {
    console.log('Progress sync failed:', error);
  }
}

// Get stored progress data from IndexedDB
async function getStoredProgressData() {
  return new Promise((resolve) => {
    const request = indexedDB.open('pmp-progress', 1);
    
    request.onsuccess = () => {
      const db = request.result;
      const transaction = db.transaction(['progress'], 'readonly');
      const store = transaction.objectStore('progress');
      const getAllRequest = store.getAll();
      
      getAllRequest.onsuccess = () => {
        resolve(getAllRequest.result || []);
      };
    };
    
    request.onerror = () => resolve([]);
  });
}

// Clear stored progress data
async function clearStoredProgressData() {
  return new Promise((resolve) => {
    const request = indexedDB.open('pmp-progress', 1);
    
    request.onsuccess = () => {
      const db = request.result;
      const transaction = db.transaction(['progress'], 'readwrite');
      const store = transaction.objectStore('progress');
      store.clear();
      resolve();
    };
    
    request.onerror = () => resolve();
  });
}

// Push notification handling
self.addEventListener('push', event => {
  const options = {
    body: event.data ? event.data.text() : 'Time for your daily PMP study!',
    icon: '/wp-content/themes/pmp-dashboard/assets/images/pmp-exam-prep-logo.png',
    badge: '/wp-content/themes/pmp-dashboard/assets/images/pmp-exam-prep-logo.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'Start Learning',
        icon: '/wp-content/themes/pmp-dashboard/assets/images/pmp-exam-prep-logo.png'
      },
      {
        action: 'close',
        title: 'Close',
        icon: '/wp-content/themes/pmp-dashboard/assets/images/pmp-exam-prep-logo.png'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification('PMP Exam Prep', options)
  );
});

// Notification click handling
self.addEventListener('notificationclick', event => {
  event.notification.close();
  
  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('/dashboard/')
    );
  }
});
