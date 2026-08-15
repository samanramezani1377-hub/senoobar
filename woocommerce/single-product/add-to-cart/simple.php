<?php
/**
 * Simple product add-to-cart (custom — no quantity display, no stock line).
 * The quantity is kept as a hidden field; the UI switches the button into a
 * stepper after the first add (handled by assets/js/product-buy-box.js).
 */

defined('ABSPATH') || exit;

global $product;

if (!$product->is_purchasable()) {
    return;
}

echo wc_get_stock_html($product); // empty if filtered

do_action('woocommerce_before_add_to_cart_form');
?>
<form class="cart" method="post" enctype='multipart/form-data'>
    <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" />
    <input type="hidden" name="quantity" id="pdHiddenQty" value="1" min="1" />

    <button type="submit"
            name="add-to-cart"
            value="<?php echo esc_attr($product->get_id()); ?>"
            class="single_add_to_cart_button button alt<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>">
        <?php echo esc_html($product->single_add_to_cart_text()); ?>
    </button>

    <?php do_action('woocommerce_after_add_to_cart_button'); ?>
</form>
<?php do_action('woocommerce_after_add_to_cart_form'); ?>
