<?php
/**
 * OIT Featured Section
 *
 * Rounded card with a red->black gradient, image on the left, headline +
 * numbered step list + CTA on the right. Sits on the brand `red-glow`
 * shadow so the card visually floats off the page.
 *
 * The 17-stop gradient is owned by style.css (an inline Tailwind value
 * with that many color stops, commas, parens etc. doesn't survive the
 * scanner reliably). Everything else is inline Tailwind.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$image    = $attributes['image']    ?? null;
$headline = $attributes['headline'] ?? '<p>How to get started with<br>Optimized IT<span class="text-brand-red">:</span></p>';
$steps    = $attributes['steps']    ?? [];
$cta      = $attributes['cta']      ?? ['url' => '#', 'text' => 'SCHEDULE AN ASSESSMENT'];
$show_image = $attributes['showImage'] ?? true;

if (empty($steps)) {
  $steps = [
    ['text' => 'Schedule a conversation'],
    ['text' => 'We\'ll gather some information'],
    ['text' => 'We\'ll meet with your team and go over a proposal'],
    ['text' => 'Leave the IT up to us'],
  ];
}

$wrapper_attributes = get_block_wrapper_attributes([
  'class' => 'oit-featured-section',
]);

$cta_arrow = '<svg class="w-3 h-3 shrink-0" viewBox="0 0 14 16" fill="none" aria-hidden="true"><path d="M1 1L7 8L1 15M7 1L13 8L7 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-featured-section__inner max-w-[1440px] mx-auto px-6 pb-12 lg:px-20 lg:pb-16">

    <div class="oit-featured-section__card oit-featured-section__card--gradient relative rounded-3xl overflow-clip shadow-red-glow text-white">

      <div class="oit-featured-section__grid grid grid-cols-1 gap-8 p-6 lg:grid-cols-2 lg:gap-12 lg:p-10 lg:items-center">

        <?php if ($show_image): ?>
        <div class="oit-featured-section__media w-full">
          <?php if ($image && !empty($image['url'])): ?>
          <img
            data-proto-field="image"
            src="<?php echo esc_url($image['url']); ?>"
            alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
            class="oit-featured-section__image block w-full h-auto rounded-3xl aspect-[610/400] object-cover bg-grey" />
          <?php else: ?>
          <div data-proto-field="image"
               class="oit-featured-section__image flex items-center justify-center w-full h-auto rounded-3xl aspect-[610/400] bg-grey text-white/70 font-grotesk text-body-xs">
            <span>Click to select image</span>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="oit-featured-section__content flex flex-col gap-6 lg:gap-7">

          <div
            data-proto-field="headline"
            class="oit-featured-section__headline m-0 max-w-[460px] font-grotesk font-bold text-[24px] leading-[1.4] text-white lg:text-[30px] [&_p]:m-0 [&_p+p]:mt-1">
            <?php echo wp_kses_post($headline); ?>
          </div>

          <ol
            data-proto-repeater="steps"
            class="oit-featured-section__steps flex flex-col gap-2.5 m-0 p-0 list-none">
            <?php foreach ($steps as $i => $step): ?>
            <li
              data-proto-repeater-item
              class="oit-featured-section__step flex items-center gap-2.5 list-none">
              <span class="oit-featured-section__step-number font-grotesk font-bold text-[18px] leading-[1.4] text-brand-red shrink-0"><?php echo (int)($i + 1); ?>.</span>
              <span
                data-proto-field="text"
                class="oit-featured-section__step-text font-grotesk font-medium text-body-sm leading-[1.4] text-white">
                <?php echo esc_html($step['text'] ?? ''); ?>
              </span>
            </li>
            <?php endforeach; ?>
          </ol>

          <a
            href="<?php echo esc_url($cta['url'] ?? '#'); ?>"
            <?php echo !empty($cta['target']) ? 'target="' . esc_attr($cta['target']) . '"' : ''; ?>
            <?php echo !empty($cta['rel']) ? 'rel="' . esc_attr($cta['rel']) . '"' : ''; ?>
            class="oit-featured-section__cta self-start inline-flex items-center gap-3 px-5 py-2.5 rounded-full bg-cta-red hover:bg-cta-red-700 text-white no-underline font-grotesk font-medium text-body-sm leading-[1.3] uppercase whitespace-nowrap transition-colors">
            <span data-proto-field="cta"><?php echo esc_html($cta['text'] ?? 'SCHEDULE AN ASSESSMENT'); ?></span>
            <?php echo $cta_arrow; ?>
          </a>

        </div>

      </div>
    </div>

  </div>
</section>
