<?php
/**
 * Senoobar — ورود با کد پیامکی (OTP) + ارسال رمز عبور پس از خرید.
 *
 * از سرویس ملی‌پیامک (Melipayamak) برای ارسال پیامک استفاده می‌کند.
 * مشخصات سرویس در «سفارشی‌سازی پوسته → ورود با کد پیامکی» وارد می‌شود
 * (در سورس هاردکد نمی‌شود تا امن بماند).
 *
 * امکانات:
 *  - ارسال کد تأیید ۵ رقمی و ورود خودکار (ورود = ثبت‌نام).
 *  - ارسال رمز عبور + نام کاربری (موبایل) پس از ساخت حساب در چک‌اوت.
 *  - بازنشانی (ریست) رمز عبور با پیامک.
 *  - محدودیت نرخ درخواست برای جلوگیری از سوءاستفاده.
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}

/* ─── ۱. تنظیمات ملی‌پیامک در سفارشی‌سازی پوسته ─── */
add_action( 'customize_register', 'senoobar_otp_customizer' );
function senoobar_otp_customizer( $c ) {
    $c->add_section( 'senoobar_otp', [
        'title'    => 'ورود با کد پیامکی (OTP)',
        'priority' => 31,
    ] );

    $fields = [
        'senoobar_otp_username' => [ 'نام کاربری ملی‌پیامک', 'مثلاً 9020467784', 'text' ],
        'senoobar_otp_password' => [ 'رمز عبور ملی‌پیامک', 'رمز عبور وب‌سرویس', 'password' ],
        'senoobar_otp_sender'   => [ 'شماره خط ارسال', 'شماره خط شما، مثلاً 5000123456', 'text' ],
    ];

    foreach ( $fields as $id => $meta ) {
        $c->add_setting( $id, [ 'default' => '' ] );
        $c->add_control( $id, [
            'label'       => $meta[0],
            'description' => $meta[1],
            'section'     => 'senoobar_otp',
            'type'        => $meta[2],
        ] );
    }
}

/* ─── ۲. دسترسی به تنظیمات ─── */
function senoobar_otp_configured() {
    return senoobar_otp_username() !== '' && senoobar_otp_password() !== '';
}
function senoobar_otp_username() {
    return trim( (string) get_theme_mod( 'senoobar_otp_username', '' ) );
}
function senoobar_otp_password() {
    return trim( (string) get_theme_mod( 'senoobar_otp_password', '' ) );
}
function senoobar_otp_sender() {
    return trim( (string) get_theme_mod( 'senoobar_otp_sender', '' ) );
}

/* ─── ۳. نرمال‌سازی شماره موبایل (همانند account.php، بدون duplicate) ─── */
if ( ! function_exists( 'senoobar_normalize_phone' ) ) {
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
}

/* ─── ۴. ارسال پیامک با REST API ملی‌پیامک ─── */
function senoobar_otp_send_sms( $to, $text ) {
    if ( ! senoobar_otp_configured() ) {
        return new WP_Error( 'otp_not_configured', 'تنظیمات پیامک کامل نشده است.' );
    }

    $to = senoobar_normalize_phone( $to );
    if ( strlen( $to ) !== 11 ) {
        return new WP_Error( 'otp_invalid_phone', 'شماره موبایل نامعتبر است.' );
    }

    // عبارت «لغو۱۱» الزامی خط‌های خدماتی ملی‌پیامک است و باید در انتهای هر
    // پیامک ارسالی باشد؛ در غیر این صورت ارسال رد می‌شود. برای جلوگیری از
    // تکرار، فقط در صورتی اضافه می‌شود که از قبل در متن نباشد.
    if ( false === strpos( $text, 'لغو۱۱' ) && false === strpos( $text, 'لغو ۱۱' ) && false === strpos( $text, 'لغو11' ) && false === strpos( $text, 'لغو 11' ) ) {
        $text .= "\nلغو۱۱";
    }

    $args = [
        'body' => [
            'username' => senoobar_otp_username(),
            'password' => senoobar_otp_password(),
            'to'       => $to,
            'from'     => senoobar_otp_sender(),
            'text'     => $text,
            'isFlash'  => 'false',
        ],
        'timeout' => 20,
    ];

    $res = wp_remote_post( 'https://rest.payamak-panel.com/api/SendSMS/SendSMS', $args );

    if ( is_wp_error( $res ) ) {
        return $res;
    }

    $body = wp_remote_retrieve_body( $res );

    // پاسخ JSON ملی‌پیامک: {"Value": 11.0, "RetStatus": 1, ...}
    $decoded = json_decode( $body, true );
    if ( is_array( $decoded ) && isset( $decoded['RetStatus'] ) ) {
        return (int) $decoded['RetStatus'] === 1
            ? true
            : new WP_Error( 'otp_send_failed', 'ارسال پیامک ناموفق بود.' );
    }

    // پاسخ عددی خام: عدد بزرگ‌تر از ۱۰ یعنی شناسه ارسال (موفق).
    $int = intval( trim( $body ) );
    if ( $int > 10 ) {
        return true;
    }
    return new WP_Error( 'otp_send_failed', 'ارسال پیامک ناموفق بود.' );
}

