/**
 * Senoobar Service Worker
 * Caching strategy: Cache First with Network Update for static assets
 * Network First for API calls
 */

const CACHE_VERSION = 'senoobar-v1.0.0';
const STATIC_CACHE = CACHE_VERSION + '-static';
const DYNAMIC_CACHE = CACHE_VERSION + '-dynamic';
const API_CACHE = CACHE_VERSION + '-api';

// Assets to pre-cache on install
const PRECACHE_URLS = [
  '/',
  '/wp-content/themes/senoobar/assets/css/critical.css',
  '/wp-content/themes/senoobar/assets/css/main.css',
  '/wp-content/themes/senoobar/assets/js/app.js',
  '/wp-content/themes/senoobar/assets/fonts/IRANSansWeb.woff2',
  '/wp-content/themes/senoobar/assets/fonts/IRANSansWeb_Bold.woff2',
  '/wp-content/themes/senoobar/assets/icons/icon-192.png',
  '/offline/',
];

// Install event - precache critical assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then(cache => {
      console.log('[SW] Precaching critical assets');
      return cache.addAll(PRECACHE_URLS).catch(err => {
        console.warn('[SW] Precache partial failure:', err);
      });
    }).then(() => self.skipWaiting())
  );
});

// Activate event - clean old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames
          .filter(name => name.startsWith('senoobar-') && name !== STATIC_CACHE && name !== DYNAMIC_CACHE && name !== API_CACHE)
          .map(name => caches.delete(name))
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch event - smart caching strategy
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests and external URLs
  if (request.method !== 'GET' || !url.origin.includes(self.location.origin)) return;

  // HTML: Network First with offline fallback
  if (request.mode === 'navigate' || request.headers.get('Accept')?.includes('text/html')) {
    event.respondWith(
      fetch(request).then(response => {
        const clone = response.clone();
        caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, clone));
        return response;
      }).catch(() => {
        return caches.match(request).then(cached => cached || caches.match('/offline/'));
      })
    );
    return;
  }

  // Static assets (CSS, JS, fonts, images): Cache First
  if (
    url.pathname.match(/\.(css|js|woff2?|png|jpg|jpeg|gif|svg|webp|ico)$/) ||
    url.pathname.includes('/assets/')
  ) {
    event.respondWith(
      caches.match(request).then(cached => {
        const fetchPromise = fetch(request).then(response => {
          caches.open(STATIC_CACHE).then(cache => cache.put(request, response.clone()));
          return response;
        });
        return cached || fetchPromise;
      })
    );
    return;
  }

  // API / AJAX: Network First, no cache for admin-ajax
  if (url.pathname.includes('/wp-json/') || url.pathname.includes('/wc-api/')) {
    event.respondWith(
      fetch(request).then(response => {
        const clone = response.clone();
        caches.open(API_CACHE).then(cache => cache.put(request, clone));
        return response;
      }).catch(() => caches.match(request))
    );
    return;
  }
});

// Push Notification
self.addEventListener('push', event => {
  let data = { title: 'صنوبر', body: 'پیشنهاد ویژه برای شما!', icon: '/wp-content/themes/senoobar/assets/icons/icon-192.png', badge: '/wp-content/themes/senoobar/assets/icons/badge-72.png', data: { url: '/' } };
  
  if (event.data) {
    try { data = { ...data, ...event.data.json() }; } catch(e) {}
  }

  event.waitUntil(
    self.registration.showNotification(data.title, {
      body: data.body,
      icon: data.icon,
      badge: data.badge,
      vibrate: [200, 100, 200],
      tag: 'senoobar-notification',
      renotify: true,
      requireInteraction: false,
      actions: [
        { action: 'view', title: 'مشاهده' },
        { action: 'close', title: 'بستن' }
      ],
      data: data.data,
      dir: 'rtl',
      lang: 'fa-IR',
    })
  );
});

// Notification click
self.addEventListener('notificationclick', event => {
  event.notification.close();
  const url = event.notification.data?.url || '/';
  
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
      for (const client of clientList) {
        if (client.url.includes(url) && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) return clients.openWindow(url);
    })
  );
});
