<?php
/**
 * Senoobar — Custom My Account page (full customer panel).
 * Loaded directly via template_include.
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();

get_header(); ?>

<main id="primary" class="site-main">
<div class="container page-content senoobar-account-wrap">

<?php if ( is_user_logged_in() ) : wc_print_notices(); endif; ?>

<?php if ( ! is_user_logged_in() ) : ?>

    <?php get_template_part( 'woocommerce/myaccount/auth' ); ?>

<?php else : ?>

    <?php
    $user_id       = $current_user->ID;
    $mobile        = get_user_meta( $user_id, 'mobile', true ) ?: get_user_meta( $user_id, 'billing_phone', true );
    $first_name    = $current_user->first_name;
    $last_name     = $current_user->last_name;
    $full_name     = trim( $first_name . ' ' . $last_name ) ?: $current_user->display_name;
    $avatar_char   = function_exists( 'mb_substr' ) ? mb_substr( $full_name, 0, 1 ) : substr( $full_name, 0, 1 );

    global $wp;
    $endpoint = WC()->query->get_current_endpoint();
    $tab = 'dashboard';
    if ( $endpoint === 'orders' )      { $tab = 'orders'; }
    elseif ( $endpoint === 'edit-address' ) { $tab = 'addresses'; }
    elseif ( $endpoint === 'edit-account' ) { $tab = 'profile'; }

    $view_order_id = absint( get_query_var( 'view-order' ) );
    if ( ! $view_order_id && isset( $wp->query_vars['view-order'] ) ) {
        $view_order_id = absint( $wp->query_vars['view-order'] );
    }

    $customer_orders = wc_get_orders( [
        'customer_id' => $user_id,
        'limit'       => 50,
        'orderby'     => 'date',
        'order'       => 'DESC',
    ] );

    $total_spent = 0;
    $order_count = count( $customer_orders );
    $delivered_count = 0;
    $processing_count = 0;
    foreach ( $customer_orders as $o ) {
        $status = $o->get_status();
        if ( $o->is_paid() || in_array( $status, [ 'completed', 'processing', 'on-hold' ], true ) ) {
            $total_spent += (float) $o->get_total();
        }
        if ( $status === 'completed' ) { $delivered_count++; }
        if ( in_array( $status, [ 'processing', 'on-hold', 'pending' ], true ) ) { $processing_count++; }
    }
    ?>

    <div class="snb-account" dir="rtl">

        <div class="snb-account-breadcrumb">
            <a href="<?php echo esc_url( home_url('/') ); ?>">خانه</a>
            <span>/</span>
            <span>حساب کاربری</span>
        </div>

        <div class="snb-account-layout">

            <aside class="snb-sidebar">
                <div class="snb-profile-card">
                    <div class="snb-avatar"><?php echo esc_html( $avatar_char ); ?></div>
                    <p class="snb-name"><?php echo esc_html( $full_name ); ?></p>
                    <p class="snb-phone"><?php echo esc_html( senoobar_phone_display( $mobile ) ); ?></p>
                    <div class="snb-profile-stats">
                        <div><strong><?php echo esc_html( $order_count ); ?></strong><span>سفارش</span></div>
                        <div class="snb-divider"></div>
                        <div><strong><?php echo esc_html( $delivered_count ); ?></strong><span>تحویل شده</span></div>
                    </div>
                </div>

                <nav class="snb-nav">
                    <?php
                    $wishlist_page = get_page_by_path( 'wishlist' );
                    $wishlist_url  = $wishlist_page ? get_permalink( $wishlist_page ) : home_url( '/wishlist/' );
                    $account_url   = wc_get_page_permalink( 'myaccount' );

                    $nav_items = [
                        'dashboard' => [ 'داشبورد', '🏠', $account_url ],
                        'orders'    => [ 'سفارش‌های من', '📦', wc_get_account_endpoint_url( 'orders' ) ],
                        'addresses' => [ 'آدرس‌های من', '📍', wc_get_account_endpoint_url( 'edit-address' ) ],
                        'profile'   => [ 'ویرایش پروفایل', '✏️', wc_get_account_endpoint_url( 'edit-account' ) ],
                        'password'  => [ 'تغییر رمز عبور', '🔐', wc_get_account_endpoint_url( 'edit-account' ) . '?tab=password' ],
                    ];
                    foreach ( $nav_items as $key => $item ) :
                        $active = ( $tab === $key );
                        if ( $key === 'password' ) {
                            $active = ( $endpoint === 'edit-account' && isset( $_GET['tab'] ) && $_GET['tab'] === 'password' );
                        }
                    ?>
                        <a href="<?php echo esc_url( $item[2] ); ?>" class="snb-nav-link<?php echo $active ? ' is-active' : ''; ?>">
                            <span class="snb-nav-icon"><?php echo $item[1]; ?></span>
                            <span><?php echo esc_html( $item[0] ); ?></span>
                        </a>
                    <?php endforeach; ?>

                    <a href="<?php echo esc_url( $wishlist_url ); ?>" class="snb-nav-link">
                        <span class="snb-nav-icon">❤️</span><span>علاقه‌مندی‌ها</span>
                    </a>
                    <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'customer-logout' ) ); ?>" class="snb-nav-link snb-logout">
                        <span class="snb-nav-icon">🚪</span><span>خروج از حساب</span>
                    </a>
                </nav>
            </aside>

            <section class="snb-main">
                <?php
                if ( $view_order_id ) {
                    $order = wc_get_order( $view_order_id );
                    if ( $order && (int) $order->get_customer_id() === (int) $user_id ) {
                        get_template_part( 'woocommerce/myaccount/view-order', null, [ 'order' => $order ] );
                    } else {
                        echo '<div class="snb-empty">سفارش پیدا نشد یا متعلق به شما نیست.</div>';
                    }
                } elseif ( $tab === 'orders' ) {
                    get_template_part( 'woocommerce/myaccount/orders', null, [ 'orders' => $customer_orders ] );
                } elseif ( $tab === 'addresses' ) {
                    get_template_part( 'woocommerce/myaccount/addresses', null, [ 'user_id' => $user_id ] );
                } elseif ( $tab === 'profile' ) {
                    get_template_part( 'woocommerce/myaccount/profile', null, [ 'user_id' => $user_id ] );
                } else {
                    get_template_part( 'woocommerce/myaccount/dashboard', null, [
                        'orders'           => $customer_orders,
                        'order_count'      => $order_count,
                        'delivered_count'  => $delivered_count,
                        'processing_count' => $processing_count,
                        'total_spent'      => $total_spent,
                        'full_name'        => $full_name,
                    ] );
                }
                ?>
            </section>

        </div>
    </div>

<?php endif; ?>

</div>
</main>

<?php get_footer();
