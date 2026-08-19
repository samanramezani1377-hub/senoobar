<?php
/**
 * Template Name: سوالات متداول
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
            <span>سوالات متداول</span>
        </nav>

        <header class="legal-page__head">
            <h1 class="section__title">سوالات متداول</h1>
            <p class="section__desc">پاسخ پرتکرارترین پرسش‌های شما درباره خرید، ارسال و خدمات صنوبر.</p>
        </header>

        <div class="legal-page__body">
            <div class="faq-list" id="senoobarFaq">

                <div class="faq-item">
                    <button class="faq-item__q" type="button" aria-expanded="false">
                        <span>چطور سفارش ثبت کنم؟</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="faq-item__a"><div class="faq-item__a-inner">محصول مورد نظر را به سبد خرید اضافه کنید، سپس از طریق صفحه «تسویه حساب» اطلاعات ارسال را وارد و سفارش خود را نهایی کنید. برای ثبت سفارش تلفنی نیز می‌توانید با پشتیبانی تماس بگیرید.</div></div>
                </div>

                <div class="faq-item">
                    <button class="faq-item__q" type="button" aria-expanded="false">
                        <span>هزینه و زمان ارسال چقدر است؟</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="faq-item__a"><div class="faq-item__a-inner">هزینه ارسال بر اساس وزن، ابعاد و مقصد سفارش محاسبه می‌شود و در هنگام تسویه حساب نمایش داده می‌شود. ارسال به سراسر کشور انجام می‌شود و زمان تقریبی تحویل بسته به مقصد معمولاً بین ۳ تا ۷ روز کاری است.</div></div>
                </div>

                <div class="faq-item">
                    <button class="faq-item__q" type="button" aria-expanded="false">
                        <span>آیا امکان خرید اقساطی وجود دارد؟</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="faq-item__a"><div class="faq-item__a-inner">بله، امکان خرید اقساطی ۳ ماهه بدون کارمزد برای بسیاری از محصولات فراهم است. برای اطلاع از شرایط و جزئیات با پشتیبانی فروش تماس بگیرید.</div></div>
                </div>

                <div class="faq-item">
                    <button class="faq-item__q" type="button" aria-expanded="false">
                        <span>شرایط بازگشت کالا چگونه است؟</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="faq-item__a"><div class="faq-item__a-inner">در صورت وجود ایراد یا مغایرت در کالا، تا ۷ روز پس از تحویل می‌توانید برای بازگشت یا تعویض اقدام کنید؛ به شرطی که کالا در وضعیت اولیه و بدون استفاده باقی مانده باشد. برای جزئیات کامل بخش «شرایط و ضوابط» را ببینید.</div></div>
                </div>

                <div class="faq-item">
                    <button class="faq-item__q" type="button" aria-expanded="false">
                        <span>چطور سفارش خود را پیگیری کنم؟</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="faq-item__a"><div class="faq-item__a-inner">پس از ثبت سفارش، کد پیگیری در اختیار شما قرار می‌گیرد. از طریق بخش «حساب کاربری» یا تماس با پشتیبانی می‌توانید وضعیت سفارش را دنبال کنید.</div></div>
                </div>

                <div class="faq-item">
                    <button class="faq-item__q" type="button" aria-expanded="false">
                        <span>آیا امکان بازدید حضوری از محصولات هست؟</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="faq-item__a"><div class="faq-item__a-inner">بله، فروشگاه حضوری صنوبر در اصفهان آماده بازدید شماست. برای آدرس دقیق و ساعات کاری به صفحه «تماس با ما» مراجعه کنید.</div></div>
                </div>

            </div>
        </div>

    </div>
</main>

<script>
(function(){
    var items = document.querySelectorAll('.faq-item');
    items.forEach(function(item){
        var btn = item.querySelector('.faq-item__q');
        var ans = item.querySelector('.faq-item__a');
        btn.addEventListener('click', function(){
            var isOpen = item.classList.contains('open');
            // close siblings
            items.forEach(function(o){
                o.classList.remove('open');
                o.querySelector('.faq-item__q').setAttribute('aria-expanded','false');
                o.querySelector('.faq-item__a').style.maxHeight = null;
            });
            if (!isOpen) {
                item.classList.add('open');
                btn.setAttribute('aria-expanded','true');
                ans.style.maxHeight = ans.scrollHeight + 'px';
            }
        });
    });
})();
</script>

<?php get_footer();
