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
    ['title' => 'Improve productivity'],
    ['title' => 'Increase uptime performance'],
    ['title' => 'Make the right IT investments'],
    ['title' => 'Deploy continuity strategies'],
    ['title' => 'Leverage the Cloud'],
    ['title' => 'Network security best practices'],
  ];
}

$show_gradient = $attributes['showGradient'] ?? true;
$bg_class = $show_gradient ? 'bg-gradient-to-b from-light-grey to-white' : 'bg-white';

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

    <ul
      data-proto-repeater="items"
      class="oit-text-list__items flex flex-wrap gap-9 m-0 p-0 list-none">
      <?php foreach ($items as $item): ?>
      <li
        data-proto-repeater-item
        class="oit-text-list__item grow basis-[160px] min-w-0 max-w-[240px] flex flex-col justify-between gap-3 min-h-[71px] list-none">
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

  </div>
</section>
