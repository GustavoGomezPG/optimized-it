<?php
/**
 * OIT Link Cards
 *
 * Row of clickable white cards on the dark site background. Each card:
 *   - Red uppercase eyebrow label
 *   - Bold heading paragraph
 *   - Trailing double-chevron rendered inline so it flows with the
 *     heading text and lands right after the final character (wraps
 *     with the text if the heading breaks across lines, matching the
 *     Figma node 20:578).
 *
 * Layout: 1 col mobile -> 2 col md -> 3 col lg. Cards stretch to the
 * tallest sibling via `h-full` so the row reads as a single shelf.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$cards = $attributes['cards'] ?? [];

if (empty($cards)) {
  $cards = [
    [
      'label' => 'SOLUTIONS',
      'link'  => ['url' => '#', 'text' => 'Secure your business with our suite of IT solutions.'],
    ],
    [
      'label' => 'LOCATIONS',
      'link'  => ['url' => '#', 'text' => 'Find an OptimizedIT office near you.'],
    ],
    [
      'label' => 'ABOUT OPTIMIZEDIT',
      'link'  => ['url' => '#', 'text' => 'Learn more about our 65+ year history managing IT.'],
    ],
  ];
}

$is_preview = !isset($block) || $block === null;

$wrapper_args = ['class' => 'oit-link-cards bg-black'];
if (!$is_preview) {
  $wrapper_args['data-proto-animate'] = 'manual';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

// Inline double-chevron. inline-block so it participates in text flow
// (wraps with the heading at line breaks); align-baseline so it sits on
// the type baseline instead of the line-box bottom; fill currentColor
// so it inherits the heading color (black on default cards).
$chevron = '<svg class="oit-link-cards__chevron w-[13px] h-3 inline-block align-baseline ml-2 fill-current shrink-0" viewBox="0 0 13 12" fill="currentColor" aria-hidden="true"><path d="M0 1.41L1.36689 0L7.18345 6L1.36689 12L0 10.59L4.43997 6L0 1.41ZM5.81656 1.41L7.18345 0L13 6L7.18345 12L5.81656 10.59L10.2565 6L5.81656 1.41Z"/></svg>';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-link-cards__inner max-w-[1440px] mx-auto px-6 lg:px-20 pt-12 lg:pt-20">

    <ul data-proto-repeater="cards"
        class="oit-link-cards__grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-16 m-0 p-0 list-none">
      <?php foreach ($cards as $card):
        $label    = $card['label'] ?? '';
        $link     = $card['link']  ?? ['url' => '#', 'text' => ''];
        $href     = $link['url']   ?? '#';
        $heading  = $link['text']  ?? '';
        // Strip any HTML that leaked into the plain-text fields (e.g.
        // the editor's <strong> wrapping); both render through esc_html
        // anyway, but stripping first means we don't show "<STRONG>X
        // </STRONG>" literally.
        $label    = wp_strip_all_tags($label);
        $heading  = wp_strip_all_tags($heading);
      ?>
      <li data-proto-repeater-item class="list-none">
        <a
          href="<?php echo esc_url($href); ?>"
          <?php echo !empty($link['target']) ? 'target="' . esc_attr($link['target']) . '"' : ''; ?>
          <?php echo !empty($link['rel']) ? 'rel="' . esc_attr($link['rel']) . '"' : ''; ?>
          class="oit-link-cards__card group flex flex-col gap-3 p-6 lg:p-9 bg-white rounded-3xl h-full min-h-[160px] no-underline transition-transform hover:-translate-y-1">

          <span
            data-proto-field="label"
            class="oit-link-cards__label font-grotesk font-bold text-body-sm leading-[1.3] text-brand-red uppercase">
            <?php echo esc_html($label); ?>
          </span>

          <p class="oit-link-cards__heading m-0 font-grotesk font-bold text-[20px] leading-[1.4] text-black"><span data-proto-field="link" class="oit-link-cards__heading-text"><?php echo esc_html($heading); ?></span><?php echo $chevron; ?></p>

        </a>
      </li>
      <?php endforeach; ?>
    </ul>

  </div>
</section>