/* ─── ۵. ساخت / ذخیره / تأیید کد OTP ─── */
function senoobar_otp_generate() {
    return (string) random_int( 10000, 99999 );
}
function senoobar_otp_store( $phone, $code ) {
    set_transient( 'senoobar_otp_' . $phone, wp_hash( $code ), 2 * MINUTE_IN_SECONDS );
}
function senoobar_otp_verify( $phone, $code ) {
    $stored = get_transient( 'senoobar_otp_' . $phone );
    if ( false === $stored ) {
        return new WP_Error( 'otp_expired', 'کد منقضی شده است. دوباره درخواست بده.' );
    }
    if ( ! hash_equals( $stored, wp_hash( $code ) ) ) {
        return new WP_Error( 'otp_wrong', 'کد وارد شده اشتباه است.' );
    }
    delete_transient( 'senoobar_otp_' . $phone );
    return true;
}

/* ─── ۶. محدودیت نرخ بر اساس IP + شماره ─── */
function senoobar_otp_rate_limit( $phone ) {
    $ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    $key = 'senoobar_otp_rl_' . md5( $ip . '_' . $phone );
    $attempts = (int) get_transient( $key );
    if ( $attempts >= 5 ) {
        return new WP_Error( 'otp_ratelimit', 'تعداد درخواست زیاد است. چند دقیقه بعد تلاش کن.' );
    }
    set_transient( $key, $attempts + 1, 5 * MINUTE_IN_SECONDS );
    return true;
}

/* ─── ۷. ایمیل مصنوعی از موبایل (سازگار با account.php) ─── */
if ( ! function_exists( 'senoobar_phone_email' ) ) {
    function senoobar_phone_email( $phone ) {
        return senoobar_normalize_phone( $phone ) . '@senoobar.local';
    }
}

/* ─── ۸. پیدا کردن یا ساخت کاربر با شماره موبایل ─── */
function senoobar_otp_find_or_create_user( $phone ) {
    $phone = senoobar_normalize_phone( $phone );

    $user = get_user_by( 'login', $phone );
    if ( ! $user ) {
        $user = get_user_by( 'email', senoobar_phone_email( $phone ) );
    }
    if ( ! $user ) {
        $found = get_users( [ 'meta_key' => 'mobile', 'meta_value' => $phone, 'number' => 1, 'fields' => 'ID' ] );
        if ( ! empty( $found ) ) {
            $user = get_userdata( $found[0] );
        }
    }

    if ( ! $user ) {
        $user_id = wp_insert_user( [
            'user_login'   => $phone,
            'user_pass'    => wp_generate_password( 12, false ),
            'user_email'   => senoobar_phone_email( $phone ),
            'display_name' => $phone,
            'role'         => 'customer',
        ] );
        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }
        update_user_meta( $user_id, 'mobile', $phone );
        update_user_meta( $user_id, 'billing_phone', $phone );
        $user = get_userdata( $user_id );
    }

    return $user;
}

