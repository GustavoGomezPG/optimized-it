<?php
/**
 * OIT Hero
 *
 * Two-column at lg+ (text left, image right and overflowing toward the
 * gutter), single-column on mobile (text first, image rounded below).
 *
 * Layout/typography use inline Tailwind utilities (the block declares
 * `useTailwind: true`). The BEM class names are kept as JS hooks for
 * view.js and as anchor points for the few things that can't be inlined:
 *   - the 17-stop linear gradient (.oit-hero--gradient in style.css)
 *   - the data-proto-animate (manual) pre-state used by the GSAP reveal
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$eyebrow = $attributes['eyebrow'] ?? 'Optimized IT will fill in the gaps or run your tech stack for you.';
$heading = $attributes['heading'] ?? 'We handle the IT<span class="oit-hero__accent text-brand-red">.</span><br>You run your business<span class="oit-hero__accent text-brand-red">.</span>';
$cta     = $attributes['cta']     ?? ['url' => '#', 'text' => 'EXPLORE OUR SOLUTIONS'];
$image   = $attributes['image']   ?? null;

$show_gradient   = $attributes['showGradient']   ?? true;
$show_decoration = $attributes['showDecoration'] ?? true;

// Solid uses Tailwind bg-black (resolves to brand --color-black #1A0303);
// gradient still needs a CSS rule for the 17-stop linear-gradient.
$bg_class = $show_gradient ? 'oit-hero--gradient' : 'bg-black';

// In editor preview $block is null -- view.js / GSAP never run there, so
// applying the pre-hide attribute would leave the hero permanently blank.
$is_preview = !isset($block) || $block === null;

$section_classes = trim(
  "oit-hero {$bg_class} relative isolate overflow-clip text-white "
  . "max-h-[80vh] lg:h-[640px] lg:max-h-[640px]"
);

$wrapper_args = ['class' => $section_classes];
if (!$is_preview) {
  $wrapper_args['data-proto-animate'] = 'manual';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

$cta_arrow = '<svg class="oit-hero__cta-arrow w-[13px] h-[13px] shrink-0" viewBox="0 0 12 13" fill="none" aria-hidden="true"><path d="M1 6.5H11M11 6.5L6 1.5M11 6.5L6 11.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

$circuit_url        = get_stylesheet_directory_uri() . '/assets/img/circuit.svg';
$circuit_lottie_url = get_stylesheet_directory_uri() . '/assets/lottie/circuit.json';
?>

<section <?php echo $wrapper_attributes; ?>>

  <?php if ($show_decoration): ?>
  <div
    class="oit-hero__decoration hidden lg:block lg:absolute lg:top-[90px] lg:right-0 lg:w-[614px] lg:max-w-[50%] lg:aspect-[610/518] lg:opacity-60 lg:pointer-events-none lg:z-0 lg:select-none"
    data-lottie-url="<?php echo esc_url($circuit_lottie_url); ?>"
    aria-hidden="true">
    <!-- Static SVG fallback. view.js replaces this with the Lottie player
         on the front end; editor preview / no-JS / reduced-motion paths
         keep the SVG visible. -->
    <img
      src="<?php echo esc_url($circuit_url); ?>"
      alt=""
      class="block w-full h-full object-contain"
      loading="lazy" />
  </div>
  <?php endif; ?>

  <div class="oit-hero__inner relative max-w-[1440px] mx-auto pt-[140px] px-6 pb-0 lg:h-full lg:pt-20 lg:px-20 lg:pb-0">

    <div class="oit-hero__grid relative z-10 grid grid-cols-1 gap-8 items-center lg:grid-cols-[490px_1fr] lg:gap-12 lg:items-stretch lg:h-full lg:min-h-0">

      <div class="oit-hero__content flex flex-col gap-5 max-w-[490px] lg:self-center">
        <p data-proto-field="eyebrow"
           class="oit-hero__eyebrow m-0 font-grotesk font-medium text-body-md text-brand-red leading-[1.3] lg:text-body-lg lg:leading-[1.4]">
          <?php echo esc_html($eyebrow); ?>
        </p>

        <div data-proto-field="heading"
             class="oit-hero__heading m-0 font-grotesk font-bold text-[36px] leading-[1.2] text-white break-words lg:text-[56px]">
          <?php echo wp_kses_post($heading); ?>
        </div>

        <a
          href="<?php echo esc_url($cta['url'] ?? '#'); ?>"
          <?php echo !empty($cta['target']) ? 'target="' . esc_attr($cta['target']) . '"' : ''; ?>
          <?php echo !empty($cta['rel']) ? 'rel="' . esc_attr($cta['rel']) . '"' : ''; ?>
          class="oit-hero__cta self-start inline-flex items-center gap-3 px-5 py-2.5 bg-cta-red hover:bg-cta-red-700 text-white no-underline font-grotesk font-medium text-body-sm leading-[1.3] uppercase whitespace-nowrap rounded-full transition-colors">
          <span data-proto-field="cta"><?php echo esc_html($cta['text'] ?? 'EXPLORE OUR SOLUTIONS'); ?></span>
          <?php echo $cta_arrow; ?>
        </a>
      </div>

      <div class="oit-hero__media relative w-full lg:self-stretch lg:flex lg:items-end lg:justify-end lg:min-h-0 lg:-mr-20">
        <?php if ($image && !empty($image['url'])): ?>
        <img
          data-proto-field="image"
          src="<?php echo esc_url($image['url']); ?>"
          alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
          class="oit-hero__image block w-full h-auto rounded-3xl aspect-[3/2] object-contain object-bottom bg-transparent lg:rounded-none lg:aspect-square lg:w-auto lg:h-full lg:max-w-full" />
        <?php else: ?>
        <div data-proto-field="image"
             class="oit-hero__image oit-hero__image--placeholder flex items-center justify-center font-grotesk text-body-xs text-grey w-full h-auto rounded-3xl aspect-[3/2] lg:rounded-none lg:aspect-square lg:w-full lg:h-full lg:bg-white/5 lg:border lg:border-dashed lg:border-white/20">
          <span>Click to select hero image</span>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>
