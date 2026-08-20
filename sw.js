/**
 * Senoobar Service Worker
 * Strategy: Cache static assets only (CSS/JS/fonts/images).
 * No HTML navigation interception -> no network error / redirect issues.
 */

const CACHE_VERSION = 'senoobar-v4.0.0';
const STATIC_CACHE = CACHE_VERSION + '-static';

const ORIGIN = self.location.origin;

// Theme asset paths
const ASSETS_TO_CACHE = [
	'/',
	'/wp-content/themes/senoobar/assets/css/main.css',
	'/wp-content/themes/senoobar/assets/css/critical.css',
	'/wp-content/themes/senoobar/assets/css/rtl.css',
	'/wp-content/themes/senoobar/assets/fonts/vazirmatn-arabic.woff2',
	'/wp-content/themes/senoobar/assets/fonts/vazirmatn-latin.woff2',
	'/wp-content/themes/senoobar/assets/icons/icon-128.png',
	'/wp-content/themes/senoobar/assets/icons/icon-152.png'
];

self.addEventListener('install', (event) => {
	event.waitUntil(
		caches.open(STATIC_CACHE).then((cache) => {
			return cache.addAll(ASSETS_TO_CACHE).catch((err) => {
				console.warn('Pre-caching assets failed, will cache on demand:', err);
			});
		}).then(() => self.skipWaiting())
	);
});

self.addEventListener('activate', (event) => {
	event.waitUntil(
		caches.keys().then((cacheNames) => {
			return Promise.all(
				cacheNames.map((cacheName) => {
					if (cacheName !== STATIC_CACHE) {
						return caches.delete(cacheName);
					}
				})
			);
		}).then(() => self.clients.claim())
	);
});

self.addEventListener('fetch', (event) => {
	const request = event.request;

	// Only cache local GET requests
	if (request.method !== 'GET' || !request.url.startsWith(ORIGIN)) {
		return;
	}

	const url = new URL(request.url);

	// Skip admin pages, checkout, cart and dynamic PHP endpoints
	if (
		url.pathname.includes('/wp-admin') ||
		url.pathname.includes('/wp-login.php') ||
		url.pathname.includes('/cart') ||
		url.pathname.includes('/checkout') ||
		url.pathname.includes('/my-account') ||
		request.url.includes('wp-json')
	) {
		return;
	}

	// Cache-First strategy for assets (CSS, JS, Fonts, Images)
	const isAsset = 
		request.destination === 'style' ||
		request.destination === 'script' ||
		request.destination === 'font' ||
		request.destination === 'image' ||
		url.pathname.endsWith('.css') ||
		url.pathname.endsWith('.js') ||
		url.pathname.match(/\.(woff2|woff|ttf|png|jpg|jpeg|gif|svg|webp)$/i);

	if (isAsset) {
		event.respondWith(
			caches.match(request).then((cachedResponse) => {
				if (cachedResponse) {
					// Return cached, but fetch fresh in background to update cache
					fetch(request).then((networkResponse) => {
						if (networkResponse.status === 200) {
							caches.open(STATIC_CACHE).then((cache) => {
								cache.put(request, networkResponse);
							});
						}
					}).catch(() => {/* ignore network failures in background */});
					return cachedResponse;
				}

				return fetch(request).then((networkResponse) => {
					if (networkResponse.status === 200) {
						const responseToCache = networkResponse.clone();
						caches.open(STATIC_CACHE).then((cache) => {
							cache.put(request, responseToCache);
						});
					}
					return networkResponse;
				});
			})
		);
	}
});