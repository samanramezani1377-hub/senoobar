<?php
/**
 * Senoobar - Custom Billing Form (minimal)
 * Renders: نام، نام خانوادگی، شماره موبایل، استان، آدرس
 * Uses the Senoobar field styling (senoobar-form-row grid).
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

$checkout = WC()->checkout();
$fields = $checkout->get_checkout_fields( 'billing' );

?>

<div class="senoobar-form-row">

    <?php foreach ( $fields as $key => $field ) : ?>

        <?php
        // Skip the hidden email field — it must exist in the DOM for core
        // validation but is filled automatically on submit.
        if ( 'billing_email' === $key ) {
            echo '<input type="hidden" name="billing_email" id="billing_email" value="">';
            continue;
        }

        $value = $checkout->get_value( $key );
        $required = ! empty( $field['required'] );
        $type = $field['type'] ?? 'text';
        $label = $field['label'] ?? '';
        $placeholder = $field['placeholder'] ?? '';

        // Full width for phone + address
        $full = in_array( 'form-row-wide', $field['class'], true );
        $group_class = 'senoobar-form-group' . ( $full ? ' full-width' : '' );
        ?>

        <div class="<?php echo esc_attr( $group_class ); ?>">
            <label for="<?php echo esc_attr( $key ); ?>" class="senoobar-form-label">
                <?php echo esc_html( $label ); ?>
                <?php if ( $required ) : ?><span class="required">*</span><?php endif; ?>
            </label>

            <?php if ( 'select' === $type ) : ?>

                <select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" class="senoobar-form-select"
                    <?php echo $required ? 'required' : ''; ?>>
                    <option value=""><?php echo esc_html( $placeholder ?: 'انتخاب کنید' ); ?></option>
                    <?php foreach ( (array) ( $field['options'] ?? [] ) as $opt_key => $opt_label ) : ?>
                        <option value="<?php echo esc_attr( $opt_key ); ?>" <?php selected( $value, $opt_key ); ?>>
                            <?php echo esc_html( $opt_label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            <?php elseif ( 'textarea' === $type ) : ?>

                <textarea name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" class="senoobar-form-textarea"
                    placeholder="<?php echo esc_attr( $placeholder ); ?>"
                    <?php echo $required ? 'required' : ''; ?>><?php echo esc_textarea( $value ); ?></textarea>

            <?php else : ?>

                <?php
                $maxlength = isset( $field['maxlength'] ) ? (int) $field['maxlength'] : 0;
                $input_class = isset( $field['input_class'] ) ? implode( ' ', $field['input_class'] ) : '';
                ?>

                <input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>"
                    class="senoobar-form-input <?php echo esc_attr( $input_class ); ?>" value="<?php echo esc_attr( $value ); ?>"
                    placeholder="<?php echo esc_attr( $placeholder ); ?>"
                    <?php echo $maxlength ? 'maxlength="' . $maxlength . '"' : ''; ?>
                    <?php echo $required ? 'required' : ''; ?>>

            <?php endif; ?>
        </div>

    <?php endforeach; ?>

</div>
