const CACHE_NAME = 'pmp-prep-v1';
const urlsToCache = [
  '/dashboard.html',
  '/wg1-building-team.html',
  '/wg2-starting-project.html',
  '/wg3-doing-work.html',
  '/wg4-keeping-track.html',
  '/wg5-focus-business.html',
  '/lesson-template.html',
  '/practice-tests.html',
  '/resources.html',
  '/pmp-exam-prep-logo.png',
  '/mohlomi_institute_logo_black_to_white.png',
  'https://cdn.tailwindcss.com',
  'https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;900&display=swap',
  'https://use.fontawesome.com/releases/v6.0.0/css/all.css'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request);
      }
    )
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});
