<?php
/**
 * Senoobar - WooCommerce Cart Template
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );

$cart = WC()->cart;
?>

<div class="senoobar-cart-page" dir="rtl">

    <div class="senoobar-cart-container">

        <!-- Breadcrumb -->
        <div class="senoobar-cart-breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                خانه
            </a>

            <span>/</span>

            <span>سبد خرید</span>
        </div>

        <!-- Header -->
        <div class="senoobar-cart-heading">
            <div>
                <h1>سبد خرید شما</h1>

                <p>
                    <span class="senoobar-cart-count">
                        <?php echo esc_html( $cart->get_cart_contents_count() ); ?>
                    </span>
                    محصول در سبد خرید
                </p>
            </div>

            <a
                href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
                class="senoobar-continue-shopping"
            >
                <span>←</span>
                ادامه خرید
            </a>
        </div>

        <!-- AJAX Message -->
        <div
            id="senoobar-cart-message"
            class="senoobar-cart-message"
            role="alert"
            aria-live="polite"
        ></div>

        <?php if ( $cart->is_empty() ) : ?>

            <!-- Empty Cart -->
            <div class="senoobar-empty-cart">

                <div class="senoobar-empty-cart-icon">
                    🛒
                </div>

                <h2>
                    سبد خرید شما خالی است
                </h2>

                <p>
                    هنوز محصولی به سبد خرید خود اضافه نکرده‌اید.
                </p>

                <a
                    href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
                    class="senoobar-primary-button"
                >
                    مشاهده محصولات
                </a>

            </div>

        <?php else : ?>

            <div class="senoobar-cart-layout">

                <!-- =========================
                     PRODUCTS
                ========================== -->

                <section class="senoobar-cart-products">

                    <div class="senoobar-cart-card">

                        <div class="senoobar-cart-table-header">

                            <div class="product-col">
                                محصول
                            </div>

                            <div class="price-col">
                                قیمت
                            </div>

                            <div class="quantity-col">
                                تعداد
                            </div>

                            <div class="subtotal-col">
                                جمع
                            </div>

                            <div class="remove-col"></div>

                        </div>

                        <div id="senoobar-cart-items">

                            <?php foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) : ?>

                                <?php
                                $_product = apply_filters(
                                    'woocommerce_cart_item_product',
                                    $cart_item['data'],
                                    $cart_item,
                                    $cart_item_key
                                );

                                if (
                                    ! $_product ||
                                    ! $_product->exists() ||
                                    $cart_item['quantity'] < 1
                                ) {
                                    continue;
                                }

                                $product_id = apply_filters(
                                    'woocommerce_cart_item_product_id',
                                    $cart_item['product_id'],
                                    $cart_item,
                                    $cart_item_key
                                );

                                $product_name = $_product->get_name();

                                $product_permalink = $_product->is_visible()
                                    ? $_product->get_permalink( $cart_item )
                                    : '';

                                $thumbnail = $_product->get_image(
                                    'woocommerce_thumbnail'
                                );

                                $unit_price = $cart->get_product_price( $_product );

                                $line_subtotal = $cart->get_product_subtotal(
                                    $_product,
                                    $cart_item['quantity']
                                );
                                ?>

                                <article
                                    class="senoobar-cart-item"
                                    data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>"
                                >

                                    <!-- Product -->
                                    <div class="senoobar-cart-product">

                                        <div class="senoobar-cart-product-image">

                                            <?php if ( $product_permalink ) : ?>

                                                <a
                                                    href="<?php echo esc_url( $product_permalink ); ?>"
                                                >
                                                    <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                </a>

                                            <?php else : ?>

                                                <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                                            <?php endif; ?>

                                        </div>

                                        <div class="senoobar-cart-product-info">

                                            <?php if ( $product_permalink ) : ?>

                                                <a
                                                    class="senoobar-cart-product-name"
                                                    href="<?php echo esc_url( $product_permalink ); ?>"
                                                >
                                                    <?php echo esc_html( $product_name ); ?>
                                                </a>

                                            <?php else : ?>

                                                <span class="senoobar-cart-product-name">
                                                    <?php echo esc_html( $product_name ); ?>
                                                </span>

                                            <?php endif; ?>

                                            <?php
                                            /**
                                             * Product variation / metadata.
                                             */
                                            echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                            ?>

                                            <?php if ( $_product->get_sku() ) : ?>

                                                <span class="senoobar-product-sku">
                                                    کد محصول:
                                                    <?php echo esc_html( $_product->get_sku() ); ?>
                                                </span>

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                    <!-- Price -->
                                    <div class="senoobar-cart-price">

                                        <span class="senoobar-mobile-label">
                                            قیمت
                                        </span>

                                        <span>
                                            <?php echo wp_kses_post( $unit_price ); ?>
                                        </span>

                                    </div>

                                    <!-- Quantity -->
                                    <div class="senoobar-cart-quantity">

                                        <span class="senoobar-mobile-label">
                                            تعداد
                                        </span>

                                        <div class="senoobar-quantity-control">

                                            <button
                                                type="button"
                                                class="senoobar-qty-button senoobar-qty-plus"
                                                data-key="<?php echo esc_attr( $cart_item_key ); ?>"
                                                aria-label="افزایش تعداد"
                                            >
                                                +
                                            </button>

                                            <input
                                                type="number"
                                                class="senoobar-qty-input"
                                                value="<?php echo esc_attr( $cart_item['quantity'] ); ?>"
                                                min="1"
                                                step="1"
                                                inputmode="numeric"
                                                data-key="<?php echo esc_attr( $cart_item_key ); ?>"
                                                aria-label="تعداد محصول"
                                            />

                                            <button
                                                type="button"
                                                class="senoobar-qty-button senoobar-qty-minus"
                                                data-key="<?php echo esc_attr( $cart_item_key ); ?>"
                                                aria-label="کاهش تعداد"
                                            >
                                                −
                                            </button>

                                        </div>

                                    </div>

                                    <!-- Subtotal -->
                                    <div class="senoobar-cart-subtotal">

                                        <span class="senoobar-mobile-label">
                                            جمع
                                        </span>

                                        <span>
                                            <?php echo wp_kses_post( $line_subtotal ); ?>
                                        </span>

                                    </div>

                                    <!-- Remove -->
                                    <div class="senoobar-cart-remove">

                                        <button
                                            type="button"
                                            class="senoobar-remove-button"
                                            data-key="<?php echo esc_attr( $cart_item_key ); ?>"
                                            aria-label="حذف <?php echo esc_attr( $product_name ); ?>"
                                        >
                                            <svg
                                                width="18"
                                                height="18"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M3 6h18"/>
                                                <path d="M8 6V4h8v2"/>
                                                <path d="M19 6l-1 14H6L5 6"/>
                                                <path d="M10 11v5"/>
                                                <path d="M14 11v5"/>
                                            </svg>
                                        </button>

                                    </div>

                                </article>

                            <?php endforeach; ?>

                        </div>

                        <!-- Continue Shopping -->
                        <div class="senoobar-cart-footer">

                            <a
                                href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
                                class="senoobar-continue-shopping"
                            >
                                <span>←</span>
                                ادامه خرید
                            </a>

                        </div>

                    </div>

                </section>


                <!-- =========================
                     SUMMARY
                ========================== -->

                <aside class="senoobar-cart-summary">

                    <div class="senoobar-summary-card">

                        <h2>
                            خلاصه سفارش
                        </h2>

                        <div class="senoobar-summary-row">

                            <span>
                                جمع جزء
                            </span>

                            <strong id="senoobar-cart-subtotal">
                                <?php echo wp_kses_post( $cart->get_cart_subtotal() ); ?>
                            </strong>

                        </div>


                        <?php if ( $cart->get_discount_total() > 0 ) : ?>

                            <div class="senoobar-summary-row discount">

                                <span>
                                    تخفیف
                                </span>

                                <strong id="senoobar-cart-discount">
                                    −<?php echo wp_kses_post( wc_price( $cart->get_discount_total() ) ); ?>
                                </strong>

                            </div>

                        <?php endif; ?>


                        <div class="senoobar-summary-row">

                            <span>
                                هزینه ارسال
                            </span>

                            <strong>
                                <?php echo wp_kses_post( $cart->get_cart_shipping_total() ); ?>
                            </strong>

                        </div>


                        <div class="senoobar-summary-total">

                            <span>
                                مبلغ قابل پرداخت
                            </span>

                            <strong id="senoobar-cart-total">
                                <?php echo wp_kses_post( $cart->get_total() ); ?>
                            </strong>

                        </div>


                        <!-- Coupon -->

                        <div class="senoobar-coupon">

                            <button
                                type="button"
                                class="senoobar-coupon-toggle"
                                aria-expanded="false"
                            >
                                <span>
                                    کد تخفیف دارید؟
                                </span>

                                <span>
                                    +
                                </span>
                            </button>


                            <div class="senoobar-coupon-content">

                                <div class="senoobar-coupon-form">

                                    <input
                                        type="text"
                                        id="senoobar-coupon-code"
                                        placeholder="کد تخفیف"
                                        autocomplete="off"
                                    />

                                    <button
                                        type="button"
                                        id="senoobar-apply-coupon"
                                    >
                                        اعمال
                                    </button>

                                </div>

                            </div>

                        </div>


                        <!-- Checkout -->

                        <a
                            href="<?php echo esc_url( wc_get_checkout_url() ); ?>"
                            class="senoobar-checkout-button"
                        >
                            ادامه جهت تسویه حساب
                        </a>

                    </div>


                    <!-- Services -->

                    <div class="senoobar-services-card">

                        <div class="senoobar-service">

                            <span class="senoobar-service-icon">
                                🚚
                            </span>

                            <div>
                                <strong>
                                    ارسال سریع
                                </strong>

                                <small>
                                    ارسال مطمئن سفارش
                                </small>
                            </div>

                        </div>


                        <div class="senoobar-service">

                            <span class="senoobar-service-icon">
                                🛡️
                            </span>

                            <div>
                                <strong>
                                    ضمانت کیفیت
                                </strong>

                                <small>
                                    تضمین کیفیت محصولات
                                </small>
                            </div>

                        </div>


                        <div class="senoobar-service">

                            <span class="senoobar-service-icon">
                                🔒
                            </span>

                            <div>
                                <strong>
                                    پرداخت امن
                                </strong>

                                <small>
                                    پرداخت امن و مطمئن
                                </small>
                            </div>

                        </div>

                    </div>

                </aside>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php

do_action( 'woocommerce_after_cart' );
