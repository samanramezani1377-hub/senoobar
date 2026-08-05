<?php if(is_search()):?>
<section class="no-results"><h2><?php esc_html_e('نتیجه‌ای یافت نشد','senoobar');?></h2><p><?php esc_html_e('متاسفانه چیزی پیدا نشد.','senoobar');?></p><?php get_search_form();?></section>
<?php else:?>
<section class="no-results"><h2><?php esc_html_e('محتوایی یافت نشد','senoobar');?></h2></section>
<?php endif;?>