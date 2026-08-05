    <footer id="colophon" class="site-footer">
        <div class="container footer-grid">
            <div class="footer-col">
                <h4 class="footer-col__title">فروشگاه صنوبر</h4>
                <p class="footer-col__desc">عرضه‌کننده تخصصی انواع تشک طبی، فنری و کالای خواب. کیفیت بالا، قیمت رقابتی، ارسال رایگان به سراسر ایران.</p>
                <ul class="footer-contact">
                    <li>📞 ۰۹۱۳۰۲۰۵۸۹۸</li><li>📞 ۰۹۰۱۰۸۹۱۸۶۱</li>
                    <li>📍 اصفهان، میدان لاله</li><li>🕐 ۱۰ صبح تا ۹ شب</li>
                </ul>
            </div>
            <div class="footer-col">
                <h4 class="footer-col__title">دسترسی سریع</h4>
                <?php wp_nav_menu(['theme_location'=>'footer','menu_class'=>'footer-menu','container'=>false,'fallback_cb'=>'__return_false']); ?>
            </div>
            <div class="footer-col">
                <h4 class="footer-col__title">نماد اعتماد</h4>
                <div class="footer-trust">
                    <a referrerpolicy="origin" target="_blank" href="https://trustseal.enamad.ir/?id=XXXXX">
                        <img src="<?php echo SENOOBAR_URI; ?>/assets/images/enamad.svg" alt="اینماد" width="80" height="80" loading="lazy">
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom"><div class="container">© <?php echo date('Y'); ?> صنوبر — تمامی حقوق محفوظ است.</div></div>
    </footer>

    <button class="totop-btn" id="js-totop" aria-label="بازگشت به بالا">↑</button>
    <?php wp_footer(); ?>
</body>
</html>
