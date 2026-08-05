<?php
/**
 * Senoobar Theme Functions
 * قالب فروشگاهی صنوبر - توابع اصلی
 */

define('SENOOBAR_VERSION', '1.0.0');
define('SENOOBAR_DIR', get_template_directory());
define('SENOOBAR_URI', get_template_directory_uri());

// Autoload
require_once SENOOBAR_DIR . '/inc/class-senoobar-theme.php';

function senoobar_init() {
    $theme = Senoobar_Theme::get_instance();
    $theme->init();
}
add_action('after_setup_theme', 'senoobar_init');