<?php
class Senoobar_Options {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }
    public function add_page() {
        add_menu_page('تنظیمات صنوبر','تنظیمات قالب','manage_options','senoobar-options',[$this,'render'],'dashicons-admin-customizer',30);
    }
    public function enqueue($hook) { if ($hook!=='toplevel_page_senoobar-options') return; wp_enqueue_media(); }
    public function register_settings() {
        register_setting('senoobar_hero','senoobar_hero_title');
        register_setting('senoobar_hero','senoobar_hero_subtitle');
        register_setting('senoobar_hero','senoobar_hero_btn1_text');
        register_setting('senoobar_hero','senoobar_hero_btn2_text');
        register_setting('senoobar_hero','senoobar_hero_img1');
        register_setting('senoobar_hero','senoobar_hero_img2');
        register_setting('senoobar_story','senoobar_story_title');
        register_setting('senoobar_story','senoobar_story_text');
        register_setting('senoobar_story','senoobar_story_btn');
        register_setting('senoobar_story','senoobar_video_thumb');
        for($i=1;$i<=4;$i++){register_setting('senoobar_services',"senoobar_service{$i}_icon");register_setting('senoobar_services',"senoobar_service{$i}_title");register_setting('senoobar_services',"senoobar_service{$i}_desc");}
        for($i=1;$i<=2;$i++){register_setting('senoobar_promo',"senoobar_promo{$i}_title");register_setting('senoobar_promo',"senoobar_promo{$i}_desc");register_setting('senoobar_promo',"senoobar_promo{$i}_btn");register_setting('senoobar_promo',"senoobar_promo{$i}_img");}
        for($i=1;$i<=3;$i++){register_setting('senoobar_tm',"senoobar_tm{$i}_stars");register_setting('senoobar_tm',"senoobar_tm{$i}_text");register_setting('senoobar_tm',"senoobar_tm{$i}_author");}
        foreach(['about','phone1','phone2','address','hours','telegram','instagram','whatsapp'] as $f)register_setting('senoobar_footer',"senoobar_footer_{$f}");
        register_setting('senoobar_sections','senoobar_section_cats_title');
        register_setting('senoobar_sections','senoobar_section_featured_title');
        register_setting('senoobar_sections','senoobar_section_featured_desc');
        register_setting('senoobar_sections','senoobar_section_bestsellers_title');
        register_setting('senoobar_sections','senoobar_section_bestsellers_desc');
        register_setting('senoobar_sections','senoobar_section_gallery_title');
        register_setting('senoobar_sections','senoobar_section_blog_title');
        register_setting('senoobar_sections','senoobar_section_blog_desc');
        register_setting('senoobar_sections','senoobar_section_newsletter_title');
        register_setting('senoobar_sections','senoobar_section_newsletter_desc');
        register_setting('senoobar_general','senoobar_announcement');
    }