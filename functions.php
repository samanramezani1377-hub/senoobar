<?php
define('SENOOBAR_VERSION', '2.0.7');
define('SENOOBAR_DIR', get_template_directory());
define('SENOOBAR_URI', get_template_directory_uri());

require_once SENOOBAR_DIR . '/inc/class-senoobar-theme.php';
require_once SENOOBAR_DIR . '/inc/cart-handlers.php';
require_once SENOOBAR_DIR . '/inc/woocommerce-setup.php';
require_once SENOOBAR_DIR . '/inc/cart-page-setup.php';
require_once SENOOBAR_DIR . '/inc/newsletter-handlers.php';
require_once SENOOBAR_DIR . '/inc/push-handlers.php';
require_once SENOOBAR_DIR . '/inc/wishlist-page-setup.php';
require_once SENOOBAR_DIR . '/inc/account.php';
require_once SENOOBAR_DIR . '/inc/wishlist.php';

function senoobar_init() {
    Senoobar_Theme::get_instance()->init();
}
add_action('after_setup_theme', 'senoobar_init');

/**
 * Render an <img> element. Images are served as optimized .jpg (theme statics
 * are pre-resized/compressed at build time), so no <picture>/webp upgrade is
 * needed. Callers pass alt, width, height and any extra attributes.
 *
 * @param string $src  Full image URL.
 * @param array  $attr Extra attributes (alt, width, height, loading, class...).
 */
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

/**
 * Performance: drop jQuery Migrate on the front-end and defer jQuery core.
 * The theme's own JS is dependency-free (vanilla). WooCommerce's AJAX
 * add-to-cart and fragments work without jQuery on modern WC, and our JS
 * guards window.jQuery usage anyway. We keep jQuery core (deferred) so any
 * third-party plugin that declares it as a dependency still works, while
 * removing the unnecessary Migrate shim saves a render-blocking request.
 */
function senoobar_optimize_jquery() {
    if (is_admin()) return;
    // Migrate is only needed for very old themes/plugins — safe to drop.
    wp_deregister_script('jquery-migrate');
    wp_dequeue_script('jquery-migrate');

    // Defer jQuery core so it no longer blocks first paint.
    add_filter('script_loader_tag', function ($tag, $handle) {
        if ($handle === 'jquery-core' || $handle === 'jquery') {
            return str_replace(' src', ' defer src', $tag);
        }
        return $tag;
    }, 10, 2);
}
add_action('wp_enqueue_scripts', 'senoobar_optimize_jquery', 20);
