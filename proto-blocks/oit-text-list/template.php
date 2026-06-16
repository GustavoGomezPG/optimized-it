<?php
/**
 * OIT Text and List
 *
 * Section that pairs an intro (H2 + body copy) with a labeled grid of short
 * "reasons to believe" items, each capped by a black rule and a brand-red
 * dot terminator. Layout / typography are owned by inline Tailwind
 * utilities; no companion stylesheet is required.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$headline   = $attributes['headline']   ?? 'Meet Your New IT Department';
$body       = $attributes['body']       ?? '<p>Optimized IT was born out of a desire to help businesses think forward when it comes to their IT solutions without worrying about overhead or trying to keep up with every new trend. We handle your entire network and support your business so you can focus more on what you love to do and less on technology.</p><p>We can manage or co-manage your platform with a secure and stable infrastructure.</p>';
$list_label = $attributes['listLabel']  ?? 'Your Business Can:';
$items      = $attributes['items']      ?? [];

if (empty($items)) {
  $items = [
    ['title' => 'Improve productivity', 'content' => '<p>Streamline workflows and remove the IT friction that slows your team down.</p>'],
    ['title' => 'Increase uptime performance', 'content' => '<p>Proactive monitoring keeps systems online so your business keeps moving.</p>'],
    ['title' => 'Make the right IT investments', 'content' => '<p>Spend on technology that maps to outcomes, not guesswork.</p>'],
    ['title' => 'Deploy continuity strategies', 'content' => '<p>Backups and disaster recovery that get you running again fast.</p>'],
    ['title' => 'Leverage the Cloud', 'content' => '<p>Scale securely with cloud infrastructure tuned to your needs.</p>'],
    ['title' => 'Network security best practices', 'content' => '<p>Layered defenses that protect your data and your reputation.</p>'],
  ];
}

$show_gradient = $attributes['showGradient'] ?? true;
$bg_class = $show_gradient ? 'bg-gradient-to-b from-light-grey to-white' : 'bg-white';

$accordion_layout     = $attributes['accordionLayout'] ?? false;
$accordion_single     = $attributes['accordionSingle'] ?? true;
$accordion_two_col    = $attributes['accordionTwoColumns'] ?? false;
$accordion_first_open = $attributes['accordionFirstOpen'] ?? true;

// One column (default) stacks rows; two columns lay out 2-up on desktop.
// items-start keeps each cell top-aligned so an expanded panel doesn't
// stretch its row-neighbour's divider.
$accordion_grid_class = $accordion_two_col
  ? 'grid grid-cols-1 md:grid-cols-2 gap-x-12 items-start max-w-[1100px]'
  : 'flex flex-col max-w-[900px]';

$wrapper_attributes = get_block_wrapper_attributes([
  'class' => "oit-text-list {$bg_class}",
]);
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="max-w-[1440px] mx-auto flex flex-col gap-10 py-16 px-6 lg:p-20">

    <div class="oit-text-list__intro flex flex-col gap-5 max-w-[900px]">
      <h2
        data-proto-field="headline"
        class="oit-text-list__headline m-0 font-grotesk font-bold text-[28px] leading-[1.2] text-black lg:text-h4">
        <?php echo esc_html($headline); ?>
      </h2>
      <div
        data-proto-field="body"
        class="oit-text-list__body font-dm font-medium text-body-sm leading-[1.5] text-black [&_p]:m-0 [&_p+p]:mt-4">
        <?php echo wp_kses_post($body); ?>
      </div>
    </div>

    <h3
      data-proto-field="listLabel"
      class="oit-text-list__label m-0 font-grotesk font-bold text-[24px] leading-[1.4] text-brand-red lg:text-[30px]">
      <?php echo esc_html($list_label); ?>
    </h3>

    <?php if ($accordion_layout): ?>
    <?php $acc_uid = wp_unique_id('oit-acc-'); ?>
    <div
      data-proto-repeater="items"
      data-acc-single="<?php echo $accordion_single ? '1' : '0'; ?>"
      data-acc-first-open="<?php echo $accordion_first_open ? '1' : '0'; ?>"
      class="oit-text-list__accordion <?php echo $accordion_grid_class; ?> m-0 p-0">
      <?php foreach ($items as $i => $item): ?>
      <?php $panel_id = $acc_uid . '-' . $i; $is_first = ($accordion_first_open && $i === 0); ?>
      <div
        data-proto-repeater-item
        class="oit-text-list__acc-item border-b border-grey">
        <button
          type="button"
          class="oit-text-list__acc-trigger w-full flex items-center justify-between gap-4 py-5 m-0 bg-transparent border-0 text-left cursor-pointer appearance-none"
          id="<?php echo esc_attr($panel_id . '-btn'); ?>"
          aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
          aria-controls="<?php echo esc_attr($panel_id); ?>">
          <span
            data-proto-field="title"
            class="oit-text-list__acc-title m-0 font-grotesk font-bold text-body-md leading-[1.4] <?php echo $is_first ? 'text-brand-red' : 'text-black'; ?>">
            <?php echo esc_html($item['title'] ?? ''); ?>
          </span>
          <svg
            class="oit-text-list__acc-chevron shrink-0 w-5 h-5 text-brand-red transition-transform duration-300 <?php echo $is_first ? 'rotate-180' : ''; ?>"
            viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M5 7.5 10 12.5 15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <?php // No collapsed height in PHP: with JS off, panels render open (readable fallback). view.js collapses non-first panels on the frontend. ?>
        <div
          id="<?php echo esc_attr($panel_id); ?>"
          role="region"
          aria-labelledby="<?php echo esc_attr($panel_id . '-btn'); ?>"
          class="oit-text-list__acc-panel overflow-hidden">
          <div
            data-proto-field="content"
            class="oit-text-list__acc-panel-inner pb-5 font-dm font-medium text-body-sm leading-[1.5] text-black [&_p]:m-0 [&_p+p]:mt-4">
            <?php echo wp_kses_post($item['content'] ?? ''); ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <ul
      data-proto-repeater="items"
      class="oit-text-list__items flex flex-wrap gap-9 m-0 p-0 list-none">
      <?php foreach ($items as $item): ?>
      <li
        data-proto-repeater-item
        class="oit-text-list__item grow basis-[160px] min-w-0 flex flex-col justify-between gap-3 min-h-[71px] list-none">
        <p
          data-proto-field="title"
          class="oit-text-list__item-title m-0 font-grotesk font-bold text-body-md leading-[1.4] text-black">
          <?php echo esc_html($item['title'] ?? ''); ?>
        </p>
        <div class="oit-text-list__item-rule relative w-full h-1.5" aria-hidden="true">
          <span class="absolute inset-x-0 top-1/2 -translate-y-1/2 h-0.5 bg-grey"></span>
          <span class="absolute right-0 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-grey"></span>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>

  </div>
</section>
