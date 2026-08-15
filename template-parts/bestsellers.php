<?php
/**
 * Best Sellers — standard WooCommerce product cards (add-to-cart + wishlist).
 */
$title  = get_theme_mod('senoobar_section_bestsellers_title', 'پرفروش‌ترین‌ها');
$has_wc = class_exists('WooCommerce');

$best = null;
if ($has_wc) {
    $best = new WP_Query([
        'post_type'      => 'product',
        'posts_per_page' => 8,
        'meta_key'       => 'total_sales',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
    ]);
    $has_wc = $best->have_posts();
}
?>
<section class="bestsellers-section">
    <div class="container">
        <div class="flex-between mb-6">
            <div>
                <h2 class="section__title" style="margin-bottom:0;"><?php echo esc_html($title); ?></h2>
            </div>
            <?php if ($has_wc): ?>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="section-link">مشاهده همه</a>
            <?php endif; ?>
        </div>

        <?php if ($has_wc): ?>
            <div class="products-grid shop-main" data-home-loop="bestsellers">
                <ul class="products columns-4">
                <?php while ($best->have_posts()): $best->the_post(); ?>
                    <?php wc_get_template_part('content', 'product'); ?>
                <?php endwhile; ?>
                </ul>
            </div>
            <?php wp_reset_postdata(); ?>
        <?php endif; ?>
    </div>
</section>
