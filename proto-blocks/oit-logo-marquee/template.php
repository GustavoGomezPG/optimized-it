<?php
/**
 * OIT Logo Marquee
 *
 * Partners carousel: an optional eyebrow + headline above a single row
 * of logos that scrolls continuously and seamlessly. Inspired by the
 * Flowbase "zen-integration-01" pattern -- clean, minimal, lots of
 * whitespace, with the logo strip fading out softly at both edges.
 *
 * Editable logos (the important bit)
 * ----------------------------------
 * The `logos` repeater follows the Proto-Blocks rules so every logo is
 * actually editable on the canvas:
 *   - container carries `data-proto-repeater="logos"`,
 *   - each item carries `data-proto-repeater-item`,
 *   - the `image` sub-field is bound with `data-proto-field="image"` on
 *     the <img> (or on a placeholder element when no image is set yet,
 *     so the slot stays clickable while empty),
 *   - the `link` sub-field is bound with `data-proto-field="link"`; the
 *     wrapping <a href> consumes `link.url` while the bound element
 *     carries `link.text`.
 * On a fresh insert the repeater is seeded with placeholder items in the
 * editor, so the parser has markup to learn the sub-fields from.
 *
 * How the seam works
 * ------------------
 * template.php renders ONE group (the editable repeater). On the front
 * end, view.js clones that group until the strip overflows the viewport,
 * then duplicates the whole filled strip once so a CSS `translateX(-50%)`
 * keyframe loops with no visible jump. Spacing is carried by per-cell
 * horizontal padding (not a flex `gap`) so the gap across the seam
 * matches the gaps inside the strip exactly.
 *
 * Editor vs front end
 * -------------------
 * In the editor preview ($is_preview) view.js does not run, so the block
 * renders the `is-preview` state: the strip wraps, the mask/animation are
 * off, and every logo (plus its link field) stays visible and editable.
 * On the front end the section starts with NO `data-marquee-state`, so
 * the animation is dormant until view.js has built the seamless track and
 * flips the state to "running" -- this avoids a single-group flash, and
 * un-linked logos render as a plain <img> rather than an empty <a>.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$eyebrow = trim((string) ($attributes['eyebrow'] ?? ''));
$title   = trim((string) ($attributes['title'] ?? ''));
$logos   = isset($attributes['logos']) && is_array($attributes['logos']) ? $attributes['logos'] : [];

$speed        = in_array(($attributes['speed'] ?? 'medium'), ['slow', 'medium', 'fast'], true) ? $attributes['speed'] : 'medium';
$direction    = (($attributes['direction'] ?? 'left') === 'right') ? 'right' : 'left';
$pause_hover  = !isset($attributes['pauseOnHover']) || (bool) $attributes['pauseOnHover'];
$edge_fade    = !isset($attributes['edgeFade']) || (bool) $attributes['edgeFade'];
$logo_height  = isset($attributes['logoHeight']) ? max(24, min(120, (int) $attributes['logoHeight'])) : 48;

$is_preview = !isset($block) || $block === null;

// Seed the editor so the block is usable on insert AND the repeater
// parser has at least one item's markup to learn the sub-fields from.
// (A repeater that renders zero items can't be edited.)
if ($is_preview) {
  if ($eyebrow === '') { $eyebrow = 'Trusted partners'; }
  if ($title === '')   { $title = 'Partnering with industry leaders'; }
  if (empty($logos)) {
    $logos = [
      ['id' => 'preview-1', 'image' => ['url' => ''], 'link' => ['url' => '', 'text' => '']],
      ['id' => 'preview-2', 'image' => ['url' => ''], 'link' => ['url' => '', 'text' => '']],
      ['id' => 'preview-3', 'image' => ['url' => ''], 'link' => ['url' => '', 'text' => '']],
      ['id' => 'preview-4', 'image' => ['url' => ''], 'link' => ['url' => '', 'text' => '']],
      ['id' => 'preview-5', 'image' => ['url' => ''], 'link' => ['url' => '', 'text' => '']],
    ];
  }
}

$has_header = ($eyebrow !== '' || $title !== '');

$classes = ['oit-logo-marquee'];
if ($is_preview)                  { $classes[] = 'is-preview'; }
if ($pause_hover && !$is_preview) { $classes[] = 'is-pausable'; }
if ($edge_fade)                   { $classes[] = 'is-faded'; }
if ($direction === 'right')       { $classes[] = 'is-reverse'; }

$wrapper_args = [
  'class'      => implode(' ', $classes),
  'data-speed' => $speed,
  'style'      => '--oit-marquee-logo-h: ' . $logo_height . 'px;',
];
if (!$is_preview) {
  // view.js builds the seamless track, then sets data-marquee-state.
  $wrapper_args['data-proto-animate'] = 'manual';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-logo-marquee__inner max-w-[1440px] mx-auto px-6 lg:px-20 py-12 lg:py-20">

    <?php if ($has_header): ?>
    <div class="oit-logo-marquee__header flex flex-col items-center text-center gap-3 max-w-[760px] mx-auto mb-10 lg:mb-14">
      <p
        data-proto-field="eyebrow"
        class="oit-logo-marquee__eyebrow m-0 font-grotesk font-medium uppercase text-[16px] leading-[1.4] tracking-wide text-brand-red<?php echo ($eyebrow === '') ? ' hidden' : ''; ?>">
        <?php echo esc_html($eyebrow); ?>
      </p>
      <h2
        data-proto-field="title"
        class="oit-logo-marquee__title m-0 font-grotesk font-bold text-[28px] leading-[1.2] text-brand-black lg:text-h4<?php echo ($title === '') ? ' hidden' : ''; ?>">
        <?php echo esc_html($title); ?>
      </h2>
    </div>
    <?php endif; ?>

    <div class="oit-logo-marquee__viewport relative overflow-hidden">
      <div class="oit-logo-marquee__track">
        <div
          data-proto-repeater="logos"
          class="oit-logo-marquee__group m-0 p-0 list-none flex items-center">
          <?php foreach ($logos as $item):
            $image     = is_array($item['image'] ?? null) ? $item['image'] : [];
            $image_url = !empty($image['url']) ? $image['url'] : '';
            $alt_text  = trim((string) ($image['alt'] ?? ''));
            $link      = is_array($item['link'] ?? null) ? $item['link'] : [];
            $link_url  = !empty($link['url']) ? $link['url'] : '';
            $link_tgt  = !empty($link['target']) ? $link['target'] : '';
            $link_rel  = !empty($link['rel']) ? $link['rel'] : ($link_tgt === '_blank' ? 'noopener noreferrer' : '');

            // Front end: drop blank rows entirely. The editor keeps the
            // slot (placeholder branch) so it stays addable/editable.
            if ($image_url === '' && !$is_preview) { continue; }

            // Shared logo markup. `data-proto-field="image"` lives on the
            // rendered element in BOTH branches so the image slot is
            // editable whether or not a file has been chosen yet.
            $img_markup = $image_url !== ''
              ? sprintf(
                  '<img data-proto-field="image" src="%s" alt="%s" loading="lazy" decoding="async" class="oit-logo-marquee__logo block w-auto max-w-none object-contain" />',
                  esc_url($image_url),
                  esc_attr($alt_text)
                )
              : '<div data-proto-field="image" class="oit-logo-marquee__logo oit-logo-marquee__logo--placeholder flex items-center justify-center whitespace-nowrap px-4 text-body-xs text-black/30 font-grotesk">Pick logo</div>';
          ?>
          <div
            data-proto-repeater-item
            class="oit-logo-marquee__cell shrink-0 flex items-center justify-center">

            <?php if ($is_preview): ?>
              <?php // Always wrap in an <a> in the editor so the repeater
                    // offers its built-in URL-only link control (the chain
                    // icon in the item toolbar). The `link` sub-field is
                    // intentionally NOT bound with data-proto-field -- doing
                    // so would mount the inline LinkField with its mandatory
                    // "Enter link text" box and suppress the toolbar button. ?>
              <a
                href="<?php echo esc_url($link_url ?: '#'); ?>"
                class="oit-logo-marquee__link inline-flex items-center justify-center">
                <?php echo $img_markup; ?>
              </a>
            <?php elseif ($link_url !== ''): ?>
              <a
                href="<?php echo esc_url($link_url); ?>"
                <?php if ($link_tgt): ?>target="<?php echo esc_attr($link_tgt); ?>"<?php endif; ?>
                <?php if ($link_rel): ?>rel="<?php echo esc_attr($link_rel); ?>"<?php endif; ?>
                aria-label="<?php echo esc_attr($alt_text ?: 'Partner link'); ?>"
                class="oit-logo-marquee__link inline-flex items-center justify-center transition-opacity duration-200 hover:opacity-70">
                <?php echo $img_markup; ?>
              </a>
            <?php else: ?>
              <?php echo $img_markup; ?>
            <?php endif; ?>

          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>
</section>
