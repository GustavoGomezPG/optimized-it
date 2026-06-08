<?php
/**
 * OIT Single Testimonial
 *
 * Centered card on a brand-black section. Two circuit decorations sit on the
 * left and right edges of the section (the same theme circuit.svg used in
 * the hero, mirrored differently per side so the lines flow inward toward
 * the card). The card has a brand-red quote bubble overlapping its top edge.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$quote       = $attributes['quote']       ?? '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>';
$attribution = $attributes['attribution'] ?? 'Name, Position, Company';
$show_circuit = $attributes['showCircuit'] ?? true;

// Review source: "manual" (single quote above) or "smileback" (live carousel).
$source      = $attributes['source'] ?? 'manual';
$max_reviews = isset($attributes['maxReviews']) ? max(1, (int) $attributes['maxReviews']) : 12;
$show_score  = $attributes['showScore'] ?? true;
$autoplay_ms = (isset($attributes['autoplaySeconds']) ? max(0, (int) $attributes['autoplaySeconds']) : 6) * 1000;
$order           = $attributes['order'] ?? 'newest';
$require_company = !empty($attributes['requireCompany']);
$min_length      = isset($attributes['minLength']) ? max(0, (int) $attributes['minLength']) : 0;

$sb_reviews = [];
$sb_score   = null;
$sb_count   = 0;
if ($source === 'smileback' && function_exists('oit_smileback_select_reviews')) {
  $sb_data    = oit_smileback_reviews();
  $sb_score   = $sb_data['score'];
  $sb_count   = $sb_data['count'];
  $sb_reviews = oit_smileback_select_reviews([
    'max'             => $max_reviews,
    'order'           => $order,
    'require_company' => $require_company,
    'min_length'      => $min_length,
  ]);
}
// Use the carousel only when SmileBack actually returned reviews; otherwise
// fall back to the manual quote so the block never renders empty.
$use_carousel = ($source === 'smileback' && !empty($sb_reviews));

$wrapper_attributes = get_block_wrapper_attributes([
  'class' => 'oit-testimonial relative isolate overflow-clip bg-black text-white py-16 px-6 lg:py-20 lg:px-20',
]);

$circuit_url        = get_stylesheet_directory_uri() . '/assets/img/circuit.svg';
$circuit_lottie_url = get_stylesheet_directory_uri() . '/assets/lottie/circuit.json';
$quote_url          = get_stylesheet_directory_uri() . '/assets/img/quote.svg';
?>

<section <?php echo $wrapper_attributes; ?>>

  <?php if ($show_circuit): ?>
  <div
    class="oit-testimonial__circuit oit-testimonial__circuit--left hidden md:block absolute left-0 top-4 w-[491px] max-w-[40vw] aspect-[610/518] opacity-60 pointer-events-none select-none -scale-x-100 z-0"
    data-lottie-url="<?php echo esc_url($circuit_lottie_url); ?>"
    aria-hidden="true">
    <img
      src="<?php echo esc_url($circuit_url); ?>"
      alt=""
      class="block w-full h-full object-contain"
      loading="lazy" />
  </div>
  <div
    class="oit-testimonial__circuit oit-testimonial__circuit--right hidden md:block absolute right-0 top-4 w-[491px] max-w-[40vw] aspect-[610/518] opacity-60 pointer-events-none select-none -scale-y-100 z-0"
    data-lottie-url="<?php echo esc_url($circuit_lottie_url); ?>"
    aria-hidden="true">
    <img
      src="<?php echo esc_url($circuit_url); ?>"
      alt=""
      class="block w-full h-full object-contain"
      loading="lazy" />
  </div>
  <?php endif; ?>

  <div class="oit-testimonial__inner relative z-10 max-w-[1280px] mx-auto pt-10">

    <?php if ($use_carousel && $show_score && $sb_score !== null): ?>
    <p class="oit-testimonial__score text-center font-dm font-medium text-body-sm leading-[1.5] text-white/80 m-0 mb-6">
      <span class="font-grotesk font-bold text-white"><?php echo esc_html(rtrim(rtrim(number_format((float) $sb_score, 1), '0'), '.')); ?>%</span>
      customer satisfaction &middot; <?php echo esc_html(number_format_i18n((int) $sb_count)); ?> reviews
    </p>
    <?php endif; ?>

    <article class="oit-testimonial__card relative bg-white border-2 border-black rounded-3xl px-6 pt-16 pb-10 lg:px-20 lg:pt-20 lg:pb-12 flex flex-col items-center gap-6 text-center">

      <div
        class="oit-testimonial__quote-icon absolute left-1/2 -translate-x-1/2 -top-8 w-16 h-16 rounded-full bg-brand-red flex items-center justify-center select-none"
        aria-hidden="true">
        <img
          class="w-7 h-auto"
          src="<?php echo esc_url($quote_url); ?>"
          alt=""
          width="28"
          height="22" />
      </div>

      <?php if ($use_carousel): ?>

      <div
        class="oit-testimonial__carousel w-full flex flex-col items-center gap-6"
        data-oit-testimonial-carousel
        data-autoplay="<?php echo esc_attr((string) $autoplay_ms); ?>"
        role="group"
        aria-roledescription="carousel"
        aria-label="Customer reviews">

        <div class="oit-testimonial__slides grid w-full">
          <?php foreach ($sb_reviews as $i => $r): ?>
          <div
            class="oit-testimonial__slide flex flex-col items-center gap-6"
            data-index="<?php echo (int) $i; ?>"<?php echo $i === 0 ? ' data-active="1"' : ' aria-hidden="true"'; ?>
            role="group"
            aria-roledescription="slide"
            aria-label="<?php echo esc_attr(sprintf('Review %d of %d', $i + 1, count($sb_reviews))); ?>">
            <div class="oit-testimonial__quote font-grotesk font-bold text-body-md leading-[1.4] text-black max-w-[900px]">
              &ldquo;<?php echo esc_html($r['quote']); ?>&rdquo;
            </div>
            <p class="oit-testimonial__attribution m-0 font-dm font-medium text-body-sm leading-[1.5] text-black">
              <?php echo esc_html($r['name']); ?><?php if (!empty($r['company'])): ?><span class="text-brand-red">, <?php echo esc_html($r['company']); ?></span><?php endif; ?>
            </p>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if (count($sb_reviews) > 1): ?>
        <div class="oit-testimonial__controls flex items-center justify-center gap-4">
          <button type="button" class="oit-testimonial__arrow" data-dir="-1" aria-label="Previous review">
            <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M15 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <div class="oit-testimonial__dots flex items-center gap-2">
            <?php foreach ($sb_reviews as $i => $r): ?>
            <button type="button" class="oit-testimonial__dot" data-index="<?php echo (int) $i; ?>"<?php echo $i === 0 ? ' data-active="1"' : ''; ?> aria-label="<?php echo esc_attr(sprintf('Go to review %d', $i + 1)); ?>"></button>
            <?php endforeach; ?>
          </div>
          <button type="button" class="oit-testimonial__arrow" data-dir="1" aria-label="Next review">
            <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
        <?php endif; ?>

      </div>

      <?php else: ?>

      <div
        data-proto-field="quote"
        class="oit-testimonial__quote font-grotesk font-bold text-body-md leading-[1.4] text-black max-w-[900px] [&_p]:m-0 [&_p+p]:mt-4">
        <?php echo wp_kses_post($quote); ?>
      </div>

      <p
        data-proto-field="attribution"
        class="oit-testimonial__attribution m-0 font-dm font-medium text-body-sm leading-[1.5] text-black">
        <?php echo esc_html($attribution); ?>
      </p>

      <?php endif; ?>

    </article>

  </div>
</section>
