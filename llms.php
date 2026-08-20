<?php
/**
 * Senoobar LLMs.txt - Dynamic PHP Version.
 *
 * Serves the /llms.txt file (the emerging standard for large language models,
 * see https://llmstxt.org) so AI crawlers/agents can understand the site's
 * structure and key pages at a glance.
 *
 * SECURITY: This file MUST NOT load wp-load.php. It is only ever included from
 * inc/class-senoobar-theme.php inside `template_redirect`, where the WordPress
 * environment is already fully bootstrapped. Loading wp-load.php here (as the
 * old manifest code did, via a guessable relative path) was both fragile and
 * dangerous. If WordPress is not loaded (direct access), bail out quietly.
 */

if ( ! defined( 'ABSPATH' ) ) {
    http_response_code( 404 );
    exit;
}

header( 'Content-Type: text/plain; charset=utf-8' );
header( 'Cache-Control: public, max-age=86400' );
header( 'X-Robots-Tag: noindex' );

$home_url = home_url('/');

$lines = [];

$lines[] = '# ' . get_bloginfo('name');
$lines[] = '';
$lines[] = '> ' . get_bloginfo('description');
$lines[] = '';

$lines[] = 'صفحه‌های اصلی:';
$lines[] = '- [خانه](' . $home_url . '): معرفی برند و محصولات';
$lines[] = '- [فروشگاه](' . $home_url . 'shop/): لیست کامل محصولات';
$lines[] = '- [سوالات متداول](' . $home_url . 'faq/): راهنمای خرید و پاسخ به پرسش‌ها';
$lines[] = '- [تماس با ما](' . $home_url . 'contact/): اطلاعات تماس';

// Add other top-level static pages when they resolve to a real permalink.
foreach (['about', 'privacy', 'terms', 'wishlist'] as $slug) {
    $lines[] = '- [' . $slug . '](' . $home_url . $slug . '/)';
}

echo implode("\n", $lines);
