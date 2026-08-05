<?php
remove_action('woocommerce_before_main_content','woocommerce_output_content_wrapper',10);
remove_action('woocommerce_after_main_content','woocommerce_output_content_wrapper_end',10);
add_action('woocommerce_before_main_content',function(){echo '<div class="container"><div class="woocommerce-wrapper">';},10);
add_action('woocommerce_after_main_content',function(){echo '</div></div>';},10);
remove_action('woocommerce_sidebar','woocommerce_get_sidebar',10);
add_filter('loop_shop_per_page',function(){return 24;});
add_filter('loop_shop_columns',function(){return 4;});
add_filter('woocommerce_sale_flash',function($h){return '<span class="onsale">'.__('sale!','senoobar').'</span>';});
add_filter('woocommerce_product_add_to_cart_text',function($t){return __('add to cart','senoobar');});
add_filter('woocommerce_product_single_add_to_cart_text',function($t){return __('add to cart','senoobar');});
add_filter('woocommerce_output_related_products_args',function($a){$a['posts_per_page']=8;$a['columns']=4;return $a;});
add_filter('woocommerce_enqueue_styles','__return_empty_array');
add_filter('woocommerce_loop_add_to_cart_args',function($a){$a['class'].=' ajax_add_to_cart';return $a;});
