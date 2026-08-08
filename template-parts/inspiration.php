<?php
?>
<section class="section"><div class="container"><div class="section__header"><h2 class="section__title section__title--lined">ایده‌هایی برای خانه</h2></div><div class="gallery-grid">
<?php $colors=['#d4cfc9','#e8e4df','#c4bdb5','#d9d3cc','#e0dad3','#c9c3bb','#ddd7d0','#d0cac3'];for($i=1;$i<=8;$i++):$img=get_theme_mod("senoobar_gallery_img{$i}");?>
<div class="gallery-item"><?php if($img):echo wp_get_attachment_image($img,'medium',false,['loading'=>'lazy']);else:?><div style="background:<?php echo $colors[$i-1];?>;width:100%;height:100%"></div><?php endif;?><div class="gallery-item__overlay"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div></div>
<?php endfor;?>
</div></div></section>