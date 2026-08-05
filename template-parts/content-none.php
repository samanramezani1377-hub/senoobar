<?php if (is_search()): ?>
<section class="no-content">
    <h2>نتیجه‌ای یافت نشد</h2>
    <p>متاسفانه چیزی با این عبارت پیدا نشد. لطفاً دوباره جستجو کنید.</p>
    <?php get_search_form(); ?>
</section>
<?php else: ?>
<section class="no-content">
    <h2>محتوایی یافت نشد</h2>
    <p>به نظر می‌رسد محتوایی برای نمایش وجود ندارد.</p>
</section>
<?php endif; ?>
