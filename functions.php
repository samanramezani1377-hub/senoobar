<?php
define('SENOOBAR_VERSION', '2.0.0');
define('SENOOBAR_DIR', get_template_directory());
define('SENOOBAR_URI', get_template_directory_uri());

require_once SENOOBAR_DIR . '/inc/class-senoobar-theme.php';
require_once SENOOBAR_DIR . '/inc/cart-handlers.php';

function senoobar_init() {
    Senoobar_Theme::get_instance()->init();
}
add_action('after_setup_theme', 'senoobar_init');
function senoobar_cart_assets() {

    if ( is_cart() ) {

        wp_enqueue_style(
            'senoobar-cart',
            get_template_directory_uri() . '/assets/css/cart.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'senoobar-cart',
            get_template_directory_uri() . '/assets/js/cart.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script(
            'senoobar-cart',
            'senoobar_cart',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('senoobar_cart_nonce')
            )
        );
    }
}

add_action('wp_enqueue_scripts', 'senoobar_cart_assets');
