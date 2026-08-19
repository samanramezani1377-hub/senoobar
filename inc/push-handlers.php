<?php
/**
 * Push Notification AJAX Handlers
 */

function senoobar_sanitize_base64_url(string $value): string
{
    $value = preg_replace('/[^A-Za-z0-9\-_=]/', '', $value);
    return $value !== false ? $value : '';
}

// ---- Subscribe ----
add_action('wp_ajax_senoobar_push_subscribe', 'senoobar_handle_push_subscribe');
add_action('wp_ajax_nopriv_senoobar_push_subscribe', 'senoobar_handle_push_subscribe');

function senoobar_handle_push_subscribe(): void
{
    check_ajax_referer('senoobar_push_nonce', 'nonce');

    $endpoint = sanitize_url($_POST['endpoint'] ?? '');
    $p256dh   = senoobar_sanitize_base64_url($_POST['p256dh'] ?? '');
    $auth     = senoobar_sanitize_base64_url($_POST['auth'] ?? '');

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
    check_ajax_referer('senoobar_push_nonce', 'nonce');

    $endpoint = sanitize_text_field($_POST['endpoint'] ?? '');
    if (empty($endpoint)) {
        wp_send_json_error(['message' => 'Missing endpoint']);
    }

    $subs = get_option('senoobar_push_subscriptions', []);
    unset($subs[$endpoint]);
    update_option('senoobar_push_subscriptions', $subs);

    wp_send_json_success(['message' => 'Unsubscribed']);
}

// ---- Send Push Notification ----
function senoobar_send_push_notification(string $title, string $body, string $url = '/', array $data = []): bool
{
    if (!current_user_can('manage_options')) {
        return false;
    }

    $subs = get_option('senoobar_push_subscriptions', []);
    if (empty($subs)) {
        return false;
    }

    $api_url  = get_theme_mod('senoobar_push_api_url', '');
    $api_key  = get_theme_mod('senoobar_push_api_key', '');

    if (empty($api_url) || empty($api_key)) {
        return false;
    }

    $payload = [
        'title'   => $title,
        'body'    => $body,
        'url'     => $url,
        'data'    => $data,
        'icon'    => get_template_directory_uri() . '/assets/icons/icon-192.webp',
        'badge'   => get_template_directory_uri() . '/assets/icons/badge-72.webp',
        'vibrate' => [200, 100, 200],
        'actions' => [
            ['action' => 'view', 'title' => 'مشاهده'],
            ['action' => 'close', 'title' => 'بستن'],
        ],
        'dir'  => 'rtl',
        'lang' => 'fa-IR',
    ];

    $headers = [
        'Content-Type'  => 'application/json',
        'Authorization' => 'Bearer ' . $api_key,
    ];

    $success = true;
    foreach ($subs as $endpoint => $sub) {
        $response = wp_remote_post($api_url, [
            'headers' => $headers,
            'body'    => wp_json_encode(array_merge($payload, ['endpoint' => $endpoint, 'p256dh' => $sub['p256dh'], 'auth' => $sub['auth']])),
            'timeout' => 10,
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            $success = false;
        }
    }

    return $success;
}
