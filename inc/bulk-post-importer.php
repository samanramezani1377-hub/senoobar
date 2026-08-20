<?php
/**
 * Senoobar — ایمپورت گروهی نوشته از فایل (ZIP / تکی).
 * 
 * قابینتایلا:
-  فایل ZIP شامل چند فایل متنی (.txt / .md / .markdown / .html / .htm)
 *      — هر فایل به یک نوشته تبدیل می‌شود (عنوان = اولین خط غیرخالی).
 *   ۲. آپلود تکی با عنوان، دسته، خلاصه و تصویر شاخص.
 *   ۳. تبدیل Markdown → HTML (تیتر، لیست، پاراگراف، بولد، ایتالیک، لینک، عکس).
 *   ۴. پردازش کامل عکس‌های درون متن با هر تعداد و هر موقعیتی:
 *      - شناسایی `![alt](src)` و `![alt](src "title")`.
 *      - عکس‌های نسبی از داخل ZIP استخراج و در کتابخانه رسانه آپلود می‌شوند.
 *      - URL واقعی جایگزین مسیر نسبی می‌شود.
 *      - URL مطلق (http/https) دست‌نخورده می‌ماند.
 *   ۵. اولین عکس مقاله به‌صورت تصویر شاخص (featured image) ست می‌شود.
 *   ۶. پشتیبانی اض WEBP / PNG / JPG / GIF.
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'senoobar_bulk_import_menu' );

function senoobar_bulk_import_menu() {
	add_posts_page(
		'ایمپورت گروهی نوشته‌ها',
		'ایمپورت گروهی',
		'edit_posts',
		'senoobar-bulk-import',
		'senoobar_bulk_import_page'
	);
}

function senoobar_bulk_import_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$notice = get_transient( 'senoobar_bulk_import_notice' );
	if ( $notice ) {
		delete_transient( 'senoobar_bulk_import_notice' );
	}
	?>
	<div class="wrap" dir="rtl">
		<h1>📥 ایمپورت گروهی نوشته‌ها</h1>

		<?php if ( $notice ) : ?>
			<div class="notice <?php echo esc_attr( $notice['type'] ); ?> is-dismissible"><p><?php echo wp_kses_post( $notice['msg'] ); ?></p></div>
		<?php endif; ?>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:16px;">
			<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
				<h2 style="margin-top:0;">📦 آپلود دسته‌ای (ZIP)</h2>
				<p style="color:#555;font-size:.9rem;">یک فایل ZIP شامل چند فایل متنی (txt / md / html) را بارگذاری کنید تا هر فایل به یک نوشته تبدیل شود.</p>
				<p style="color:#1e3a2f;font-size:.9rem;background:#f0f7f4;padding:10px;border-radius:8px;">
					🖼️ عکس‌های درون متن (با هر تعداد و موقعیتی) به‌صورت خودکار از ZIP استخراج و به کتابخانه رسانه اضافه می‌شوند؛ اولین عکس، تصویر شاخص می‌شود.
				</p>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'senoobar_zip_import', 'senoobar_zip_nonce' ); ?>
					<input type="hidden" name="action" value="senoobar_zip_import">
					<p>
						<label><strong>فایل ZIP:</strong><br>
						<input type="file" name="zip_file" accept=".zip" required style="margin-top:6px;"></label>
					</p>
					<p>
						<label><strong>دسته (اختیاری):</strong><br>
						<?php wp_dropdown_categories( array( 'show_option_all' => '— بدون دسته —', 'hide_empty' => 0, 'name' => 'zip_cat', 'taxonomy' => 'category' ) ); ?></label>
					</p>
					<p>
						<label><strong>وضعیت انتشار:</strong><br>
						<select name="zip_status" style="margin-top:6px;">
							<option value="publish">منتشر شده</option>
							<option value="draft">پیش‌نویس</option>
						</select></label>
					</p>
					<button type="submit" class="button button-primary button-large">افزودن نوشته‌ها</button>
				</form>
			</div>

			<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
				<h2 style="margin-top:0;">📄 افزودن تک‌نوشته</h2>
				<p style="color:#555;font-size:.9rem;">یک فایل متنی (txt / md / html) بارگذاری کنید. عنوان از اولین خط گرفته می‌شود مگر اینکه خودتان عنوان بنویسید.</p>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'senoobar_single_import', 'senoobar_single_nonce' ); ?>
					<input type="hidden" name="action" value="senoobar_single_import">
					<p>
						<label><strong>فایل نوشته:</strong><br>
						<input type="file" name="post_file" accept=".txt,.md,.html,.htm" required style="margin-top:6px;"></label>
					</p>
					<p>
						<label><strong>عنوان (اختیاری — خالی بماند تا از فایل خوانده شود):</strong><br>
						<input type="text" name="post_title" class="regular-text" style="margin-top:6px;" dir="rtl"></label>
					</p>
					<p>
						<label><strong>دسته (اختیاری):</strong><br>
						<?php wp_dropdown_categories( array( 'show_option_all' => '— بدون دسته —', 'hide_empty' => 0, 'name' => 'single_cat', 'taxonomy' => 'category' ) ); ?></label>
					</p>
					<p>
						<label><strong>خلاصه (اختیاری):</strong><br>
						<textarea name="post_excerpt" rows="2" class="large-text" style="margin-top:6px;" dir="rtl"></textarea></label>
					</p>
					<p>
						<label><strong>تصویر شاخص (اختیاری):</strong><br>
						<input type="file" name="post_image" accept="image/*" style="margin-top:6px;"></label>
					</p>
					<p>
						<label><strong>وضعیت انتشار:</strong><br>
						<select name="single_status" style="margin-top:6px;">
							<option value="publish">منتشر شده</option>
							<option value="draft">پیش‌نویس</option>
						</select></label>
					</p>
					<button type="submit" class="button button-primary button-large">افزودن نوشته</button>
				</form>
			</div>
		</div>
	</div>
	<?php
}
