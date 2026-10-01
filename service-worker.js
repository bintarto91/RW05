const CACHE_NAME = 'rw05-pwa-v18';
const APP_SHELL = [
  '/',
  '/assets/style.css?v=pwa-20261001-18',
  '/assets/script.js?v=pwa-20261001-18',
  '/assets/logo-rw05.png',
  '/manifest.webmanifest'
];
const PUBLIC_CACHE_PATHS = new Set([
  '/assets/style.css',
  '/assets/script.js',
  '/assets/logo-rw05.png',
  '/favicon.svg',
  '/manifest.webmanifest'
]);

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

  // Cache hanya aset publik yang telah ditentukan. Aset admin dan halaman dinamis
  // selalu diambil dari jaringan agar HTML baru tidak bercampur dengan CSS lama.
  if (requestUrl.origin !== self.location.origin || !PUBLIC_CACHE_PATHS.has(requestUrl.pathname)) {
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
