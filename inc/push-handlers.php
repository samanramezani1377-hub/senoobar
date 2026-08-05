<?php
add_action('wp_ajax_senoobar_push_subscribe','senoobar_push_subscribe');
add_action('wp_ajax_nopriv_senoobar_push_subscribe','senoobar_push_subscribe');
function senoobar_push_subscribe(){
    check_ajax_referer('senoobar_nonce','nonce');
    $endpoint=sanitize_text_field($_POST['endpoint']??'');
    if(!$endpoint)wp_send_json_error('Missing endpoint');
    $subs=get_option('senoobar_push_subscriptions',[]);
    $subs[$endpoint]=['endpoint'=>$endpoint,'created'=>current_time('mysql')];
    update_option('senoobar_push_subscriptions',$subs);
    wp_send_json_success(['message'=>'Subscribed']);
}
add_action('wp_ajax_senoobar_push_unsubscribe','senoobar_push_unsubscribe');
add_action('wp_ajax_nopriv_senoobar_push_unsubscribe','senoobar_push_unsubscribe');
function senoobar_push_unsubscribe(){
    check_ajax_referer('senoobar_nonce','nonce');
    $endpoint=sanitize_text_field($_POST['endpoint']??'');
    if(!$endpoint)wp_send_json_error('Missing endpoint');
    $subs=get_option('senoobar_push_subscriptions',[]);
    unset($subs[$endpoint]);
    update_option('senoobar_push_subscriptions',$subs);
    wp_send_json_success(['message'=>'Unsubscribed']);
}
