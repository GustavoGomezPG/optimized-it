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

    </article>

  </div>
</section>
