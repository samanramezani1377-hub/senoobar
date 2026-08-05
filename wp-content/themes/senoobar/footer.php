    <footer id="colophon" class="site-footer">
        <div class="container footer-grid">
            <div class="footer-col">
                <h4 class="footer-title">صنوبر</h4>
                <p>فروشگاه اینترنتی مبلمان، سرویس خواب و تشک با بهترین کیفیت و قیمت. از سال ۱۳۹۰ همراه شما هستیم.</p>
                <div class="footer-contact">
                    <p>📞 ۰۲۱-۱۲۳۴۵۶۷۸</p>
                    <p>📧 info@senoobar.ir</p>
                    <p>📍 تهران، بازار مبل یافت‌آباد</p>
                </div>
            </div>
            <div class="footer-col">
                <h4 class="footer-title">دسترسی سریع</h4>
                <?php
                wp_nav_menu([
                    'theme_location' => 'footer',
                    'menu_class'     => 'footer-menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                ]);
                ?>
            </div>
            <div class="footer-col">
                <h4 class="footer-title">نمادهای اعتماد</h4>
                <div class="trust-badges">
                    <a href="#" class="enamad" title="نماد اعتماد الکترونیک">
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
                <p>© <?php echo date('Y'); ?> تمامی حقوق برای فروشگاه صنوبر محفوظ است.</p>
            </div>
        </div>
    </footer>
    
    <!-- Push Notification -->
    <button id="pushSubscribe" class="push-subscribe-btn" style="display:none" aria-label="عضویت در نوتیفیکیشن">
        🔔 اطلاع از تخفیف‌ها
    </button>
    
    <!-- Install App Banner -->
    <div id="installBanner" class="install-banner" style="display:none">
        <div class="install-banner__inner">
            <span>📱 اپلیکیشن صنوبر را نصب کنید</span>
            <button id="installApp" class="btn btn--primary btn--sm">نصب</button>
            <button class="install-banner__close" id="installDismiss">✕</button>
        </div>
    </div>
    
    <?php wp_footer(); ?>
</body>
</html>