<?php
/**
 * Senoobar — Account (My Account) logic.
 * Login/register with mobile (username = mobile, auto email), Persian labels,
 * forces my-account page onto custom template.
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}

/* ── 1. Mobile number helpers ── */
function senoobar_normalize_phone( $raw ) {
    $digits = preg_replace( '/\D+/', '', (string) $raw );
    if ( strlen( $digits ) > 10 && substr( $digits, 0, 2 ) === '98' ) {
        $digits = substr( $digits, 2 );
    }
    if ( strlen( $digits ) === 10 && substr( $digits, 0, 1 ) !== '0' ) {
        $digits = '0' . $digits;
    }
    return $digits;
}

function senoobar_phone_display( $phone ) {
    $d = senoobar_normalize_phone( $phone );
    if ( strlen( $d ) === 11 ) {
        return substr( $d, 0, 4 ) . ' ' . substr( $d, 4, 3 ) . ' ' . substr( $d, 7 );
    }
    return $d;
}

function senoobar_phone_email( $phone ) {
    return senoobar_normalize_phone( $phone ) . '@senoobar.local';
}

/* ── 2. Login by mobile ── */
add_filter( 'authenticate', function ( $user, $username, $password ) {
    if ( $user instanceof WP_User || $user instanceof WP_Error || empty( $username ) ) {
        return $user;
    }
    $phone = senoobar_normalize_phone( $username );
    if ( strlen( $phone ) >= 10 ) {
        $by_user  = get_user_by( 'login', $phone );
        $by_email = get_user_by( 'email', senoobar_phone_email( $phone ) );
        if ( $by_user ) {
            $username = $by_user->user_login;
        } elseif ( $by_email ) {
            $username = $by_email->user_login;
        } else {
            $found = get_users( [ 'meta_key' => 'mobile', 'meta_value' => $phone, 'number' => 1, 'fields' => 'ID' ] );
            if ( ! empty( $found ) ) {
                $u = get_userdata( $found[0] );
                if ( $u ) { $username = $u->user_login; }
            }
        }
    }
    return wp_authenticate_username_password( null, $username, $password );
}, 20, 3 );

/* ── Persian labels ── */
add_filter( 'gettext', function ( $translated, $text, $domain ) {
    if ( $domain !== 'woocommerce' ) { return $translated; }
    $map = [
        'Username or email address' => 'شماره موبایل',
        'Username or email'         => 'شماره موبایل',
        'Username'                  => 'شماره موبایل',
        'Email address'             => 'ایمیل',
        'Password'                  => 'رمز عبور',
        'Remember me'               => 'مرا به خاطر بسپار',
        'Login'                     => 'ورود',
        'Register'                  => 'ثبت نام',
        'Lost your password?'       => 'رمز عبور را فراموش کرده اید؟',
    ];
    return isset( $map[ $text ] ) ? $map[ $text ] : $translated;
}, 20, 3 );

/* ── 3. Registration fields ── */
function senoobar_register_fields() {
    return [
        'mobile'     => [ 'type' => 'tel',      'label' => 'شماره موبایل',  'placeholder' => '۰۹۱۲ ۳۴۵ ۶۷۸۹', 'required' => true ],
        'first_name' => [ 'type' => 'text',     'label' => 'نام',           'placeholder' => 'نام شما', 'required' => true ],
        'last_name'  => [ 'type' => 'text',     'label' => 'نام خانوادگی',  'placeholder' => 'نام خانوادگی شما', 'required' => true ],
        'password'   => [ 'type' => 'password', 'label' => 'رمز عبور',      'placeholder' => 'حداقل ۶ کاراکتر', 'required' => true ],
    ];
}

