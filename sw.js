/**
 * Senoobar Service Worker
 * Strategy: Cache static assets only (CSS/JS/fonts/images).
 * No HTML navigation interception -> no network error / redirect issues.
 */

const CACHE_VERSION = 'senoobar-v2.3.0';
const STATIC_CACHE = CACHE_VERSION + '-static';

// Origin + theme base path are derived at runtime so the worker keeps working
// on any domain / theme directory name (the old hardcoded senoobar.ir / -main
// paths broke the PWA the moment the site or theme slug changed).
const ORIGIN = self.location.origin;

// Theme asset base is passed as a `?theme=` query param at registration time
// (see assets/js/app.js + the senoobarData.themeBase localization), so the
// worker's precache + notification icon paths are correct on any domain or
// theme directory name. If the param is missing (bare register('/sw.js')),
// fall back to deriving the path from the worker's own URL.
const THEME_BASE = (function () {
  var p = new URL(self.location.href).searchParams.get('theme');
  if (p) {
    // Strip a trailing slash if present.
    return p.replace(/\/+$/, '');
  }
  // Fallback: the worker is served from the theme dir or from /sw.js via a
  // rewrite; in the latter case we cannot reliably detect the theme slug, so
  // leave a relative-safe base (origin) and let the runtime push data override.
  return ORIGIN;
})();

// Assets to pre-cache on install (all return 200)
const PRECACHE_URLS = [
  THEME_BASE + '/assets/css/critical.css',
  THEME_BASE + '/assets/css/main.css',
  THEME_BASE + '/assets/js/app.js',
  THEME_BASE + '/assets/icons/icon-192.png'
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
    title: 'فروشگاه صنوبر',
    body: 'جدیدترین محصولات را مشاهده کنید!',
    icon: THEME_BASE + '/assets/icons/icon-192.png',
    badge: THEME_BASE + '/assets/icons/badge-72.png',
    data: { url: ORIGIN + '/' }
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
  const url = notifData.url || (ORIGIN + '/');

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
