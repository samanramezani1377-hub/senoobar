<?php
/**
 * Cart/Checkout/Account Page Setup — Senoobar
 *
 * NOTE: This file intentionally does NOT auto-create or overwrite WooCommerce
 * pages anymore. Auto-creating pages (and especially overwriting their content
 * with shortcodes like [woocommerce_cart]) was hijacking the theme's own custom
 * templates (woocommerce/cart/cart.php, etc.) and breaking the AJAX cart.
 *
 * WooCommerce itself creates these pages on install and the theme ships
 * custom templates for them. Leave page management to WooCommerce / the site
 * admin.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