add_action( 'template_redirect', function () {
    if ( ! isset( $_POST['senoobar_register'] ) || ! wp_verify_nonce( $_POST['senoobar_register_nonce'] ?? '', 'senoobar_register' ) ) {
        return;
    }
    $phone    = senoobar_normalize_phone( $_POST['mobile'] ?? '' );
    $first    = sanitize_text_field( $_POST['first_name'] ?? '' );
    $last     = sanitize_text_field( $_POST['last_name'] ?? '' );
    $password = $_POST['password'] ?? '';

    if ( strlen( $phone ) !== 11 || substr( $phone, 0, 2 ) !== '09' ) {
        wc_add_notice( 'شماره موبایل معتبر نیست. لطفاً یک شماره ۱۱ رقمی با ۰۹ وارد کنید.', 'error' );
        return;
    }
    if ( $first === '' || $last === '' ) {
        wc_add_notice( 'نام و نام خانوادگی را وارد کنید.', 'error' );
        return;
    }
    if ( strlen( $password ) < 6 ) {
        wc_add_notice( 'رمز عبور باید حداقل ۶ کاراکتر باشد.', 'error' );
        return;
    }
    if ( username_exists( $phone ) || email_exists( senoobar_phone_email( $phone ) ) || count( get_users( [ 'meta_key' => 'mobile', 'meta_value' => $phone, 'number' => 1 ] ) ) ) {
        wc_add_notice( 'این شماره موبایل قبلاً ثبت شده است. لطفاً وارد شوید.', 'error' );
        return;
    }

    $user_id = wp_insert_user( [
        'user_login'   => $phone,
        'user_pass'    => $password,
        'user_email'   => senoobar_phone_email( $phone ),
        'first_name'   => $first,
        'last_name'    => $last,
        'display_name' => trim( $first . ' ' . $last ),
        'role'         => 'customer',
    ] );

    if ( is_wp_error( $user_id ) ) {
        wc_add_notice( $user_id->get_error_message(), 'error' );
        return;
    }

    update_user_meta( $user_id, 'mobile', $phone );
    update_user_meta( $user_id, 'billing_phone', $phone );
    update_user_meta( $user_id, 'billing_first_name', $first );
    update_user_meta( $user_id, 'billing_last_name', $last );
    update_user_meta( $user_id, 'billing_email', senoobar_phone_email( $phone ) );

    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id, true );

    wc_add_notice( 'حساب شما با موفقیت ساخته شد. خوش آمدید!', 'success' );
    wp_safe_redirect( wc_get_account_endpoint_url( 'dashboard' ) );
    exit;
} );

/* ── 4. Sync mobile -> billing on save ── */
add_action( 'woocommerce_save_account_details', function ( $user_id ) {
    if ( isset( $_POST['mobile'] ) && $_POST['mobile'] !== '' ) {
        $phone = senoobar_normalize_phone( $_POST['mobile'] );
        update_user_meta( $user_id, 'mobile', $phone );
        update_user_meta( $user_id, 'billing_phone', $phone );
        if ( empty( $_POST['account_email'] ) ) {
            update_user_meta( $user_id, 'billing_email', senoobar_phone_email( $phone ) );
        }
    }
}, 10, 1 );

/* ── 5. Save address from custom form ── */
add_action( 'template_redirect', function () {
    if ( ! isset( $_POST['senoobar_save_address'] ) || ! is_user_logged_in() ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'senoobar_save_address' ) ) {
        return;
    }
    $uid = get_current_user_id();
    foreach ( [ 'billing_first_name', 'billing_last_name', 'billing_phone', 'billing_state', 'billing_postcode', 'billing_address_1' ] as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_user_meta( $uid, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
        }
    }
    wc_add_notice( 'آدرس با موفقیت ذخیره شد.', 'success' );
    wp_safe_redirect( wc_get_account_endpoint_url( 'edit-address' ) );
    exit;
} );

/* ── 6. Force my-account template ── */
add_filter( 'template_include', function ( $template ) {
    if ( is_account_page() ) {
        $custom = get_stylesheet_directory() . '/woocommerce/myaccount/senoobar-account.php';
        if ( file_exists( $custom ) ) {
            return $custom;
        }
    }
    return $template;
}, 100 );
