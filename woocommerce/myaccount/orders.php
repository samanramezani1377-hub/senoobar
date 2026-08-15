<?php
/**
 * Senoobar — Orders list.
 * Args: orders.
 */

defined( 'ABSPATH' ) || exit;

$orders = $args['orders'] ?? [];
?>

<div class="snb-orders">
    <header class="snb-dash-header">
        <h2>سفارش‌های من</h2>
        <p>تاریخچه تمام سفارش‌های ثبت‌شده شما.</p>
    </header>

    <?php if ( empty( $orders ) ) : ?>
        <div class="snb-empty">هنوز سفارشی ندارید. <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">شروع خرید</a></div>
    <?php else : ?>
        <div class="snb-orders-list">
            <?php foreach ( $orders as $order ) :
                $status = $order->get_status();
                $status_label = wc_get_order_status_name( $status );
                $date = $order->get_date_created();
                $date_str = $date ? wc_format_datetime( $date ) : '';
            ?>
                <div class="snb-order-card">
                    <div class="snb-order-top">
                        <div>
                            <span class="snb-order-id">#<?php echo esc_html( $order->get_id() ); ?></span>
                            <span class="snb-order-date"><?php echo esc_html( $date_str ); ?></span>
                        </div>
                        <span class="snb-status snb-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status_label ); ?></span>
                    </div>
                    <div class="snb-order-body">
                        <div class="snb-order-info">
                            <span><?php echo esc_html( $order->get_item_count() ); ?> محصول</span>
                            <span class="snb-order-total"><?php echo number_format_i18n( $order->get_total() ); ?> تومان</span>
                        </div>
                        <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="snb-btn snb-btn-primary snb-btn-sm">جزئیات سفارش</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
