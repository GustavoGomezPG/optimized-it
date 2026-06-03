<?php
/**
 * OIT Logo Gallery
 *
 * Partner / technology logo wall. Headline + supporting paragraph at
 * the top, followed by a responsive grid of logo cells. Each cell is
 * the same height (driven by the `cellHeight` control) and the logo
 * image inside is object-contain, so logos with very different aspect
 * ratios all sit on the same baseline and read as a coherent wall.
 *
 * Layout uses CSS Grid `repeat(auto-fit, minmax(120px, 1fr))` so the
 * column count adapts to viewport width without per-breakpoint rules.
 * Mobile naturally fits ~2 cells per row; desktop fits 8-9.
 *
 * Each repeater item exposes an optional `link` -- when set, the cell
 * wraps the image in an <a>. Empty link renders a plain <img>.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$title       = !empty($attributes['title']) ? $attributes['title'] : 'Our Technology Partners';
$intro       = !empty($attributes['intro']) ? $attributes['intro'] : '<p>We\'re committed to providing unrivaled service to our customers. That means we only work with providers that help us go above and beyond on their behalf. These are just a few of the companies we introduce our clients to.</p>';
$logos       = isset($attributes['logos']) && is_array($attributes['logos']) ? $attributes['logos'] : [];
$cell_height = isset($attributes['cellHeight']) ? max(40, min(160, (int) $attributes['cellHeight'])) : 84;

$is_preview = !isset($block) || $block === null;

$wrapper_args = ['class' => 'oit-logo-gallery'];
if (!$is_preview) {
  $wrapper_args['data-proto-animate'] = 'manual';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-logo-gallery__inner max-w-[1440px] mx-auto px-6 lg:px-20 pb-12 lg:pb-20">

    <div class="oit-logo-gallery__header flex flex-col gap-4 max-w-[900px] mb-10 lg:mb-16">
      <h2
        data-proto-field="title"
        class="oit-logo-gallery__title m-0 font-grotesk font-bold text-[28px] leading-[1.2] text-black lg:text-h4">
        <?php echo esc_html(wp_strip_all_tags($title)); ?>
      </h2>
      <div
        data-proto-field="intro"
        class="oit-logo-gallery__intro font-dm font-medium text-body-sm leading-[1.5] text-black [&_p]:m-0 [&_p+p]:mt-3">
        <?php echo wp_kses_post(wpautop($intro)); ?>
      </div>
    </div>

    <ul
      data-proto-repeater="logos"
      class="oit-logo-gallery__grid m-0 p-0 list-none"
      style="--oit-logo-cell-h: <?php echo (int) $cell_height; ?>px;">
      <?php foreach ($logos as $item):
        $image      = $item['image'] ?? null;
        $image_url  = !empty($image['url']) ? $image['url'] : '';
        $alt_text   = trim((string) ($item['alt'] ?? ($image['alt'] ?? '')));
        $link       = $item['link'] ?? null;
        $link_url   = !empty($link['url']) ? $link['url'] : '';
        $link_blank = !empty($link['target']) && $link['target'] === '_blank';

        // Skip empty rows entirely on the front end -- the editor still
        // shows the slot via the placeholder branch below.
        if ($image_url === '' && !$is_preview) { continue; }

        $img_markup = $image_url !== ''
          ? sprintf(
              '<img src="%s" alt="%s" loading="lazy" decoding="async" class="oit-logo-gallery__logo block max-h-full max-w-full object-contain" />',
              esc_url($image_url),
              esc_attr($alt_text)
            )
          : '<div class="oit-logo-gallery__logo block max-h-full max-w-full flex items-center justify-center text-body-xs text-black/30 font-grotesk">Pick logo</div>';
      ?>
      <li
        data-proto-repeater-item
        class="oit-logo-gallery__cell list-none flex items-center justify-center">

        <?php if ($link_url): ?>
        <a
          href="<?php echo esc_url($link_url); ?>"
          <?php if ($link_blank): ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
          aria-label="<?php echo esc_attr($alt_text ?: 'Partner link'); ?>"
          class="oit-logo-gallery__link block w-full h-full flex items-center justify-center transition-opacity duration-200 hover:opacity-70">
          <?php echo $img_markup; ?>
        </a>
        <?php else: ?>
          <?php echo $img_markup; ?>
        <?php endif; ?>

      </li>
      <?php endforeach; ?>
    </ul>

  </div>
</section>
