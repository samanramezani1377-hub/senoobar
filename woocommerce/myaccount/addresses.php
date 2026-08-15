<?php
/**
 * Senoobar — Addresses (billing) edit.
 * Args: user_id.
 */

defined( 'ABSPATH' ) || exit;

$user_id = $args['user_id'] ?? get_current_user_id();

$billing = [
    'first_name' => get_user_meta( $user_id, 'billing_first_name', true ),
    'last_name'  => get_user_meta( $user_id, 'billing_last_name', true ),
    'phone'      => get_user_meta( $user_id, 'billing_phone', true ),
    'state'      => get_user_meta( $user_id, 'billing_state', true ),
    'postcode'   => get_user_meta( $user_id, 'billing_postcode', true ),
    'address_1'  => get_user_meta( $user_id, 'billing_address_1', true ),
];

$states = function_exists( 'senoobar_iran_provinces' ) ? senoobar_iran_provinces() : [];
?>

<div class="snb-addresses">
    <header class="snb-dash-header">
        <h2>آدرس‌های من</h2>
        <p>آدرس پیش‌فرض ارسال سفارش‌ها.</p>
    </header>

    <form method="post" action="" class="snb-form">
        <?php wp_nonce_field( 'senoobar_save_address' ); ?>
        <div class="snb-card">
            <h3>آدرس اصلی (صورتحساب)</h3>
            <div class="snb-form-grid">
                <div class="snb-field">
                    <label>نام <span class="snb-req">*</span></label>
                    <input type="text" name="billing_first_name" value="<?php echo esc_attr( $billing['first_name'] ); ?>" required>
                </div>
                <div class="snb-field">
                    <label>نام خانوادگی <span class="snb-req">*</span></label>
                    <input type="text" name="billing_last_name" value="<?php echo esc_attr( $billing['last_name'] ); ?>" required>
                </div>
                <div class="snb-field">
                    <label>شماره موبایل <span class="snb-req">*</span></label>
                    <input type="tel" name="billing_phone" value="<?php echo esc_attr( $billing['phone'] ); ?>" required dir="ltr" style="text-align:right">
                </div>
                <div class="snb-field">
                    <label>کد پستی</label>
                    <input type="text" name="billing_postcode" value="<?php echo esc_attr( $billing['postcode'] ); ?>" maxlength="10" dir="ltr" style="text-align:right">
                </div>
                <div class="snb-field snb-field-full">
                    <label>استان <span class="snb-req">*</span></label>
                    <select name="billing_state" required>
                        <option value="">انتخاب استان</option>
                        <?php foreach ( $states as $key => $label ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $billing['state'], $key ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="snb-field snb-field-full">
                    <label>آدرس کامل <span class="snb-req">*</span></label>
                    <textarea name="billing_address_1" rows="3" required><?php echo esc_textarea( $billing['address_1'] ); ?></textarea>
                </div>
            </div>
        </div>
        <button type="submit" name="senoobar_save_address" value="1" class="snb-btn snb-btn-primary">ذخیره آدرس</button>
    </form>
</div>
