<?php
/**
 * Senoobar - Thank You / Order Received Template
 * Styled order confirmation page
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $order ) || ! is_object( $order ) ) {
    // Fallback when loaded via the WooCommerce thankyou hook: build from order id.
    $order_id = absint( isset( $order_id ) ? $order_id : get_query_var( 'order-received' ) );
    $order = wc_get_order( $order_id );
}

if ( ! $order ) {
    return;
}

$order_items = $order->get_items();
$order_status = $order->get_status();
?>

<div class="senoobar-thankyou-page" dir="rtl">

    <div class="senoobar-checkout-container">

        <!-- Success Animation -->
        <div class="senoobar-thankyou-success">
            <div class="senoobar-success-icon">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9 12l2 2 4-4"/>
                </svg>
            </div>
            <h1>سفارش شما با موفقیت ثبت شد 🎉</h1>
            <p>از خرید شما سپاسگزاریم. سفارش شما دریافت و در حال بررسی است.</p>
            <div class="senoobar-order-number-chip">
                شماره سفارش: <strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong>
            </div>
        </div>

        <!-- Order Details -->
        <div class="senoobar-thankyou-grid">

            <section class="senoobar-form-card">
                <h2 class="senoobar-section-title">اطلاعات سفارش</h2>

                <div class="senoobar-thankyou-details">
                    <div class="senoobar-detail-row">
                        <span class="senoobar-detail-label">شماره سفارش</span>
                        <span class="senoobar-detail-value">#<?php echo esc_html( $order->get_order_number() ); ?></span>
                    </div>
                    <div class="senoobar-detail-row">
                        <span class="senoobar-detail-label">تاریخ سفارش</span>
                        <span class="senoobar-detail-value"><?php echo esc_html( $order->get_date_created()->date_i18n( 'j F Y' ) ); ?></span>
                    </div>
                    <div class="senoobar-detail-row">
                        <span class="senoobar-detail-label">مبلغ کل</span>
                        <span class="senoobar-detail-value"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
                    </div>
                    <div class="senoobar-detail-row">
                        <span class="senoobar-detail-label">روش پرداخت</span>
                        <span class="senoobar-detail-value"><?php echo esc_html( $order->get_payment_method_title() ); ?></span>
                    </div>
                    <div class="senoobar-detail-row">
                        <span class="senoobar-detail-label">وضعیت</span>
                        <span class="senoobar-detail-value">
                            <span class="senoobar-status-badge senoobar-status-<?php echo esc_attr( $order_status ); ?>">
                                <?php echo esc_html( wc_get_order_status_name( $order_status ) ); ?>
                            </span>
                        </span>
                    </div>
                    <div class="senoobar-detail-row">
                        <span class="senoobar-detail-label">شماره موبایل</span>
                        <span class="senoobar-detail-value" dir="ltr"><?php echo esc_html( $order->get_billing_phone() ); ?></span>
                    </div>
                </div>

                <?php if ( $order->get_customer_note() ) : ?>
                    <div class="senoobar-customer-note">
                        <span class="senoobar-detail-label">یادداشت شما</span>
                        <p><?php echo esc_html( $order->get_customer_note() ); ?></p>
                    </div>
                <?php endif; ?>
            </section>

            <section class="senoobar-form-card">
                <h2 class="senoobar-section-title">اطلاعات گیرنده سفارش</h2>

                <address class="senoobar-address">
                    <?php
                    $address_parts = array_filter([
                        $order->get_formatted_billing_full_name(),
                        $order->get_billing_address_1(),
                        $order->get_billing_state(),
                        $order->get_billing_postcode(),
                    ]);
                    echo implode('<br>', array_map('esc_html', $address_parts));

                    if ( $order->get_billing_phone() ) {
                        echo '<div class="senoobar-recipient-phone">📞 <span dir="ltr">' . esc_html( $order->get_billing_phone() ) . '</span></div>';
                    }
                    ?>
                </address>
            </section>

        </div>

        <!-- Order Items -->
        <section class="senoobar-form-card">
            <h2 class="senoobar-section-title">محصولات سفارش</h2>

            <table class="senoobar-review-table">
                <thead>
                    <tr>
                        <th class="product-name">محصول</th>
                        <th class="product-quantity">تعداد</th>
                        <th class="product-total">مجموع</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $order_items as $item_id => $item ) : ?>
                        <?php
                        $product = $item->get_product();
                        $thumbnail = $product ? $product->get_image( array( 48, 48 ) ) : '';
                        $name = $item->get_name();
                        $quantity = $item->get_quantity();
                        $total = $order->get_formatted_line_subtotal( $item );
                        $permalink = $product && $product->is_visible() ? $product->get_permalink() : '';
                        ?>
                        <tr>
                            <td class="product-name">
                                <div class="senoobar-review-product">
                                    <div class="senoobar-review-thumb">
                                        <?php if ( $permalink ) : ?>
                                            <a href="<?php echo esc_url( $permalink ); ?>"><?php echo $thumbnail; ?></a>
                                        <?php else : ?>
                                            <?php echo $thumbnail; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="senoobar-review-info">
                                        <?php if ( $permalink ) : ?>
                                            <a href="<?php echo esc_url( $permalink ); ?>" class="senoobar-review-name"><?php echo esc_html( $name ); ?></a>
                                        <?php else : ?>
                                            <span class="senoobar-review-name"><?php echo esc_html( $name ); ?></span>
                                        <?php endif; ?>
                                        <?php
                                        // Use WC's own formatted meta (auto-skips hidden/internal
                                        // keys) — safe across WooCommerce versions.
                                        $formatted_meta = $item->get_formatted_meta_data( '_' );
                                        if ( ! empty( $formatted_meta ) ) {
                                            foreach ( $formatted_meta as $meta_id => $meta ) {
                                                echo '<div class="senoobar-review-meta">' . esc_html( $meta->display_key ) . ': ' . wp_kses_post( $meta->display_value ) . '</div>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </td>
                            <td class="product-quantity">
                                <span class="senoobar-review-qty"><?php echo esc_html( $quantity ); ?> عدد</span>
                            </td>
                            <td class="product-total"><?php echo wp_kses_post( $total ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="senoobar-order-totals">
                <?php
                $totals = $order->get_order_item_totals();
                foreach ( $totals as $total_key => $total ) {
                    if ( $total_key === 'order_total' ) {
                        ?>
                        <div class="senoobar-summary-total">
                            <span><?php echo esc_html( $total['label'] ); ?></span>
                            <strong><?php echo wp_kses_post( $total['value'] ); ?></strong>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="senoobar-summary-row<?php echo $total_key === 'discount' ? ' discount' : ''; ?>">
                            <span><?php echo esc_html( $total['label'] ); ?></span>
                            <strong><?php echo wp_kses_post( $total['value'] ); ?></strong>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </section>

        <!-- Next Steps -->
        <section class="senoobar-form-card">
            <h2 class="senoobar-section-title">مراحل بعدی</h2>
            <div class="senoobar-next-steps">
                <div class="senoobar-step">
                    <span class="senoobar-step-number">1</span>
                    <div>
                        <strong>تایید سفارش</strong>
                        <p>تیم ما سفارش شما را بررسی و تایید می‌کند</p>
                    </div>
                </div>
                <div class="senoobar-step">
                    <span class="senoobar-step-number">2</span>
                    <div>
                        <strong>آماده‌سازی و بسته‌بندی</strong>
                        <p>محصولات با مراقبت بسته‌بندی و برای ارسال تحویل می‌شوند</p>
                    </div>
                </div>
                <div class="senoobar-step">
                    <span class="senoobar-step-number">3</span>
                    <div>
                        <strong>ارسال و تحویل</strong>
                        <p>کد رهگیری به پیامک/ایمیل شما ارسال می‌شود</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Actions -->
        <div class="senoobar-thankyou-actions">
            <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="senoobar-btn senoobar-btn-primary">
                مشاهده جزئیات سفارش
            </a>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="senoobar-btn senoobar-btn-outline">
                ادامه خرید
            </a>
        </div>

    </div>

</div>

<style>
.senoobar-thankyou-page {
    --senoobar-green: #1e3a2f;
    --senoobar-green-dark: #152a21;
    --senoobar-green-light: #f0f7f4;
    --checkout-text: #171a18;
    --checkout-muted: #777d79;
    --checkout-border: #e9ecea;
    --checkout-bg: #f7f8f7;
    background: var(--checkout-bg);
    min-height: 70vh;
    padding: 35px 0 70px;
    font-family: Vazirmatn, IRANSans, Tahoma, sans-serif;
    color: var(--checkout-text);
}

.senoobar-checkout-container { width: min(1180px, calc(100% - 32px)); margin: 0 auto; }

/* Success Animation */
.senoobar-thankyou-success {
    text-align: center;
    padding: 40px 20px;
    margin-bottom: 24px;
}
.senoobar-success-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 20px;
    background: var(--senoobar-green-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--senoobar-green);
    position: relative;
    animation: popIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.senoobar-success-icon::before,
