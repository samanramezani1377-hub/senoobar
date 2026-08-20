<?php
/**
 * Senoobar — تأیید شماره موبایل با کد پیامکی هنگام ثبت‌نام.
 *
 * جریان:
 *   ۱. کاربر فرم ثبت‌نام (نام / نام‌خانوادگی / موبایل / رمز) را پر می‌کند.
 *   ۲. روی «ارسال کد تأیید» می‌زند → کد ۵ رقمی پیامک می‌شود و ورودی‌ها
 *      به‌صورت موقت (غیرحساس به رمز عبور: فقط metadata) نگه‌داری می‌شود.
 *   ۳. کد را وارد و تأیید می‌کند → در صورت درستی، حساب ساخته می‌شود
 *      و کاربر وارد می‌شود.
 *
 * از زیرساخت OTP موجود در inc/otp-login.php (ملی‌پیامک، rate-limit،
 * store/verify) استفاده می‌کند تا منطق تکراری نداشته باشیم.
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/* ─── ۱. AJAX: ارسال کد تأیید برای ثبت‌نام ─── */
add_action( 'wp_ajax_nopriv_senoobar_reg_otp_send', 'senoobar_reg_otp_send_ajax' );
add_action( 'wp_ajax_senoobar_reg_otp_send', 'senoobar_reg_otp_send_ajax' );
function senoobar_reg_otp_send_ajax() {
	check_ajax_referer( 'senoobar_otp_nonce', 'nonce' );

	$phone    = isset( $_POST['phone'] ) ? senoobar_normalize_phone( $_POST['phone'] ) : '';
	$first    = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
	$last     = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
	$password = isset( $_POST['password'] ) ? (string) $_POST['password'] : '';

	// اعتبارسنجی شماره.
	if ( strlen( $phone ) !== 11 || substr( $phone, 0, 2 ) !== '09' ) {
		wp_send_json_error( array( 'message' => 'شماره موبایل معتبر نیست. لطفاً یک شماره ۱۱ رقمی با ۰۹ وارد کنید.' ) );
	}
	if ( '' === $first || '' === $last ) {
		wp_send_json_error( array( 'message' => 'نام و نام خانوادگی را وارد کنید.' ) );
	}
	if ( strlen( $password ) < 6 ) {
		wp_send_json_error( array( 'message' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.' ) );
	}

	// شماره نباید از قبل ثبت شده باشد.
	if ( senoobar_otp_user_exists( $phone ) ) {
		wp_send_json_error( array( 'message' => 'این شماره موبایل قبلاً ثبت شده است. لطفاً وارد شوید.' ) );
	}

	// محدودیت نرخ درخواست.
	$rl = senoobar_otp_rate_limit( $phone );
	if ( is_wp_error( $rl ) ) {
		wp_send_json_error( array( 'message' => $rl->get_error_message() ) );
	}

	// تولید و ذخیره کد OTP.
	$code = senoobar_otp_generate();
	senoobar_otp_store( $phone, $code );

	// داده‌های فرم تأیید نمی‌شوند و در مرحله‌ی تأیید دوباره از خود فرم AJAX
	// ارسال می‌شوند؛ بنابراین نیازی به ذخیره موقت جداگانه نداریم.

	// متن پیامک.
	$text = "فروشگاه صنوبر\nکد تأیید ثبت‌نام: {$code}\nلغو11";
	$sent = senoobar_otp_send_sms( $phone, $text );

	if ( is_wp_error( $sent ) ) {
		wp_send_json_error( array( 'message' => $sent->get_error_message() ) );
	}

	wp_send_json_success( array( 'message' => 'کد تأیید ارسال شد.' ) );
}

/* ─── ۲. AJAX: تأیید کد و ساخت حساب ─── */
add_action( 'wp_ajax_nopriv_senoobar_reg_otp_verify', 'senoobar_reg_otp_verify_ajax' );
add_action( 'wp_ajax_senoobar_reg_otp_verify', 'senoobar_reg_otp_verify_ajax' );
function senoobar_reg_otp_verify_ajax() {
	check_ajax_referer( 'senoobar_otp_nonce', 'nonce' );

	$phone    = isset( $_POST['phone'] ) ? senoobar_normalize_phone( $_POST['phone'] ) : '';
	$code     = isset( $_POST['code'] ) ? preg_replace( '/\D+/', '', $_POST['code'] ) : '';
	$first    = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
	$last     = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
	$password = isset( $_POST['password'] ) ? (string) $_POST['password'] : '';

	if ( strlen( $phone ) !== 11 || substr( $phone, 0, 2 ) !== '09' ) {
		wp_send_json_error( array( 'message' => 'شماره موبایل معتبر نیست.' ) );
	}

	// تأیید کد (با محدودیت تلاش داخلی).
	$v = senoobar_otp_verify( $phone, $code );
	if ( is_wp_error( $v ) ) {
		wp_send_json_error( array( 'message' => $v->get_error_message() ) );
	}

	// اعتبارسنجی مجدد داده‌ها (ممکن است بعد از ارسال کد تغییر کرده باشند).
	if ( '' === $first || '' === $last ) {
		wp_send_json_error( array( 'message' => 'نام و نام خانوادگی را وارد کنید.' ) );
	}
	if ( strlen( $password ) < 6 ) {
		wp_send_json_error( array( 'message' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.' ) );
	}

	// بررسی نهایی تکراری نبودن (race condition).
	if ( senoobar_otp_user_exists( $phone ) ) {
		wp_send_json_error( array( 'message' => 'این شماره موبایل قبلاً ثبت شده است. لطفاً وارد شوید.' ) );
	}

	// ساخت حساب.
	$user_id = wp_insert_user( array(
		'user_login'   => $phone,
		'user_pass'    => $password,
		'user_email'   => senoobar_phone_email( $phone ),
		'first_name'   => $first,
		'last_name'    => $last,
		'display_name' => trim( $first . ' ' . $last ),
		'role'         => 'customer',
	) );

	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
	}

	update_user_meta( $user_id, 'mobile', $phone );
	update_user_meta( $user_id, 'billing_phone', $phone );
	update_user_meta( $user_id, 'billing_first_name', $first );
	update_user_meta( $user_id, 'billing_last_name', $last );
	update_user_meta( $user_id, 'billing_email', senoobar_phone_email( $phone ) );

	// کد OTP پس از تأیید موفق، توسط senoobar_otp_verify پاک شده است.
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );

	$redirect = wc_get_account_endpoint_url( 'dashboard' );

	wp_send_json_success( array( 'message' => 'حساب شما با موفقیت ساخته شد. خوش آمدید!', 'redirect' => $redirect ) );
}

/* ─── ۳. جاوااسکریپت فرم ثبت‌نام (ارسال + تأیید کد) ─── */
add_action( 'wp_footer', 'senoobar_reg_otp_footer_js', 32 );
function senoobar_reg_otp_footer_js() {
	// فقط در صفحه حساب کاربری و وقتی فرم ثبت‌نام نمایش داده می‌شود.
	if ( ! is_account_page() ) {
		return;
	}
	$ajax_url = admin_url( 'admin-ajax.php' );
	$nonce    = wp_create_nonce( 'senoobar_otp_nonce' );
	?>
	<script>
	(function () {
		var form = document.getElementById('snb-register-form');
		if (!form) return;

		var phoneEl   = document.getElementById('reg_mobile');
		var firstEl   = document.getElementById('reg_first_name');
		var lastEl    = document.getElementById('reg_last_name');
		var passEl    = document.getElementById('reg_password');
		var codeWrap  = document.getElementById('snb-reg-code-wrap');
		var codeEl    = document.getElementById('reg_code');
		var msgEl     = document.getElementById('snb-reg-msg');
		var sendBtn   = document.getElementById('snb-reg-send');
		var verifyBtn = document.getElementById('snb-reg-verify');
		var editEl    = document.getElementById('snb-reg-edit-phone');

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
		function lockAll(lock) {
			// فیلد کد تأیید (codeEl) عمداً قفل نمی‌شود؛ کاربر باید بتواند کد را وارد کند.
			[ phoneEl, firstEl, lastEl, passEl ].forEach(function (el) {
				if (el) { el.disabled = !!lock; }
			});
		}
		var resendTimer = null;
		function startCountdown() {
			var left = 60;
			sendBtn.disabled = true;
			function tick() {
				if (left <= 0) {
					sendBtn.disabled = false;
					sendBtn.textContent = 'ارسال مجدد کد';
					return;
				}
				sendBtn.textContent = 'ارسال مجدد کد (' + left + ' ثانیه)';
				left--;
			}
			tick();
			resendTimer = setInterval(tick, 1000);
		}

		sendBtn.addEventListener('click', function (e) {
			e.preventDefault();
			var phone = normPhone(phoneEl.value);
			var first = (firstEl && firstEl.value) || '';
			var last  = (lastEl && lastEl.value) || '';
			var pass  = passEl ? passEl.value : '';

			if (phone.length !== 11 || phone.slice(0,2) !== '09') {
				showMsg('شماره موبایل معتبر نیست.', false);
				return;
			}
			if (!first || !last) { showMsg('نام و نام خانوادگی را وارد کنید.', false); return; }
			if ((pass || '').length < 6) { showMsg('رمز عبور باید حداقل ۶ کاراکتر باشد.', false); return; }

			sendBtn.disabled = true;
			sendBtn.textContent = 'در حال ارسال…';
			post('senoobar_reg_otp_send', { phone: phone, first_name: first, last_name: last, password: pass }).then(function (res) {
				if (res && res.success) {
					lockAll(true);
					codeWrap.style.display = 'block';
					verifyBtn.style.display = 'block';
					if (editEl) { editEl.style.display = 'block'; }
					showMsg(res.data && res.data.message ? res.data.message : 'کد ارسال شد.', true);
					startCountdown();
				} else {
					sendBtn.disabled = false;
					sendBtn.textContent = 'ارسال کد تأیید';
					showMsg((res && res.data && res.data.message) || 'خطا در ارسال کد.', false);
				}
			}).catch(function () {
				sendBtn.disabled = false;
				sendBtn.textContent = 'ارسال کد تأیید';
				showMsg('خطای شبکه. دوباره تلاش کن.', false);
			});
		});

		if (editEl) {
			editEl.addEventListener('click', function (e) {
				e.preventDefault();
				if (resendTimer) { clearInterval(resendTimer); resendTimer = null; }
				lockAll(false);
				codeWrap.style.display = 'none';
				verifyBtn.style.display = 'none';
				editEl.style.display = 'none';
				sendBtn.disabled = false;
				sendBtn.textContent = 'ارسال کد تأیید';
				showMsg('شماره را اصلاح کنید و دوباره کد بگیرید.', false);
				phoneEl.focus();
			});
		}

		verifyBtn.addEventListener('click', function (e) {
			e.preventDefault();
			var phone = normPhone(phoneEl.value);
			var code  = (codeEl.value || '').replace(/\D+/g, '');
			var first = (firstEl && firstEl.value) || '';
			var last  = (lastEl && lastEl.value) || '';
			var pass  = passEl ? passEl.value : '';

			if (code.length !== 5) { showMsg('کد ۵ رقمی را وارد کنید.', false); return; }

			verifyBtn.disabled = true;
			verifyBtn.textContent = 'در حال بررسی…';
			post('senoobar_reg_otp_verify', { phone: phone, code: code, first_name: first, last_name: last, password: pass }).then(function (res) {
				verifyBtn.disabled = false;
				verifyBtn.textContent = 'تأیید و ساخت حساب';
				if (res && res.success) {
					showMsg(res.data && res.data.message ? res.data.message : 'حساب ساخته شد.', true);
					setTimeout(function () { window.location.href = (res.data && res.data.redirect) || window.location.href; }, 700);
				} else {
					showMsg((res && res.data && res.data.message) || 'کد اشتباه است.', false);
				}
			}).catch(function () {
				verifyBtn.disabled = false;
				verifyBtn.textContent = 'تأیید و ساخت حساب';
				showMsg('خطای شبکه. دوباره تلاش کن.', false);
			});
		});
	})();
	</script>
	<?php
}
