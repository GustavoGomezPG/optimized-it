<?php
/**
 * OIT CTA
 *
 * One-line conversion callout. A wide rounded pill with the brand-black
 * to dark-red gradient surface, a single-line white headline on the
 * left, and a solid red pill button with chevron on the right. Designed
 * to sit between content sections as a quick nudge to action.
 *
 * No body paragraph, no corner arrow -- if you need those, reach for
 * oit-call-to-action instead.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$headline = !empty($attributes['headline']) ? $attributes['headline'] : 'Ready to transform your IT with OptimizedIT?';
$cta = is_array($attributes['cta'] ?? null) ? $attributes['cta'] : [];
$cta_url = !empty($cta['url']) ? $cta['url'] : '#';
$cta_text = !empty($cta['text']) ? $cta['text'] : 'SCHEDULE A CONSULTATION';
$cta_target = !empty($cta['target']) ? ' target="' . esc_attr($cta['target']) . '"' : '';
$cta_rel = !empty($cta['rel']) ? ' rel="' . esc_attr($cta['rel']) . '"' : '';

// Slate variant: solid slate surface + white button (white bg, red
// border, black text). Default is the red gradient surface + red button.
$slate = !empty($attributes['slateVariant']);
$surface_classes = $slate
  ? 'bg-slate'
  : 'bg-linear-to-tl from-black to-brand-red-700';
$button_classes = $slate
  ? 'bg-white hover:bg-light-grey text-brand-black border border-brand-red'
  : 'bg-cta-red hover:bg-cta-red-700 text-white border border-brand-red';

$is_preview = !isset($block) || $block === null;

$wrapper_args = ['class' => 'oit-cta'];
if (!$is_preview) {
  $wrapper_args['data-proto-animate'] = 'manual';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

// Chevron matches the one used by other CTA buttons across the theme
// so the visual language is consistent. fill="currentColor" lets it
// pick up the white text color from the button.
$chevron = '<svg class="w-[13px] h-3 shrink-0" viewBox="0 0 13 12" fill="currentColor" aria-hidden="true"><path d="M0 1.41L1.36689 0L7.18345 6L1.36689 12L0 10.59L4.43997 6L0 1.41ZM5.81656 1.41L7.18345 0L13 6L7.18345 12L5.81656 10.59L10.2565 6L5.81656 1.41Z"/></svg>';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-cta__inner max-w-[1440px] mx-auto px-6 lg:px-20 pb-12 lg:pb-20">

    <div
      class="oit-cta__card relative rounded-3xl overflow-hidden shadow-red-glow <?php echo esc_attr($surface_classes); ?> p-6 lg:p-12">

      <div class="oit-cta__row flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between lg:gap-10">

        <h2 data-proto-field="headline"
          class="oit-cta__headline m-0 font-grotesk font-bold text-[22px] leading-[1.3] text-white lg:text-[28px] lg:leading-[1.3] max-w-[700px]">
          <?php echo esc_html($headline); ?>
        </h2>

        <a href="<?php echo esc_url($cta_url); ?>" <?php echo $cta_target;
           echo $cta_rel; ?>
          class="oit-cta__button self-start shrink-0 inline-flex items-center gap-3 px-5 py-2.5 rounded-full <?php echo esc_attr($button_classes); ?> no-underline font-grotesk font-medium text-body-sm leading-[1.3] uppercase whitespace-nowrap transition-colors lg:self-auto">
          <span data-proto-field="cta"><?php echo esc_html($cta_text); ?></span>
          <?php echo $chevron; ?>
        </a>

      </div>

    </div>

  </div>
</section>