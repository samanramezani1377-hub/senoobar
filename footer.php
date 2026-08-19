<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-col">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                <div style="width:40px;height:40px;border-radius:var(--radius-xl);background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;"><svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24" style="color:var(--color-white);"><path d="M12 2L8 8H4l4 4-1.5 6L12 15l5.5 3L16 12l4-4h-4L12 2z"/></svg></div>
                <div><div style="font-weight:700;font-size:1.1rem;">فروشگاه صنوبر</div><div style="font-size:0.7rem;opacity:0.6;">میلمان خانه شما</div></div>
            </div>
            <p style="opacity:0.6;font-size:0.85rem;line-height:2;"><?php echo esc_html(get_theme_mod('senoobar_footer_about','فروشگاه صنوبر یک فروشگاه تخصصی در زمینه سرویس خواب، تشک و مبلمان در اصفهان است. ما انواع تشک، سرویس خواب، تخت خواب، مبل و مبلمان منزل را با تنوع بالا و کیفیت مناسب ارائه می‌دهیم.')); ?></p>
            <div class="footer-social"><?php foreach(['instagram'=>'I','telegram'=>'T','pinterest'=>'P','linkedin'=>'L'] as $k=>$l): ?><a href="<?php echo esc_url(get_theme_mod("senoobar_footer_{$k}",'#')); ?>" aria-label="<?php echo $k; ?>"><?php echo $l; ?></a><?php endforeach; ?></div>
        </div>
        <div class="footer-col"><h4>دسته‌بندی محصولات</h4><ul><li><a href="#">سرویس خواب</a></li><li><a href="#">تشک</a></li><li><a href="#">تخت خواب</a></li><li><a href="#">مبل و مبلمان</a></li><li><a href="#">میز جلو مبلی</a></li><li><a href="#">اکسسوری</a></li></ul></div>
<?php
// Resolve legal-page URLs (created via legal-pages-setup.php).
$snb_f_about   = function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('about')   ? senoobar_legal_page_url('about')   : home_url('/about/');
$snb_f_contact = function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('contact') ? senoobar_legal_page_url('contact') : home_url('/contact/');
$snb_f_faq     = function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('faq')     ? senoobar_legal_page_url('faq')     : home_url('/faq/');
$snb_f_terms   = function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('terms')   ? senoobar_legal_page_url('terms')   : home_url('/terms-and-conditions/');
$snb_f_privacy = function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('privacy') ? senoobar_legal_page_url('privacy') : home_url('/privacy-policy/');
?>
        <div class="footer-col"><h4>دسترسی سریع</h4><ul><li><a href="<?php echo esc_url($snb_f_about); ?>">درباره ما</a></li><li><a href="<?php echo esc_url($snb_f_contact); ?>">تماس با ما</a></li><li><a href="<?php echo esc_url($snb_f_faq); ?>">سوالات متداول</a></li><li><a href="<?php echo esc_url($snb_f_terms); ?>">شرایط و قوانین</a></li><li><a href="<?php echo esc_url($snb_f_privacy); ?>">حریم خصوصی</a></li></ul></div>
        <div class="footer-col"><h4>اطلاعات تماس</h4><div class="footer-contact"><div><span>📞</span><span><?php echo esc_html(get_theme_mod('senoobar_footer_phone1','۰۹۱۳۰۲۰۵۸۹۸')); ?></span></div><div><span>📞</span><span><?php echo esc_html(get_theme_mod('senoobar_footer_phone2','۰۹۱۳۰۲۰۵۸۶۸')); ?></span></div><div><span>📞</span><span><?php echo esc_html(get_theme_mod('senoobar_footer_phone3','۰۹۱۳۰۲۰۵۳۲۳')); ?></span></div><div><span>📍</span><span><?php echo esc_html(get_theme_mod('senoobar_footer_address','اصفهان، شهرک صنعتی دولت‌آباد، خیابان شماره ۱۰ (خیام)، فروشگاه صنوبر')); ?></span></div><div><span>🕐</span><span><?php echo esc_html(get_theme_mod('senoobar_footer_hours','شنبه تا پنجشنبه، ۱۰ صبح تا ۹ شب')); ?></span></div></div></div>
    </div>
    <div class="footer-bottom">
        <p>© <?php echo date('Y'); ?> فروشگاه صنوبر — کلیه حقوق این وب‌سایت متعلق به صنوبر است.</p>
        <div class="footer-bottom__badges">
            <?php
            $enamad_code = get_theme_mod('senoobar_enamad_code', '');
            if (!empty($enamad_code)) :
                echo '<div class="enamad-badge">' . $enamad_code . '</div>';
            endif;
            ?>
            <span>استفاده از مطالب با ذکر منبع</span>
        </div>
    </div>
</footer>