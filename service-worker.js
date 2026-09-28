// Service Worker for WebRetailMinuman PWA
const CACHE_VERSION = 'webretailminuman-v2';
const CACHE_FILES = [
  '/WebRetailMinuman/',
  '/WebRetailMinuman/index.php',
  '/WebRetailMinuman/dashboard/dashboard.php',
  '/WebRetailMinuman/assets/css/main.css',
  '/WebRetailMinuman/assets/css/components.css',
  '/WebRetailMinuman/assets/css/modules.css',
  '/WebRetailMinuman/assets/js/app.js',
  '/WebRetailMinuman/assets/js/pwa.js',
  '/WebRetailMinuman/manifest.json'
];

// Install event - cache resources
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing...');
  event.waitUntil(
    caches.open(CACHE_VERSION).then((cache) => {
      console.log('[Service Worker] Caching app shell');
      return cache.addAll(CACHE_URLS).catch((err) => {
        console.warn('[Service Worker] Cache addAll error:', err);
      });
    }).then(() => self.skipWaiting())
  );
});

// Activate event - clean old caches
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activating...');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((cacheName) => cacheName !== CACHE_VERSION)
          .map((cacheName) => {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }

  // Skip cross-origin requests
  if (url.origin !== location.origin) {
    return;
  }

  // API calls - Network first, fallback to cache
  if (url.pathname.includes('/api/') || url.pathname.endsWith('.php')) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          // Cache successful responses
          if (response.status === 200) {
            const cache = caches.open(CACHE_VERSION);
            cache.then((c) => c.put(request, response.clone()));
          }
          return response;
        })
        .catch(() => {
          // Fallback to cache
          return caches.match(request).then((cachedResponse) => {
            return cachedResponse || new Response('Offline: Data tidak tersedia', { status: 503 });
          });
        })
    );
    return;
  }

  // Static assets - Cache first, fallback to network
  event.respondWith(
    caches.match(request).then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse;
      }

      return fetch(request)
        .then((response) => {
          // Cache successful responses
          if (response.status === 200 && response.type === 'basic') {
            const cache = caches.open(CACHE_VERSION);
            cache.then((c) => c.put(request, response.clone()));
          }
          return response;
        })
        .catch(() => {
          // Offline fallback
          if (request.destination === 'image') {
            return caches.match('/WebRetailMinuman/assets/img/icon-192.svg');
          }
          return new Response('Offline: Resource tidak tersedia', { status: 503 });
        });
    })
  );
});

// Background sync (optional - for offline transactions)
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-transaksi') {
    event.waitUntil(
      // Handle offline transaksi sync
      Promise.resolve()
    );
  }
});

console.log('[Service Worker] Loaded successfully');

