/**
 * Senoobar Service Worker
 * Strategy: Cache static assets only (CSS/JS/fonts/images).
 * No HTML navigation interception -> no network error / redirect issues.
 */

const CACHE_VERSION = 'senoobar-v2.4.0';