/* ─── ۹. AJAX: ارسال کد ─── */
add_action( 'wp_ajax_nopriv_senoobar_otp_send', 'senoobar_otp_send_ajax' );
add_action( 'wp_ajax_senoobar_otp_send', 'senoobar_otp_send_ajax' );
function senoobar_otp_send_ajax() {
    check_ajax_referer( 'senoobar_otp_nonce', 'nonce' );

    $phone = isset( $_POST['phone'] ) ? senoobar_normalize_phone( $_POST['phone'] ) : '';

    if ( strlen( $phone ) !== 11 || substr( $phone, 0, 2 ) !== '09' ) {
        wp_send_json_error( [ 'message' => 'شماره موبایل نامعتبر است.' ] );
    }

    $rl = senoobar_otp_rate_limit( $phone );
    if ( is_wp_error( $rl ) ) {
        wp_send_json_error( [ 'message' => $rl->get_error_message() ] );
    }

    $code = senoobar_otp_generate();
    senoobar_otp_store( $phone, $code );

    $text = "سایت سنوبر\nکد ورود: {$code}";
    $sent = senoobar_otp_send_sms( $phone, $text );

    if ( is_wp_error( $sent ) ) {
        wp_send_json_error( [ 'message' => $sent->get_error_message() ] );
    }

    wp_send_json_success( [ 'message' => 'کد تأیید ارسال شد.' ] );
}

/* ─── ۱۰. AJAX: تأیید کد و ورود ─── */
add_action( 'wp_ajax_nopriv_senoobar_otp_verify', 'senoobar_otp_verify_ajax' );
add_action( 'wp_ajax_senoobar_otp_verify', 'senoobar_otp_verify_ajax' );
function senoobar_otp_verify_ajax() {
    check_ajax_referer( 'senoobar_otp_nonce', 'nonce' );

    $phone = isset( $_POST['phone'] ) ? senoobar_normalize_phone( $_POST['phone'] ) : '';
    $code  = isset( $_POST['code'] ) ? preg_replace( '/\D+/', '', $_POST['code'] ) : '';

    if ( strlen( $phone ) !== 11 ) {
        wp_send_json_error( [ 'message' => 'شماره موبایل نامعتبر است.' ] );
    }

    $v = senoobar_otp_verify( $phone, $code );
    if ( is_wp_error( $v ) ) {
        wp_send_json_error( [ 'message' => $v->get_error_message() ] );
    }

    $user = senoobar_otp_find_or_create_user( $phone );
    if ( is_wp_error( $user ) ) {
        wp_send_json_error( [ 'message' => 'خطا در ورود. دوباره تلاش کن.' ] );
    }

    wp_set_current_user( $user->ID );
    wp_set_auth_cookie( $user->ID, true );

    $redirect = isset( $_POST['redirect'] ) && $_POST['redirect'] !== ''
        ? esc_url_raw( $_POST['redirect'] )
        : wc_get_account_endpoint_url( 'dashboard' );

    wp_send_json_success( [ 'message' => 'ورود موفق.', 'redirect' => $redirect ] );
}

/* ─── ۱۱. ارسال رمز عبور پس از ساخت حساب در چک‌اوت ─── */
function senoobar_otp_send_password_sms( $phone, $username, $password ) {
    if ( ! senoobar_otp_configured() ) {
        return false;
    }
    $text = "سایت سنوبر\nحساب شما ساخته شد\nنام کاربری: {$username}\nرمز عبور: {$password}";
    return senoobar_otp_send_sms( $phone, $text );
}

