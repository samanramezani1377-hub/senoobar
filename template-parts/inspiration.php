<?php
/**
 * Inspiration Gallery — 4 images (senoobar2 style)
 */
$title = get_theme_mod('senoobar_section_gallery_title', 'ایده‌هایی برای خانه شما');

$ideas = [
    ['label' => 'اتاق نشیمن مدرن',     'img' => SENOOBAR_URI . '/assets/images/hero-1.jpg'],
    ['label' => 'اتاق خواب آرامش‌بخش',  'img' => SENOOBAR_URI . '/assets/images/hero-2.jpg'],
    ['label' => 'ناهارخوری شیک',       'img' => SENOOBAR_URI . '/assets/images/featured-dining.jpg'],
    ['label' => 'پذیرایی مینیمال',     'img' => SENOOBAR_URI . '/assets/images/inspiration-living.jpg'],
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
                <?php echo senoobar_img($idea['img'], ["alt"=>$idea['label'], "loading"=>"lazy"]); ?>
                <div class="gallery-item__overlay"></div>
                <div class="gallery-item__label"><?php echo esc_html($idea['label']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
