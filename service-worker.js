const CACHE_NAME = 'rw05-pwa-v10';
const APP_SHELL = [
  '/',
  '/assets/style.css',
  '/assets/script.js',
  '/assets/logo-rw05.png',
  '/manifest.webmanifest'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(APP_SHELL))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
    )).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  const requestUrl = new URL(event.request.url);

  // Only document navigations may fall back to the cached homepage. Returning
  // HTML for a failed stylesheet, script, or image makes the whole site appear
  // unstyled even though the server itself is healthy.
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(() => caches.match('/'))
    );
    return;
  }

  // Do not cache dynamic pages, admin responses, or third-party resources.
  const cacheableDestinations = ['style', 'script', 'image', 'font', 'manifest'];
  if (requestUrl.origin !== self.location.origin || !cacheableDestinations.includes(event.request.destination)) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        if (response.ok) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
        }
        return response;
      })
      .catch(() => caches.match(event.request, { ignoreSearch: true })
        .then((cached) => cached || Response.error()))
  );
});
