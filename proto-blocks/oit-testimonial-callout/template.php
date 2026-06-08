<?php
/**
 * OIT Testimonial Callout
 *
 * Two-column gradient card -- "how to get started" steps + CTA on the
 * left, framed testimonial quote on the right. Sits on the brand
 * red-glow shadow.
 *
 * Layout:
 *   - mobile: single column (steps stack above testimonial)
 *   - lg+:    two columns of equal weight, items-stretch so the
 *             testimonial frame matches the steps column height
 *
 * The 17-stop gradient is in style.css (too many comma-separated stops
 * to inline reliably as a Tailwind arbitrary value). Everything else is
 * inline Tailwind.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$headline    = !empty($attributes['headline'])    ? $attributes['headline']    : '<p>How to get started with Optimized IT<span class="oit-testimonial-callout__accent text-brand-red">:</span></p>';
$steps       = $attributes['steps']               ?? [];
$cta         = $attributes['cta']                 ?? ['url' => '#', 'text' => 'SCHEDULE A CONSULTATION'];
$quote       = !empty($attributes['quote'])       ? $attributes['quote']       : '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>';
$attribution = !empty($attributes['attribution']) ? $attributes['attribution'] : 'Name, Position, Company';

$show_steps        = $attributes['showSteps']        ?? true;
$show_testimonial  = $attributes['showTestimonial']  ?? true;

// Testimonial source: "manual" (the quote above) or "smileback" (live, rotating).
$source      = $attributes['source'] ?? 'manual';
$max_reviews = isset($attributes['maxReviews']) ? max(1, (int) $attributes['maxReviews']) : 12;
$autoplay_ms = (isset($attributes['autoplaySeconds']) ? max(0, (int) $attributes['autoplaySeconds']) : 6) * 1000;
$order           = $attributes['order'] ?? 'newest';
$require_company = !empty($attributes['requireCompany']);
$min_length      = isset($attributes['minLength']) ? max(0, (int) $attributes['minLength']) : 0;
$sb_reviews  = [];
if ($source === 'smileback' && function_exists('oit_smileback_select_reviews')) {
  $sb_reviews = oit_smileback_select_reviews([
    'max'             => $max_reviews,
    'order'           => $order,
    'require_company' => $require_company,
    'min_length'      => $min_length,
  ]);
}
$use_carousel = ($source === 'smileback' && !empty($sb_reviews));

if (empty($steps)) {
  $steps = [
    ['text' => 'Schedule a conversation'],
    ['text' => 'We\'ll gather some information'],
    ['text' => 'We\'ll meet with your team and go over a proposal'],
    ['text' => 'Leave the IT up to us'],
  ];
}

$is_preview = !isset($block) || $block === null;

$wrapper_args = ['class' => 'oit-testimonial-callout'];
if (!$is_preview) {
  $wrapper_args['data-proto-animate'] = 'manual';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

// Reused chevron from the rest of the OIT vocabulary (double arrow,
// currentColor fill so it picks up the surrounding text color).
$chevron = '<svg class="w-[13px] h-3 shrink-0" viewBox="0 0 13 12" fill="currentColor" aria-hidden="true"><path d="M0 1.41L1.36689 0L7.18345 6L1.36689 12L0 10.59L4.43997 6L0 1.41ZM5.81656 1.41L7.18345 0L13 6L7.18345 12L5.81656 10.59L10.2565 6L5.81656 1.41Z"/></svg>';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-testimonial-callout__inner max-w-[1440px] mx-auto px-6 lg:px-20 py-12 lg:py-16">

    <div class="oit-testimonial-callout__card oit-testimonial-callout__card--gradient relative rounded-3xl overflow-clip shadow-red-glow text-white">
      <div class="oit-testimonial-callout__grid grid grid-cols-1 gap-8 p-6 lg:grid-cols-2 lg:gap-12 lg:p-12 lg:items-stretch">

        <?php if ($show_steps): ?>
        <div class="oit-testimonial-callout__steps-col flex flex-col gap-6 lg:gap-7 lg:py-2">

          <div
            data-proto-field="headline"
            class="oit-testimonial-callout__headline m-0 font-grotesk font-bold text-[24px] leading-[1.3] text-white lg:text-[30px] lg:leading-[1.4] [&_p]:m-0 [&_p+p]:mt-1">
            <?php echo wp_kses_post($headline); ?>
          </div>

          <ol
            data-proto-repeater="steps"
            class="oit-testimonial-callout__steps flex flex-col gap-2.5 m-0 p-0 list-none">
            <?php foreach ($steps as $i => $step): ?>
            <li
              data-proto-repeater-item
              class="oit-testimonial-callout__step flex items-center gap-2.5 list-none">
              <span class="oit-testimonial-callout__step-number font-grotesk font-bold text-[18px] leading-[1.4] text-white shrink-0"><?php echo (int)($i + 1); ?>.</span>
              <span
                data-proto-field="text"
                class="oit-testimonial-callout__step-text font-grotesk font-medium text-body-sm leading-[1.4] text-white">
                <?php echo esc_html($step['text'] ?? ''); ?>
              </span>
            </li>
            <?php endforeach; ?>
          </ol>

          <a
            href="<?php echo esc_url($cta['url'] ?? '#'); ?>"
            <?php echo !empty($cta['target']) ? 'target="' . esc_attr($cta['target']) . '"' : ''; ?>
            <?php echo !empty($cta['rel']) ? 'rel="' . esc_attr($cta['rel']) . '"' : ''; ?>
            class="oit-testimonial-callout__cta self-start inline-flex items-center gap-3 px-5 py-2.5 rounded-full bg-cta-red hover:bg-cta-red-700 border border-brand-red text-white no-underline font-grotesk font-medium text-body-sm leading-[1.3] uppercase whitespace-nowrap transition-colors">
            <span data-proto-field="cta"><?php echo esc_html($cta['text'] ?? 'SCHEDULE A CONSULTATION'); ?></span>
            <?php echo $chevron; ?>
          </a>

        </div>
        <?php endif; ?>

        <?php if ($show_testimonial): ?>
        <figure class="oit-testimonial-callout__quote-frame border-2 border-white rounded-3xl p-6 lg:p-10 m-0 flex flex-col gap-4 lg:gap-6">

          <svg
            class="oit-testimonial-callout__quote-mark block self-start w-[30px] h-[24px] select-none"
            viewBox="0 0 30 24"
            fill="currentColor"
            aria-hidden="true">
            <path d="M27.4323 -4.43459e-05V4.89596H24.6243C22.4163 4.89596 21.3123 5.99996 21.3123 8.20796V10.512H22.5363C24.4563 10.512 26.0403 11.088 27.2883 12.24C28.5843 13.392 29.2323 14.856 29.2323 16.632C29.2323 18.552 28.5843 20.112 27.2883 21.312C26.0403 22.512 24.4563 23.112 22.5363 23.112C20.5683 23.112 18.9603 22.512 17.7123 21.312C16.4643 20.064 15.8403 18.408 15.8403 16.344V8.06395C15.8403 2.68796 18.5283 -4.43459e-05 23.9043 -4.43459e-05H27.4323ZM11.5923 -4.43459e-05V4.89596H8.78431C6.57631 4.89596 5.47231 5.99996 5.47231 8.20796V10.512H6.6963C8.6163 10.512 10.2003 11.088 11.4483 12.24C12.7443 13.392 13.3923 14.856 13.3923 16.632C13.3923 18.552 12.7443 20.112 11.4483 21.312C10.2003 22.512 8.6163 23.112 6.6963 23.112C4.7283 23.112 3.12031 22.512 1.87231 21.312C0.624305 20.064 0.000304788 18.408 0.000304788 16.344V8.06395C0.000304788 2.68796 2.6883 -4.43459e-05 8.06431 -4.43459e-05H11.5923Z"/>
          </svg>

          <?php if ($use_carousel): ?>

          <div
            class="oit-testimonial-callout__carousel flex flex-col gap-4 lg:gap-6"
            data-oit-callout-carousel
            data-autoplay="<?php echo esc_attr((string) $autoplay_ms); ?>"
            role="group"
            aria-roledescription="carousel"
            aria-label="Customer reviews">
            <div class="oit-testimonial-callout__slides grid">
              <?php foreach ($sb_reviews as $i => $r): ?>
              <div
                class="oit-testimonial-callout__slide flex flex-col gap-4 lg:gap-6"
                data-index="<?php echo (int) $i; ?>"<?php echo $i === 0 ? ' data-active="1"' : ' aria-hidden="true"'; ?>
                role="group"
                aria-roledescription="slide"
                aria-label="<?php echo esc_attr(sprintf('Review %d of %d', $i + 1, count($sb_reviews))); ?>">
                <blockquote class="oit-testimonial-callout__quote m-0 font-grotesk font-medium text-body-sm leading-[1.4] text-white">
                  &ldquo;<?php echo esc_html($r['quote']); ?>&rdquo;
                </blockquote>
                <figcaption class="oit-testimonial-callout__attribution m-0 font-dm font-medium text-body-sm leading-[1.5] text-white">
                  <?php echo esc_html($r['name']); ?><?php if (!empty($r['company'])): ?><span class="text-white/70">, <?php echo esc_html($r['company']); ?></span><?php endif; ?>
                </figcaption>
              </div>
              <?php endforeach; ?>
            </div>

            <?php if (count($sb_reviews) > 1): ?>
            <div class="oit-testimonial-callout__controls flex items-center gap-3">
              <button type="button" class="oit-testimonial-callout__arrow" data-dir="-1" aria-label="Previous review">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M15 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>
              <div class="oit-testimonial-callout__dots flex items-center gap-2">
                <?php foreach ($sb_reviews as $i => $r): ?>
                <button type="button" class="oit-testimonial-callout__dot" data-index="<?php echo (int) $i; ?>"<?php echo $i === 0 ? ' data-active="1"' : ''; ?> aria-label="<?php echo esc_attr(sprintf('Go to review %d', $i + 1)); ?>"></button>
                <?php endforeach; ?>
              </div>
              <button type="button" class="oit-testimonial-callout__arrow" data-dir="1" aria-label="Next review">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>
            </div>
            <?php endif; ?>
          </div>

          <?php else: ?>

          <blockquote
            data-proto-field="quote"
            class="oit-testimonial-callout__quote m-0 font-grotesk font-medium text-body-sm leading-[1.4] text-white [&_p]:m-0 [&_p+p]:mt-2">
            <?php echo wp_kses_post($quote); ?>
          </blockquote>

          <figcaption
            data-proto-field="attribution"
            class="oit-testimonial-callout__attribution m-0 font-dm font-medium text-body-sm leading-[1.5] text-white">
            <?php echo esc_html($attribution); ?>
          </figcaption>

          <?php endif; ?>

        </figure>
        <?php endif; ?>

      </div>
    </div>

  </div>
</section>
