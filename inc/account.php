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

    // اگر ورودی شبیه شماره موبایل است، آن را به نام کاربری واقعی نگاشت کن.
    if ( strlen( $phone ) >= 10 ) {
        $found_user = null;
        $by_user  = get_user_by( 'login', $phone );
        $by_email = get_user_by( 'email', senoobar_phone_email( $phone ) );
        if ( $by_user ) {
            $found_user = $by_user;
        } elseif ( $by_email ) {
            $found_user = $by_email;
        } else {
            $found = get_users( [ 'meta_key' => 'mobile', 'meta_value' => $phone, 'number' => 1, 'fields' => 'ID' ] );
            if ( ! empty( $found ) ) {
                $u = get_userdata( $found[0] );
                if ( $u ) { $found_user = $u; }
            }
        }

        // شماره وارد شده ولی هیچ کاربری پیدا نشد → پیام با لینک ثبت‌نام.
        if ( ! $found_user ) {
            $register_url = add_query_arg( 'register', '1', wc_get_page_permalink( 'myaccount' ) );
            return new WP_Error(
                'senoobar_no_account',
                'حسابی با این شماره تلفن وجود ندارد. <a href="' . esc_url( $register_url ) . '">ثبت نام کنید</a>'
            );
        }

        $username = $found_user->user_login;

        // کاربر پیدا شده؛ رمز را بررسی کن تا پیام دقیق بدهیم.
        $checked = wp_authenticate_username_password( null, $username, $password );
        if ( is_wp_error( $checked ) && isset( $checked->errors['incorrect_password'] ) ) {
            return new WP_Error( 'senoobar_incorrect_password', 'رمز عبوری که وارد کردید اشتباه است.' );
        }
        return $checked;
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

/* ── 4. Save account details (first/last name + email) ── */
// The mobile number is NOT user-editable anymore, so we keep the stored value
// untouched. First/last name and email are saved explicitly here so the custom
// profile form works regardless of WooCommerce's form-handler quirks.
add_action( 'template_redirect', function () {
    if ( ! isset( $_POST['save_account_details'] ) || ! is_user_logged_in() ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['save-account-details-nonce'] ?? '', 'save_account_details' ) ) {
        return;
    }
    $user_id = get_current_user_id();

    if ( isset( $_POST['account_first_name'] ) ) {
        $fn = sanitize_text_field( wp_unslash( $_POST['account_first_name'] ) );
        wp_update_user( [ 'ID' => $user_id, 'first_name' => $fn ] );
        update_user_meta( $user_id, 'billing_first_name', $fn );
    }
    if ( isset( $_POST['account_last_name'] ) ) {
        $ln = sanitize_text_field( wp_unslash( $_POST['account_last_name'] ) );
        wp_update_user( [ 'ID' => $user_id, 'last_name' => $ln ] );
        update_user_meta( $user_id, 'billing_last_name', $ln );
    }

    // Refresh display name from first + last.
    $u     = get_userdata( $user_id );
    $first = $u->first_name;
    $last  = $u->last_name;
    $dn    = trim( $first . ' ' . $last );
    if ( $dn !== '' && $dn !== $u->user_login ) {
        wp_update_user( [ 'ID' => $user_id, 'display_name' => $dn ] );
    }

    if ( isset( $_POST['account_email'] ) ) {
        $em = sanitize_email( wp_unslash( $_POST['account_email'] ) );
        if ( is_email( $em ) ) {
            wp_update_user( [ 'ID' => $user_id, 'user_email' => $em ] );
        }
    }
}, 5 );

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


