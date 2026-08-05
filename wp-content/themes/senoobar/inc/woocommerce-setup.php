<?php
/**
 * WooCommerce Template Override
 * Override default WooCommerce templates
 */

// Remove default wrappers
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

// Add theme wrappers
add_action('woocommerce_before_main_content', function() {
    echo '<div class="container woocommerce-container"><div class="woocommerce-wrapper">';
}, 10);

add_action('woocommerce_after_main_content', function() {
    echo '</div></div>';
}, 10);

// Remove sidebar
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

// Products per page
add_filter('loop_shop_per_page', function() {
    return 24;
});

// Columns
add_filter('loop_shop_columns', function() {
    return 4;
});

// Sale badge
add_filter('woocommerce_sale_flash', function($html) {
    return '<span class="onsale senoobar-onsale">' . esc_html__('حراج!', 'senoobar') . '</span>';
});

// Add to cart text
add_filter('woocommerce_product_add_to_cart_text', function($text) {
    return __('افزودن به سبد', 'senoobar');
});

add_filter('woocommerce_product_single_add_to_cart_text', function($text) {
    return __('افزودن به سبد خرید', 'senoobar');
});

// Breadcrumb separator
add_filter('woocommerce_breadcrumb_defaults', function($defaults) {
    $defaults['delimiter'] = ' <span class="breadcrumb-sep">/</span> ';
    return $defaults;
});

// Related products count
add_filter('woocommerce_output_related_products_args', function($args) {
    $args['posts_per_page'] = 8;
    $args['columns'] = 4;
    return $args;
});

// Disable WooCommerce default styles (we handle it)
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

// AJAX add to cart on archive pages
add_filter('woocommerce_loop_add_to_cart_args', function($args) {
    $args['class'] .= ' ajax_add_to_cart';
    return $args;
});
