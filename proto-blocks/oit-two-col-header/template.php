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

// In the editor preview $block is null. The data-animate="pending"
// pre-state would leave everything invisible there because view.js
// doesn't run; only apply it on the front end.
$is_preview = !isset($block) || $block === null;

$wrapper_args = ['class' => 'oit-two-col-header relative'];
if (!$is_preview) {
    $wrapper_args['data-animate'] = 'pending';
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

    <?php oit_render_breadcrumb(); ?>

    <div class="oit-two-col-header__grid relative z-10 mt-6 lg:mt-10 grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-start">

      <div class="oit-two-col-header__content flex flex-col gap-5 lg:gap-6">

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

        <div
          data-proto-field="title"
          class="oit-two-col-header__title m-0 font-grotesk font-bold text-[36px] leading-[1.3] lg:text-[56px] lg:leading-[1.2] text-black [&_p]:m-0 break-words">
          <?php echo wp_kses_post($title); ?>
        </div>

        <div
          data-proto-field="subtitle"
          class="oit-two-col-header__subtitle m-0 font-dm font-medium text-body-sm lg:text-body-md leading-[1.5] text-black max-w-[820px] [&_p]:m-0 [&_p+p]:mt-1">
          <?php echo wp_kses_post(wpautop($subtitle)); ?>
        </div>

      </div>

      <div class="oit-two-col-header__image-wrap relative w-full aspect-[608/400] rounded-3xl overflow-clip shadow-red-glow bg-light-grey">
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
