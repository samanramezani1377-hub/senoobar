<?php
class Senoobar_Options {
    public function __construct(){add_action('admin_menu',[$this,'add_page']);add_action('admin_init',[$this,'register_settings']);add_action('admin_enqueue_scripts',[$this,'enqueue']);}
    public function add_page(){add_menu_page('تنظیمات صنوبر','تنظیمات قالب','manage_options','senoobar-options',[$this,'render'],'dashicons-admin-customizer',30);}
    public function enqueue($hook){if($hook!=='toplevel_page_senoobar-options')return;wp_enqueue_media();}
    public function register_settings(){
        foreach(['senoobar_hero_title','senoobar_hero_subtitle','senoobar_hero_btn1_text','senoobar_hero_btn2_text','senoobar_hero_img1','senoobar_hero_img2'] as $k)register_setting('senoobar_hero',$k);
        foreach(['senoobar_story_title','senoobar_story_text','senoobar_story_btn','senoobar_video_thumb'] as $k)register_setting('senoobar_story',$k);
        for($i=1;$i<=4;$i++)foreach(['icon','title','desc'] as $k)register_setting('senoobar_services',"senoobar_service{$i}_{$k}");
        for($i=1;$i<=2;$i++)foreach(['title','desc','btn','img'] as $k)register_setting('senoobar_promo',"senoobar_promo{$i}_{$k}");
        for($i=1;$i<=3;$i++)foreach(['stars','text','author'] as $k)register_setting('senoobar_tm',"senoobar_tm{$i}_{$k}");
        foreach(['about','phone1','phone2','address','hours','telegram','instagram','whatsapp'] as $f)register_setting('senoobar_footer',"senoobar_footer_{$f}");
        foreach(['cats_title','featured_title','featured_desc','bestsellers_title','bestsellers_desc','gallery_title','blog_title','blog_desc','newsletter_title','newsletter_desc'] as $s)register_setting('senoobar_sections',"senoobar_section_{$s}");
        register_setting('senoobar_general','senoobar_announcement');
    }
    public function render(){if(!current_user_can('manage_options'))return;?><div class="wrap" style="direction:rtl;"><h1 style="color:#1e3a2f;font-size:1.8rem;margin-bottom:24px;">⚙️ تنظیمات قالب صنوبر</h1><p style="color:#666;margin-bottom:32px;">تمامی تنظیمات از طریق Customizer نیز در دسترس است. به <a href="<?php echo admin_url('customize.php'); ?>">سفارشی‌سازی زنده</a> مراجعه کنید.</p><div style="background:white;border-radius:16px;padding:32px;box-shadow:0 1px 3px rgba(0,0,0,0.08);max-width:700px;"><p style="font-size:0.95rem;color:#374151;line-height:2;"><strong>نسخه ۲.۰.۰</strong> — دیزاین جدید با پالت سبز تیره برگرفته از Figma Make.</p><p style="font-size:0.9rem;color:#6b6560;margin-top:12px;">برای تنظیمات دقیق‌تر هر بخش، از منوی <strong>نمایش > سفارشی‌سازی</strong> استفاده کنید.</p><div style="margin-top:24px;padding:16px;background:#f0fdf4;border-radius:12px;"><p><strong>🟢 راهنمای سریع:</strong></p><ul style="list-style:disc;padding-right:20px;"><li>لوگو: نمایش > سفارشی‌سازی > هویت سایت</li><li>منوها: نمایش > فهرست‌ها</li><li>ابزارک‌ها: نمایش > ابزارک‌ها</li><li>تنظیمات ووکامرس: ووکامرس > تنظیمات</li></ul></div></div></div><?php }}
new Senoobar_Options();