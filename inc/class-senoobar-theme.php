<?php
final class Senoobar_Theme {
    private static $instance = null;
    public static function get_instance() { if(null===self::$instance) self::$instance=new self(); return self::$instance; }
    private function __construct() {}
    public function init() { $this->setup(); $this->assets(); $this->woo(); $this->menus(); $this->customizer(); $this->pwa(); $this->perf(); }
    private function setup() {
        add_theme_support('title-tag'); add_theme_support('post-thumbnails');
        add_theme_support('custom-logo',['height'=>60,'width'=>180,'flex-height'=>true,'flex-width'=>true]);
        add_theme_support('html5',['search-form','comment-form','comment-list','gallery','caption','style','script']);
        add_theme_support('responsive-embeds'); add_theme_support('wp-block-styles'); add_theme_support('align-wide');
        add_image_size('senoobar-product-thumb',400,400,true); add_image_size('senoobar-product-medium',600,600,true);
        add_image_size('senoobar-product-large',1200,800,true); add_image_size('senoobar-hero',800,1000,true);
    }
    private function menus() { register_nav_menus(['primary'=>__('منوی اصلی','senoobar'),'footer'=>__('منوی فوتر','senoobar')]); }
    private function woo() {
        if(!class_exists('WooCommerce')) return;
        add_theme_support('woocommerce'); add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox'); add_theme_support('wc-product-gallery-slider');
        add_filter('woocommerce_enqueue_styles','__return_empty_array');
        add_filter('woocommerce_add_to_cart_fragments',function($f){ob_start();echo '<span class="cart-badge">'.WC()->cart->get_cart_contents_count().'</span>';$f['.cart-badge']=ob_get_clean();return $f;});
    }
    private function pwa() {
        add_action('wp_head',function(){echo '<meta name="mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-title" content="'.esc_attr(get_bloginfo('name')).'"><link rel="apple-touch-icon" href="'.esc_url(SENOOBAR_URI.'/assets/images/logo.png').'">';});
    }
    private function perf() {
        remove_action('wp_head','print_emoji_detection_script',7); remove_action('wp_print_styles','print_emoji_styles');
        add_filter('script_loader_tag',function($t,$h){if(in_array($h,['senoobar-app','senoobar-push']))return str_replace(' src',' defer src',$t);return $t;},10,2);
    }
    private function customizer() {
        add_action('customize_register',function($c){
            $c->add_setting('senoobar_announcement',['default'=>'ارسال رایگان برای سفارش‌های بالای ۳۰ جنتیون تومان']);
            $c->add_control('senoobar_announcement',['label'=>'متن اعلان','section'=>'title_tagline','type'=>'text']);
            $c->add_section('senoobar_hero',['title'=>'هیرو','priority'=>30]);
            $c->add_setting('senoobar_hero_title',['default'=>'میلمان خانه‌ای درخور شما']); $c->add_control('senoobar_hero_title',['label'=>'عنوان','section'=>'senoobar_hero','type'=>'text']);
            $c->add_setting('senoobar_hero_subtitle',['default'=>'تجربه‌ای متفاوت از راحتی و زیبایی']); $c->add_control('senoobar_hero_subtitle',['label'=>'زیرعنوان','section'=>'senoobar_hero','type'=>'textarea']);
            foreach(['senoobar_hero_img1'=>'تصویر یک','senoobar_hero_img2'=>'تصویر دو'] as $id=>$l){$c->add_setting($id);$c->add_control(new WP_Customize_Media_Control($c,$id,['label'=>$l,'section'=>'senoobar_hero']));}
            $c->add_section('senoobar_promo',['title'=>'بنرهای تبلیغاتی','priority'=>35]);
            foreach(['senoobar_promo_img1'=>'بنر ۱','senoobar_promo_img2'=>'بنر ۲'] as $id=>$l){$c->add_setting($id);$c->add_control(new WP_Customize_Media_Control($c,$id,['label'=>$l,'section'=>'senoobar_promo']));}
            $c->add_section('senoobar_gallery',['title'=>'گالری','priority'=>40]);
            for($i=1;$i<=8;$i++){$c->add_setting("senoobar_gallery_img{$i}");$c->add_control(new WP_Customize_Media_Control($c,"senoobar_gallery_img{$i}",['label'=>"تصویر {$i}",'section'=>'senoobar_gallery']));}
            $c->add_setting('senoobar_video_thumb');$c->add_control(new WP_Customize_Media_Control($c,'senoobar_video_thumb',['label'=>'تصویر ویدیو','section'=>'senoobar_hero']));
            $c->add_section('senoobar_services',['title'=>'خدمات','priority'=>32]);
            for($i=1;$i<=4;$i++){foreach(['icon','title','desc'] as $k){$c->add_setting("senoobar_service{$i}_{$k}",['default'=>$k==='icon'?'⭐':($k==='title'?"خدمت {$i}":'')]);$c->add_control("senoobar_service{$i}_{$k}",['label'=>"خدمت {$i} - ".($k==='icon'?'آیکون':($k==='title'?'عنوان':'توضیح')),'section'=>'senoobar_services','type'=>$k==='desc'?'textarea':'text']);}}
            $c->add_section('senoobar_tm',['title'=>'نظرات مشتریان','priority'=>38]);
            for($i=1;$i<=3;$i++){foreach(['stars'=>'5','text'=>'','author'=>''] as $k=>$df){$c->add_setting("senoobar_tm{$i}_{$k}",['default'=>$df]);$c->add_control("senoobar_tm{$i}_{$k}",['label'=>"نظر {$i} - {$k}",'section'=>'senoobar_tm','type'=>$k==='text'?'textarea':'text']);}}
            $c->add_section('senoobar_footer',['title'=>'فوتر','priority'=>90]);
            foreach(['about','phone1','phone2','address','hours','telegram','instagram','whatsapp'] as $f){$c->add_setting("senoobar_footer_{$f}");$c->add_control("senoobar_footer_{$f}",['label'=>$f,'section'=>'senoobar_footer','type'=>in_array($f,['about','address'])?'textarea':'text']);}
            $c->add_section('senoobar_sections',['title'=>'عناوین بخش‌ها','priority'=>33]);
            $ss=['cats_title'=>'دسته‌بندی‌ها','featured_title'=>'محصولات ویژه','featured_desc'=>'بهترین انتخاب‌های هفته با تخفیف‌های استثنایی','bestsellers_title'=>'پرفروش‌ترین‌ها','bestsellers_desc'=>'','gallery_title'=>'ایده‌هایی برای خانه شما','blog_title'=>'آخرین مقالات','blog_desc'=>'','newsletter_title'=>'در خبرنامه صنوبر عضو شوید!','newsletter_desc'=>'از تخفیف‌ها و جدیدترین محصولات باخبر شوید.'];
            foreach($ss as $k=>$v){$c->add_setting("senoobar_section_{$k}",['default'=>$v]);$c->add_control("senoobar_section_{$k}",['label'=>$k,'section'=>'senoobar_sections','type'=>str_contains($k,'desc')?'textarea':'text']);}
            $c->add_setting('senoobar_story_title',['default'=>'داستان صنوبر']);$c->add_control('senoobar_story_title',['label'=>'عنوان داستان','section'=>'senoobar_hero','type'=>'text']);
            $c->add_setting('senoobar_story_text',['default'=>'همراه شما در ساختن خانه‌ای زیباتر از مبلمان با کیفیت و طراحی مدرن']);$c->add_control('senoobar_story_text',['label'=>'متن داستان','section'=>'senoobar_hero','type'=>'textarea']);
            $c->add_setting('senoobar_story_btn',['default'=>'تماشای ویدیو']);$c->add_control('senoobar_story_btn',['label'=>'متن دکمه','section'=>'senoobar_hero','type'=>'text']);
        });
    }
    private function assets() {
        add_action('wp_enqueue_scripts',function(){
            wp_enqueue_style('senoobar-critical',SENOOBAR_URI.'/assets/css/critical.css',[],SENOOBAR_VERSION);
            wp_enqueue_style('senoobar-main',SENOOBAR_URI.'/assets/css/main.css',['senoobar-critical'],SENOOBAR_VERSION);
            if(is_rtl()) wp_enqueue_style('senoobar-rtl',SENOOBAR_URI.'/assets/css/rtl.css',['senoobar-main'],SENOOBAR_VERSION);
            wp_enqueue_script('senoobar-app',SENOOBAR_URI.'/assets/js/app.js',[],SENOOBAR_VERSION,true);
            wp_localize_script('senoobar-app','senoobarData',['ajaxUrl'=>admin_url('admin-ajax.php'),'cartUrl'=>class_exists('WooCommerce')?wc_get_cart_url():'','isRTL'=>is_rtl(),'siteUrl'=>home_url()]);
        });
        add_action('wp_head',function(){echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><style>@import url("https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap");</style>';},1);
    }
}