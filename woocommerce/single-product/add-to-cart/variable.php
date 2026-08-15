<?php
/**
 * Variable product add-to-cart (custom — keep variations, no quantity display).
 */

defined('ABSPATH') || exit;

global $product;

$attribute_keys  = array_keys($product->get_attributes());
$variation_count = count($product->get_children());

do_action('woocommerce_before_add_to_cart_form');
?>
<form class="variations_form cart" method="post" enctype='multipart/form-data'
      data-product_id="<?php echo absint($product->get_id()); ?>"
      data-product_variations="<?php echo htmlspecialchars(wp_json_encode($product->get_available_variations())); ?>">

    <?php do_action('woocommerce_before_variations_form'); ?>

    <?php if (empty($variation_count)): ?>
        <p class="stock out-of-stock"><?php echo esc_html(apply_filters('woocommerce_out_of_stock_message', __('This product is currently out of stock and unavailable.', 'woocommerce'))); ?></p>
    <?php else: ?>
        <table class="variations" cellspacing="0" role="presentation">
            <tbody>
            <?php foreach ($product->get_attributes() as $attribute_name => $options): ?>
                <tr>
                    <th class="label"><label for="<?php echo esc_attr(sanitize_title($attribute_name)); ?>"><?php echo wc_attribute_label($attribute_name); ?></label></th>
                    <td class="value">
                        <?php
                        wc_dropdown_variation_attribute_options([
                            'options'   => $options,
                            'attribute' => $attribute_name,
                            'product'   => $product,
                        ]);
                        echo end($attribute_keys) === $attribute_name ? wp_kses_post(apply_filters('woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__('Clear', 'woocommerce') . '</a>')) : '';
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="single_variation_wrap">
            <?php do_action('woocommerce_before_single_variation'); ?>

            <div class="woocommerce-variation single_variation"></div>
            <div class="woocommerce-variation-add-to-cart variations_button">
                <input type="hidden" name="quantity" id="pdHiddenQty" value="1" min="1" />
                <button type="submit" class="single_add_to_cart_button button alt"><?php echo esc_html($product->single_add_to_cart_text()); ?></button>
                <input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>" />
                <input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>" />
                <input type="hidden" name="variation_id" class="variation_id" value="0" />
            </div>

            <?php do_action('woocommerce_after_single_variation'); ?>
        </div>
    <?php endif; ?>

    <?php do_action('woocommerce_after_variations_form'); ?>
</form>
<?php do_action('woocommerce_after_add_to_cart_form'); ?>
