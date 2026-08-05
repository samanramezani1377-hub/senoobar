    <footer id="colophon" class="site-footer">
        <div class="container footer-grid">
            <div class="footer-col">
                <h4 class="footer-col__title">درباره فروشگاه صنوبر</h4>
                <p class="footer-col__desc">عرضه‌کننده تخصصی انواع تشک طبی، فنری، سرویس خواب و کالای خواب با بهترین کیفیت و قیمت. هدف ما آرامش و راحتی شماست. <span class="heart">💜</span></p>
            </div>
            <div class="footer-col">
                <h4 class="footer-col__title">دسترسی سریع</h4>
                <?php wp_nav_menu(['theme_location'=>'footer','menu_class'=>'footer-menu','container'=>false,'fallback_cb'=>'__return_false']); ?>
            </div>
            <div class="footer-col">
                <h4 class="footer-col__title">تماس با ما</h4>
                <ul class="footer-contact">
                    <li>📞 ۰۹۱۳۰۲۰۵۸۹۸</li><li>📞 ۰۹۰۱۰۸۹۱۸۶۱</li>
                    <li>📍 اصفهان، میدان لاله</li><li>🕐 ۱۰ صبح تا ۹ شب</li>
                </ul>
                <div class="footer-trust" style="margin-top:16px">
                    <a referrerpolicy="origin" target="_blank" href="https://trustseal.enamad.ir/?id=XXXXX">
                        <img src="<?php echo SENOOBAR_URI; ?>/assets/images/enamad.svg" alt="اینماد" width="70" height="70" loading="lazy">
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom"><div class="container">© <?php echo date('Y'); ?> صنوبر — تمامی حقوق محفوظ است. | طراحی با 💜</div></div>
    </footer>

    <button class="totop-btn" id="js-totop" aria-label="بازگشت به بالا">↑</button>
    <?php wp_footer(); ?>
</body>
</html>
