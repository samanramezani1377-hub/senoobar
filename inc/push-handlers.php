<?php
/**
 * Push Notification AJAX Handlers
 */

// ---- Subscribe ----
add_action('wp_ajax_senoobar_push_subscribe', 'senoobar_handle_push_subscribe');
add_action('wp_ajax_nopriv_senoobar_push_subscribe', 'senoobar_handle_push_subscribe');

function senoobar_handle_push_subscribe(): void
{
    check_ajax_referer('senoobar_nonce', 'nonce');

    $endpoint = sanitize_text_field($_POST['endpoint'] ?? '');
    $p256dh   = sanitize_text_field($_POST['p256dh'] ?? '');
    $auth     = sanitize_text_field($_POST['auth'] ?? '');

    if (empty($endpoint)) {
        wp_send_json_error(['message' => 'Missing endpoint']);
    }

    $subs = get_option('senoobar_push_subscriptions', []);
    $subs[$endpoint] = [
        'endpoint' => $endpoint,
        'p256dh'   => $p256dh,
        'auth'     => $auth,
        'created'  => current_time('mysql'),
    ];
    update_option('senoobar_push_subscriptions', $subs);

    wp_send_json_success(['message' => 'Subscribed']);
}

// ---- Unsubscribe ----
add_action('wp_ajax_senoobar_push_unsubscribe', 'senoobar_handle_push_unsubscribe');
add_action('wp_ajax_nopriv_senoobar_push_unsubscribe', 'senoobar_handle_push_unsubscribe');

function senoobar_handle_push_unsubscribe(): void
{
    check_ajax_referer('senoobar_nonce', 'nonce');

    $endpoint = sanitize_text_field($_POST['endpoint'] ?? '');
    if (empty($endpoint)) {
        wp_send_json_error(['message' => 'Missing endpoint']);
    }

    $subs = get_option('senoobar_push_subscriptions', []);
    unset($subs[$endpoint]);
    update_option('senoobar_push_subscriptions', $subs);

    wp_send_json_success(['message' => 'Unsubscribed']);
}
