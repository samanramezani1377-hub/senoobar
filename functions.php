<?php
/**
 * Senoobar Theme — Bootstrap
 */

define('SENOOBAR_VERSION', '2.0.0');
define('SENOOBAR_DIR', get_template_directory());
define('SENOOBAR_URI', get_template_directory_uri());

// Load core class
require_once SENOOBAR_DIR . '/inc/class-senoobar-theme.php';

// Load WooCommerce integration (hooks fire on `after_setup_theme`)
require_once SENOOBAR_DIR . '/inc/woocommerce-setup.php';

// Load push notification AJAX handlers
require_once SENOOBAR_DIR . '/inc/push-handlers.php';

/**
 * Boot theme on after_setup_theme
 */
add_action('after_setup_theme', function () {
    Senoobar_Theme::get_instance()->init();
});
