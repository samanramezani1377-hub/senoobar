<?php
/**
 * Senoobar - WooCommerce Checkout Template (minimal)
 * Only asks: name, last name, phone, province, postal code, address.
 * Renders its own header/footer so it works even with an empty page content.
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

// When loaded directly via template_include, $checkout is not passed in the
// way wc_get_template() normally would, so ensure it is always available.
if ( ! isset( $checkout ) || ! is_object( $checkout ) ) {
    $checkout = WC()->checkout();
}

get_header(); ?>

<main id="primary" class="site-main">
<div class="container page-content">

<?php
do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is required and not logged in, show login/register.
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
                <p>اطلاعات ارسال را تکمیل کنید تا سفارش نهایی شود</p>
            </div>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>" class="senoobar-btn senoobar-btn-outline">
                <span>←</span> بازگشت به سبد
            </a>
        </div>

        <?php wc_print_notices(); ?>

        <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" novalidate>

            <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

            <div class="senoobar-checkout-layout">

                <!-- =========================
                     BILLING FORM (minimal)
                ========================== -->

                <section class="senoobar-form-card" aria-labelledby="billing-heading">

                    <h2 id="billing-heading" class="senoobar-section-title">اطلاعات سفارش</h2>

                    <?php
                    // Render the billing form directly so it does not depend on
                    // the woocommerce_checkout_billing hook firing (which can be
                    // unreliable when the template is loaded via template_include).
                    wc_get_template( 'checkout/form-billing.php' );
                    ?>

                </section>

                <!-- =========================
                     ORDER SUMMARY / PAYMENT
                ========================== -->

                <aside>

                    <div class="senoobar-order-summary-card" aria-labelledby="order-summary-heading">
                        <h2 id="order-summary-heading">خلاصه سفارش</h2>

                        <div class="senoobar-checkout-section">
                            <?php wc_get_template( 'checkout/review-order.php' ); ?>
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

            </div>

            <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

        </form>

    </div>

</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

</div>
</main>

<?php get_footer();
