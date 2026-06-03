<?php
/**
 * OIT Breadcrumbs + Eyebrow + Title
 *
 * Top-of-page section for single resources. Three things stacked on a
 * light-grey -> white linear gradient background:
 *
 *   1. Breadcrumb row:  Home  >  Resource Library  >  [current title]
 *      Home + parent are dark brand-black links; the trailing current
 *      crumb is brand-red text.
 *
 *   2. White rounded card (`shadow-red-glow`) containing:
 *        - a small red uppercase eyebrow (defaults to the post's first
 *          Resource Type term, override-able via control)
 *        - the post title as an h1 (override-able via control)
 *      Both centered horizontally with a content-max of ~900px.
 *
 * Everything is content-driven from the post + taxonomy so editors do
 * not have to touch fields on the canvas for the common case.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$home_label       = (string) ($attributes['homeLabel'] ?? 'Home');
$parent_label     = (string) ($attributes['parentLabel'] ?? 'Resource Library');
$eyebrow_override = trim((string) ($attributes['eyebrowOverride'] ?? ''));
$title_override   = trim((string) ($attributes['titleOverride'] ?? ''));

$current_id = is_singular('oit_resource') ? (int) get_queried_object_id() : 0;

// Auto eyebrow from the first assigned Resource Type term. The
// override always wins when set; otherwise the eyebrow is hidden when
// the post has no term assigned (no hardcoded fallback).
$eyebrow = $eyebrow_override;
if ($eyebrow === '' && $current_id) {
  $terms = get_the_terms($current_id, 'resource_type');
  if (!is_wp_error($terms) && !empty($terms)) {
    $first = reset($terms);
    if ($first && !empty($first->name)) {
      $eyebrow = $first->name;
    }
  }
}

// Title resolution.
if ($title_override !== '') {
  $title = $title_override;
} elseif ($current_id) {
  $title = get_the_title($current_id);
} else {
  $title = 'Resource Title';
}

// Breadcrumb URLs.
$home_url    = esc_url(home_url('/'));
$parent_url  = esc_url(get_post_type_archive_link('oit_resource') ?: home_url('/resources/'));

$is_preview = !isset($block) || $block === null;
$wrapper_args = ['class' => 'oit-bet'];
if (!$is_preview) {
  $wrapper_args['data-proto-animate'] = 'pending';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

$chevron = '<svg class="w-[13px] h-3 shrink-0 text-brand-black" viewBox="0 0 13 12" fill="currentColor" aria-hidden="true"><path d="M0 1.41L1.36689 0L7.18345 6L1.36689 12L0 10.59L4.43997 6L0 1.41ZM5.81656 1.41L7.18345 0L13 6L7.18345 12L5.81656 10.59L10.2565 6L5.81656 1.41Z"/></svg>';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-bet__bg relative pt-[160px]" style="background-image: linear-gradient(180deg, rgb(217, 217, 217) 0%, rgb(255, 255, 255) 100%);">

    <div class="oit-bet__inner max-w-[1440px] mx-auto px-6 lg:px-20">

      <nav class="oit-bet__crumbs flex items-center gap-2 mb-6 lg:mb-8 font-grotesk font-medium text-[16px] leading-[1.3]" aria-label="Breadcrumb">
        <a href="<?php echo $home_url; ?>" class="text-brand-black hover:text-brand-red transition-colors no-underline">
          <?php echo esc_html($home_label); ?>
        </a>
        <?php echo $chevron; ?>
        <a href="<?php echo $parent_url; ?>" class="text-brand-black hover:text-brand-red transition-colors no-underline">
          <?php echo esc_html($parent_label); ?>
        </a>
        <?php echo $chevron; ?>
        <span class="text-brand-red"><?php echo esc_html($title); ?></span>
      </nav>

      <div class="oit-bet__card relative rounded-3xl bg-white px-6 py-12 lg:px-15 lg:py-15 overflow-clip">
        <div class="oit-bet__card-inner mx-auto max-w-[900px] flex flex-col items-center gap-5 text-center">
          <?php if ($eyebrow !== ''): ?>
          <p class="oit-bet__eyebrow m-0 font-grotesk font-medium uppercase text-[16px] leading-[1.4] text-brand-red tracking-wide">
            <?php echo esc_html($eyebrow); ?>
          </p>
          <?php endif; ?>
          <h1 class="oit-bet__title m-0 font-grotesk font-bold text-[28px] lg:text-[40px] leading-[1.2] text-brand-black">
            <?php echo esc_html($title); ?>
          </h1>
        </div>
      </div>

    </div>

  </div>
</section>
