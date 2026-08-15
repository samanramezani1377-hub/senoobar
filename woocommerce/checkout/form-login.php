<?php
/**
 * Senoobar - Login Form for Checkout
 * Styled to match Senoobar design
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="senoobar-login-form" style="display: flex; flex-direction: column; gap: 16px;">
    <div class="senoobar-form-group">
        <label for="username" class="senoobar-form-label">نام کاربری یا ایمیل <span class="required">*</span></label>
        <input type="text" class="senoobar-form-input" name="username" id="username" autocomplete="username" required />
    </div>

    <div class="senoobar-form-group">
        <label for="password" class="senoobar-form-label">رمز عبور <span class="required">*</span></label>
        <input type="password" class="senoobar-form-input" name="password" id="password" autocomplete="current-password" required />
    </div>

    <div class="senoobar-checkbox-group">
        <label class="senoobar-checkbox">
            <input type="checkbox" name="rememberme" id="rememberme" value="forever" />
            <span class="senoobar-checkbox-label">مرا به خاطر بسپار</span>
        </label>
    </div>

    <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
    <button type="submit" class="senoobar-btn senoobar-btn-primary" name="login" value="<?php esc_attr_e( 'ورود', 'woocommerce' ); ?>">ورود</button>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="senoobar-form-error" style="font-size: 13px; color: var(--senoobar-green); text-decoration: none;">رمز عبور را فراموش کرده‌ام</a>
    </div>
</div>