/* ── 7. Auto-create account on checkout (guest checkout => account) ── */
// When a guest places an order, create a customer account keyed on the mobile
// number (username = normalized phone, synthetic email). If an account for
// that phone already exists, link the order to it instead of creating a new
// one. Password is auto-generated and sent via SMS (see senoobar_otp_send_password_sms).
add_action( 'woocommerce_checkout_order_processed', function ( $order_id, $posted_data, $order ) {

    // Only auto-create accounts for guests.
    if ( is_user_logged_in() ) {
        return;
    }

    $phone = isset( $posted_data['billing_phone'] ) ? senoobar_normalize_phone( $posted_data['billing_phone'] ) : '';

    // If we can't resolve a valid mobile, bail (let WooCommerce handle it).
    if ( strlen( $phone ) !== 11 || substr( $phone, 0, 2 ) !== '09' ) {
        return;
    }

    // 1) Look for an existing account with this phone.
    $existing_id = 0;
    $by_user  = get_user_by( 'login', $phone );
    $by_email = get_user_by( 'email', senoobar_phone_email( $phone ) );
    $by_meta  = get_users( [ 'meta_key' => 'mobile', 'meta_value' => $phone, 'number' => 1, 'fields' => 'ID' ] );

    if ( $by_user ) {
        $existing_id = $by_user->ID;
    } elseif ( $by_email ) {
        $existing_id = $by_email->ID;
    } elseif ( ! empty( $by_meta ) ) {
        $existing_id = (int) $by_meta[0];
    }

    $first = isset( $posted_data['billing_first_name'] ) ? sanitize_text_field( $posted_data['billing_first_name'] ) : '';
    $last  = isset( $posted_data['billing_last_name'] ) ? sanitize_text_field( $posted_data['billing_last_name'] ) : '';

    // Determine the order number up-front (used in every SMS variant).
    $order_number = ( $order ) ? $order->get_order_number() : $order_id;

    // Resolve a friendly first name for the greeting. The customer's first name
    // (billing_first_name) is the name they just typed into the checkout form,
    // which is always the correct, current first name. Fall back to the stored
    // profile first name only if the posted first name is empty.
    $greet_name = $first;
    if ( $greet_name === '' && $existing_id ) {
        $stored_first = get_user_meta( $existing_id, 'first_name', true );
        $greet_name   = ( $stored_first !== '' ) ? $stored_first : '';
    }

    if ( $existing_id ) {
        // ── Account already exists (returning or previously-registered) ──
        $user_id = $existing_id;

        // Check whether this customer has any prior completed/processing order,
        // so we can send a "welcome back" note instead of a plain confirmation.
        $has_previous_order = false;
        if ( function_exists( 'wc_get_orders' ) ) {
            $prev_orders = wc_get_orders( [
                'customer_id' => $existing_id,
                'exclude'     => [ $order_id ],
                'limit'       => 1,
                'return'      => 'ids',
            ] );
            $has_previous_order = ! empty( $prev_orders );
        }

        if ( $has_previous_order ) {
            // Returning customer — thank them, no password.
            if ( function_exists( 'senoobar_otp_send_returning_customer_sms' ) ) {
                senoobar_otp_send_returning_customer_sms( $phone, $order_number, $greet_name );
            }
        } else {
            // Existing account, first order — confirm without sending a password.
            if ( function_exists( 'senoobar_otp_send_order_confirm_sms' ) ) {
                senoobar_otp_send_order_confirm_sms( $phone, $order_number, $greet_name );
            }
        }
    } else {
        // 2) Create a new customer account with a random password.
        $temp_password = wp_generate_password( 10, false, false ); // readable (letters+digits)
        $user_id = wp_insert_user( [
            'user_login'   => $phone,
            'user_pass'    => $temp_password,
            'user_email'   => senoobar_phone_email( $phone ),
            'first_name'   => $first,
            'last_name'    => $last,
            'display_name' => trim( $first . ' ' . $last ) !== '' ? trim( $first . ' ' . $last ) : $phone,
            'role'         => 'customer',
        ] );

        if ( is_wp_error( $user_id ) ) {
            return;
        }

        update_user_meta( $user_id, 'mobile', $phone );

        // Send a complete welcome SMS with the order number + login credentials
        // (instead of showing the password on the thank-you page).
        if ( function_exists( 'senoobar_otp_send_order_welcome_sms' ) ) {
            senoobar_otp_send_order_welcome_sms( $phone, $phone, $temp_password, $order_number, $greet_name );
        }
    }

    // 3) Persist billing details onto the account.
    //    SECURITY: only write billing/address data onto a NEWLY-created account.
    //    For an EXISTING account we must NOT overwrite the stored name/address,
    //    otherwise a third party could place a guest order with someone else's
    //    phone number and silently change that customer's saved shipping address.
    if ( ! $existing_id ) {
        update_user_meta( $user_id, 'billing_phone', $phone );
        if ( $first !== '' ) { update_user_meta( $user_id, 'billing_first_name', $first ); }
        if ( $last !== '' )  { update_user_meta( $user_id, 'billing_last_name', $last ); }
        update_user_meta( $user_id, 'billing_email', senoobar_phone_email( $phone ) );

        foreach ( [ 'billing_state', 'billing_postcode', 'billing_address_1' ] as $key ) {
            if ( isset( $posted_data[ $key ] ) && $posted_data[ $key ] !== '' ) {
                update_user_meta( $user_id, $key, sanitize_text_field( wp_unslash( $posted_data[ $key ] ) ) );
            }
        }
    }

    // 4) Link the order to this account.
    if ( $order ) {
        $order->set_customer_id( $user_id );
        // Preserve guest billing but also ensure the customer id persists.
        try {
            $order->save();
        } catch ( Exception $e ) {
            // Non-critical: order is already created.
        }
    }

    // 5) Mark account-created flag so the thank-you page can show a hint.
    if ( ! $existing_id ) {
        WC()->session->set( 'senoobar_account_created', $phone );
        if ( $order ) {
            update_post_meta( $order_id, '_senoobar_account_created', $phone );
        }
    }

}, 10, 3 );
