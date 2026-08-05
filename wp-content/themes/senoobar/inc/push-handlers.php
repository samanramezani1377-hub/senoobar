<?php
/**
 * Push Notification AJAX Handlers
 * پردازش اشتراک و لغو اشتراک Push Notification
 */

// Handle subscription
add_action('wp_ajax_senoobar_push_subscribe', 'senoobar_push_subscribe');
add_action('wp_ajax_nopriv_senoobar_push_subscribe', 'senoobar_push_subscribe');

function senoobar_push_subscribe() {
    check_ajax_referer('senoobar_nonce', 'nonce');
    
    $endpoint = sanitize_text_field($_POST['endpoint'] ?? '');
    $p256dh   = sanitize_text_field($_POST['p256dh'] ?? '');
    $auth     = sanitize_text_field($_POST['auth'] ?? '');
    
    if (!$endpoint) {
        wp_send_json_error('Missing endpoint');
    }
    
    // Store subscription (use your preferred storage: options, custom table, etc.)
    $subscriptions = get_option('senoobar_push_subscriptions', []);
    $subscriptions[$endpoint] = [
        'endpoint' => $endpoint,
        'p256dh'   => $p256dh,
        'auth'     => $auth,
        'user_id'  => get_current_user_id(),
        'created'  => current_time('mysql'),
    ];
    update_option('senoobar_push_subscriptions', $subscriptions);
    
    wp_send_json_success(['message' => 'Subscribed']);
}

// Handle unsubscription
add_action('wp_ajax_senoobar_push_unsubscribe', 'senoobar_push_unsubscribe');
add_action('wp_ajax_nopriv_senoobar_push_unsubscribe', 'senoobar_push_unsubscribe');

function senoobar_push_unsubscribe() {
    check_ajax_referer('senoobar_nonce', 'nonce');
    
    $endpoint = sanitize_text_field($_POST['endpoint'] ?? '');
    if (!$endpoint) {
        wp_send_json_error('Missing endpoint');
    }
    
    $subscriptions = get_option('senoobar_push_subscriptions', []);
    unset($subscriptions[$endpoint]);
    update_option('senoobar_push_subscriptions', $subscriptions);
    
    wp_send_json_success(['message' => 'Unsubscribed']);
}

// Send push notification to all subscribers
function senoobar_send_push($title, $body, $url = '/', $icon = '') {
    $subscriptions = get_option('senoobar_push_subscriptions', []);
    if (empty($subscriptions)) return false;
    
    // Web Push API requires server-side implementation
    // This is a placeholder - use a library like minishlink/web-push in production
    $payload = json_encode([
        'title' => $title,
        'body'  => $body,
        'icon'  => $icon ?: get_template_directory_uri() . '/assets/icons/icon-192.png',
        'badge' => get_template_directory_uri() . '/assets/icons/badge-72.png',
        'data'  => ['url' => $url],
    ]);
    
    $results = [];
    foreach ($subscriptions as $sub) {
        // Use Web Push library to send
        // WebPush::sendNotification($sub['endpoint'], $payload, $sub['p256dh'], $sub['auth']);
        $results[$sub['endpoint']] = 'queued';
    }
    
    return $results;
}
