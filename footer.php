    <footer id="colophon" class="site-footer">
        <div class="container footer-grid">
            <div class="footer-col">
                <h4>درباره صنوبر</h4>
                <p>عرضه‌کننده تخصصی انواع تشک طبی، فنری، سرویس خواب و کالای خواب با بهترین کیفیت و قیمت. هدف ما آرامش و راحتی شماست.</p>
                <div class="footer-contact">
                    <p>📞 ۰۹۱۳۰۲۰۵۸۹۸</p>
                    <p>📞 ۰۹۰۱۰۸۹۱۸۶۱</p>
                    <p>📍 اصفهان، میدان لاله</p>
                    <p>🕐 ۱۰ صبح تا ۹ شب</p>
                </div>
            </div>
            <div class="footer-col">
                <h4>خدمات مشتریان</h4>
                <ul>
                    <li><a href="#">سوالات متداول</a></li>
                    <li><a href="#">شرایط و قوانین</a></li>
                    <li><a href="#">حریم خصوصی</a></li>
                    <li><a href="#">بازگشت کالا</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>دسترسی سریع</h4>
                <?php wp_nav_menu(['theme_location'=>'footer','menu_class'=>'','container'=>false,'fallback_cb'=>'__return_false']); ?>
            </div>
            <div class="footer-col">
                <h4>نمادهای اعتماد</h4>
                <div class="trust-badges">
                    <a referrerpolicy="origin" target="_blank" href="https://trustseal.enamad.ir/?id=XXXXX">
                        <img src="<?php echo SENOOBAR_URI; ?>/assets/images/enamad.svg" alt="اینماد" width="80" height="80" loading="lazy">
                    </a>
                    <a href="#" class="samandehi" title="ساماندهی">
                        <img src="<?php echo SENOOBAR_URI; ?>/assets/images/samandehi.svg" alt="ساماندهی" width="80" height="80" loading="lazy">
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>© <?php echo date('Y'); ?> صنوبر — تمامی حقوق محفوظ است.</p>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top -->
    <button id="backToTop" class="back-to-top" aria-label="بازگشت به بالا">↑</button>
    
    <?php wp_footer(); ?>
</body>
</html>