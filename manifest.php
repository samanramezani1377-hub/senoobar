<?php
/**
 * Senoobar PWA Manifest - Dynamic PHP Version.
 *
 * SECURITY: This file MUST NOT load wp-load.php. It is only ever included from
 * inc/class-senoobar-theme.php inside `template_redirect`, where the WordPress
 * environment is already fully bootstrapped. Loading wp-load.php here (as the
 * old code did, via a guessable relative path) was both fragile (it broke on
 * any non-standard directory layout) and dangerous (a guessable include path).
 *
 * If WordPress is not loaded (direct access), bail out quietly.
 */

if ( ! defined( 'ABSPATH' ) ) {
    http_response_code( 404 );
    exit;
}

header( 'Content-Type: application/manifest+json' );
header( 'Cache-Control: public, max-age=86400' );

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
    'categories'        => ['shopping', 'lifestyle'],
    'prefer_related_applications' => false,
];

echo json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);