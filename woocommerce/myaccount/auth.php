<?php
/**
 * Senoobar — Login + Register (mobile based).
 */

defined( 'ABSPATH' ) || exit;

$account_url    = wc_get_page_permalink( 'myaccount' );
$show_register  = isset( $_GET['register'] ) || ( isset( $_GET['action'] ) && $_GET['action'] === 'register' );
$show_otp       = isset( $_GET['otp'] );
?>

<div class="snb-auth" dir="rtl">
    <div class="snb-auth-intro">
        <h1>حساب کاربری صنوبر</h1>
        <p>برای مشاهده سفارش‌ها، آدرس‌ها و علاقه‌مندی‌های خود وارد شوید.</p>
    </div>

    <div class="snb-auth-tabs">
        <a href="<?php echo esc_url( $account_url ); ?>" class="snb-auth-tab<?php echo ( ! $show_register && ! $show_otp ) ? ' is-active' : ''; ?>">ورود</a>
        <a href="<?php echo esc_url( $account_url . '?register' ); ?>" class="snb-auth-tab<?php echo $show_register ? ' is-active' : ''; ?>">ثبت نام</a>
        <a href="<?php echo esc_url( $account_url . '?otp' ); ?>" class="snb-auth-tab<?php echo $show_otp ? ' is-active' : ''; ?>">ورود با کد پیامکی</a>
    </div>

    <?php if ( ! $show_register && ! $show_otp ) : ?>
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
                    <a href="<?php echo esc_url( wc_lostpassword_url() ); ?>" class="snb-forgot">فراموشی رمز عبور</a>
                </div>
                <input type="hidden" name="redirect" value="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>">
                <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
                <button type="submit" name="login" value="ورود" class="snb-btn snb-btn-primary snb-btn-block">ورود</button>
            </form>
        </div>
    <?php elseif ( $show_otp ) : ?>
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
    <?php endif; ?>
</div>
