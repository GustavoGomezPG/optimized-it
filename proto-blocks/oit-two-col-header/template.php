<?php
/**
 * OIT Two-Col Header
 *
 * Industry / section page header. Two-column layout on desktop -- left
 * column is industry icon + headline + subtitle, right column is a
 * featured image at 608x400. Above both columns sits an auto-derived
 * breadcrumb (Home -> ancestors -> current). Behind both columns sits
 * the giant uppercase wordmark in #F5D6DA @ 40% opacity. Mobile stacks
 * everything single-column with the featured image at the bottom.
 *
 * Light theme only (see global memory: feedback-no-dark-mode). No
 * isLight toggle, no decoration variants, no CTAs.
 *
 * Breadcrumb and wordmark markup is produced by the shared helpers in
 * inc/oit-header-partials.php so this block and oit-page-header keep
 * the same look without duplicating template code.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$title         = !empty($attributes['title'])    ? $attributes['title']    : '<p>Industry name<span class="text-brand-red">.</span></p>';
$subtitle      = !empty($attributes['subtitle']) ? $attributes['subtitle'] : 'Short supporting line that explains what this industry page covers.';
$wordmark      = !empty($attributes['wordmark']) ? $attributes['wordmark'] : '';
$industry_icon = $attributes['industryIcon']  ?? null;
$featured      = $attributes['featuredImage'] ?? null;
$highlight     = trim((string) ($attributes['highlightWord'] ?? ''));

// Optional breadcrumb + optional CTA button below the subtitle, and an
// industry-icon toggle that suppresses the icon (and its placeholder)
// for variants leading with the title. All three default to the
// previous behavior so existing blocks stay visually identical.
$show_breadcrumb = $attributes['showBreadcrumb'] ?? true;
$show_icon       = $attributes['showIcon']       ?? true;
$show_button     = !empty($attributes['showButton']);
$button_solid    = $attributes['buttonSolid']    ?? true;
$button          = is_array($attributes['button'] ?? null) ? $attributes['button'] : [];

// Three flat layout/style switches added in this revision. All default
// to the existing behavior so older blocks stay visually identical.
//   removeShadow   -> strip shadow-red-glow off the featured image.
//   centerText     -> align the grid row items vertically center (text
//                     centers against the image) instead of items-start.
//                     Typically paired with showIcon=off when the left
//                     column ends up shorter than the image.
//   smallSubtitle  -> lock subtitle to 16px at every breakpoint.
$remove_shadow   = !empty($attributes['removeShadow']);
$center_text     = !empty($attributes['centerText']);
$small_subtitle  = !empty($attributes['smallSubtitle']);

$grid_align_class      = $center_text   ? 'items-center' : 'items-start';
$image_shadow_class    = $remove_shadow ? '' : 'shadow-red-glow';
$subtitle_size_classes = $small_subtitle
  ? 'text-body-sm'
  : 'text-body-sm lg:text-body-md';

// Highlight pass -- same approach as oit-page-header. Find the first
// case-insensitive occurrence of $highlight inside the title and wrap
// it in a brand-red span. Preserves the matched substring's original
// case via substr() rather than echoing the highlight value back.
if ($highlight !== '') {
    $pos = stripos($title, $highlight);
    if ($pos !== false) {
        $matched = substr($title, $pos, strlen($highlight));
        $title   = substr($title, 0, $pos)
            . '<span class="text-brand-red">' . $matched . '</span>'
            . substr($title, $pos + strlen($highlight));
    }
}

// In the editor preview $block is null. The data-proto-animate (manual)
// pre-state would leave everything invisible there because view.js
// doesn't run; only apply it on the front end.
$is_preview = !isset($block) || $block === null;

$wrapper_args = ['class' => 'oit-two-col-header relative'];
if (!$is_preview) {
    $wrapper_args['data-proto-animate'] = 'manual';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

// Featured image: pull width/height from the attachment when available
// so the browser reserves space (avoid CLS). The container also locks
// aspect-[608/400], which gives mobile the same proportional box at
// full width without an extra media query.
$featured_url     = !empty($featured['url']) ? $featured['url'] : '';
$featured_alt     = $featured['alt'] ?? '';
$featured_width   = isset($featured['width'])  ? (int) $featured['width']  : 608;
$featured_height  = isset($featured['height']) ? (int) $featured['height'] : 400;

// Industry icon: explicit width/height so the column reserves the icon
// box even before the image network-loads.
$icon_url    = !empty($industry_icon['url']) ? $industry_icon['url'] : '';
$icon_alt    = $industry_icon['alt'] ?? '';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-two-col-header__inner relative max-w-[1440px] mx-auto px-6 lg:px-20 pt-10 lg:pt-16 pb-0">

    <?php if ($show_breadcrumb) { oit_render_breadcrumb(); } ?>

    <div class="oit-two-col-header__grid relative z-10 mt-6 lg:mt-10 grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 <?php echo esc_attr($grid_align_class); ?>">

      <div class="oit-two-col-header__content flex flex-col gap-5 lg:gap-6">

        <?php if ($show_icon): ?>
          <?php if ($icon_url): ?>
          <img
            data-proto-field="industryIcon"
            src="<?php echo esc_url($icon_url); ?>"
            alt="<?php echo esc_attr($icon_alt); ?>"
            width="75"
            height="82"
            class="oit-two-col-header__icon block w-[75px] h-[82px] object-contain" />
          <?php else: ?>
          <div data-proto-field="industryIcon"
               class="oit-two-col-header__icon w-[75px] h-[82px] bg-light-grey rounded-md flex items-center justify-center text-black/40 text-center text-body-xs font-grotesk px-2">
            <span>Icon</span>
          </div>
          <?php endif; ?>
        <?php endif; ?>

        <div
          data-proto-field="title"
          class="oit-two-col-header__title m-0 font-grotesk font-bold text-[36px] leading-[1.3] lg:text-[56px] lg:leading-[1.2] text-black [&_p]:m-0 break-words">
          <?php echo wp_kses_post($title); ?>
        </div>

        <div
          data-proto-field="subtitle"
          class="oit-two-col-header__subtitle m-0 font-dm font-medium <?php echo esc_attr($subtitle_size_classes); ?> leading-[1.5] text-black max-w-[820px] [&_p]:m-0 [&_p+p]:mt-1">
          <?php echo wp_kses_post(wpautop($subtitle)); ?>
        </div>

        <?php if ($show_button):
          // CTA pill below the subtitle. Two skins:
          //   solid    -> filled brand-red pill, white text, darker red on hover.
          //   outlined -> transparent pill with a red border, black text on the
          //              light surface; fills on hover. Matches the convention
          //              used by oit-page-header and oit-call-to-action.
          $btn_base     = 'oit-two-col-header__cta inline-flex items-center gap-3 px-5 py-2.5 rounded-full font-grotesk font-medium text-body-sm leading-[1.3] uppercase whitespace-nowrap no-underline transition-colors self-start';
          $btn_skin     = $button_solid
            ? 'bg-cta-red hover:bg-cta-red-700 text-white border border-brand-red'
            : 'bg-transparent text-black border-2 border-brand-red hover:bg-brand-red hover:text-white';
          $btn_url      = !empty($button['url']) ? $button['url'] : '#';
          $btn_text     = !empty($button['text']) ? $button['text'] : 'Learn more';
          $btn_target   = !empty($button['target']) ? ' target="' . esc_attr($button['target']) . '"' : '';
          $btn_rel      = !empty($button['rel'])    ? ' rel="'    . esc_attr($button['rel'])    . '"' : '';
          // Chevron mirrors the one used by oit-page-header's CTA so the
          // button is visually consistent across the section-header family.
          $btn_chevron  = '<svg class="w-[13px] h-3 shrink-0" viewBox="0 0 13 12" fill="currentColor" aria-hidden="true"><path d="M0 1.41L1.36689 0L7.18345 6L1.36689 12L0 10.59L4.43997 6L0 1.41ZM5.81656 1.41L7.18345 0L13 6L7.18345 12L5.81656 10.59L10.2565 6L5.81656 1.41Z"/></svg>';
        ?>
        <a
          href="<?php echo esc_url($btn_url); ?>"<?php echo $btn_target; echo $btn_rel; ?>
          class="<?php echo esc_attr($btn_base . ' ' . $btn_skin); ?>">
          <span data-proto-field="button"><?php echo esc_html($btn_text); ?></span>
          <?php echo $btn_chevron; ?>
        </a>
        <?php endif; ?>

      </div>

      <div class="oit-two-col-header__image-wrap relative w-full aspect-[608/400] rounded-3xl overflow-clip <?php echo esc_attr($image_shadow_class); ?> bg-light-grey">
        <?php if ($featured_url): ?>
        <img
          data-proto-field="featuredImage"
          src="<?php echo esc_url($featured_url); ?>"
          alt="<?php echo esc_attr($featured_alt); ?>"
          width="<?php echo esc_attr($featured_width); ?>"
          height="<?php echo esc_attr($featured_height); ?>"
          class="oit-two-col-header__image absolute inset-0 w-full h-full object-cover" />
        <?php else: ?>
        <div data-proto-field="featuredImage"
             class="oit-two-col-header__image absolute inset-0 w-full h-full flex items-center justify-center text-black/40 text-center text-body-sm font-grotesk px-4">
          <span>Pick featured image</span>
        </div>
        <?php endif; ?>
      </div>

    </div>

    <?php oit_render_wordmark($wordmark); ?>

  </div>
</section>
