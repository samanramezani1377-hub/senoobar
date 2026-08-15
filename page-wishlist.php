<?php
/**
 * Template Name: علاقه‌مندی‌ها (Wishlist)
 *
 * Senoobar wishlist page. Server-renders the logged-in user's wishlist;
 * the client script additionally merges a guest's localStorage list and
 * handles remove / add-to-cart / grid-list / sort locally.
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Wishlist product IDs (logged-in user server-side; guests fill via JS).
$wishlist_ids = function_exists( 'senoobar_wishlist_ids' ) ? senoobar_wishlist_ids() : [];
$is_logged_in = is_user_logged_in();
?>

<main class="snb-wishlist-page" dir="rtl">
    <div class="snb-wishlist-container">

        <!-- Breadcrumb -->
        <nav class="snb-wish-breadcrumb">
            <a href="<?php echo esc_url( home_url('/') ); ?>">خانه</a>
            <span>/</span>
            <span>علاقه‌مندی‌ها</span>
        </nav>

        <!-- Header -->
        <header class="snb-wish-header">
            <div>
                <h1>لیست علاقه‌مندی‌ها</h1>
                <p id="snbWishCount"><?php echo esc_html( count( $wishlist_ids ) ); ?> محصول ذخیره شده</p>
            </div>
            <div class="snb-wish-tools">
                <select id="snbWishSort" class="snb-wish-select" aria-label="مرتب‌سازی">
                    <option value="default">پیش‌فرض</option>
                    <option value="price-asc">ارزان‌ترین</option>
                    <option value="price-desc">گران‌ترین</option>
                </select>
                <div class="snb-wish-view">
                    <button type="button" class="snb-wish-view-btn is-active" data-view="grid" aria-label="نمایش جدولی">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h5v5H3V3zm0 7h5v5H3v-5zm0 7h5v5H3v-5zm7-14h5v5h-5V3zm0 7h5v5h-5v-5zm0 7h5v5h-5v-5zm7-14h5v5h-5V3zm0 7h5v5h-5v-5zm0 7h5v5h-5v-5z"/></svg>
                    </button>
                    <button type="button" class="snb-wish-view-btn" data-view="list" aria-label="نمایش لیستی">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 5h18v2H3V5zm0 6h18v2H3v-2zm0 6h18v2H3v-2z"/></svg>
                    </button>
                </div>
            </div>
        </header>

        <!-- Wishlist grid (populated by JS: server IDs for logged-in users,
             localStorage for guests) -->
        <div class="snb-wish-grid" id="snbWishGrid" data-view="grid"></div>

        <script>
            // Expose the server-side wishlist IDs so the client script can
            // merge the logged-in user's list (guests use localStorage only).
            window.SENOOBAR_WISHLIST = {
                loggedIn: <?php echo $is_logged_in ? 'true' : 'false'; ?>,
                ids: <?php echo wp_json_encode( array_values( $wishlist_ids ) ); ?>
            };
        </script>

        <!-- Empty state (hidden by JS when there are items) -->
        <div class="snb-wish-empty" id="snbWishEmpty">
            <div class="snb-wish-empty-icon">❤️</div>
            <h3>لیست علاقه‌مندی‌های شما خالی است</h3>
            <p>محصولات مورد علاقه خود را با کلیک روی آیکون قلب ذخیره کنید.</p>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="snb-wish-empty-btn">مشاهده محصولات</a>
        </div>

        <!-- CTA -->
        <div class="snb-wish-cta" id="snbWishCta">
            <div>
                <p class="snb-wish-cta-title">می‌خواهید ادامه دهید؟</p>
                <p class="snb-wish-cta-sub">محصولات بیشتری در فروشگاه ما موجود است.</p>
            </div>
            <div class="snb-wish-cta-actions">
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="snb-wish-cta-outline">ادامه خرید</a>
                <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="snb-wish-cta-primary">رفتن به سبد خرید</a>
            </div>
        </div>

    </div>
</main>

<?php get_footer();
