<?php
$tms = [];
for ($i = 1; $i <= 3; $i++) {
    $tms[] = [
        'name'  => get_theme_mod("senoobar_tm{$i}_author", ['سارا احمدی', 'عرفان محمدی', 'مینا کریمی'][$i - 1]),
        'city'  => ['شیراز', 'اصفهان', 'تهران'][$i - 1],
        'text'  => get_theme_mod("senoobar_tm{$i}_text", [
            'مبلمان صنوبر سبک و باکیفیت هستند. کاملاً راضی‌ام و همیشه خرید می‌کنم.',
            'ارسال سریع و بسته‌بندی عالی! کیفیت محصولات فوق‌العاده است. خرید از صنوبر را توصیه می‌کنم.',
            'کیفیت محصولات صنوبر عالی است. هم از نظر طراحی و هم از نظر دوام بی‌نظیر می‌باشد.'
        ][$i - 1]),
        'stars' => (int) get_theme_mod("senoobar_tm{$i}_stars", [5, 5, 4][$i - 1]),
    ];
}
?>
<section class="testimonials-section">
    <div class="container">
        <h2 class="section__title mb-8" style="text-align:right;">نظرات مشتریان</h2>
        <div class="testimonials-grid">
            <?php foreach ($tms as $tm): ?>
                <div class="testimonial-card">
                    <svg class="testimonial-card__quote" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H14.017zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10H0z"/>
                    </svg>
                    <div class="testimonial-card__stars">
                        <?php for ($s = 0; $s < $tm['stars']; $s++): ?>
                            <span>★</span>
                        <?php endfor; ?>
                    </div>
                    <p class="testimonial-card__text"><?php echo esc_html($tm['text']); ?></p>
                    <div class="testimonial-card__author">
                        <div class="testimonial-card__avatar" aria-hidden="true">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="testimonial-card__name"><?php echo esc_html($tm['name']); ?></p>
                            <p class="testimonial-card__city"><?php echo esc_html($tm['city']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="slider-dots">
            <?php for ($i = 0; $i < 3; $i++): ?>
                <button class="slider-dot<?php echo $i === 0 ? ' active' : ''; ?>" aria-label="نمایش نظر <?php echo $i + 1; ?>"></button>
            <?php endfor; ?>
        </div>
    </div>
</section>