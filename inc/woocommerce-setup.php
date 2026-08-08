<?php
/**
 * WooCommerce Integration (v2)
 * Deep Green Palette — senoobar2 style
 */

if (!class_exists('WooCommerce')) {
    return;
}

// ─── 1. Theme Support ──────────────────────
add_action('after_setup_theme', function () {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
});

// ─── 2. Kill default Woo styles ─────────────
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

// ─── 3. Content wrappers ────────────────────
// archive-product.php handles its own wrapper, so only wrap in non-shop contexts
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', function () {
    // archive-product.php has its own <main> wrapper
    if (is_shop() || is_product_category() || is_product_tag()) {
        return;
    }
    echo '<main id="primary" class="site-main"><div class="container page-content">';
}, 10);

add_action('woocommerce_after_main_content', function () {
    if (is_shop() || is_product_category() || is_product_tag()) {
        return;
    }
    echo '</div></main>';
}, 10);

// ─── 4. Remove sidebar ──────────────────────
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

// ─── 5. Products per page ───────────────────
add_filter('loop_shop_per_page', function () {
    return 24;
});

// ─── 6. Products per row ────────────────────
// CSS Grid handles columns; let WooCommerce know we have 3
add_filter('loop_shop_columns', function () {
    return 3;
});

// Override WooCommerce body class columns
add_filter('body_class', function ($classes) {
    if (is_shop() || is_product_category() || is_product_tag()) {
        $classes = array_diff($classes, ['columns-4', 'columns-3', 'columns-2']);
        $classes[] = 'columns-3';
    }
    return $classes;
});

// Remove WooCommerce columns class from ul.products
add_filter('post_class', function ($classes) {
    if (function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag())) {
        $classes = array_diff($classes, ['columns-4', 'columns-3', 'columns-2']);
        $classes[] = 'columns-3';
    }
    return $classes;
});

// ─── 7. Sale badge ──────────────────────────
add_filter('woocommerce_sale_flash', function ($html) {
    return '<span class="onsale-badge">حراج</span>';
});

// ─── 8. Add-to-cart button text ──────────────
add_filter('woocommerce_product_add_to_cart_text', function () {
    return 'افزودن به سبد خرید';
});

add_filter('woocommerce_product_single_add_to_cart_text', function () {
    return 'افزودن به سبد خرید';
});

// ─── 9. Related products ─────────────────────
add_filter('woocommerce_output_related_products_args', function ($args) {
    $args['posts_per_page'] = 8;
    $args['columns']        = 4;
    return $args;
});

// ─── 10. AJAX add-to-cart ──────────────────
add_filter('woocommerce_loop_add_to_cart_args', function ($args) {
    $args['class'] .= ' ajax_add_to_cart';
    return $args;
});

// ─── 11. AJAX Cart Fragments (cart count) ──
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    $count = WC()->cart->get_cart_contents_count();
    ob_start();
    ?>
    <span class="cart-badge"><?php echo $count; ?></span>
    <?php
    $fragments['.cart-badge'] = ob_get_clean();
    return $fragments;
});

// ─── 12. Disable password strength meter ────
add_action('wp_print_scripts', function () {
    if (wp_script_is('wc-password-strength-meter', 'enqueued')) {
        wp_dequeue_script('wc-password-strength-meter');
    }
}, 100);
