<?php
/**
 * Categories Grid — Fully configurable from Customizer
 *
 * Customizer settings:
 *   senoobar_cats_count        — How many categories to show (default: 6)
 *   senoobar_cats_columns      — Columns per row: 2|3|4|6 (default: 6)
 *   senoobar_cats_display_mode — 'auto' (WooCommerce) or 'manual' (hand-picked)
 *   senoobar_cats_manual_ids[] — Comma-separated category IDs for manual mode
 *   senoobar_cats_orderby      — 'name' | 'id' | 'count' | 'menu_order'
 *   senoobar_cats_order        — 'ASC' | 'DESC'
 *   senoobar_cats_title         — Section title (default: empty = no title)
 */

$cat_count   = get_theme_mod('senoobar_cats_count', 6);
$cat_columns = get_theme_mod('senoobar_cats_columns', 6);
$cat_mode    = get_theme_mod('senoobar_cats_display_mode', 'auto');
$cat_orderby = get_theme_mod('senoobar_cats_orderby', 'name');
$cat_order   = get_theme_mod('senoobar_cats_order', 'ASC');
$cat_title   = get_theme_mod('senoobar_section_cats_title', '');

// Only show title if it's explicitly set in Customizer
$show_title = !empty($cat_title);

// Build columns CSS class
$col_class = 'cats-grid--' . $cat_columns;

// Fallback static data (only when WooCommerce is not active)
$fallback_cats = [
  ['name' => 'سرویس خواب', 'img' => 'https://images.unsplash.com/photo-1696762932825-2737db830bbe?w=120&h=90&fit=crop&auto=format'],
  ['name' => 'تشک',       'img' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=120&h=90&fit=crop&auto=format'],
  ['name' => 'تخت خواب',   'img' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=120&h=90&fit=crop&auto=format'],
  ['name' => 'مبل و مبلمان','img' => 'https://images.unsplash.com/photo-1554995207-c18c203602cb?w=120&h=90&fit=crop&auto=format'],
  ['name' => 'میز جلو مبلی','img' => 'https://images.unsplash.com/photo-1628744876497-eb30460be9f6?w=120&h=90&fit=crop&auto=format'],
  ['name' => 'کمد و باخاخت','img' => 'https://images.unsplash.com/photo-1642541070065-3912f347e7c6?w=120&h=90&fit=crop&auto=format'],
];

$display = [];

if (class_exists('WooCommerce')):

  if ($cat_mode === 'manual'):
    // Manual mode: use hand-picked category IDs
    $manual_ids_str = get_theme_mod('senoobar_cats_manual_ids', '');
    $manual_ids = array_filter(array_map('intval', explode(',', $manual_ids_str)));
    if (!empty($manual_ids)):
      $terms = get_terms([
        'taxonomy'   => 'product_cat',
        'include'    => $manual_ids,
        'hide_empty' => false,
        'orderby'    => 'include', // preserve manual order
      ]);
      if (!is_wp_error($terms) && !empty($terms)):
        // Re-sort to match the exact order in manual_ids
        $term_map = [];
        foreach ($terms as $t) $term_map[$t->term_id] = $t;
        $terms = [];
        foreach ($manual_ids as $mid) {
          if (isset($term_map[$mid])) $terms[] = $term_map[$mid];
        }
        foreach ($terms as $t):
          $tid = get_term_meta($t->term_id, 'thumbnail_id', true);
          $display[] = [
            'name' => $t->name,
            'link' => get_term_link($t),
            'img'  => $tid ? wp_get_attachment_url($tid) : '',
          ];
        endforeach;
      endif;
    endif;
  endif;

  if (empty($display)):
    // Auto mode (default): get top categories from WooCommerce
    $terms = get_terms([
      'taxonomy'   => 'product_cat',
      'hide_empty' => false,
      'number'     => $cat_count,
      'orderby'    => $cat_orderby,
      'order'      => $cat_order,
    ]);
    if (!is_wp_error($terms) && !empty($terms)):
      foreach ($terms as $t):
        $tid = get_term_meta($t->term_id, 'thumbnail_id', true);
        $display[] = [
          'name' => $t->name,
          'link' => get_term_link($t),
          'img'  => $tid ? wp_get_attachment_url($tid) : '',
        ];
      endforeach;
    endif;
  endif;

endif;

// Fallback to hardcoded if empty
if (empty($display)):
  $display = array_slice($fallback_cats, 0, $cat_count);
endif;
?>

<style>
/* Dynamic columns via CSS custom property */
.cats-grid--2  { --cats-cols: 2; }
.cats-grid--3  { --cats-cols: 3; }
.cats-grid--4  { --cats-cols: 4; }
.cats-grid--6  { --cats-cols: 6; }
.cats-grid { display: grid; grid-template-columns: repeat(var(--cats-cols, 6), 1fr); gap: 12px; }
@media (max-width: 768px) {
  .cats-grid { grid-template-columns: repeat(min(var(--cats-cols, 3), 3), 1fr); }
}
@media (max-width: 480px) {
  .cats-grid { grid-template-columns: repeat(min(var(--cats-cols, 2), 2), 1fr); }
}
</style>

<section class="cats-section">
  <div class="container">
    <?php if ($show_title): ?>
      <h2 class="section__title" style="text-align:right;margin-bottom:16px;"><?php echo esc_html($cat_title); ?></h2>
    <?php endif; ?>
    <div class="cats-grid <?php echo $col_class; ?>">
      <?php foreach ($display as $c): ?>
        <a href="<?php echo isset($c['link']) ? esc_url($c['link']) : '#'; ?>" class="cat-item">
          <div class="cat-item__thumb">
            <?php if (!empty($c['img'])): ?>
              <img src="<?php echo esc_url($c['img']); ?>" alt="<?php echo esc_attr($c['name']); ?>" loading="lazy">
            <?php else: ?>
              <div class="placeholder">🛋️</div>
            <?php endif; ?>
          </div>
          <span class="cat-item__title"><?php echo esc_html($c['name']); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
