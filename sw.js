/**
 * Senoobar Service Worker
 * Caching strategy: Cache First with Network Update for static assets
 * Network First for API calls
 */

const CACHE_VERSION = 'senoobar-v2.0.9';
const STATIC_CACHE = CACHE_VERSION + '-static';
const DYNAMIC_CACHE = CACHE_VERSION + '-dynamic';
const API_CACHE = CACHE_VERSION + '-api';

// Assets to pre-cache on install (all must return 200)
const PRECACHE_URLS = [
  'https://senoobar.ir/',
  'https://senoobar.ir/wp-content/themes/senoobar.ir-main/assets/css/critical.css',
  'https://senoobar.ir/wp-content/themes/senoobar.ir-main/assets/css/main.css',
  'https://senoobar.ir/wp-content/themes/senoobar.ir-main/assets/js/app.js',
  'https://senoobar.ir/wp-content/themes/senoobar.ir-main/assets/icons/icon-192.png'
];

// Install event - precache critical assets (individually so one failure doesn't break all)
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then(cache => {
      console.log('[SW] Precaching critical assets');
      return Promise.all(
        PRECACHE_URLS.map(url =>
          cache.add(url).catch(err => {
            console.warn('[SW] Precache failed for', url, err);
          })
        )
      );
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

  // Only handle GET requests on our own origin
  if (request.method !== 'GET' || url.origin !== self.location.origin) return;

  // NEVER cache these WooCommerce dynamic pages
  const dynamicPaths = [
    '/cart/', '/checkout/', '/my-account/', '/login/', '/register/',
    '/lost-password/', '/order-received/', '/wc-api/',
    '/wp-admin/admin-ajax.php', '/wp-json/wc/'
  ];

  if (dynamicPaths.some(path => url.pathname.includes(path))) {
    return; // Network only - let the browser handle it
  }

  // HTML navigation: Network First with offline fallback
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).then(response => {
        if (response && response.ok && response.type === 'basic') {
          const clone = response.clone();
          caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, clone)).catch(() => {});
        }
        return response;
      }).catch(() => {
        return caches.match(request).then(cached => cached || caches.match('https://senoobar.ir/'));
      })
    );
    return;
  }

  // Static assets (CSS, JS, fonts, images): Cache First
  if (url.pathname.match(/\.(css|js|woff2?|png|jpg|jpeg|gif|svg|webp|ico)$/) || url.pathname.includes('/assets/')) {
    event.respondWith(
      caches.match(request).then(cached => {
        if (cached) return cached;
        return fetch(request).then(response => {
          if (response && response.ok && response.type === 'basic') {
            const clone = response.clone();
            caches.open(STATIC_CACHE).then(cache => cache.put(request, clone)).catch(() => {});
          }
          return response;
        });
      })
    );
    return;
  }

  // Other API / AJAX: Network First
  if (url.pathname.includes('/wp-json/') || url.pathname.includes('/wc-api/')) {
    event.respondWith(
      fetch(request).then(response => {
        if (response && response.ok && response.type === 'basic') {
          const clone = response.clone();
          caches.open(API_CACHE).then(cache => cache.put(request, clone)).catch(() => {});
        }
        return response;
      }).catch(() => caches.match(request))
    );
    return;
  }

  // Default: network first, no cache
  event.respondWith(fetch(request));
});

// Push Notification
self.addEventListener('push', event => {
  let data = {
    title: 'صنوبر',
    body: 'پیشنهاد ویژه برای شما!',
    icon: 'https://senoobar.ir/wp-content/themes/senoobar.ir-main/assets/icons/icon-192.png',
    badge: 'https://senoobar.ir/wp-content/themes/senoobar.ir-main/assets/icons/badge-72.png',
    data: { url: 'https://senoobar.ir/' }
  };

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
    }).catch(() => {})
  );
});

// Notification click
self.addEventListener('notificationclick', event => {
  event.notification.close();
  var notifData = event.notification.data || {};
  const url = notifData.url || 'https://senoobar.ir/';

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
