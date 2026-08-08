<?php
?>
<section class="testimonials-section section"><div class="container"><div class="section__header"><h2 class="section__title section__title--lined">نظرات مشتریان</h2></div><div class="testimonials-grid">
<?php $testimonials=[['stars'=>'★★★★★','text'=>'کیفیت محصولات صنوبر فوق‌العاده است.','author'=>'مبینا رضایی'],['stars'=>'★★★★★','text'=>'ارسال سریع و بسته‌بندی عالی.','author'=>'امیر حسینی'],['stars'=>'★★★★★','text'=>'خریدی مطمئن و تجربه‌ای خاص.','author'=>'سارا محمدی']];foreach($testimonials as $t):?>
<div class="testimonial-card"><div class="testimonial-card__stars"><?php echo $t['stars']; ?></div><p class="testimonial-card__text"><?php echo $t['text']; ?></p><span class="testimonial-card__author"><?php echo $t['author']; ?></span></div>
<?php endforeach;?>
</div></div></section>