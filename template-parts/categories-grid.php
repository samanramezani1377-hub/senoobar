<?php
/**
 * Template Part: Categories Grid
 */
if (!class_exists('WooCommerce')) return;

$categories = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'number'     => 6,
    'parent'     => 0,
]);

if (empty($categories) || is_wp_error($categories)) return;
?>
<section class="categories section" id="categories">
    <div class="container">
        <div class="section-heading">
            <h2 class="section-heading__title">دسته‌بندی محصولات</h2>
            <p class="section-heading__desc">تشک و کالای خواب خود را بر اساس نیازتان انتخاب کنید</p>
        </div>

        <div class="categories-grid">
            <?php foreach ($categories as $cat):
                $thumb_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
                $img      = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'senoobar-medium') : '';
            ?>
            <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="cat-card">
                <div class="cat-card__img">
                    <?php if ($img): ?>
                        <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($cat->name); ?>" loading="lazy">
                    <?php else: ?>
                        <span class="cat-card__placeholder">📦</span>
                    <?php endif; ?>
                </div>
                <div class="cat-card__info">
                    <h3 class="cat-card__name"><?php echo esc_html($cat->name); ?></h3>
                    <span class="cat-card__count"><?php echo (int) $cat->count; ?> محصول</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
