<?php
/**
 * Senoobar — Account Dashboard.
 */

defined( 'ABSPATH' ) || exit;

$orders           = $args['orders'] ?? [];
$order_count      = $args['order_count'] ?? 0;
$delivered_count  = $args['delivered_count'] ?? 0;
$processing_count = $args['processing_count'] ?? 0;
$total_spent      = $args['total_spent'] ?? 0;
$full_name        = $args['full_name'] ?? '';

$wishlist_items = 0;
$wishlist = get_user_meta( get_current_user_id(), 'senoobar_wishlist', true );
if ( is_array( $wishlist ) ) { $wishlist_items = count( $wishlist ); }

// Wishlist page URL (auto-created via wishlist-page-setup.php).
$wishlist_url = '';
if ( function_exists( 'senoobar_wishlist_page_url' ) ) {
    $wishlist_url = senoobar_wishlist_page_url();
}
if ( empty( $wishlist_url ) ) {
    $wishlist_url = home_url( '/wishlist/' );
}
?>

<div class="snb-dashboard">
    <header class="snb-dash-header">
        <h2>داشبورد</h2>
        <p>سلام <?php echo esc_html( $full_name ); ?>! به حساب کاربری خود خوش آمدید.</p>
    </header>

    <div class="snb-stats-grid">
        <div class="snb-stat"><div class="snb-stat-icon" style="background:#dbeafe">📦</div><strong style="color:#2563eb"><?php echo esc_html( $order_count ); ?></strong><span>کل سفارش‌ها</span></div>
        <div class="snb-stat"><div class="snb-stat-icon" style="background:#dcfce7">✅</div><strong style="color:#16a34a"><?php echo esc_html( $delivered_count ); ?></strong><span>تحویل شده</span></div>
        <div class="snb-stat"><div class="snb-stat-icon" style="background:#fef3c7">🚚</div><strong style="color:#d97706"><?php echo esc_html( $processing_count ); ?></strong><span>در پردازش</span></div>
        <a href="<?php echo esc_url( $wishlist_url ); ?>" class="snb-stat snb-stat--link"><div class="snb-stat-icon" style="background:#fee2e2">❤️</div><strong style="color:#dc2626"><?php echo esc_html( $wishlist_items ); ?></strong><span>علاقه‌مندی‌ها</span></a>
    </div>

    <div class="snb-total-spent">
        <div><p>کل خرید شما از صنوبر</p><strong><?php echo number_format_i18n( $total_spent ); ?> <span>تومان</span></strong></div>
        <div class="snb-total-icon">🛒</div>
    </div>

    <div class="snb-card">
        <div class="snb-card-head">
            <h3>آخرین سفارش‌ها</h3>
            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">مشاهده همه</a>
        </div>
        <?php if ( empty( $orders ) ) : ?>
            <div class="snb-empty">هنوز سفارشی ثبت نکرده‌اید. <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">خرید را شروع کنید</a></div>
        <?php else : ?>
            <div class="snb-recent-orders">
                <?php foreach ( array_slice( $orders, 0, 3 ) as $order ) :
                    $status = $order->get_status();
                    $status_label = wc_get_order_status_name( $status );
                ?>
                    <div class="snb-recent-row">
                        <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="snb-recent-main">
                            <span class="snb-recent-id">#<?php echo esc_html( $order->get_id() ); ?></span>
                            <span class="snb-recent-meta"><?php echo esc_html( $order->get_item_count() ); ?> محصول</span>
                            <span class="snb-recent-total"><?php echo number_format_i18n( $order->get_total() ); ?> تومان</span>
                        </a>
                        <span class="snb-status snb-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status_label ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