/* ─── ۱۲. JS برای فرم OTP (فقط وقتی فرم OTP در صفحه باشد) ─── */
add_action( 'wp_footer', 'senoobar_otp_footer_js', 30 );
function senoobar_otp_footer_js() {
    // فقط در صفحه حساب کاربری (جایی که فرم OTP است) خروجی بده.
    if ( ! is_account_page() ) {
        return;
    }
    $ajax_url = admin_url( 'admin-ajax.php' );
    $nonce    = wp_create_nonce( 'senoobar_otp_nonce' );
    ?>
    <script>
    (function () {
        var form     = document.getElementById('snb-otp-form');
        if (!form) return;
        var phoneEl  = document.getElementById('otp_phone');
        var codeWrap = document.getElementById('snb-otp-code-wrap');
        var codeEl   = document.getElementById('otp_code');
        var msgEl    = document.getElementById('snb-otp-msg');
        var sendBtn  = document.getElementById('snb-otp-send');
        var verifyBtn= document.getElementById('snb-otp-verify');

        function showMsg(text, ok) {
            msgEl.style.display = 'block';
            msgEl.textContent = text;
            msgEl.style.color = ok ? '#2e7d32' : '#c62828';
        }
        function normPhone(v) {
            var d = (v || '').replace(/\D+/g, '');
            if (d.length > 10 && d.slice(0,2) === '98') { d = d.slice(2); }
            if (d.length === 10 && d.charAt(0) !== '0') { d = '0' + d; }
            return d;
        }
        function post(action, data) {
            data.action = action;
            data.nonce  = '<?php echo esc_js( $nonce ); ?>';
            var fd = new FormData();
            for (var k in data) { fd.append(k, data[k]); }
            return fetch('<?php echo esc_url( $ajax_url ); ?>', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); });
        }

        sendBtn.addEventListener('click', function () {
            var phone = normPhone(phoneEl.value);
            if (phone.length !== 11 || phone.slice(0,2) !== '09') {
                showMsg('شماره موبایل نامعتبر است.', false);
                return;
            }
            sendBtn.disabled = true;
            sendBtn.textContent = 'در حال ارسال…';
            post('senoobar_otp_send', { phone: phone }).then(function (res) {
                sendBtn.disabled = false;
                sendBtn.textContent = 'ارسال مجدد کد';
                if (res && res.success) {
                    codeWrap.style.display = 'block';
                    verifyBtn.style.display = 'block';
                    showMsg(res.data.message || 'کد ارسال شد.', true);
                } else {
                    showMsg((res && res.data && res.data.message) || 'خطا در ارسال کد.', false);
                }
            }).catch(function () {
                sendBtn.disabled = false;
                sendBtn.textContent = 'ارسال کد تأیید';
                showMsg('خطای شبکه. دوباره تلاش کن.', false);
            });
        });

        verifyBtn.addEventListener('click', function () {
            var phone = normPhone(phoneEl.value);
            var code  = (codeEl.value || '').replace(/\D+/g, '');
            if (code.length !== 5) {
                showMsg('کد ۵ رقمی را وارد کنید.', false);
                return;
            }
            verifyBtn.disabled = true;
            verifyBtn.textContent = 'در حال بررسی…';
            var redirect = '<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>';
            post('senoobar_otp_verify', { phone: phone, code: code, redirect: redirect }).then(function (res) {
                verifyBtn.disabled = false;
                verifyBtn.textContent = 'تأیید و ورود';
                if (res && res.success) {
                    showMsg('ورود موفق. در حال انتقال…', true);
                    setTimeout(function () { window.location.href = res.data.redirect || redirect; }, 600);
                } else {
                    showMsg((res && res.data && res.data.message) || 'کد اشتباه است.', false);
                }
            }).catch(function () {
                verifyBtn.disabled = false;
                verifyBtn.textContent = 'تأیید و ورود';
                showMsg('خطای شبکه. دوباره تلاش کن.', false);
            });
        });
    })();
    </script>
    <?php
}

/* ─── ۱۲ب. پیدا کردن کاربر با موبایل (بدون ساخت کاربر) ─── */
if ( ! function_exists( 'senoobar_otp_find_user' ) ) {
    function senoobar_otp_find_user( $phone ) {
        $phone = senoobar_normalize_phone( $phone );
        $user = get_user_by( 'login', $phone );
        if ( ! $user ) {
            $user = get_user_by( 'email', senoobar_phone_email( $phone ) );
        }
        if ( ! $user ) {
            $found = get_users( [ 'meta_key' => 'mobile', 'meta_value' => $phone, 'number' => 1, 'fields' => 'ID' ] );
            if ( ! empty( $found ) ) {
                $user = get_userdata( $found[0] );
            }
        }
        return $user ?: null;
    }
}

/* ─── ۱۳. AJAX: ارسال کد بازنشانی ─── */
add_action( 'wp_ajax_nopriv_senoobar_reset_send', 'senoobar_reset_send_ajax' );
add_action( 'wp_ajax_senoobar_reset_send', 'senoobar_reset_send_ajax' );
function senoobar_reset_send_ajax() {
    check_ajax_referer( 'senoobar_otp_nonce', 'nonce' );

    $phone = isset( $_POST['phone'] ) ? senoobar_normalize_phone( $_POST['phone'] ) : '';

    if ( strlen( $phone ) !== 11 || substr( $phone, 0, 2 ) !== '09' ) {
        wp_send_json_error( [ 'message' => 'شماره موبایل نامعتبر است.' ] );
    }

    $user = senoobar_otp_find_user( $phone );
    if ( ! $user ) {
        wp_send_json_error( [ 'message' => 'حسابی با این شماره پیدا نشد.' ] );
    }

    $rl = senoobar_otp_rate_limit( $phone );
    if ( is_wp_error( $rl ) ) {
        wp_send_json_error( [ 'message' => $rl->get_error_message() ] );
    }

    $code = senoobar_otp_generate();
    senoobar_otp_store( 'reset_' . $phone, $code );

    $text = "سایت صنوبر\nکد تایید بازنشانی رمز: {$code}";
    $sent = senoobar_otp_send_sms( $phone, $text );

    if ( is_wp_error( $sent ) ) {
        wp_send_json_error( [ 'message' => $sent->get_error_message() ] );
    }

    wp_send_json_success( [ 'message' => 'کد بازنشانی ارسال شد.' ] );
}

