/**
 * Senoobar Service Worker
 * Strategy: Cache static assets only (CSS/JS/fonts/images).
 * No HTML navigation interception -> no network error / redirect issues.
 */

const CACHE_VERSION = 'senoobar-v2.1.0';
const STATIC_CACHE = CACHE_VERSION + '-static';

// Assets to pre-cache on install (all return 200)
const PRECACHE_URLS = [
  'https://senoobar.ir/wp-content/themes/senoobar.ir-main/assets/css/critical.css',
  'https://senoobar.ir/wp-content/themes/senoobar.ir-main/assets/css/main.css',
  'https://senoobar.ir/wp-content/themes/senoobar.ir-main/assets/js/app.js',
  'https://senoobar.ir/wp-content/themes/senoobar.ir-main/assets/icons/icon-192.png'
];

// Install: precache static assets individually (one failure won't break the rest)
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

// Activate: remove old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames
          .filter(name => name.startsWith('senoobar-') && name !== STATIC_CACHE)
          .map(name => caches.delete(name))
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch: only cache static assets. Never touch HTML/API/navigation.
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Only GET on our own origin
  if (request.method !== 'GET' || url.origin !== self.location.origin) return;

  // Only handle static assets
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
        }).catch(() => {
          // If network fails, try cache once more (already handled above)
          return caches.match(request);
        });
      })
    );
    return;
  }

  // Everything else: pass through to network untouched
  return;
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
