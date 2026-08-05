<?php
/**
 * Template Part: Featured Products
 * محصولات ویژه صفحه اصلی
 */

$args = [
    'post_type'      => 'product',
    'posts_per_page' => 12,
    'meta_key'       => '_featured',
    'meta_value'     => 'yes',
];
$featured = new WP_Query($args);

if (!$featured->have_posts()) {
    // Fallback: get latest products
    $args = ['post_type' => 'product', 'posts_per_page' => 12];
    $featured = new WP_Query($args);
}

if (!$featured->have_posts()) return;
?>

<section class="featured-products section">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title">محصولات ویژه</h2>
            <p class="section__desc">منتخبی از پرفروش‌ترین تشک‌ها و کالای خواب صنوبر با تخفیف‌های استثنایی</p>
        </div>
        
        <div class="products-grid">
            <?php while ($featured->have_posts()) : $featured->the_post();
                wc_get_template_part('content', 'product');
            endwhile; wp_reset_postdata(); ?>
        </div>
        
        <div class="section__footer">
            <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn btn--outline">
                مشاهده همه محصولات
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </div>
</section>

<style>
.section {
    padding: 48px 0;
}

.section__header {
    text-align: center;
    margin-bottom: 32px;
}

.section__title {
    font-size: 1.5rem;
    font-weight: 900;
    color: var(--color-primary-dark);
    margin-bottom: 8px;
}

@media (min-width: 768px) {
    .section__title { font-size: 2rem; }
    .section { padding: 64px 0; }
}

.section__desc {
    color: var(--color-text-light);
    font-size: 0.9rem;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

@media (min-width: 640px) {
    .products-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; }
}

@media (min-width: 992px) {
    .products-grid { grid-template-columns: repeat(4, 1fr); gap: 20px; }
}

.section__footer {
    text-align: center;
    margin-top: 32px;
}
</style>