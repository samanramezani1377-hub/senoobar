<?php
/**
 * Senoobar — Login + Register (mobile based).
 *
 * Layout:
 *  - Two top-level tabs: "ورود" and "ثبت نام".
 *  - Inside "ورود" there are two sub-tabs: "ورود با رمز عبور" and "ورود با پیامک".
 *  - "فراموشی رمز عبور" opens a password-reset flow that works over SMS
 *    (mobile = username, so the default WP email reset cannot work — the
 *    account email is a synthetic @senoobar.local address).
 */

defined( 'ABSPATH' ) || exit;

$account_url   = wc_get_page_permalink( 'myaccount' );
$show_register = isset( $_GET['register'] ) || ( isset( $_GET['action'] ) && $_GET['action'] === 'register' );
$show_otp      = isset( $_GET['otp'] );
$show_lost     = isset( $_GET['lostpassword'] );
?>

<style>
.snb-auth-subtabs { display: flex; background: #fff; border-radius: 12px; padding: 5px; gap: 5px; box-shadow: var(--snb-shadow); margin-bottom: 16px; }
.snb-auth-subtab { flex: 1; text-align: center; padding: 9px; border-radius: 8px; font-size: 13px; font-weight: 600; color: var(--snb-muted); transition: all 0.15s; text-decoration: none; }
.snb-auth-subtab.is-active { background: #eef4f0; color: var(--snb-brand); }
.snb-auth-subtab:hover:not(.is-active) { color: var(--snb-brand); }
</style>

<div class="snb-auth" dir="rtl">
    <div class="snb-auth-intro">
        <h1>حساب کاربری صنوبر</h1>
        <p>برای مشاهده سفارش‌ها، آدرس‌ها و علاقه‌مندی‌های خود وارد شوید.</p>
    </div>

    <?php if ( ! $show_lost ) : ?>

    <!-- Top-level tabs: ورود / ثبت نام -->
    <div class="snb-auth-tabs">
        <a href="<?php echo esc_url( $account_url ); ?>" class="snb-auth-tab<?php echo ( ! $show_register ) ? ' is-active' : ''; ?>">ورود</a>
        <a href="<?php echo esc_url( $account_url . '?register' ); ?>" class="snb-auth-tab<?php echo $show_register ? ' is-active' : ''; ?>">ثبت نام</a>
    </div>

        <?php if ( $show_register ) : ?>

        <!-- ===== ثبت نام ===== -->
        <div class="snb-auth-card">
            <h2>ساخت حساب جدید</h2>
            <p class="snb-auth-sub">با شماره موبایل خود ثبت نام کنید.</p>
            <form method="post" class="snb-form">
                <?php wp_nonce_field( 'senoobar_register', 'senoobar_register_nonce' ); ?>
                <?php foreach ( senoobar_register_fields() as $key => $f ) : ?>
                    <div class="snb-field">
                        <label for="reg_<?php echo esc_attr( $key ); ?>">
                            <?php echo esc_html( $f['label'] ); ?>
                            <?php if ( ! empty( $f['required'] ) ) : ?><span class="snb-req">*</span><?php endif; ?>
                        </label>
                        <input type="<?php echo esc_attr( $f['type'] ); ?>" id="reg_<?php echo esc_attr( $key ); ?>"
                               name="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $f['placeholder'] ); ?>"
                               <?php echo ! empty( $f['required'] ) ? 'required' : ''; ?>
                               <?php echo in_array( $f['type'], [ 'tel', 'password' ], true ) ? 'dir="ltr"' : ''; ?> style="text-align:right">
                    </div>
                <?php endforeach; ?>
                <button type="submit" name="senoobar_register" value="1" class="snb-btn snb-btn-primary snb-btn-block">ساخت حساب</button>
            </form>
        </div>

        <?php else : ?>

        <!-- ===== ورود ===== -->
        <!-- Sub-tabs: ورود با رمز / ورود با پیامک -->
        <div class="snb-auth-subtabs">
            <a href="<?php echo esc_url( $account_url ); ?>" class="snb-auth-subtab<?php echo ( ! $show_otp ) ? ' is-active' : ''; ?>">ورود با رمز عبور</a>
            <a href="<?php echo esc_url( $account_url . '?otp' ); ?>" class="snb-auth-subtab<?php echo $show_otp ? ' is-active' : ''; ?>">ورود با پیامک</a>
        </div>

            <?php if ( $show_otp ) : ?>

            <!-- ورود با کد پیامکی -->
            <div class="snb-auth-card">
                <h2>ورود با کد پیامکی</h2>
                <p class="snb-auth-sub">شماره موبایل را وارد کنید تا کد تأیید برایتان پیامک شود.</p>
                <div class="snb-form" id="snb-otp-form">
                    <div class="snb-field">
                        <label for="otp_phone">شماره موبایل</label>
                        <input type="tel" id="otp_phone" placeholder="۰۹۱۲ ۳۴۵ ۶۷۸۹" required dir="ltr" style="text-align:right">
                    </div>
                    <div class="snb-field" id="snb-otp-code-wrap" style="display:none;">
                        <label for="otp_code">کد تأیید</label>
                        <input type="tel" id="otp_code" inputmode="numeric" maxlength="5" placeholder="کد ۵ رقمی" dir="ltr" style="text-align:right">
                    </div>
                    <div class="snb-field" id="snb-otp-msg" style="display:none;font-size:13px;"></div>
                    <button type="button" id="snb-otp-send" class="snb-btn snb-btn-primary snb-btn-block">ارسال کد تأیید</button>
                    <button type="button" id="snb-otp-verify" class="snb-btn snb-btn-primary snb-btn-block" style="display:none;">تأیید و ورود</button>
                </div>
            </div>

            <?php else : ?>

            <!-- ورود با رمز عبور -->
            <div class="snb-auth-card">
                <h2>ورود به حساب</h2>
                <p class="snb-auth-sub">شماره موبایل و رمز عبور خود را وارد کنید.</p>
                <form method="post" class="snb-form">
                    <div class="snb-field">
                        <label for="login_username">شماره موبایل</label>
                        <input type="tel" id="login_username" name="username" placeholder="۰۹۱۲ ۳۴۵ ۶۷۸۹" required dir="ltr" style="text-align:right">
                    </div>
                    <div class="snb-field">
                        <label for="login_password">رمز عبور</label>
                        <input type="password" id="login_password" name="password" placeholder="رمز عبور" required dir="ltr" style="text-align:right">
                    </div>
                    <div class="snb-field-row">
                        <label class="snb-remember"><input type="checkbox" name="rememberme" value="forever"> مرا به خاطر بسپار</label>
                        <a href="<?php echo esc_url( $account_url . '?lostpassword' ); ?>" class="snb-forgot">فراموشی رمز عبور</a>
                    </div>
                    <input type="hidden" name="redirect" value="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>">
                    <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
                    <button type="submit" name="login" value="ورود" class="snb-btn snb-btn-primary snb-btn-block">ورود</button>
                </form>
            </div>

            <?php endif; ?>

        <?php endif; ?>

    <?php else : ?>

    <!-- ===== فراموشی رمز عبور (بازنشانی با پیامک) ===== -->
    <div class="snb-auth-card">
        <h2>بازنشانی رمز عبور</h2>
        <p class="snb-auth-sub">شماره موبایل حساب خود را وارد کنید تا کد تأیید برایتان پیامک شود.</p>
        <div class="snb-form" id="snb-reset-form">
            <div class="snb-field">
                <label for="reset_phone">شماره موبایل</label>
                <input type="tel" id="reset_phone" placeholder="۰۹۱۲ ۳۴۵ ۶۷۸۹" required dir="ltr" style="text-align:right">
            </div>
            <div class="snb-field" id="snb-reset-code-wrap" style="display:none;">
                <label for="reset_code">کد تأیید</label>
                <input type="tel" id="reset_code" inputmode="numeric" maxlength="5" placeholder="کد ۵ رقمی" dir="ltr" style="text-align:right">
            </div>
            <div class="snb-field" id="snb-reset-pass-wrap" style="display:none;">
                <label for="reset_pass">رمز عبور جدید</label>
                <input type="password" id="reset_pass" placeholder="حداقل ۶ کاراکتر" dir="ltr" style="text-align:right">
            </div>
            <div class="snb-field" id="snb-reset-msg" style="display:none;font-size:13px;"></div>
            <button type="button" id="snb-reset-send" class="snb-btn snb-btn-primary snb-btn-block">ارسال کد تأیید</button>
            <button type="button" id="snb-reset-verify" class="snb-btn snb-btn-primary snb-btn-block" style="display:none;">تأیید کد</button>
            <button type="button" id="snb-reset-save" class="snb-btn snb-btn-primary snb-btn-block" style="display:none;">ذخیره رمز جدید</button>
            <a href="<?php echo esc_url( $account_url ); ?>" class="snb-forgot" style="display:block;margin-top:16px;text-align:center;">بازگشت به ورود</a>
        </div>
    </div>

    <?php endif; ?>
</div>
