<?php
/**
 * Senoobar - WooCommerce Checkout Template
 * Fully styled to match Senoobar design system
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is required and not logged in, show login/register forms
if ( ! is_user_logged_in() && $checkout->is_registration_required() ) {
    echo '<div class="senoobar-auth-section">';
    wc_get_template_part( 'checkout/login' );
    echo '</div>';
}

?>

<div class="senoobar-checkout-page" dir="rtl">

    <div class="senoobar-checkout-container">

        <!-- Breadcrumb -->
        <div class="senoobar-checkout-breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a>
            <span>/</span>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>">سبد خرید</a>
            <span>/</span>
            <span>تسویه حساب</span>
        </div>

        <!-- Heading -->
        <div class="senoobar-checkout-heading">
            <div>
                <h1>تسویه حساب</h1>
                <p>اطلاعات خود را تکمیل کنید تا سفارش نهایی شود</p>
            </div>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>" class="senoobar-btn senoobar-btn-outline">
                <span>←</span> بازگشت به سبد
            </a>
        </div>

        <!-- Messages -->
        <div id="senoobar-checkout-message" class="senoobar-checkout-message" role="alert" aria-live="polite"></div>

        <?php wc_print_notices(); ?>

        <form name="checkout" method="post" class="checkout woocommerce-checkout" enctype="multipart/form-data" novalidate>

            <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

            <div class="senoobar-checkout-layout">

                <!-- =========================
                     BILLING / SHIPPING FORM
                ========================== -->

                <section class="senoobar-form-card" aria-labelledby="billing-heading">

                    <h2 id="billing-heading" class="senoobar-section-title">اطلاعات صورتحساب</h2>

                    <?php do_action( 'woocommerce_checkout_billing' ); ?>

                    <?php if ( ! is_user_logged_in() && $checkout->is_registration_required() ) : ?>
                        <div class="senoobar-checkout-section">
                            <h3 class="senoobar-section-title">ایجاد حساب کاربری</h3>
                            <div class="senoobar-auth-toggle" role="tablist">
                                <button type="button" class="senoobar-auth-btn active" role="tab" aria-selected="true" data-target="login-form">ورود</button>
                                <button type="button" class="senoobar-auth-btn" role="tab" aria-selected="false" data-target="register-form">ثبت‌نام</button>
                            </div>
                            <div id="login-form" class="senoobar-auth-panel" role="tabpanel">
                                <?php wc_get_template( 'checkout/form-login.php' ); ?>
                            </div>
                            <div id="register-form" class="senoobar-auth-panel" role="tabpanel" hidden>
                                <?php wc_get_template( 'checkout/form-register.php' ); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="senoobar-checkout-section">
                        <h3 class="senoobar-section-title">آدرس rozdreviewی</h3>

                        <div class="senoobar-form-row">
                            <?php
                            $billing_fields = $checkout->get_checkout_fields( 'billing' );
                            foreach ( $billing_fields as $key => $field ) :
                                woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
                            endforeach;
                            ?>
                        </div>
                    </div>

                    <?php if ( $checkout->is_registration_enabled() && ! is_user_logged_in() ) : ?>
                        <div class="senoobar-checkout-section">
                            <h3 class="senoobar-section-title">اطلاعات حساب کاربری (اختیاری)</h3>
                            <div class="senoobar-form-row">
                                <?php
                                $account_fields = $checkout->get_checkout_fields( 'account' );
                                foreach ( $account_fields as $key => $field ) :
                                    woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
                                endforeach;
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </section>

                <!-- =========================
                     SHIPPING ADDRESS
                ========================== -->

                <?php if ( $checkout->get_checkout_fields( 'shipping' ) ) : ?>
                <section class="senoobar-form-card" aria-labelledby="shipping-heading">
                    <h2 id="shipping-heading" class="senoobar-section-title">آدرس ارسال</h2>

                    <div class="senoobar-checkout-section">
                        <div class="senoobar-checkbox-group">
                            <label class="senoobar-checkbox">
                                <input type="checkbox" id="ship_to_different_address" name="ship_to_different_address" value="1" <?php checked( 1, $checkout->get_value( 'ship_to_different_address' ) ); ?>>
                                <span class="senoobar-checkbox-label">ارسال به آدرس متفاوت</span>
                            </label>
                        </div>

                        <div id="shipping_address_fields" style="<?php echo 1 === (int) $checkout->get_value( 'ship_to_different_address' ) ? 'display:block' : 'display:none'; ?>">
                            <div class="senoobar-form-row">
                                <?php
                                $shipping_fields = $checkout->get_checkout_fields( 'shipping' );
                                foreach ( $shipping_fields as $key => $field ) :
                                    woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
                                endforeach;
                                ?>
                            </div>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <!-- =========================
                     ORDER NOTES
                ========================== -->

                <section class="senoobar-form-card" aria-labelledby="notes-heading">
                    <h2 id="notes-heading" class="senoobar-section-title">یادداشت سفارش</h2>

                    <div class="senoobar-checkout-section">
                        <div class="senoobar-form-row">
                            <?php
                            $order_fields = $checkout->get_checkout_fields( 'order' );
                            foreach ( $order_fields as $key => $field ) :
                                woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
                            endforeach;
                            ?>
                        </div>
                    </div>
                </section>

                <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

            </div>

            <!-- =========================
                 ORDER SUMMARY / PAYMENT
            ========================== -->

            <aside>

                <div class="senoobar-order-summary-card" aria-labelledby="order-summary-heading">
                    <h2 id="order-summary-heading">خلاصه سفارش</h2>

                    <div class="senoobar-checkout-section">
                        <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                    </div>
                </div>

                <!-- Trust Badges -->
                <div class="senoobar-trust-badges">
                    <h4>امنیت و اطمینان</h4>
                    <div class="senoobar-trust-list">
                        <span class="senoobar-trust-item">🔒 پرداخت امن</span>
                        <span class="senoobar-trust-item">🛡️ ضمانت کیفیت</span>
                        <span class="senoobar-trust-item">🚚 ارسال سریع</span>
                        <span class="senoobar-trust-item">🔄 مرجوعی آسان</span>
                    </div>
                </div>

            </aside>

        </form>

    </div>

</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

<script>
(function() {
    'use strict';

    // Auth tab toggle
    document.querySelectorAll('.senoobar-auth-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.dataset.target;
            document.querySelectorAll('.senoobar-auth-btn').forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            document.querySelectorAll('.senoobar-auth-panel').forEach(p => {
                p.hidden = true;
            });
            document.getElementById(target)?.hidden = false;
        });
    });

    // Ship to different address toggle
    const shipCheckbox = document.getElementById('ship_to_different_address');
    const shippingFields = document.getElementById('shipping_address_fields');
    if (shipCheckbox && shippingFields) {
        shipCheckbox.addEventListener('change', function() {
            shippingFields.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Payment method selection styling
    document.querySelectorAll('.senoobar-payment-method').forEach(method => {
        method.addEventListener('click', function(e) {
            if (e.target.type !== 'radio') {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
            }
            document.querySelectorAll('.senoobar-payment-method').forEach(m => m.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
})();
</script>