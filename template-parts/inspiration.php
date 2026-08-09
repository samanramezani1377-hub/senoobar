<?php
/**
 * Inspiration Gallery — 4 images (senoobar2 style)
 */
$title = get_theme_mod('senoobar_section_gallery_title', 'ایده‌هایی برای خانه شما');

$ideas = [
    ['label' => 'اتاق نشیمن مدرن',     'img' => 'https://images.unsplash.com/photo-1554995207-c18c203602cb?w=400&h=300&fit=crop&auto=format'],
    ['label' => 'اتاق خواب آرامش‌بخش',  'img' => 'https://images.unsplash.com/photo-1696762932825-2737db830bbe?w=400&h=300&fit=crop&auto=format'],
    ['label' => 'ناهارخوری شیک',       'img' => 'https://images.unsplash.com/photo-1656403002413-2ac6137237d6?w=400&h=300&fit=crop&auto=format'],
    ['label' => 'پذیرایی مینیمال',     'img' => 'https://images.unsplash.com/photo-1628744876525-f2678d8af47f?w=400&h=300&fit=crop&auto=format'],
];

// Try gallery images from customizer
$gallery = [];
for ($i = 1; $i <= 4; $i++) {
    $img_id = get_theme_mod("senoobar_gallery_img{$i}");
    if ($img_id) {
        $gallery[] = $img_id;
    }
}
?>

<section class="section">
    <div class="container">
        <div class="flex-between mb-6">
            <h2 class="section__title" style="margin-bottom:0;"><?php echo esc_html($title); ?></h2>
            <a href="#" class="section-link">گالری ایده‌ها</a>
        </div>

        <?php if (!empty($gallery)): ?>
        <div class="gallery-grid">
            <?php foreach ($gallery as $img_id): ?>
            <div class="gallery-item">
                <?php echo wp_get_attachment_image($img_id, 'medium', false, ['loading' => 'lazy']); ?>
                <div class="gallery-item__overlay"></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="gallery-grid">
            <?php foreach ($ideas as $idea): ?>
            <div class="gallery-item">
                <img src="<?php echo esc_url($idea['img']); ?>" alt="<?php echo esc_attr($idea['label']); ?>" loading="lazy">
                <div class="gallery-item__overlay"></div>
                <div class="gallery-item__label"><?php echo esc_html($idea['label']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
