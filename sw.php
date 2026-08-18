<?php
/**
 * Senoobar Service Worker - Dynamic PHP Version
 * This file is served as service-worker.js via rewrite rule
 */

// Load WordPress
$wp_load_path = __DIR__ . '/../../../wp-load.php';
if (!file_exists($wp_load_path)) {
    // Try alternative paths
    $possible_paths = [
        __DIR__ . '/../../../../wp-load.php',
        __DIR__ . '/../../wp-load.php',
    ];
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            $wp_load_path = $path;
            break;
        }
    }
}

if (file_exists($wp_load_path)) {
    require_once $wp_load_path;
} else {
    // Fallback if WordPress not loaded
    header('Content-Type: application/javascript');
    echo "console.error('[SW] WordPress not loaded');";
    exit;
}

// Set headers
header('Content-Type: application/javascript');
header('Service-Worker-Allowed: /');
header('Cache-Control: no-cache, must-revalidate');

$theme_uri = get_template_directory_uri();
$home_url = home_url('/');
$offline_url = $home_url . 'offline/';
$cache_version = 'senoobar-v' . wp_get_theme()->get('Version');
$static_cache = $cache_version . '-static';
$dynamic_cache = $cache_version . '-dynamic';
$api_cache = $cache_version . '-api';

// Precaching URLs - use dynamic theme URI
$precache_urls = [
    $home_url,
    $theme_uri . '/assets/css/critical.css',
    $theme_uri . '/assets/css/main.css',
    $theme_uri . '/assets/js/app.js',
    $theme_uri . '/assets/icons/icon-192.png',
    $offline_url,
];

// Convert to JS array
$precache_js = '[' . implode(', ', array_map(function($url) { return "'" . esc_js($url) . "'"; }, $precache_urls)) . ']';
?>
/**
 * Senoobar Service Worker
 * Caching strategy: Cache First with Network Update for static assets
 * Network First for API calls
 * Dynamic version - paths resolved via WordPress
 */

const CACHE_VERSION = '<?php echo esc_js($cache_version); ?>';
const STATIC_CACHE = CACHE_VERSION + '-static';
const DYNAMIC_CACHE = CACHE_VERSION + '-dynamic';
const API_CACHE = CACHE_VERSION + '-api';

// Assets to pre-cache on install
const PRECACHE_URLS = <?php echo $precache_js; ?>;

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

  // NEVER cache these WooCommerce dynamic pages
  const dynamicPaths = [
    '/cart/',
    '/checkout/',
    '/my-account/',
    '/login/',
    '/register/',
    '/lost-password/',
    '/order-received/',
    '/wc-api/',
    '/wp-admin/admin-ajax.php',
    '/wp-json/wc/',
  ];
  
  const isDynamicPage = dynamicPaths.some(path => url.pathname.includes(path));
  const isWcAjax = url.pathname.includes('/wp-admin/admin-ajax.php');
  const isWcRest = url.pathname.includes('/wp-json/wc/');

  if (isDynamicPage || isWcAjax || isWcRest) {
    // Network only - never cache dynamic WooCommerce pages
    event.respondWith(fetch(request));
    return;
  }

  // HTML: Network First with offline fallback
  if (request.mode === 'navigate' || (request.headers.get('Accept') || '').indexOf('text/html') !== -1) {
    event.respondWith(
      fetch(request).then(response => {
        const clone = response.clone();
        caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, clone));
        return response;
      }).catch(() => {
        return caches.match(request).then(cached => cached || caches.match('<?php echo esc_js($offline_url); ?>'));
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

  // Other API / AJAX: Network First
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
  
  // Default: network first
  event.respondWith(fetch(request));
});

// Push Notification
self.addEventListener('push', event => {
  let data = { 
    title: 'صنوبر', 
    body: 'پیشنهاد ویژه برای شما!', 
    icon: '<?php echo esc_js($theme_uri . '/assets/icons/icon-192.png'); ?>', 
    badge: '<?php echo esc_js($theme_uri . '/assets/icons/badge-72.png'); ?>', 
    data: { url: '<?php echo esc_js($home_url); ?>' } 
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
    })
  );
});

// Notification click
self.addEventListener('notificationclick', event => {
  event.notification.close();
  var notifData = event.notification.data || {};
  const url = notifData.url || '<?php echo esc_js($home_url); ?>';
  
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