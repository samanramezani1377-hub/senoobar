<?php
define('SENOOBAR_VERSION', '2.8.0');
define('SENOOBAR_DIR', get_template_directory());
define('SENOOBAR_URI', get_template_directory_uri());

require_once SENOOBAR_DIR . '/inc/class-senoobar-theme.php';
require_once SENOOBAR_DIR . '/inc/critical-css.php';
require_once SENOOBAR_DIR . '/inc/cart-handlers.php';
require_once SENOOBAR_DIR . '/inc/woocommerce-setup.php';
require_once SENOOBAR_DIR . '/inc/cart-page-setup.php';
require_once SENOOBAR_DIR . '/inc/newsletter-handlers.php';
require_once SENOOBAR_DIR . '/inc/push-handlers.php';
require_once SENOOBAR_DIR . '/inc/wishlist-page-setup.php';
require_once SENOOBAR_DIR . '/inc/legal-pages-setup.php';
require_once SENOOBAR_DIR . '/inc/account.php';
require_once SENOOBAR_DIR . '/inc/wishlist.php';
require_once SENOOBAR_DIR . '/inc/otp-login.php';
require_once SENOOBAR_DIR . '/inc/seo.php';
require_once SENOOBAR_DIR . '/inc/showroom-pages-setup.php';
require_once SENOOBAR_DIR . '/inc/ideas-setup.php';
require_once SENOOBAR_DIR . '/inc/idea-admin.php';

function senoobar_init() {
    Senoobar_Theme::get_instance()->init();
}
add_action('after_setup_theme', 'senoobar_init');

function senoobar_img($src, $attr = []) {
    $alt    = isset($attr['alt'])    ? $attr['alt']    : '';
    $width  = isset($attr['width'])  ? ' width="'  . (int)$attr['width']  . '"' : '';
    $height = isset($attr['height']) ? ' height="' . (int)$attr['height'] . '"' : '';
    $extra  = '';
    foreach ($attr as $k => $v) {
        if (in_array($k, ['alt', 'width', 'height'], true)) continue;
        $extra .= ' ' . $k . '="' . esc_attr($v) . '"';
    }
    return '<img src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '"' . $width . $height . $extra . '>';
}

function senoobar_optimize_jquery() {
    if (is_admin()) return;
    wp_deregister_script('jquery-migrate');
    wp_dequeue_script('jquery-migrate');
    add_filter('script_loader_tag', function ($tag, $handle) {
        if ($handle === 'jquery-core' || $handle === 'jquery') {
            return str_replace(' src', ' defer src', $tag);
        }
        return $tag;
    }, 10, 2);
}
add_action('wp_enqueue_scripts', 'senoobar_optimize_jquery', 20);
