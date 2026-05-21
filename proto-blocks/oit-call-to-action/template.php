<?php
/**
 * OIT Call to Action
 *
 * Headline + body on the left, outline CTA pinned to the bottom-right on
 * desktop (stacks below on mobile). Designed to sit immediately above
 * content sections like oit-featured-cards as a section intro, but works
 * on its own as a callout.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$headline     = $attributes['headline'] ?? 'Solutions That Secure Your Business';
$body         = $attributes['body']     ?? '<p>If your IT guy got stuck on a cruise ship with no wifi and something went down at your office, how would your business do? For the same cost as one IT guy, you can have an entire department at your disposal. Or, if you just need a few key services to fill in the gaps, we can do that too.</p>';
$cta          = $attributes['cta']      ?? ['url' => '#', 'text' => 'EXPLORE OUR SOLUTIONS'];
$show_cta     = $attributes['showCta']     ?? true;
$solid_button = !empty($attributes['solidButton']);

// If the body field is empty (whitespace-only after stripping tags),
// drop the headline->body gap so the headline doesn't hang in space
// above an empty div. The body element still renders so the
// Proto-Blocks editor has somewhere to mount its inline WYSIWYG UI.
$has_body  = trim(wp_strip_all_tags((string) $body)) !== '';
$intro_gap = $has_body ? 'gap-5' : 'gap-0';

// CTA color variants. Outline (default) paints black text inside a red
// border and fills on hover. Solid is filled brand red with white text;
// hover darkens to the cta-red-700 token.
$cta_color_classes = $solid_button
  ? 'bg-cta-red text-white hover:bg-cta-red-700 hover:border-cta-red-700'
  : 'text-black hover:bg-cta-red hover:text-white';

$wrapper_attributes = get_block_wrapper_attributes([
  'class' => 'oit-call-to-action',
]);
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="max-w-[1440px] mx-auto px-6 lg:px-20">

    <div class="oit-call-to-action__row flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between lg:gap-10">

      <div class="oit-call-to-action__intro flex flex-col <?php echo esc_attr($intro_gap); ?> max-w-[900px] min-w-0 lg:flex-1">
        <h2
          data-proto-field="headline"
          class="oit-call-to-action__headline m-0 font-grotesk font-bold text-[28px] leading-[1.2] text-black lg:text-h4">
          <?php echo esc_html($headline); ?>
        </h2>
        <div
          data-proto-field="body"
          class="oit-call-to-action__body font-dm font-medium text-body-sm leading-[1.5] text-black [&_p]:m-0 [&_p+p]:mt-4">
          <?php echo wp_kses_post($body); ?>
        </div>
      </div>

      <?php if ($show_cta): ?>
      <a
        href="<?php echo esc_url($cta['url'] ?? '#'); ?>"
        <?php echo !empty($cta['target']) ? 'target="' . esc_attr($cta['target']) . '"' : ''; ?>
        <?php echo !empty($cta['rel']) ? 'rel="' . esc_attr($cta['rel']) . '"' : ''; ?>
        class="oit-call-to-action__cta self-start shrink-0 inline-flex items-center gap-3 px-5 py-2.5 rounded-full border-2 border-cta-red no-underline font-grotesk font-medium text-body-sm leading-[1.3] uppercase whitespace-nowrap transition-colors lg:self-end <?php echo esc_attr($cta_color_classes); ?>">
        <span data-proto-field="cta"><?php echo esc_html($cta['text'] ?? 'EXPLORE OUR SOLUTIONS'); ?></span>
        <svg class="w-3 h-3 shrink-0" viewBox="0 0 14 16" fill="none" aria-hidden="true"><path d="M1 1L7 8L1 15M7 1L13 8L7 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
      <?php endif; ?>

    </div>

  </div>
</section>
