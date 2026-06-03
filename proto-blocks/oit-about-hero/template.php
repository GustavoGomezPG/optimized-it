<?php
/**
 * OIT About Hero
 *
 * About / company-overview page header. Two-column layout on desktop --
 * left column is headline + supporting paragraph + (optional) awards row,
 * right column is a featured image at 608x400. Above both columns sits an
 * auto-derived breadcrumb (Home -> ancestors -> current). Behind both
 * columns sits a giant two-color uppercase wordmark anchored to the
 * bottom-right (primary segment in light-grey, accent segment in pink,
 * both at 40% opacity). Mobile stacks everything single-column with the
 * featured image at the bottom and the awards row above it.
 *
 * Light theme only (see global memory: feedback-no-dark-mode). No
 * isLight toggle, no decoration variants.
 *
 * Breadcrumb and wordmark markup is produced by the shared helpers in
 * inc/oit-header-partials.php so this block stays in sync with
 * oit-page-header and oit-two-col-header without duplicating template code.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$title             = !empty($attributes['title'])    ? $attributes['title']    : '<p>About OptimizedIT</p>';
$subtitle          = !empty($attributes['subtitle']) ? $attributes['subtitle'] : 'Short supporting line that explains who we are.';
$wordmark_primary  = (string) ($attributes['wordmarkPrimary'] ?? '');
$wordmark_accent   = (string) ($attributes['wordmarkAccent']  ?? '');
$awards            = is_array($attributes['awards'] ?? null) ? $attributes['awards'] : [];
$featured          = $attributes['featuredImage'] ?? null;
$highlight         = trim((string) ($attributes['highlightWord'] ?? ''));

// Highlight pass -- same approach as oit-two-col-header. Find the first
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

// In the editor preview $block is null. The data-proto-animate="manual"
// pre-state would leave everything invisible there because view.js
// doesn't run; only apply it on the front end.
$is_preview = !isset($block) || $block === null;

$wrapper_args = ['class' => 'oit-about-hero relative'];
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
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-about-hero__inner relative max-w-[1440px] mx-auto px-6 lg:px-20 pt-10 lg:pt-16 pb-16 lg:pb-24">

    <?php oit_render_breadcrumb(); ?>

    <div class="oit-about-hero__grid relative z-10 mt-6 lg:mt-10 grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-start">

      <div class="oit-about-hero__content flex flex-col gap-5 lg:gap-6">

        <div
          data-proto-field="title"
          class="oit-about-hero__title m-0 font-grotesk font-bold text-[36px] leading-[1.3] lg:text-[56px] lg:leading-[1.2] text-black [&_p]:m-0 break-words">
          <?php echo wp_kses_post($title); ?>
        </div>

        <div
          data-proto-field="subtitle"
          class="oit-about-hero__subtitle m-0 font-dm font-medium text-body-sm leading-[1.5] text-black max-w-[820px] [&_p]:m-0 [&_p+p]:mt-3">
          <?php echo wp_kses_post(wpautop($subtitle)); ?>
        </div>

        <?php if (!empty($awards)): ?>
        <ul class="oit-about-hero__awards list-none p-0 m-0 mt-2 lg:mt-3 flex flex-wrap items-center gap-4 lg:gap-6">
          <?php foreach ($awards as $award):
              $logo      = $award['logo'] ?? null;
              $logo_url  = !empty($logo['url']) ? $logo['url'] : '';
              $alt_text  = trim((string) ($award['alt'] ?? ($logo['alt'] ?? '')));
              $link      = $award['link'] ?? null;
              $link_url  = !empty($link['url']) ? $link['url'] : '';
              $link_open = !empty($link['target']) && $link['target'] === '_blank';
              if ($logo_url === '') { continue; }
              $img = sprintf(
                  '<img src="%s" alt="%s" class="oit-about-hero__award-logo h-10 lg:h-12 w-auto object-contain" loading="lazy" decoding="async" />',
                  esc_url($logo_url),
                  esc_attr($alt_text)
              );
          ?>
          <li class="oit-about-hero__award">
            <?php if ($link_url): ?>
            <a href="<?php echo esc_url($link_url); ?>"
               class="oit-about-hero__award-link inline-flex items-center"
               <?php if ($link_open): ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
              <?php echo $img; ?>
            </a>
            <?php else: ?>
              <?php echo $img; ?>
            <?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>

      </div>

      <div class="oit-about-hero__image-wrap relative w-full aspect-[608/400] rounded-3xl overflow-clip shadow-red-glow bg-light-grey">
        <?php if ($featured_url): ?>
        <img
          data-proto-field="featuredImage"
          src="<?php echo esc_url($featured_url); ?>"
          alt="<?php echo esc_attr($featured_alt); ?>"
          width="<?php echo esc_attr($featured_width); ?>"
          height="<?php echo esc_attr($featured_height); ?>"
          class="oit-about-hero__image absolute inset-0 w-full h-full object-cover" />
        <?php else: ?>
        <div data-proto-field="featuredImage"
             class="oit-about-hero__image absolute inset-0 w-full h-full flex items-center justify-center text-black/40 text-center text-body-sm font-grotesk px-4">
          <span>Pick featured image</span>
        </div>
        <?php endif; ?>
      </div>

    </div>

    <?php oit_render_wordmark_split($wordmark_primary, $wordmark_accent); ?>

  </div>
</section>