/* ─── ۱۴. AJAX: تأیید کد بازنشانی ─── */
add_action( 'wp_ajax_nopriv_senoobar_reset_verify', 'senoobar_reset_verify_ajax' );
add_action( 'wp_ajax_senoobar_reset_verify', 'senoobar_reset_verify_ajax' );
function senoobar_reset_verify_ajax() {
    check_ajax_referer( 'senoobar_otp_nonce', 'nonce' );

    $phone = isset( $_POST['phone'] ) ? senoobar_normalize_phone( $_POST['phone'] ) : '';
    $code  = isset( $_POST['code'] ) ? preg_replace( '/\D+/', '', $_POST['code'] ) : '';

    if ( strlen( $phone ) !== 11 ) {
        wp_send_json_error( [ 'message' => 'شماره موبایل نامعتبر است.' ] );
    }

    $v = senoobar_otp_verify( 'reset_' . $phone, $code );
    if ( is_wp_error( $v ) ) {
        wp_send_json_error( [ 'message' => $v->get_error_message() ] );
    }

    $token = wp_generate_password( 20, false );
    set_transient( 'senoobar_reset_ok_' . $phone, wp_hash( $token ), 10 * MINUTE_IN_SECONDS );

    wp_send_json_success( [ 'message' => 'کد تأیید شد. رمز جدید را وارد کنید.', 'token' => $token ] );
}

