<?php
require_once __DIR__ . '/config/app.php';
header('Content-Type: application/javascript');
$b = APP_BASE;
?>
// Service Worker for SKRIPSIS8 PWA (dynamic paths)
const CACHE_VERSION = 'skripsis8-v3';
const BASE = '<?php echo addslashes($b); ?>';
const CACHE_URLS = [
  BASE + '/',
  BASE + '/index.php',
  BASE + '/dashboard/dashboard.php',
  BASE + '/assets/css/main.css',
  BASE + '/assets/css/components.css',
  BASE + '/assets/css/modules.css',
  BASE + '/assets/js/app.js',
  BASE + '/assets/js/pwa.js',
  BASE + '/manifest.php'
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

  if (request.method !== 'GET') return;
  if (url.origin !== location.origin) return;

  // PHP pages - Network first, fallback to cache
  if (url.pathname.endsWith('.php')) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          if (response.status === 200) {
            caches.open(CACHE_VERSION).then((c) => c.put(request, response.clone()));
          }
          return response;
        })
        .catch(() => caches.match(request).then((r) => r || new Response('Offline: Data tidak tersedia', { status: 503 })))
    );
    return;
  }

  // Static assets - Cache first
  event.respondWith(
    caches.match(request).then((cachedResponse) => {
      if (cachedResponse) return cachedResponse;
      return fetch(request).then((response) => {
        if (response.status === 200 && response.type === 'basic') {
          caches.open(CACHE_VERSION).then((c) => c.put(request, response.clone()));
        }
        return response;
      }).catch(() => {
        if (request.destination === 'image') {
          return caches.match(BASE + '/assets/img/icon-192.svg');
        }
        return new Response('Offline: Resource tidak tersedia', { status: 503 });
      });
    })
  );
});

self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-transaksi') {
    event.waitUntil(Promise.resolve());
  }
});

console.log('[Service Worker] Loaded (dynamic, base=' + BASE + ')');
