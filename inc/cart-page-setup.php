<?php
/**
 * Cart Page Setup — Senoobar
 * Idempotent Cart Page creation and WooCommerce configuration
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Setup Cart Page and WooCommerce pages
 * Idempotent - safe to run multiple times
 */
function senoobar_setup_cart_page(): void {
    // Check if already done
    if (get_option('senoobar_wc_pages_setup_done')) {
        return;
    }

    $pages = [
        'cart'        => ['title' => 'سبد خرید',        'shortcode' => '[woocommerce_cart]',        'option' => 'woocommerce_cart_page_id'],
        'checkout'    => ['title' => 'تسویه حساب',      'shortcode' => '[woocommerce_checkout]',    'option' => 'woocommerce_checkout_page_id'],
        'myaccount'   => ['title' => 'حساب کاربری',      'shortcode' => '[woocommerce_my_account]',  'option' => 'woocommerce_myaccount_page_id'],
    ];

    foreach ($pages as $key => $data) {
        // Find existing page by title or shortcode
        $page = get_page_by_title($data['title']);
        
        if (!$page) {
            // Create new page
            $page_id = wp_insert_post([
                'post_title'   => $data['title'],
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_content' => $data['shortcode'],
                'post_name'    => $key, // slug
            ], true);
            
            if (is_wp_error($page_id)) {
                continue;
            }
        } else {
            $page_id = $page->ID;
            // Update content if empty or wrong shortcode
            $current_content = get_post_field('post_content', $page_id);
            if (empty($current_content) || strpos($current_content, $data['shortcode']) === false) {
                wp_update_post([
                    'ID'           => $page_id,
                    'post_content' => $data['shortcode'],
                ]);
            }
        }

        // Set WooCommerce page option
        if (!empty($page_id)) {
            update_option($data['option'], $page_id);
        }
    }

    // Mark as done
    update_option('senoobar_wc_pages_setup_done', true);
}

/**
 * Run on theme activation (for fresh installs)
 */
function senoobar_theme_activation_setup(): void {
    // Schedule for next request to ensure WooCommerce is loaded
    add_action('init', 'senoobar_setup_cart_page', 20);
}
add_action('after_switch_theme', 'senoobar_theme_activation_setup');

/**
 * Also run on init for existing installations
 * Idempotent - safe to run every request
 */
add_action('init', 'senoobar_setup_cart_page', 20);

/**
 * Helper: Ensure Cart Page exists before Cart template loads
 * Fallback for edge cases where Cart Page might be missing
 */
function senoobar_ensure_cart_page_exists(): void {
    if (is_cart() && !get_option('woocommerce_cart_page_id')) {
        // Trigger setup immediately
        senoobar_setup_cart_page();
    }
}
add_action('template_redirect', 'senoobar_ensure_cart_page_exists', 5);