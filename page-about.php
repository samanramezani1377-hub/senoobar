<?php
/**
 * Template Name: درباره ما
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="legal-page" dir="rtl">
    <div class="container">

        <nav class="legal-page__crumb" aria-label="مسیر">
            <a href="<?php echo esc_url( home_url('/') ); ?>">خانه</a>
            <span>/</span>
            <span>درباره ما</span>
        </nav>

        <header class="legal-page__head">
            <h1 class="section__title">درباره فروشگاه صنوبر</h1>
            <p class="section__desc">خانه‌ای درخور شما، با کیفیت و تنوعی که شایسته آرامش‌تان است.</p>
        </header>

        <div class="legal-page__body">
            <p>
                فروشگاه <strong>صنوبر</strong> با سال‌ها تجربه در صنعت مبل و وسایل خانه، یکی از فروشگاه‌های تخصصی
                در زمینه <strong>سرویس خواب، تشک، تخت خواب و مبلمان</strong> در اصفهان است. هدف ما از آغاز، فراهم کردن
                آرامش و آسایش خانواده‌های ایرانی با ارائه محصولاتی باکیفیت، بادوام و خوش‌قیمت بوده است.
            </p>

            <p>
                ما باور داریم که یک خانه زیبا با انتخاب‌های درست شکل می‌گیرد؛ به همین دلیل مجموعه‌ای متنوع از جدیدترین
                مدل‌ها و طراحی‌های روز را گرد هم آورده‌ایم تا برای هر سلیقه و بودجه‌ای، گزینه‌ای مناسب وجود داشته باشد.
            </p>

            <div class="about-grid">
                <div class="about-card">
                    <div class="about-card__icon">🛏️</div>
                    <div class="about-card__title">سرویس خواب</div>
                    <div class="about-card__desc">تنوع بالا در طرح و جنس</div>
                </div>
                <div class="about-card">
                    <div class="about-card__icon">🛋️</div>
                    <div class="about-card__title">مبل و مبلمان</div>
                    <div class="about-card__desc">راحتی و طراحی مدرن</div>
                </div>
                <div class="about-card">
                    <div class="about-card__icon">🛡️</div>
                    <div class="about-card__title">ضمانت اصالت</div>
                    <div class="about-card__desc">کیفیت تضمین‌شده کالا</div>
                </div>
                <div class="about-card">
                    <div class="about-card__icon">🚚</div>
                    <div class="about-card__title">ارسال سراسری</div>
                    <div class="about-card__desc">ارسال به سراسر کشور</div>
                </div>
            </div>

            <h2>چرا صنوبر؟</h2>
            <ul>
                <li><strong>کیفیت واقعی:</strong> محصولاتی که سال‌ها برایتان کار می‌کنند، نه فقط در ظاهر زیبا.</li>
                <li><strong>قیمت منصفانه:</strong> حذف واسطه‌ها و ارائه مستقیم به مشتری.</li>
                <li><strong>مشاوره صادقانه:</strong> راهنمایی دقیق برای انتخابی متناسب با نیاز و بودجه شما.</li>
                <li><strong>پشتیبانی واقعی:</strong> همراهی از لحظه خرید تا پس از تحویل.</li>
            </ul>

            <h2>ارزش‌های ما</h2>
            <p>
                صداقت، کیفیت و احترام به مشتری، سه ستونی هستند که فعالیت صنوبر روی آن‌ها بنا شده است. ما تلاش می‌کنیم
                تجربه خریدی ساده، مطمئن و لذت‌بخش را برای شما رقم بزنیم؛ از انتخاب محصول تا تحویل درب منزل.
            </p>

            <div class="legal-page__note">
                صنوبر یعنی ریشه‌ای مستحکم و سایه‌ای خنک؛ ما هم می‌خواهیم خانه شما به همین اندازه آرام و دوست‌داشتنی باشد.
            </div>

            <div class="legal-page__cta">
                <a href="<?php echo esc_url( function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/') ); ?>" class="btn btn--primary">مشاهده محصولات</a>
            </div>
        </div>

    </div>
</main>

<?php get_footer();