/* ─── ۱۵. AJAX: ذخیره رمز عبور جدید ─── */
add_action( 'wp_ajax_nopriv_senoobar_reset_save', 'senoobar_reset_save_ajax' );
add_action( 'wp_ajax_senoobar_reset_save', 'senoobar_reset_save_ajax' );
function senoobar_reset_save_ajax() {
    check_ajax_referer( 'senoobar_otp_nonce', 'nonce' );

    $phone    = isset( $_POST['phone'] ) ? senoobar_normalize_phone( $_POST['phone'] ) : '';
    $token    = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : '';
    $password = isset( $_POST['password'] ) ? (string) $_POST['password'] : '';

    if ( strlen( $phone ) !== 11 ) {
        wp_send_json_error( [ 'message' => 'شماره موبایل نامعتبر است.' ] );
    }
    if ( strlen( $password ) < 6 ) {
        wp_send_json_error( [ 'message' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.' ] );
    }

    $stored = get_transient( 'senoobar_reset_ok_' . $phone );
    if ( false === $stored || ! hash_equals( $stored, wp_hash( $token ) ) ) {
        wp_send_json_error( [ 'message' => 'نشست بازنشانی منقضی شده است. دوباره شروع کنید.' ] );
    }

    $user = senoobar_otp_find_user( $phone );
    if ( ! $user ) {
        wp_send_json_error( [ 'message' => 'کاربر پیدا نشد.' ] );
    }

    wp_set_password( $password, $user->ID );
    delete_transient( 'senoobar_reset_ok_' . $phone );

    wp_send_json_success( [ 'message' => 'رمز عبور با موفقیت تغییر کرد.' ] );
}

/* ─── ۱۶. JS فرم بازنشانی رمز (فقط در صفحه حساب) ─── */
add_action( 'wp_footer', 'senoobar_reset_footer_js', 31 );
function senoobar_reset_footer_js() {
    if ( ! is_account_page() ) {
        return;
    }
    $ajax_url = admin_url( 'admin-ajax.php' );
    $nonce    = wp_create_nonce( 'senoobar_otp_nonce' );
    ?>
    <script>
    (function () {
        var form = document.getElementById('snb-reset-form');
        if (!form) return;
        var phoneEl  = document.getElementById('reset_phone');
        var codeWrap = document.getElementById('snb-reset-code-wrap');
        var codeEl   = document.getElementById('reset_code');
        var passWrap = document.getElementById('snb-reset-pass-wrap');
        var passEl   = document.getElementById('reset_pass');
        var msgEl    = document.getElementById('snb-reset-msg');
        var sendBtn  = document.getElementById('snb-reset-send');
        var verifyBtn= document.getElementById('snb-reset-verify');
        var saveBtn  = document.getElementById('snb-reset-save');
        var token    = '';

        function showMsg(text, ok) {
            msgEl.style.display = 'block';
            msgEl.textContent = text;
            msgEl.style.color = ok ? '#2e7d32' : '#c62828';
        }
        function normPhone(v) {
            var d = (v || '').replace(/\D+/g, '');
            if (d.length > 10 && d.slice(0,2) === '98') { d = d.slice(2); }
            if (d.length === 10 && d.charAt(0) !== '0') { d = '0' + d; }
            return d;
        }
        function post(action, data) {
            data.action = action;
            data.nonce  = '<?php echo esc_js( $nonce ); ?>';
            var fd = new FormData();
            for (var k in data) { fd.append(k, data[k]); }
            return fetch('<?php echo esc_url( $ajax_url ); ?>', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); });
        }

        sendBtn.addEventListener('click', function () {
            var phone = normPhone(phoneEl.value);
            if (phone.length !== 11 || phone.slice(0,2) !== '09') {
                showMsg('شماره موبایل نامعتبر است.', false);
                return;
            }
            sendBtn.disabled = true;
            sendBtn.textContent = 'در حال ارسال…';
            post('senoobar_reset_send', { phone: phone }).then(function (res) {
                sendBtn.disabled = false;
                sendBtn.textContent = 'ارسال مجدد کد';
                if (res && res.success) {
                    codeWrap.style.display = 'block';
                    verifyBtn.style.display = 'block';
                    showMsg(res.data.message || 'کد ارسال شد.', true);
                } else {
                    showMsg((res.data && res.data.message) || 'خطا در ارسال کد.', false);
                }
            }).catch(function () {
                sendBtn.disabled = false;
                sendBtn.textContent = 'ارسال کد تأیید';
                showMsg('خطای شبکه. دوباره تلاش کن.', false);
            });
        });

        verifyBtn.addEventListener('click', function () {
            var phone = normPhone(phoneEl.value);
            var code  = (codeEl.value || '').replace(/\D+/g, '');
            if (code.length !== 5) {
                showMsg('کد ۵ رقمی را وارد کنید.', false);
                return;
            }
            verifyBtn.disabled = true;
            verifyBtn.textContent = 'در حال بررسی…';
            post('senoobar_reset_verify', { phone: phone, code: code }).then(function (res) {
                verifyBtn.disabled = false;
                verifyBtn.textContent = 'تأیید کد';
                if (res && res.success) {
                    token = res.data.token || '';
                    codeWrap.style.display = 'none';
                    verifyBtn.style.display = 'none';
                    passWrap.style.display = 'block';
                    saveBtn.style.display = 'block';
                    showMsg(res.data.message || 'کد تأیید شد.', true);
                } else {
                    showMsg((res.data && res.data.message) || 'کد اشتباه است.', false);
                }
            }).catch(function () {
                verifyBtn.disabled = false;
                verifyBtn.textContent = 'تأیید کد';
                showMsg('خطای شبکه. دوباره تلاش کن.', false);
            });
        });

        saveBtn.addEventListener('click', function () {
            var phone = normPhone(phoneEl.value);
            var pass  = passEl.value || '';
            if (pass.length < 6) {
                showMsg('رمز عبور باید حداقل ۶ کاراکتر باشد.', false);
                return;
            }
            saveBtn.disabled = true;
            saveBtn.textContent = 'در حال ذخیره…';
            post('senoobar_reset_save', { phone: phone, token: token, password: pass }).then(function (res) {
                saveBtn.disabled = false;
                saveBtn.textContent = 'ذخیره رمز جدید';
                if (res && res.success) {
                    showMsg('رمز عبور تغییر کرد. در حال انتقال به ورود…', true);
                    setTimeout(function () {
                        window.location.href = '<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>';
                    }, 1200);
                } else {
                    showMsg((res.data && res.data.message) || 'خطا در ذخیره رمز.', false);
                }
            }).catch(function () {
                saveBtn.disabled = false;
                saveBtn.textContent = 'ذخیره رمز جدید';
                showMsg('خطای شبکه. دوباره تلاش کن.', false);
            });
        });
    })();
    </script>
    <?php
}
