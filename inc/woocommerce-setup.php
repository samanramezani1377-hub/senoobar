<?php
/**
 * WooCommerce Integration
 */

if (!class_exists('WooCommerce')) {
    return;
}

// -------------------------------------------------------
// 1. Theme support
// -------------------------------------------------------
add_action('after_setup_theme', function () {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
});

// -------------------------------------------------------
// 2. Kill default WooCommerce styles (we ship our own)
// -------------------------------------------------------
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

// -------------------------------------------------------
// 3. Content wrappers
// -------------------------------------------------------
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', function () {
    echo '<main id="primary" class="site-main"><div class="container">';
}, 10);

add_action('woocommerce_after_main_content', function () {
    echo '</div></main>';
}, 10);

// -------------------------------------------------------
// 4. Remove sidebar
// -------------------------------------------------------
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

// -------------------------------------------------------
// 5. Products per page
// -------------------------------------------------------
add_filter('loop_shop_per_page', function () {
    return 24;
});

// -------------------------------------------------------
// 6. Products per row
// -------------------------------------------------------
add_filter('loop_shop_columns', function () {
    return 4;
});

// -------------------------------------------------------
// 7. Sale badge
// -------------------------------------------------------
add_filter('woocommerce_sale_flash', function ($html) {
    return '<span class="onsale">حراج</span>';
});

// -------------------------------------------------------
// 8. Add-to-cart button text
// -------------------------------------------------------
add_filter('woocommerce_product_add_to_cart_text', function () {
    return 'افزودن به سبد خرید';
});

add_filter('woocommerce_product_single_add_to_cart_text', function () {
    return 'افزودن به سبد خرید';
});

// -------------------------------------------------------
// 9. Related products
// -------------------------------------------------------
add_filter('woocommerce_output_related_products_args', function ($args) {
    $args['posts_per_page'] = 8;
    $args['columns']        = 4;
    return $args;
});

// -------------------------------------------------------
// 10. AJAX add-to-cart
// -------------------------------------------------------
add_filter('woocommerce_loop_add_to_cart_args', function ($args) {
    $args['class'] .= ' ajax_add_to_cart';
    return $args;
});

// -------------------------------------------------------
// 11. AJAX Cart Fragments (cart count in header)
// -------------------------------------------------------
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    $count = WC()->cart->get_cart_contents_count();
    ob_start();
    ?>
    <span class="cart-count" data-cart-count="<?php echo $count; ?>"><?php echo $count; ?></span>
    <?php
    $fragments['.cart-count'] = ob_get_clean();
    return $fragments;
});

// -------------------------------------------------------
// 12. Disable password strength meter (performance)
// -------------------------------------------------------
add_action('wp_print_scripts', function () {
    if (wp_script_is('wc-password-strength-meter', 'enqueued')) {
        wp_dequeue_script('wc-password-strength-meter');
    }
}, 100);
