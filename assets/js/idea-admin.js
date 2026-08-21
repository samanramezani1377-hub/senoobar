/**
 * Senoobar — منطق فرم «افزودن ایده».
 * باز شدن کتابخانه رسانه برای تصویر کاور و ویدیو.
 */
(function ($) {
	if (typeof wp === 'undefined' || !wp.media) return;

	$(function () {
		// انتخاب / آپلود رسانه (تصویر یا ویدیو)
		$('.senoobar-media-btn').on('click', function (e) {
			e.preventDefault();
			var btn      = $(this);
			var fieldId  = btn.data('field');       // نام input مخفی
			var type     = btn.data('type');        // image | video
			var preview  = '#' + btn.closest('.senoobar-idea-media').find('.senoobar-idea-media__preview').attr('id');
			var isVideo  = (type === 'video');

			var frame = wp.media({
				title: isVideo ? 'انتخاب ویدیو' : 'انتخاب تصویر کاور',
				library: { type: isVideo ? 'video' : 'image' },
				multiple: false,
				button: { text: isVideo ? 'انتخاب این ویدیو' : 'انتخاب این تصویر' }
			});

			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();

				if (isVideo) {
					$('#' + fieldId).val(att.url);
					$(preview).html('<video src="' + att.url + '" controls muted></video>');
				} else {
					$('#' + fieldId).val(att.id);
					$(preview).html('<img src="' + att.url + '" alt="">');
				}
			});

			frame.open();
		});

		// پاک کردن
		$('.senoobar-media-clear').on('click', function (e) {
			e.preventDefault();
			var btn     = $(this);
			var fieldId = btn.data('field');
			var preview = '#' + btn.data('preview');
			var type    = btn.closest('.senoobar-idea-media').data('type');

			$('#' + fieldId).val('');
			$(preview).html('<span class="senoobar-idea-media__empty">' +
				(type === 'video' ? 'ویدیویی انتخاب نشده' : 'تصویری انتخاب نشده') +
				'</span>');
		});
	});
})(jQuery);
