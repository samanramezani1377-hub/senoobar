<?php
/**
 * Senoobar — Wishlist page setup (idempotent).
 *
 * Creates a "علاقه‌مندی‌ها" page using the page-wishlist.php template once,
 * then remembers its ID so the bottom-nav links to it directly.
 */

if (!defined('ABSPATH')) {
    exit;
}

function senoobar_wishlist_page_url(): string {
    $id = (int) get_option('senoobar_wishlist_page_id');
    if ($id && 'publish' === get_post_status($id)) {
        return get_permalink($id);
    }
    // Fallback: locate a page using the template.
    $pages = get_pages(['meta_key' => '_wp_page_template', 'meta_value' => 'page-wishlist.php', 'number' => 1]);
    if (!empty($pages)) {
        update_option('senoobar_wishlist_page_id', $pages[0]->ID);
        return get_permalink($pages[0]->ID);
    }
    return '';
}

function senoobar_ensure_wishlist_page(): void {
    if (get_option('senoobar_wishlist_page_created')) {
        return;
    }

    // Look for an existing page with the template.
    $existing = get_pages(['meta_key' => '_wp_page_template', 'meta_value' => 'page-wishlist.php', 'number' => 1]);
    if (!empty($existing)) {
        update_option('senoobar_wishlist_page_id', $existing[0]->ID);
        update_option('senoobar_wishlist_page_created', 1);
        return;
    }

    // Create it.
    $page_id = wp_insert_post([
        'post_title'   => 'علاقه‌مندی‌ها',
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_name'    => 'wishlist',
        'post_content' => '',
    ], true);

    if (!is_wp_error($page_id)) {
        update_post_meta($page_id, '_wp_page_template', 'page-wishlist.php');
        update_option('senoobar_wishlist_page_id', $page_id);
    }

    update_option('senoobar_wishlist_page_created', 1);
}

add_action('init', 'senoobar_ensure_wishlist_page', 20);
