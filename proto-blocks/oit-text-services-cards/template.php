<?php
/**
 * OIT Text + Services Cards
 *
 * Industry-page intro: heading + body copy + red subhead, followed by
 * a grid of service cards and an optional "View All" CTA card. Light
 * theme only.
 *
 * Layout: desktop renders cards in 4-column wrapping grid; mobile
 * stacks them 1-per-row. The "View All" card always sits last in
 * source order so it wraps naturally with the rest of the grid.
 *
 * Background is editor-toggleable between the brand light-grey-to-
 * white gradient (default) and solid white.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$heading       = !empty($attributes['heading']) ? $attributes['heading'] : '<p>Section heading<span class="text-brand-red">.</span></p>';
$body          = !empty($attributes['body'])    ? $attributes['body']    : 'Supporting paragraph(s) for the intro. Two or three paragraphs maximum.';
$subhead       = !empty($attributes['subhead']) ? $attributes['subhead'] : 'Some of the services we offer:';
$cards         = $attributes['cards'] ?? [];
$highlight     = trim((string) ($attributes['highlightWord'] ?? ''));

$show_gradient = $attributes['showGradient'] ?? true;
$show_view_all = !empty($attributes['showViewAll']) || !isset($attributes['showViewAll']);
$view_all_url  = trim((string) ($attributes['viewAllUrl'] ?? ''));
$view_all_lbl  = trim((string) ($attributes['viewAllLabel'] ?? '')) ?: 'View All Services';

// Highlight pass on the heading -- same approach as the other blocks.
if ($highlight !== '') {
    $pos = stripos($heading, $highlight);
    if ($pos !== false) {
        $matched = substr($heading, $pos, strlen($highlight));
        $heading = substr($heading, 0, $pos)
            . '<span class="text-brand-red">' . $matched . '</span>'
            . substr($heading, $pos + strlen($highlight));
    }
}

// Editor preview detection -- skip the data-animate pre-state in the
// canvas iframe so the canvas isn't blank before view.js runs.
$is_preview = !isset($block) || $block === null;

// Background -- matches the showGradient pattern used by
// oit-text-list and oit-title-columns. The Tailwind gradient
// utilities resolve to the theme tokens declared in
// tailwind-theme.css (--color-light-grey = #E6E6E8).
$bg_class = $show_gradient
    ? 'bg-gradient-to-b from-light-grey to-white'
    : 'bg-white';

$wrapper_args = ['class' => 'oit-text-services-cards relative ' . $bg_class];
if (!$is_preview) {
    $wrapper_args['data-animate'] = 'pending';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

// Card chevron -- the trailing arrow on each service card title row.
// Fill is currentColor so it inherits the card's text color (black on
// the white service cards, white on the black View All card).
$card_chevron = '<svg class="oit-text-services-cards__chevron w-4 h-4 shrink-0 transition-transform duration-200 ease-out" viewBox="0 0 14 16" fill="currentColor" aria-hidden="true"><path d="M0 1.88L1.47213 0L7.73818 8L1.47213 16L0 14.12L4.78228 8L0 1.88ZM6.26182 1.88L7.73818 0L14 8L7.73818 16L6.26182 14.12L11.0441 8L6.26182 1.88Z"/></svg>';

// Split the View All label on the first space so the card can render
// it as two stacked lines (matches the Figma "View All / Services"
// wrap). If the label has no space (single word), render it as one
// line.
$view_all_parts = explode(' ', $view_all_lbl, 2);
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-text-services-cards__inner relative max-w-[1440px] mx-auto px-6 lg:px-20 pt-16 lg:pt-24 pb-0">

    <div class="oit-text-services-cards__intro flex flex-col gap-6 lg:gap-8 max-w-[900px]">
      <div
        data-proto-field="heading"
        class="oit-text-services-cards__heading m-0 font-grotesk font-bold text-[28px] leading-[1.3] lg:text-[40px] lg:leading-[1.2] text-black [&_p]:m-0 break-words">
        <?php echo wp_kses_post($heading); ?>
      </div>

      <div
        data-proto-field="body"
        class="oit-text-services-cards__body m-0 font-dm font-medium text-body-sm leading-[1.5] text-black [&_p]:m-0 [&_p+p]:mt-4">
        <?php echo wp_kses_post(wpautop($body)); ?>
      </div>

      <div
        data-proto-field="subhead"
        class="oit-text-services-cards__subhead m-0 mt-2 lg:mt-4 font-grotesk font-bold text-[24px] leading-[1.4] lg:text-[30px] text-brand-red [&_p]:m-0">
        <?php echo wp_kses_post($subhead); ?>
      </div>
    </div>

    <div
      data-proto-repeater="cards"
      class="oit-text-services-cards__grid mt-10 lg:mt-12 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 lg:gap-10">

      <?php foreach ($cards as $card):
        $icon   = $card['icon'] ?? null;
        $link   = $card['link'] ?? [];
        $title  = $link['text']   ?? '';
        $href   = $link['url']    ?? '';
        $target = $link['target'] ?? '';
        $rel    = $link['rel']    ?? '';
        if (trim((string) $href) === '') {
            $href = '#';
        }
      ?>
      <a
        data-proto-repeater-item
        href="<?php echo esc_url($href); ?>"
        <?php if ($target): ?>target="<?php echo esc_attr($target); ?>"<?php endif; ?>
        <?php if ($rel): ?>rel="<?php echo esc_attr($rel); ?>"<?php endif; ?>
        class="oit-text-services-cards__card group bg-white border-2 border-black rounded-3xl p-6 flex flex-col gap-4 min-h-[157px] no-underline text-black transition-colors duration-200 ease-out hover:border-brand-red focus-visible:border-brand-red focus-visible:outline-none">
        <?php if (!empty($icon['url'])): ?>
        <img
          data-proto-field="icon"
          src="<?php echo esc_url($icon['url']); ?>"
          alt="<?php echo esc_attr($icon['alt'] ?? ''); ?>"
          width="50"
          height="65"
          class="oit-text-services-cards__icon block w-[50px] h-[65px] object-contain" />
        <?php else: ?>
        <div data-proto-field="icon"
             class="oit-text-services-cards__icon w-[50px] h-[65px] bg-light-grey rounded-md flex items-center justify-center text-black/40 text-center text-body-xs font-grotesk">
          <span>Icon</span>
        </div>
        <?php endif; ?>

        <div class="oit-text-services-cards__card-foot flex items-center gap-2 w-full">
          <span
            data-proto-field="link"
            class="oit-text-services-cards__card-title font-grotesk font-bold text-[20px] leading-[1.4] text-black">
            <?php echo esc_html($title); ?>
          </span>
          <?php echo $card_chevron; ?>
        </div>
      </a>
      <?php endforeach; ?>

      <?php if ($show_view_all): ?>
      <a
        href="<?php echo esc_url($view_all_url !== '' ? $view_all_url : '#'); ?>"
        class="oit-text-services-cards__card oit-text-services-cards__card--view-all group bg-black border-2 border-black rounded-3xl p-6 flex flex-col justify-end gap-2 min-h-[157px] no-underline text-white transition-colors duration-200 ease-out hover:border-brand-red focus-visible:border-brand-red focus-visible:outline-none">
        <span class="oit-text-services-cards__view-all-label font-grotesk font-bold text-[20px] leading-[1.4] text-white">
          <?php echo esc_html($view_all_parts[0]); ?>
          <?php if (!empty($view_all_parts[1])): ?>
          <br /><?php echo esc_html($view_all_parts[1]); ?>
          <?php endif; ?>
        </span>
        <?php echo $card_chevron; ?>
      </a>
      <?php endif; ?>

    </div>

  </div>
</section>
