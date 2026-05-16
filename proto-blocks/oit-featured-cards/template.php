<?php
/**
 * OIT Featured Cards
 *
 * Responsive grid of bordered cards. One card index can be flagged via
 * the `highlightedIndex` control to render in the red variant (filled
 * brand-red bg, white text, white action button, red glow).
 *
 * Pair with oit-call-to-action above for the intro headline + body + CTA.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$cards = $attributes['cards'] ?? [];
// 1-based: 0 = no highlight, 1+ = card index (we compare against $i + 1).
$highlighted_index = isset($attributes['highlightedIndex']) ? (int) $attributes['highlightedIndex'] : 6;

if (empty($cards)) {
  $cards = [
    ['icon' => null, 'title' => 'Managed IT Services',     'description' => 'We own and manage your technology so you can get back to running your business.', 'link' => ['url' => '#']],
    ['icon' => null, 'title' => 'Co-Managed IT Services',  'description' => 'Already have IT staff? We\'ll fill in the gaps, cover the hours, and give your team the support they need.', 'link' => ['url' => '#']],
    ['icon' => null, 'title' => 'Cybersecurity',            'description' => 'Threats are getting smarter. Your security needs to keep up—and we have an entire team making sure it does.', 'link' => ['url' => '#']],
    ['icon' => null, 'title' => 'Cloud & Modern Office',    'description' => 'We move your business to the cloud, manage your environment, and make sure your team can work from anywhere—securely.', 'link' => ['url' => '#']],
    ['icon' => null, 'title' => 'Strategic IT/vCIO',        'description' => 'Technology without strategy is expensive. We bring executive-level planning to your growing business needs.', 'link' => ['url' => '#']],
    ['icon' => null, 'title' => 'Data Protection',          'description' => 'Ransomware. Hardware failure. Human error. When something goes wrong, the businesses that survive are the ones that have a plan.', 'link' => ['url' => '#']],
  ];
}

$wrapper_attributes = get_block_wrapper_attributes([
  'class' => 'oit-featured-cards',
]);
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-featured-cards__inner max-w-[1440px] mx-auto px-6 pb-12 lg:px-20 lg:pb-20">

    <ul data-proto-repeater="cards"
        class="oit-featured-cards__grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-9 m-0 p-0 list-none">
      <?php foreach ($cards as $i => $card):
        $is_highlight = ($highlighted_index > 0 && ($i + 1) === $highlighted_index);
        $card_link = $card['link'] ?? ['url' => '#'];
        $card_url  = $card_link['url'] ?? '#';
        $card_title = $card['title'] ?? '';
        $action_label = $card_title ? sprintf('Learn more about %s', $card_title) : 'Learn more';

        // Default cards adopt the red look on hover; the locked highlight
        // card just stays in the red state.
        $card_classes = $is_highlight
          ? 'bg-brand-red border-brand-red text-white shadow-red-glow'
          : 'bg-white border-black text-black hover:bg-brand-red hover:border-brand-red hover:text-white hover:shadow-red-glow';

        $action_classes = $is_highlight
          ? 'bg-white text-brand-red'
          : 'bg-brand-red text-white group-hover:bg-white group-hover:text-brand-red';

        // Icon is invert-1 (black -> white) on the highlight card and on
        // hover for default cards, so a single black-on-white icon asset
        // works for both states. transition-[filter] smooths the swap.
        $icon_classes = $is_highlight
          ? 'invert'
          : 'group-hover:invert';
      ?>
      <li data-proto-repeater-item
          class="oit-featured-cards__card oit-featured-cards__card<?php echo $is_highlight ? '--highlight' : '--default'; ?> group relative flex flex-col gap-4 p-7 lg:p-9 min-h-[255px] rounded-3xl border-2 list-none overflow-clip transition-colors duration-300 <?php echo $card_classes; ?>">

        <?php if (!empty($card['icon']['url'])): ?>
        <img data-proto-field="icon"
             src="<?php echo esc_url($card['icon']['url']); ?>"
             alt=""
             class="oit-featured-cards__card-icon w-12 h-12 object-contain transition-[filter] duration-300 <?php echo $icon_classes; ?>" />
        <?php else: ?>
        <div data-proto-field="icon"
             class="oit-featured-cards__card-icon w-12 h-12 rounded-md border border-dashed border-current opacity-30 flex items-center justify-center text-[10px]"
             aria-hidden="true">+</div>
        <?php endif; ?>

        <div class="oit-featured-cards__card-content mt-auto pr-14">
          <p data-proto-field="title"
             class="oit-featured-cards__card-title font-grotesk font-bold text-body-md leading-[1.4] m-0 mb-2">
            <?php echo esc_html($card_title); ?>
          </p>
          <p data-proto-field="description"
             class="oit-featured-cards__card-description font-dm font-medium text-body-xs leading-[1.5] m-0">
            <?php echo esc_html($card['description'] ?? ''); ?>
          </p>
        </div>

        <a
          href="<?php echo esc_url($card_url); ?>"
          <?php echo !empty($card_link['target']) ? 'target="' . esc_attr($card_link['target']) . '"' : ''; ?>
          <?php echo !empty($card_link['rel']) ? 'rel="' . esc_attr($card_link['rel']) . '"' : ''; ?>
          aria-label="<?php echo esc_attr($action_label); ?>"
          class="oit-featured-cards__card-action absolute bottom-6 right-6 lg:bottom-7 lg:right-7 inline-flex items-center justify-center w-10 h-10 rounded-full no-underline transition-[colors,transform] duration-300 hover:scale-110 <?php echo $action_classes; ?>">
          <svg class="w-[14px] h-[16px] shrink-0" viewBox="0 0 14 16" fill="none" aria-hidden="true"><path d="M1 1L7 8L1 15M7 1L13 8L7 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>

      </li>
      <?php endforeach; ?>
    </ul>

  </div>
</section>
