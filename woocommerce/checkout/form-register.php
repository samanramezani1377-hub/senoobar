<?php
/**
 * Senoobar - Registration Form for Checkout
 * Styled to match Senoobar design
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="senoobar-register-form" style="display: flex; flex-direction: column; gap: 16px;">
    <p style="margin: 0; font-size: 13px; color: #777d79;">حساب کاربری جدید ایجاد کنید تا خریدهای بعدی سریع‌تر باشد.</p>

    <div class="senoobar-form-row">
        <div class="senoobar-form-group">
            <label for="reg_billing_first_name" class="senoobar-form-label">نام <span class="required">*</span></label>
            <input type="text" class="senoobar-form-input" name="billing_first_name" id="reg_billing_first_name" autocomplete="given-name" required />
        </div>
        <div class="senoobar-form-group">
            <label for="reg_billing_last_name" class="senoobar-form-label">نام خانوادگی <span class="required">*</span></label>
            <input type="text" class="senoobar-form-input" name="billing_last_name" id="reg_billing_last_name" autocomplete="family-name" required />
        </div>
    </div>

    <div class="senoobar-form-group">
        <label for="reg_email" class="senoobar-form-label">ایمیل <span class="required">*</span></label>
        <input type="email" class="senoobar-form-input" name="email" id="reg_email" autocomplete="email" required />
    </div>

    <div class="senoobar-form-group">
        <label for="reg_password" class="senoobar-form-label">رمز عبور <span class="required">*</span></label>
        <input type="password" class="senoobar-form-input" name="password" id="reg_password" autocomplete="new-password" required />
        <p style="margin: 4px 0 0; font-size: 11px; color: #9b9f9c;">حداقل ۸ کاراکتر</p>
    </div>

    <div class="senoobar-checkbox-group">
        <label class="senoobar-checkbox">
            <input type="checkbox" name="create_account_terms" id="create_account_terms" required />
            <span class="senoobar-checkbox-label">من <a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_terms_page_id' ) ) ); ?>" target="_blank">قوانین و مقررات</a> را می‌پذیرم <span class="required">*</span></span>
        </label>
    </div>

    <?php do_action( 'woocommerce_register_form' ); ?>
    <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
    <button type="submit" class="senoobar-btn senoobar-btn-primary" name="register" value="<?php esc_attr_e( 'ثبت‌نام', 'woocommerce' ); ?>">ثبت‌نام</button>
</div>