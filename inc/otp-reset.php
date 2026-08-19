<?php
/**
 * Senoobar — بازنشانی رمز عبور با پیامک (فراموشی رمز عبور).
 *
 * چون آدرس ایمیل حساب‌ها مصنوعی (@senoobar.local) است، مکانیزم پیش‌فرض
 * وردپرس (ارسال لینک ریست به ایمیل) کار نمی‌کند؛ بنابراین بازنشانی رمز از
 * طریق کد تأیید پیامکی (SMS OTP) انجام می‌شود.
 *
 * سه مرحله:
 *  ۱) senoobar_reset_send    → دریافت موبایل و ارسال کد OTP
 *  ۲) senoobar_reset_verify  → تأیید کد OTP
 *  ۳) senoobar_reset_save    → ذخیره رمز عبور جدید
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'senoobar_otp_send_sms' ) ) {
    return;
}

/* ─── پیدا کردن کاربر با موبایل (بدون ساخت کاربر) ─── */
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

/* ─── AJAX: ارسال کد بازنشانی ─── */
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
        wp_send_json_error( [ 'message' => 'حسابی با این شماره پیدا نشد. ابتدا ثبت نام کنید.' ] );
    }

    $rl = senoobar_otp_rate_limit( $phone );
    if ( is_wp_error( $rl ) ) {
        wp_send_json_error( [ 'message' => $rl->get_error_message() ] );
    }

    $code = senoobar_otp_generate();
    senoobar_otp_store( 'reset_' . $phone, $code );

    $text = "فروشگاه صنوبر\nکد بازنشانی رمز: {$code}";
    $sent = senoobar_otp_send_sms( $phone, $text );

    if ( is_wp_error( $sent ) ) {
        wp_send_json_error( [ 'message' => $sent->get_error_message() ] );
    }

    wp_send_json_success( [ 'message' => 'کد بازنشانی ارسال شد.' ] );
}

/* ─── AJAX: تأیید کد بازنشانی ─── */
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

/* ─── AJAX: ذخیره رمز عبور جدید ─── */
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

/* ─── JS فرم بازنشانی رمز ─── */
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
