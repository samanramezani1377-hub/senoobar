<?php
/**
 * Senoobar - Payment Methods Template
 * Styled payment method list
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

if ( ! $available_gateways ) {
    return;
}

$gateway_count = count( $available_gateways );
?>

<div class="senoobar-payment-section" id="payment">

    <div class="senoobar-payment-methods" role="radiogroup" aria-labelledby="payment-heading">
        <?php
        $chosen_gateway = WC()->session ? WC()->session->chosen_payment_method : '';
        $available_gateways = apply_filters( 'woocommerce_available_payment_gateways', $available_gateways );

        foreach ( $available_gateways as $gateway ) : ?>
            <div class="senoobar-payment-method <?php echo $chosen_gateway === $gateway->id ? 'selected' : ''; ?>" data-gateway="<?php echo esc_attr( $gateway->id ); ?>">
                <input
                    type="radio"
                    id="payment_method_<?php echo esc_attr( $gateway->id ); ?>"
                    name="payment_method"
                    value="<?php echo esc_attr( $gateway->id ); ?>"
                    <?php checked( $gateway->id, $chosen_gateway ); ?>
                    aria-label="<?php echo esc_attr( $gateway->get_title() ); ?>"
                >
                <div class="senoobar-payment-icon">
                    <?php
                    // Payment icons
                    $icon_map = [
                        'bacs'      => '🏦',
                        'cheque'    => '📝',
                        'cod'       => '💵',
                        'paypal'    => '🅿️',
                        'stripe'    => '💳',
                        'zarinpal'  => '🏦',
                        'idpay'     => '💳',
                        'nextpay'   => '💳',
                        'mellat'    => '🏦',
                        'parsian'   => '🏦',
                        'saman'     => '🏦',
                        'pasargad'  => '🏦',
                    ];
                    $icon = '💳';
                    foreach ( $icon_map as $key => $emoji ) {
                        if ( strpos( $gateway->id, $key ) !== false ) {
                            $icon = $emoji;
                            break;
                        }
                    }
                    echo $icon;
                    ?>
                </div>
                <div class="senoobar-payment-info">
                    <div class="senoobar-payment-name"><?php echo wp_kses_post( $gateway->get_title() ); ?></div>
                    <?php if ( $gateway->get_description() ) : ?>
                        <div class="senoobar-payment-desc"><?php echo wp_kses_post( $gateway->get_description() ); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php do_action( 'woocommerce_checkout_after_payment_methods', $available_gateways ); ?>

</div>

<script>
(function() {
    'use strict';
    // Ensure payment method radio styling updates on change
    document.querySelectorAll('.senoobar-payment-method input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.senoobar-payment-method').forEach(m => m.classList.remove('selected'));
            this.closest('.senoobar-payment-method')?.classList.add('selected');
        });
    });
})();
</script>