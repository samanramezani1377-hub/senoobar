<?php
/**
 * Senoobar PWA Manifest - Dynamic PHP Version
 */

// Load WordPress
$wp_load_path = __DIR__ . '/../../../wp-load.php';
if (!file_exists($wp_load_path)) {
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
    header('Content-Type: application/manifest+json');
    echo json_encode(['error' => 'WordPress not loaded']);
    exit;
}

header('Content-Type: application/manifest+json');
header('Cache-Control: public, max-age=86400');

$theme_uri = get_template_directory_uri();
$home_url = home_url('/');

$manifest = [
    'name'              => 'صنوبر - تشک طبی، فنری و کالای خواب',
    'short_name'        => 'صنوبر',
    'description'       => 'فروشگاه اینترنتی صنوبر - عرضه‌کننده تخصصی انواع تشک طبی، طبی فنری و کالای خواب با بهترین کیفیت و قیمت',
    'start_url'         => $home_url,
    'display'           => 'standalone',
    'background_color'  => '#ffffff',
    'theme_color'       => '#1e3a2f',
    'orientation'       => 'portrait-primary',
    'lang'              => 'fa-IR',
    'dir'               => 'rtl',
    'icons'             => [
        ['src' => $theme_uri . '/assets/icons/icon-72.png',   'sizes' => '72x72',   'type' => 'image/png', 'purpose' => 'any maskable'],
        ['src' => $theme_uri . '/assets/icons/icon-96.png',   'sizes' => '96x96',   'type' => 'image/png', 'purpose' => 'any maskable'],
        ['src' => $theme_uri . '/assets/icons/icon-128.png',  'sizes' => '128x128', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ['src' => $theme_uri . '/assets/icons/icon-144.png',  'sizes' => '144x144', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ['src' => $theme_uri . '/assets/icons/icon-152.png',  'sizes' => '152x152', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ['src' => $theme_uri . '/assets/icons/icon-192.png',  'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ['src' => $theme_uri . '/assets/icons/icon-384.png',  'sizes' => '384x384', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ['src' => $theme_uri . '/assets/icons/icon-512.png',  'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
    ],
    'screenshots'       => [
        ['src' => $theme_uri . '/assets/screenshots/mobile-1.png',  'sizes' => '1080x1920', 'type' => 'image/png', 'form_factor' => 'narrow'],
        ['src' => $theme_uri . '/assets/screenshots/desktop-1.png', 'sizes' => '1920x1080', 'type' => 'image/png', 'form_factor' => 'wide'],
    ],
    'categories'        => ['shopping', 'lifestyle'],
    'prefer_related_applications' => false,
];

echo json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);