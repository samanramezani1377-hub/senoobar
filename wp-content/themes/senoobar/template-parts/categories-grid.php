<?php
/**
 * Template Part: Categories Grid
 * دسته‌بندی محصولات در صفحه اصلی
 */

$categories = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'number'     => 6,
    'parent'     => 0,
]);

if (empty($categories) || is_wp_error($categories)) return;
?>

<section class="categories-section section" id="categories">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title">دسته‌بندی محصولات</h2>
            <p class="section__desc">تشک و کالای خواب خود را بر اساس نیازتان انتخاب کنید</p>
        </div>
        
        <div class="categories-grid">
            <?php foreach ($categories as $cat) :
                $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
                $image = wp_get_attachment_url($thumbnail_id);
            ?>
            <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="category-card">
                <div class="category-card__image">
                    <?php if ($image) : ?>
                        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($cat->name); ?>" loading="lazy" width="300" height="300">
                    <?php else : ?>
                        <div class="category-card__placeholder">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.3">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="category-card__info">
                    <h3 class="category-card__title"><?php echo esc_html($cat->name); ?></h3>
                    <span class="category-card__count"><?php echo esc_html($cat->count); ?> محصول</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.categories-section {
    background: var(--color-off-white);
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

@media (min-width: 640px) {
    .categories-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; }
}

@media (min-width: 992px) {
    .categories-grid { grid-template-columns: repeat(3, 1fr); gap: 20px; }
}

.category-card {
    display: block;
    background: var(--color-white);
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
}

.category-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.category-card__image {
    aspect-ratio: 1;
    overflow: hidden;
}

.category-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}

.category-card:hover .category-card__image img {
    transform: scale(1.05);
}

.category-card__placeholder {
    width: 100%;
    height: 100%;
    background: var(--color-gray-100);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-gray-600);
}

.category-card__info {
    padding: 12px 16px;
    text-align: center;
}

.category-card__title {
    font-size: 0.95rem;
    font-weight: 700;
    margin-bottom: 4px;
}

.category-card__count {
    font-size: 0.75rem;
    color: var(--color-text-light);
}
</style>