<?php
/**
 * Template Part: CTA Banner
 * بنر دعوت به اقدام
 */
?>

<section class="cta-banner section">
    <div class="container">
        <div class="cta-banner__card">
            <div class="cta-banner__content">
                <h2 class="cta-banner__title">تضمین کیفیت و قیمت</h2>
                <p class="cta-banner__text">
                    تمامی محصولات صنوبر با گارانتی معتبر و ضمانت بازگشت کالا عرضه می‌شوند. 
                    ما بهترین قیمت را برای شما تضمین می‌کنیم.
                </p>
                <div class="cta-banner__features">
                    <div class="cta-feature">
                        <span class="cta-feature__icon">🚚</span>
                        <span>ارسال سریع و رایگان</span>
                    </div>
                    <div class="cta-feature">
                        <span class="cta-feature__icon">🔄</span>
                        <span>۷ روز ضمانت بازگشت</span>
                    </div>
                    <div class="cta-feature">
                        <span class="cta-feature__icon">💯</span>
                        <span>تضمین اصالت کالا</span>
                    </div>
                    <div class="cta-feature">
                        <span class="cta-feature__icon">🎧</span>
                        <span>پشتیبانی ۲۴ ساعته</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.cta-banner__card {
    background: linear-gradient(135deg, var(--color-primary-dark) 0%, #2d3436 100%);
    border-radius: var(--radius-lg);
    padding: 40px 24px;
    color: var(--color-white);
    text-align: center;
}

@media (min-width: 768px) {
    .cta-banner__card { padding: 56px 40px; }
}

.cta-banner__title {
    font-size: 1.5rem;
    font-weight: 900;
    margin-bottom: 12px;
}

@media (min-width: 768px) {
    .cta-banner__title { font-size: 2rem; }
}

.cta-banner__text {
    opacity: 0.85;
    max-width: 600px;
    margin: 0 auto 24px;
    line-height: 2;
}

.cta-banner__features {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    max-width: 600px;
    margin: 0 auto;
}

@media (min-width: 640px) {
    .cta-banner__features { grid-template-columns: repeat(4, 1fr); }
}

.cta-feature {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.1);
    padding: 16px 12px;
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
}

.cta-feature__icon {
    font-size: 1.5rem;
}
</style>