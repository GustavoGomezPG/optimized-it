<?php
/**
 * OIT Mini Cards Grid
 *
 * Compact grid of clickable pill-cards. Each card is icon + bold title +
 * chevron on a soft drop shadow. One card can be flagged with the red
 * highlight variant via the `highlightedIndex` control.
 *
 * The two card shadows (default subtle black, highlight subtle red) live
 * in style.css because Tailwind arbitrary multi-layer shadows with commas
 * and rgba() don't survive the scanner.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$cards = $attributes['cards'] ?? [];
// 1-based: 0 = no highlight, 1+ = card index (we compare against $i + 1).
$highlighted_index = isset($attributes['highlightedIndex']) ? (int) $attributes['highlightedIndex'] : 8;

$show_bottom_text = !empty($attributes['showBottomText']);
$bottom_heading   = !empty($attributes['bottomHeading']) ? $attributes['bottomHeading'] : 'Don\'t see your industry?';
$bottom_body      = !empty($attributes['bottomBody'])    ? $attributes['bottomBody']    : '<p>Optimized IT services businesses and industries across the board. Get in touch with our team, we\'ll learn more about your business and industry and work together to craft a solution that works.</p>';

if (empty($cards)) {
  $cards = [
    ['icon' => null, 'link' => ['url' => '#', 'text' => 'Manufacturing']],
    ['icon' => null, 'link' => ['url' => '#', 'text' => 'Government']],
    ['icon' => null, 'link' => ['url' => '#', 'text' => 'Healthcare']],
    ['icon' => null, 'link' => ['url' => '#', 'text' => 'Education']],
    ['icon' => null, 'link' => ['url' => '#', 'text' => 'Financial Services']],
    ['icon' => null, 'link' => ['url' => '#', 'text' => 'Nonprofit']],
    ['icon' => null, 'link' => ['url' => '#', 'text' => 'Professional Services']],
    ['icon' => null, 'link' => ['url' => '#', 'text' => 'Legal']],
  ];
}

$wrapper_attributes = get_block_wrapper_attributes([
  'class' => 'oit-mini-cards-grid',
]);

$chevron = '<svg class="oit-mini-cards-grid__card-chevron w-3 h-3 shrink-0" viewBox="0 0 14 16" fill="none" aria-hidden="true"><path d="M1 1L7 8L1 15M7 1L13 8L7 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-mini-cards-grid__inner max-w-[1440px] mx-auto px-6 pb-12 lg:px-20 lg:pb-20">

    <ul data-proto-repeater="cards"
        class="oit-mini-cards-grid__grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-9 m-0 p-0 list-none mb-12 lg:mb-16 last:mb-0">
      <?php foreach ($cards as $i => $card):
        $is_highlight = ($highlighted_index > 0 && ($i + 1) === $highlighted_index);
        $link = $card['link'] ?? ['url' => '#', 'text' => ''];
        $href = $link['url'] ?? '#';

        // Default cards adopt the highlight border + red title on hover;
        // the locked highlight card just stays in that state.
        $card_classes = $is_highlight
          ? 'oit-mini-cards-grid__card--highlight border-cta-red text-brand-red'
          : 'oit-mini-cards-grid__card--default border-transparent text-black hover:border-cta-red hover:text-brand-red';
      ?>
      <li data-proto-repeater-item class="list-none">
        <a
          href="<?php echo esc_url($href); ?>"
          <?php echo !empty($link['target']) ? 'target="' . esc_attr($link['target']) . '"' : ''; ?>
          <?php echo !empty($link['rel']) ? 'rel="' . esc_attr($link['rel']) . '"' : ''; ?>
          class="oit-mini-cards-grid__card <?php echo $card_classes; ?> flex items-center gap-4 p-4 rounded-3xl bg-white border-2 no-underline overflow-clip transition-transform hover:-translate-y-0.5">

          <?php if (!empty($card['icon']['url'])): ?>
          <img
            data-proto-field="icon"
            src="<?php echo esc_url($card['icon']['url']); ?>"
            alt=""
            class="oit-mini-cards-grid__card-icon w-12 h-[54px] object-contain shrink-0" />
          <?php else: ?>
          <div
            data-proto-field="icon"
            class="oit-mini-cards-grid__card-icon w-12 h-[54px] rounded-md border border-dashed border-current opacity-30 flex items-center justify-center text-[10px] shrink-0"
            aria-hidden="true">+</div>
          <?php endif; ?>

          <span
            data-proto-field="link"
            class="oit-mini-cards-grid__card-title font-grotesk font-bold text-body-md leading-[1.4]">
            <?php echo esc_html($link['text'] ?? ''); ?>
          </span>

          <?php echo $chevron; ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>

    <?php if ($show_bottom_text): ?>
    <div class="oit-mini-cards-grid__bottom flex flex-col gap-4 max-w-[900px]">
      <h3
        data-proto-field="bottomHeading"
        class="oit-mini-cards-grid__bottom-heading m-0 font-grotesk font-medium text-[20px] leading-[1.4] text-brand-red lg:text-[24px]">
        <?php echo esc_html(wp_strip_all_tags($bottom_heading)); ?>
      </h3>
      <div
        data-proto-field="bottomBody"
        class="oit-mini-cards-grid__bottom-body font-dm font-medium text-body-sm leading-[1.5] text-black [&_p]:m-0 [&_p+p]:mt-3">
        <?php echo wp_kses_post(wpautop($bottom_body)); ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>