.senoobar-success-icon::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 2px solid var(--senoobar-green);
    opacity: 0;
    animation: ripple 2s ease-out infinite;
}
.senoobar-success-icon::after { animation-delay: 1s; }
@keyframes ripple {
    0% { transform: scale(1); opacity: 0.6; }
    100% { transform: scale(1.8); opacity: 0; }
}
.senoobar-order-number-chip {
    display: inline-block;
    margin-top: 16px;
    padding: 10px 22px;
    background: #fff;
    border: 1px dashed var(--senoobar-green);
    border-radius: 12px;
    color: var(--checkout-muted);
    font-size: 14px;
}
.senoobar-order-number-chip strong { color: var(--senoobar-green); font-size: 16px; }

@keyframes popIn {
    from { transform: scale(0); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.senoobar-thankyou-success h1 { margin: 0 0 12px; font-size: 28px; font-weight: 800; color: #171a18; }
.senoobar-thankyou-success p { margin: 0; color: var(--checkout-muted); font-size: 15px; }

/* Grid */
.senoobar-thankyou-grid {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 22px;
    margin-bottom: 22px;
}
@media (max-width: 1000px) { .senoobar-thankyou-grid { grid-template-columns: 1fr; } }

/* Details */
.senoobar-thankyou-details { display: flex; flex-direction: column; gap: 14px; }
.senoobar-detail-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f0f1f0; }
.senoobar-detail-label { font-size: 14px; color: var(--checkout-muted); }
.senoobar-detail-value { font-size: 14px; font-weight: 600; color: #171a18; text-align: left; }
.senoobar-detail-row:last-child { border-bottom: none; }

.senoobar-status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}
.senoobar-status-processing { background: #fff3e0; color: #e65100; }
.senoobar-status-completed { background: #e8f5e9; color: #2e7d32; }
.senoobar-status-on-hold { background: #f3e5f5; color: #7b1fa2; }
.senoobar-status-pending { background: #fff3e0; color: #e65100; }
.senoobar-status-cancelled { background: #fce4ec; color: #c62828; }
.senoobar-status-failed { background: #fce4ec; color: #c62828; }
.senoobar-status-refunded { background: #e3f2fd; color: #1565c0; }

.senoobar-customer-note { margin-top: 20px; padding-top: 16px; border-top: 1px solid #e9ecea; }
.senoobar-customer-note .senoobar-detail-label { display: block; margin-bottom: 8px; }
.senoobar-customer-note p { margin: 0; padding: 12px; background: #fafbfa; border-radius: 8px; font-size: 13px; }

/* Address */
.senoobar-address { font-style: normal; line-height: 2; color: var(--checkout-muted); font-size: 13px; }
.senoobar-recipient-phone {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px dashed var(--checkout-border);
    font-weight: 600;
    color: var(--checkout-text);
}

/* Table */
.senoobar-review-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
.senoobar-review-table th { text-align: right; padding: 12px 16px; font-size: 13px; font-weight: 700; color: var(--checkout-muted); background: #fafbfa; border-bottom: 1px solid var(--checkout-border); }
.senoobar-review-table td { padding: 16px; border-bottom: 1px solid #f0f1f0; vertical-align: middle; }
.senoobar-review-table tr:last-child td { border-bottom: none; }
.senoobar-review-product { display: flex; align-items: center; gap: 12px; }
.senoobar-review-thumb { width: 56px; height: 56px; flex: 0 0 56px; border-radius: 10px; overflow: hidden; background: #f4f5f4; }
.senoobar-review-thumb img { width: 100%; height: 100%; object-fit: cover; }
.senoobar-review-info { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.senoobar-review-name { font-weight: 600; font-size: 13px; color: #171a18; text-decoration: none; line-height: 1.5; }
.senoobar-review-name:hover { color: var(--senoobar-green); }
.senoobar-review-meta { font-size: 11px; color: #8a908c; }
.senoobar-review-qty { display: inline-block; padding: 2px 8px; background: var(--senoobar-green-light); color: var(--senoobar-green); font-size: 11px; font-weight: 600; border-radius: 6px; }
.senoobar-review-table .product-quantity { text-align: center; width: 80px; }
.senoobar-review-table .product-total { text-align: left; font-weight: 600; }

.senoobar-order-totals { border-top: 1px solid var(--checkout-border); padding-top: 16px; }
.senoobar-summary-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f1f0; font-size: 13px; color: #666c68; }
.senoobar-summary-row strong { color: #333835; }
.senoobar-summary-row.discount strong { color: #2e805d; }
.senoobar-summary-total { display: flex; justify-content: space-between; padding: 16px 0 18px; font-size: 14px; font-weight: 700; }
.senoobar-summary-total strong { color: var(--senoobar-green); font-size: 21px; font-weight: 800; }

/* Next Steps */
.senoobar-next-steps { display: flex; flex-direction: column; gap: 16px; }
.senoobar-step { display: flex; align-items: flex-start; gap: 16px; }
.senoobar-step-number {
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    background: var(--senoobar-green);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 14px;
}
.senoobar-step strong { display: block; margin-bottom: 4px; color: #171a18; }
.senoobar-step p { margin: 0; font-size: 13px; color: var(--checkout-muted); line-height: 1.6; }

/* Actions */
.senoobar-thankyou-actions { display: flex; gap: 12px; justify-content: center; margin-top: 24px; flex-wrap: wrap; }
.senoobar-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 14px 28px; border-radius: 12px; font-family: inherit; font-size: 14px; font-weight: 700; text-decoration: none; transition: all .2s ease; }
.senoobar-btn-primary { background: var(--senoobar-green); color: #fff; border: none; }
.senoobar-btn-primary:hover { background: var(--senoobar-green-dark); }
.senoobar-btn-outline { border: 1px solid var(--senoobar-green); color: var(--senoobar-green); background: transparent; }
.senoobar-btn-outline:hover { background: var(--senoobar-green-light); }

/* Form Card */
.senoobar-form-card { background: #fff; border: 1px solid var(--checkout-border); border-radius: 20px; box-shadow: 0 5px 20px rgba(22, 35, 28, .035); padding: 24px; }
.senoobar-section-title { display: flex; align-items: center; gap: 10px; margin: 0 0 20px; font-size: 18px; font-weight: 800; color: #171a18; }
.senoobar-section-title::before { content: ''; width: 4px; height: 22px; background: var(--senoobar-green); border-radius: 2px; }

@media (max-width: 760px) {
    .senoobar-thankyou-page { padding: 22px 0 45px; }
    .senoobar-checkout-container { width: min(100% - 22px, 650px); }
    .senoobar-form-card { padding: 20px; }
    .senoobar-thankyou-success h1 { font-size: 22px; }
    .senoobar-thankyou-actions { flex-direction: column; }
    .senoobar-btn { width: 100%; }
}
</style>